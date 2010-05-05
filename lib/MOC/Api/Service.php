<?php
/**
 * MOC Api Service class
 * 
 * @author Christian Winther
 * @since 26.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC_Api_Service {
    /**
     * Static list of named instances of MOC_Api_Service
     * 
     * @var array
     */
    protected static $instances = array();

    /**
     * List of service objects for the current instance of service object
     * 
     * @var array
     */
    protected $services = array();

    /**
     * Register a MOC_Api_Service_Base object
     * 
     * @param string $name Must be unique!
     * @param  MOC_Api_Service_Interface $Object
     */
    public function register($name, $Object) {
        if (!is_object($Object)) {
            $Object = new $Object();
        }

        if (!($Object instanceof MOC_Api_Service_Interface)) {
            throw new MOC_Api_Service_Exception(sprintf('Object %s does not implement MOC_Api_Service_Interface', get_class($Object)));
        }

        if (array_key_exists($name, $this->services)) {
            throw new MOC_Api_Exception(sprintf('The service "%s" has already been registered', $name));
        }

        $this->services[$name] = $Object;
    }

    /**
     * Unregister a MOC_Api_Service_Base object from the service object
     * 
     * @param string $name
     */
    public function unregister($name) {
        if (!array_key_exists($name, $this->services)) {
            throw new MOC_Api_Exception(sprintf('The service "%s" has not been registered', $name));
        }
        unset($this->services[$name]);
    }

    /**
     * Find a MOC_Api_Abstract object that provides implementation for the given method
     * 
     * @param string $method
     * @param mixed $params
     * @return mixed
     */
    public function invoke($method = null, $params = null) {
        if (empty($method) || !is_string($method)) {
            throw new MOC_Api_Service_Exception('Missing or invalid "method" parameter');
        }

        foreach ($this->services as $name => $Object) {
            if ($Object->provides($method)) {
                $return = $Object->invoke($method, $params);

                // Check if the return value is empty
                if (is_null($return)) {
                    throw new MOC_Api_Exception(sprintf('API method "%s" from object "%s" did not return any data', $method, get_class($Object)));
                }
                return $return;
            }
        }

        throw new MOC_Api_Service_Exception(sprintf('No API services provide the method "%s"', $method));
    }

    /**
     * Protected constructor, we don't want people to create instances without our knowledge
     * 
     */
    protected function __construct() {

    }

    /**
     * Clone method
     * 
     * We don't want people to create clones
     */
    public function __clone() {
        throw new MOC_Api_Exception(sprintf('Your not allowed to clone this object (%s))', get_class($this)));
    }

    /**
     * Get an instance of a MOC_Api_Service object 
     * 
     * @param string $name
     * @return MOC_Api_Service
     */
    public static function getInstance($name = 'default') {
        if (!array_key_exists($name, self::$instances)) {
            self::$instances[$name] = new MOC_Api_Service();
        }
        return self::$instances[$name];
    }
}
