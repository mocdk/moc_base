<?php
/**
 * MOC Configuration Ini Class
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 13.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC_Configuration_Ini {
    public static function read($file, $section = null, $options = array()) {
        $Zend_Config = new Zend_Config_Ini($file, $section, $options);
        $MOC_Config = new MOC_Configuration($Zend_Config->toArray());

        return $MOC_Config;
    }

    public static function write(MOC_Configuration $Configuration, $filename) {
        $Zend_Config = new Zend_Config($Configuration->getAll());
        $Zend_Writer = new Zend_Config_Writer_Ini(array('config' => $Zend_Config, 'filename' => $filename));
        $Zend_Writer->write();
    }
}
