<?php
/**
 * Data Sanitization class from CakePHP project
 *
 * Removal of alpahnumeric characters, HTML-friendly strings and all of the above on arrays.
 *
 * @author Christian Winther <cwin@mocsystems.com>
 * @author CakePHP Framework <contact@cakephp.org>
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 * @since 02.12.2009
 */
class MOC_Sanitize {
    /**
     * Removes any non-alphanumeric characters.
     *
     * @param string $string String to sanitize
     * @return string Sanitized string
     * @access public
     * @static
     */
	public static function paranoid($string, $allowed = array()) {
		$allow = null;
		if (!empty($allowed)) {
			foreach ($allowed as $value) {
				$allow .= "\\$value";
			}
		}

		if (is_array($string)) {
			$cleaned = array();
			foreach ($string as $key => $clean) {
				$cleaned[$key] = preg_replace("/[^{$allow}a-zA-Z0-9]/", '', $clean);
			}
		} else {
			$cleaned = preg_replace("/[^{$allow}a-zA-Z0-9]/", '', $string);
		}
		return $cleaned;
	}
    
    /**
     * Returns given string safe for display as HTML. Renders entities.
     *
     * @param string $string String from where to strip tags
     * @param boolean $remove If true, the string is stripped of all HTML tags
     * @return string Sanitized string
     * @access public
     * @static
     */
	public static function html($string, $remove = false) {
		if ($remove) {
			$string = strip_tags($string);
		} else {
			$patterns = array("/\&/", "/%/", "/</", "/>/", '/"/', "/'/", "/\(/", "/\)/", "/\+/", "/-/");
			$replacements = array("&amp;", "&#37;", "&lt;", "&gt;", "&quot;", "&#39;", "&#40;", "&#41;", "&#43;", "&#45;");
			$string = preg_replace($patterns, $replacements, $string);
		}
		return $string;
	}
    
    /**
     * Strips extra whitespace from output
     *
     * @param string $str String to sanitize
     * @return string whitespace sanitized string
     * @access public
     * @static
     */
	public static function stripWhitespace($str) {
		$r = preg_replace('/[\n\r\t]+/', '', $str);
		return preg_replace('/\s{2,}/', ' ', $r);
	}
    
    /**
     * Strips image tags from output
     *
     * @param string $str String to sanitize
     * @return string Sting with images stripped.
     * @access public
     * @static
     */
	public static function stripImages($str) {
		$str = preg_replace('/(<a[^>]*>)(<img[^>]+alt=")([^"]*)("[^>]*>)(<\/a>)/i', '$1$3$5<br />', $str);
		$str = preg_replace('/(<img[^>]+alt=")([^"]*)("[^>]*>)/i', '$2<br />', $str);
		$str = preg_replace('/<img[^>]*>/i', '', $str);
		return $str;
	}
    
    /**
     * Strips scripts and stylesheets from output
     *
     * @param string $str String to sanitize
     * @return string String with <script>, <style>, <link> elements removed.
     * @access public
     * @static
     */
	public static function stripScripts($str) {
		return preg_replace('/(<link[^>]+rel="[^"]*stylesheet"[^>]*>|<img[^>]*>|style="[^"]*")|<script[^>]*>.*?<\/script>|<style[^>]*>.*?<\/style>|<!--.*?-->/i', '', $str);
	}
    
    /**
     * Strips extra whitespace, images, scripts and stylesheets from output
     *
     * @param string $str String to sanitize
     * @return string sanitized string
     * @access public
     */
	public static function stripAll($str) {
		$str = self::stripWhitespace($str);
		$str = self::stripImages($str);
		$str = self::stripScripts($str);
		return $str;
	}
    
    /**
     * Strips the specified tags from output. First parameter is string from
     * where to remove tags. All subsequent parameters are tags.
     *
     * @param string $str String to sanitize
     * @param string $tag Tag to remove (add more parameters as needed)
     * @return string sanitized String
     * @access public
     * @static
     */
	public static function stripTags() {
		$params = params(func_get_args());
		$str = $params[0];

		for ($i = 1; $i < count($params); $i++) {
			$str = preg_replace('/<' . $params[$i] . '\b[^>]*>/i', '', $str);
			$str = preg_replace('/<\/' . $params[$i] . '[^>]*>/i', '', $str);
		}
		return $str;
	}
    
    /**
     * Sanitizes given array or value for safe input. Use the options to specify
     * what filters should be applied (with a boolean value). 
     * Valid filters: odd_spaces, encode, dollar, carriage, unicode, escape, backslash.
     *
     * @param mixed $data Data to sanitize
     * @param mixed $options Set of options
     * @return mixed Sanitized data
     * @access public
     * @static
     */
	public static function clean($data, $options = array()) {
		if (empty($data)) {
			return $data;
		}

        if (!is_array($options)) {
			$options = array();
		}

		$options = array_merge(array(
			'odd_spaces' => true,
			'encode' => true,
			'dollar' => true,
			'carriage' => true,
			'unicode' => true,
			'escape' => true,
			'backslash' => true
		), $options);

		if (is_array($data)) {
			foreach ($data as $key => $val) {
				$data[$key] = self::clean($val, $options);
			}
			return $data;
		} else {
			if ($options['odd_spaces']) {
				$data = str_replace(chr(0xCA), '', str_replace(' ', ' ', $data));
			}
			if ($options['encode']) {
				$data = self::html($data);
			}
			if ($options['dollar']) {
				$data = str_replace("\\\$", "$", $data);
			}
			if ($options['carriage']) {
				$data = str_replace("\r", "", $data);
			}

			$data = str_replace("'", "'", str_replace("!", "!", $data));

			if ($options['unicode']) {
				$data = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $data);
			}
			if ($options['backslash']) {
				$data = preg_replace("/\\\(?!&amp;#|\?#)/", "\\", $data);
			}
			return $data;
		}
	}
}