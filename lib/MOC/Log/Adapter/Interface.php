<?php
/**
 * MOC Log Adepter Interface
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
interface MOC_Log_Adapter_Interface {
    /**
     * Add a new log event
     * 
     * @param integer $severity LOG_* or MOC_LOG constants
     * @param string $message
     * @param string $ext_key
     * @param mixed $additional_info String or variable that can be serialized
     */
    public function add($severity, $message, $ext_key = null, $additional_info = null);

    /**
     * Set adapter options
     * 
     * @param array $options
     */
    public function setOptions($options);

}
