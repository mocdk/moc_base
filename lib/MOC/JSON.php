<?php
/**
 * MOC JSON class  
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 08.01.2010
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC_JSON {
    /**
     * Raise an JSON error 
     * 
     * @param string $message
     * @param string|integer $code
     * @param boolean $die If TRUE the script will exit else the json code will be returned
     * @return null|string
     */
    public static function raiseError($message, $code = false, $die = true) {
        $json = json_encode(array('error' => compact('message', 'code')));
        if ($die) {
            die($json);
        }
        return $json;
    }

    /**
     * Convert an PHP exception to XML error 
     * 
     * @param Exception $E
     */
    public static function convertException(exception $E, $detailed = false) {
        $array = self::raiseError($E->getMessage(), $E->getCode(), false);
        if ($detailed) {
            $array = json_decode($array, true);
            $array['exception'] = array('class' => get_class($E), 'file' => MOC_Misc::stripPaths($E->getFile()), 'line' => $E->getLine(), 'trace' => MOC_MISC::stripPaths($E->getTraceAsString()));
            $array = json_encode($array);
        }

        die($array);
    }

    /**
     * Register the MOC_JSON class to be ExceptionHandler in the current request
     * 
     */
    public static function registerAsExceptionHandler() {
        set_exception_handler(array('MOC_JSON', 'convertException'));
    }
}
