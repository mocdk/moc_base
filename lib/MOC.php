<?php
/**
 * MOC base class 
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 26.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC {
    /**
     * Debug a variable 
     * 
     * Shows the file and line of the debug call
     * and the variable wrapped in pre tags
     * 
     * @param mixed $var
     * @param boolean $die Call die() ?
     */
    public static function debug($var, $die = false) {
        $trace = debug_backtrace();
        echo '<strong>' . MOC_Misc::stripPaths($trace[0]['file']) . '</strong>';
        echo ' (line <strong>' . $trace[0]['line'] . '</strong>)';

        echo '<pre>';
        var_dump($var);
        echo '</pre>';

        if ($die) {
            die();
        }
    }

    /**
     * Get path to typo3temp with an optional path suffixed
     * 
     * @param string|null $path
     * @return string
     */
    public static function t3temp($path = null) {
        return PATH_site . 'typo3temp' . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Get an envoriment variable
     * 
     * @param string $key
     * @return mixed
     */
    public static function env($key) {
        if ($key == 'HTTPS') {
            if (isset($_SERVER['HTTPS'])) {
                return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            }
            return (strpos(MOC::env('SCRIPT_URI'), 'https://') === 0);
        }

        if ($key == 'SCRIPT_NAME') {
            if (MOC::env('CGI_MODE') && isset($_ENV['SCRIPT_URL'])) {
                $key = 'SCRIPT_URL';
            }
        }

        $val = null;
        if (isset($_SERVER[$key])) {
            $val = $_SERVER[$key];
        } elseif (isset($_ENV[$key])) {
            $val = $_ENV[$key];
        } elseif (getMOC::env($key) !== false) {
            $val = getMOC::env($key);
        }

        if ($key === 'REMOTE_ADDR' && $val === MOC::env('SERVER_ADDR')) {
            $addr = MOC::env('HTTP_PC_REMOTE_ADDR');
            if ($addr !== null) {
                $val = $addr;
            }
        }

        if ($val !== null) {
            return $val;
        }

        switch ($key) {
            case 'SCRIPT_FILENAME':
                if (defined('SERVER_IIS') && SERVER_IIS === true) {
                    return str_replace('\\\\', '\\', MOC::env('PATH_TRANSLATED'));
                }
                break;
            case 'DOCUMENT_ROOT':
                $name = MOC::env('SCRIPT_NAME');
                $filename = MOC::env('SCRIPT_FILENAME');
                $offset = 0;
                if (!strpos($name, '.php')) {
                    $offset = 4;
                }
                return substr($filename, 0, strlen($filename) - (strlen($name) + $offset));
                break;
            case 'PHP_SELF':
                return str_replace(MOC::env('DOCUMENT_ROOT'), '', MOC::env('SCRIPT_FILENAME'));
                break;
            case 'CGI_MODE':
                return (PHP_SAPI === 'cgi');
                break;
            case 'HTTP_BASE':
                $host = MOC::env('HTTP_HOST');
                if (substr_count($host, '.') !== 1) {
                    return preg_replace('/^([^.])*/i', null, MOC::env('HTTP_HOST'));
                }
                return '.' . $host;
                break;
        }
        return null;
    }
}
