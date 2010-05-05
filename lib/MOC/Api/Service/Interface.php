<?php
/**
 * MOC Api Service interface 
 * 
 * All service objects has to implement these methods.
 * 
 * It's recommended to extend MOC_Api_Abstract 
 * 
 * @author Christian Winther
 * @since 26.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
interface MOC_Api_Service_Interface {
    /**
     * Method invoked by MOC_Api_Service
     * 
     * If the method returns true, the MOC_Api_Service object calls
     * invoke on this object
     * 
     * @return boolean
     */
    public function provides($method);

    /**
     * Request to invoke an API method from the MOC_Api_Service  
     * 
     * @param string $method
     * @param array $data
     * @return string
     */
    public function invoke($method, $data);

}
