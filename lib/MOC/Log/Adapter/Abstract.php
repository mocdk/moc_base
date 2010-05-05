<?php
/**
 * MOC Log Adepter Abstract
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
abstract class MOC_Log_Adapter_Abstract implements MOC_Log_Adapter_Interface {
    /**
     * Options array
     * 
     * @var array
     */
    protected $options = array();

    /**
     * Set adapter options
     * 
     * @param array $options
     */
    public function setOptions($options) {
        $this->options = $options;
    }
}
