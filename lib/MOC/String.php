<?php
/**
 * String handling methods.
 *
 * Thanks CakePHP project
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 07.01.2010
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC_String {
    /**
     * Generate a random UUID
     *
     * @see http://www.ietf.org/rfc/rfc4122.txt
     * @return RFC 4122 UUID
     * @static
     */
    public static function uuid() {
        $node = MOC::env('SERVER_ADDR');
        $pid = null;

        if (strpos($node, ':') !== false) {
            if (substr_count($node, '::')) {
                $node = str_replace('::', str_repeat(':0000', 8 - substr_count($node, ':')) . ':', $node);
            }
            $node = explode(':', $node);
            $ipv6 = '';

            foreach ($node as $id) {
                $ipv6 .= str_pad(base_convert($id, 16, 2), 16, 0, STR_PAD_LEFT);
            }
            $node = base_convert($ipv6, 2, 10);

            if (strlen($node) < 38) {
                $node = null;
            } else {
                $node = crc32($node);
            }
        } elseif (empty($node)) {
            $host = MOC::env('HOSTNAME');

            if (empty($host)) {
                $host = MOC::env('HOST');
            }

            if (!empty($host)) {
                $ip = gethostbyname($host);

                if ($ip === $host) {
                    $node = crc32($host);
                } else {
                    $node = ip2long($ip);
                }
            }
        } elseif ($node !== '127.0.0.1') {
            $node = ip2long($node);
        } else {
            $node = null;
        }

        if (empty($node)) {
            $node = crc32(Configure::read('Security.salt'));
        }

        if (function_exists('zend_thread_id')) {
            $pid = zend_thread_id();
        } else {
            $pid = getmypid();
        }

        if (!$pid || $pid > 65535) {
            $pid = mt_rand(0, 0xfff) | 0x4000;
        }

        list($timeMid, $timeLow) = explode(' ', microtime());
        $uuid = sprintf("%08x-%04x-%04x-%02x%02x-%04x%08x", (int)$timeLow, (int)substr($timeMid, 2) & 0xffff, mt_rand(0, 0xfff) | 0x4000, mt_rand(0, 0x3f) | 0x80, mt_rand(0, 0xff), $pid, $node);

        return $uuid;
    }
    /**
     * Tokenizes a string using $separator, ignoring any instance of $separator that appears between $leftBound
     * and $rightBound
     *
     * @param string $data The data to tokenize
     * @param string $separator The token to split the data on
     * @return array
     * @access public
     * @static
     */
    public static function tokenize($data, $separator = ',', $leftBound = '(', $rightBound = ')') {
        if (empty($data) || is_array($data)) {
            return $data;
        }

        $depth = 0;
        $offset = 0;
        $buffer = '';
        $results = array();
        $length = strlen($data);
        $open = false;

        while ($offset <= $length) {
            $tmpOffset = -1;
            $offsets = array(strpos($data, $separator, $offset), strpos($data, $leftBound, $offset), strpos($data, $rightBound, $offset));
            for ($i = 0; $i < 3; $i++) {
                if ($offsets[$i] !== false && ($offsets[$i] < $tmpOffset || $tmpOffset == -1)) {
                    $tmpOffset = $offsets[$i];
                }
            }
            if ($tmpOffset !== -1) {
                $buffer .= substr($data, $offset, ($tmpOffset - $offset));
                if ($data{$tmpOffset} == $separator && $depth == 0) {
                    $results[] = $buffer;
                    $buffer = '';
                } else {
                    $buffer .= $data{$tmpOffset};
                }
                if ($leftBound != $rightBound) {
                    if ($data{$tmpOffset} == $leftBound) {
                        $depth++;
                    }
                    if ($data{$tmpOffset} == $rightBound) {
                        $depth--;
                    }
                } else {
                    if ($data{$tmpOffset} == $leftBound) {
                        if (!$open) {
                            $depth++;
                            $open = true;
                        } else {
                            $depth--;
                            $open = false;
                        }
                    }
                }
                $offset = ++$tmpOffset;
            } else {
                $results[] = $buffer . substr($data, $offset);
                $offset = $length + 1;
            }
        }
        if (empty($results) && !empty($buffer)) {
            $results[] = $buffer;
        }

        if (!empty($results)) {
            $data = array_map('trim', $results);
        } else {
            $data = array();
        }
        return $data;
    }
    /**
     * Replaces variable placeholders inside a $str with any given $data. Each key in the $data array corresponds to a variable
     * placeholder name in $str. Example:
     *
     * Sample: String::insert('My name is :name and I am :age years old.', array('name' => 'Bob', '65'));
     * Returns: My name is Bob and I am 65 years old.
     *
     * Available $options are:
     * 	before: The character or string in front of the name of the variable placeholder (Defaults to ':')
     * 	after: The character or string after the name of the variable placeholder (Defaults to null)
     * 	escape: The character or string used to escape the before character / string (Defaults to '\')
     * 	format: A regex to use for matching variable placeholders. Default is: '/(?<!\\)\:%s/' (Overwrites before, after, breaks escape / clean)
     * 	clean: A boolean or array with instructions for String::cleanInsert
     *
     * @param string $str A string containing variable placeholders
     * @param string $data A key => val array where each key stands for a placeholder variable name to be replaced with val
     * @param string $options An array of options, see description above
     * @return string
     * @access public
     * @static
     */
    public static function insert($str, $data, $options = array()) {
        $defaults = array('before' => ':', 'after' => null, 'escape' => '\\', 'format' => null, 'clean' => false);
        $options += $defaults;
        $format = $options['format'];

        if (!isset($format)) {
            $format = sprintf('/(?<!%s)%s%%s%s/', preg_quote($options['escape'], '/'), str_replace('%', '%%', preg_quote($options['before'], '/')), str_replace('%', '%%', preg_quote($options['after'], '/')));
        }
        if (!is_array($data)) {
            $data = array($data);
        }

        if (array_keys($data) === array_keys(array_values($data))) {
            $offset = 0;
            while (($pos = strpos($str, '?', $offset)) !== false) {
                $val = array_shift($data);
                $offset = $pos + strlen($val);
                $str = substr_replace($str, $val, $pos, 1);
            }
        } else {
            asort($data);

            $hashKeys = array_map('md5', array_keys($data));
            $tempData = array_combine(array_keys($data), array_values($hashKeys));
            foreach ($tempData as $key => $hashVal) {
                $key = sprintf($format, preg_quote($key, '/'));
                $str = preg_replace($key, $hashVal, $str);
            }
            $dataReplacements = array_combine($hashKeys, array_values($data));
            foreach ($dataReplacements as $tmpHash => $data) {
                $str = str_replace($tmpHash, $data, $str);
            }
        }

        if (!isset($options['format']) && isset($options['before'])) {
            $str = str_replace($options['escape'] . $options['before'], $options['before'], $str);
        }
        if (!$options['clean']) {
            return $str;
        }
        return string ::cleanInsert($str, $options);
    }
    /**
     * Cleans up a Set::insert formated string with given $options depending on the 'clean' key in $options. The default method used is
     * text but html is also available. The goal of this function is to replace all whitespace and uneeded markup around placeholders
     * that did not get replaced by Set::insert.
     *
     * @param string $str
     * @param string $options
     * @return string
     * @access public
     * @static
     */
    public static function cleanInsert($str, $options) {
        $clean = $options['clean'];
        if (!$clean) {
            return $str;
        }
        if ($clean === true) {
            $clean = array('method' => 'text');
        }
        if (!is_array($clean)) {
            $clean = array('method' => $options['clean']);
        }
        switch ($clean['method']) {
            case 'html':
                $clean = array_merge(array('word' => '[\w,.]+', 'andText' => true, 'replacement' => '', ), $clean);
                $kleenex = sprintf('/[\s]*[a-z]+=(")(%s%s%s[\s]*)+\\1/i', preg_quote($options['before'], '/'), $clean['word'], preg_quote($options['after'], '/'));
                $str = preg_replace($kleenex, $clean['replacement'], $str);
                if ($clean['andText']) {
                    $options['clean'] = array('method' => 'text');
                    $str = string ::cleanInsert($str, $options);
                }
                break;
            case 'text':
                $clean = array_merge(array('word' => '[\w,.]+', 'gap' => '[\s]*(?:(?:and|or)[\s]*)?', 'replacement' => '', ), $clean);

                $kleenex = sprintf('/(%s%s%s%s|%s%s%s%s)/', preg_quote($options['before'], '/'), $clean['word'], preg_quote($options['after'], '/'), $clean['gap'], $clean['gap'], preg_quote($options['before'], '/'), $clean['word'], preg_quote($options['after'], '/'));
                $str = preg_replace($kleenex, $clean['replacement'], $str);
                break;
        }
        return $str;
    }

    /**
     * Truncates text.
     *
     * Cuts a string to the length of $length and replaces the last characters
     * with the ending if the text is longer than length.
     *
     * @param string  $text String to truncate.
     * @param integer $length Length of returned string, including ellipsis.
     * @param mixed $ending If string, will be used as Ending and appended to the trimmed string. Can also be an associative array that can contain the last three params of this method.
     * @param boolean $exact If false, $text will not be cut mid-word
     * @param boolean $considerHtml If true, HTML tags would be handled correctly
     * @return string Trimmed string.
     */
    public static function truncate($text, $length = 100, $ending = '...', $exact = true, $considerHtml = false) {
        if (is_array($ending)) {
            extract($ending);
        }
        if ($considerHtml) {
            if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            $totalLength = mb_strlen($ending);
            $openTags = array();
            $truncate = '';
            preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
            foreach ($tags as $tag) {
                if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
                    if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
                        array_unshift($openTags, $tag[2]);
                    } else
                        if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
                            $pos = array_search($closeTag[1], $openTags);
                            if ($pos !== false) {
                                array_splice($openTags, $pos, 1);
                            }
                        }
                }
                $truncate .= $tag[1];

                $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
                if ($contentLength + $totalLength > $length) {
                    $left = $length - $totalLength;
                    $entitiesLength = 0;
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entitiesLength <= $left) {
                                $left--;
                                $entitiesLength += mb_strlen($entity[0]);
                            } else {
                                break;
                            }
                        }
                    }

                    $truncate .= mb_substr($tag[3], 0, $left + $entitiesLength);
                    break;
                } else {
                    $truncate .= $tag[3];
                    $totalLength += $contentLength;
                }
                if ($totalLength >= $length) {
                    break;
                }
            }

        } else {
            if (mb_strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = mb_substr($text, 0, $length - strlen($ending));
            }
        }
        if (!$exact) {
            $spacepos = mb_strrpos($truncate, ' ');
            if (isset($spacepos)) {
                if ($considerHtml) {
                    $bits = mb_substr($truncate, $spacepos);
                    preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
                    if (!empty($droppedTags)) {
                        foreach ($droppedTags as $closingTag) {
                            if (!in_array($closingTag[1], $openTags)) {
                                array_unshift($openTags, $closingTag[1]);
                            }
                        }
                    }
                }
                $truncate = mb_substr($truncate, 0, $spacepos);
            }
        }

        $truncate .= $ending;

        if ($considerHtml) {
            foreach ($openTags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }

        return $truncate;
    }

    /**
     * Highlights a given phrase in a text. You can specify any expression in highlighter that
     * may include the \1 expression to include the $phrase found.
     *
     * @param string $text Text to search the phrase in
     * @param string $phrase The phrase that will be searched
     * @param string $highlighter The piece of html with that the phrase will be highlighted
     * @param boolean $considerHtml If true, will ignore any HTML tags, ensuring that only the correct text is highlighted
     * @return string The highlighted text
     * @access public
     */
    public static function highlight($text, $phrase, $highlighter = '<span class="highlight">\1</span>', $considerHtml = false) {
        if (empty($phrase)) {
            return $text;
        }

        if (is_array($phrase)) {
            $replace = array();
            $with = array();

            foreach ($phrase as $key => $value) {
                $key = $value;
                $value = $highlighter;
                $key = '(' . $key . ')';
                if ($considerHtml) {
                    $key = '(?![^<]+>)' . $key . '(?![^<]+>)';
                }
                $replace[] = '|' . $key . '|iu';
                $with[] = empty($value) ? $highlighter : $value;
            }

            return preg_replace($replace, $with, $text);
        } else {
            $phrase = '(' . $phrase . ')';
            if ($considerHtml) {
                $phrase = '(?![^<]+>)' . $phrase . '(?![^<]+>)';
            }

            return preg_replace('|' . $phrase . '|iu', $highlighter, $text);
        }
    }
    
    /**
     * Check if a string is valid UTF-8 
     *
     * @param string $str
     * @return boolean
     */
    public static function isValidUTF8($str) { 
        $len = strlen($str); 
        for($i = 0; $i < $len; $i++){ 
            $c = ord($str[$i]); 
            if ($c > 128) { 
                if (($c > 247)) return false; 
                elseif ($c > 239) $bytes = 4; 
                elseif ($c > 223) $bytes = 3; 
                elseif ($c > 191) $bytes = 2; 
                else return false; 
                if (($i + $bytes) > $len) return false; 
                while ($bytes > 1) { 
                    $i++; 
                    $b = ord($str[$i]); 
                    if ($b < 128 || $b > 191) return false; 
                    $bytes--; 
                } 
            } 
        } 
        return true; 
    }
}
