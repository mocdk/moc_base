<?php
/**
 * MOC XML Error class
 * 
 * A nice little wrapper to raise XML errors 
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 24.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */ 
class MOC_XML_Error extends MOC_XML_Node {
    /**
     * Raise an XML error 
     * 
     * @param string $message
     * @param string|integer $code
     * @param boolean $die If TRUE the script will exit else the MOC_XML_Node instance will be returned
     * @return null|MOC_XML_Node
     */ 
    public static function raiseError($message, $code = false, $die = true) {
        $Error = MOC_XML_Node::getInstance('error');
        if (is_numeric($code)) {
            $Error->addCData('code', $code);
        }    
        $Error->addCData('message', $message);
        if ($die) {
            die($Error->asXML());
        }
        return $Error;
    }
    
    /**
     * Convert an PHP exception to XML error 
     * 
     * @param Exception $E
     */ 
    public static function raiseException(Exception $E, $detailed = true) {
        $Xml = MOC_XML_Error::raiseError($E->getMessage(), $E->getCode(), false);
        if ($detailed) {
            $Exception = $Xml->addChild('exception');
            $Exception->addCData('class', get_class($E));
            $Exception->addCData('file', MOC_Misc::stripPaths($E->getFile()));
            $Exception->addCData('line', $E->getLine());
            $Exception->addCData('trace', MOC_Misc::stripPaths($E->getTraceAsString()));
        }
        
        die($Xml->asXML());
    }
    
    /**
     * Register the MOC_XML_Error class to be ExceptionHandler in the current request
     * 
     */ 
    public static function registerAsExceptionHandler() {
        set_exception_handler(array('MOC_XML_Error', 'raiseException'));
    }
}