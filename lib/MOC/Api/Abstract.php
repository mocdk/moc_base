<?php
/**
 * MOC Api Abstract class
 * 
 * All service objects should extend this class
 * 
 * @author Christian Winther
 * @since 26.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
abstract class MOC_Api_Abstract implements MOC_Api_Service_Interface {
    /**
     * MOC Configuration instance
     * 
     * @var MOC_Configuration
     */
    public $Configuration;

    /**
     * Prefix to strip from API method names
     * 
     * @var string
     */
    protected $apiPrefix;

    /**
     * Enforce check if a validation method exists
     * 
     * @var boolean
     */
    protected $enforceValidation = true;

    /**
     * PHP method used to invoke the method
     * 
     * @var string
     */
    protected $invokeMethod = 'call_user_func';

    /**
     * Default constructor
     * 
     * Initializes MOC_Configuration object
     */
    public function __construct() {
        $this->Configuration = new MOC_Configuration();

        // Execute bootstrap method
        $this->bootstrap();
    }

    /**
     * Method invoked by MOC_Api_Service
     * 
     * If the method returns true, the MOC_Api_Service object calls
     * invoke on this object
     * 
     * @return boolean
     */
    public function provides($method) {
        return method_exists($this, $this->getMethodName($method));
    }

    /**
     * Request to invoke an API method from the MOC_Api_Service 
     * 
     * 1) validate (Required: Validate parameters)
     * 2) initialize (Optional: intiailize objects and variables for invoke)
     * 3) invoke (Required: Of the actual method)
     * 4) after (Optional: Post process the data from invoke)
     * 
     * @param string $method
     * @param array $data
     * @return string
     */
    public function invoke($method, $data) {
        // Store parameters and method name in Configuration
        $this->Configuration->set(compact('method', 'data'));

        // Check if the class provides the method
        if (!$this->provides($method)) {
            throw new BadMethodCallException(sprintf('Method "%s" (%s) is not implemented by "%s"', $this->getMethodName($method), $method, get_class($this)));
        }

        // Check if can validate the method arguments
        if (!$this->runValidateMethodArguments($method, $data)) {
            throw new MOC_Api_Exception(sprintf('Parameter validation failed for method "%s" (%s)', $this->getMethodName($method, 'validate'), $method));
        }

        // Run global initialize
        $this->initialize();

        // Intialize method if needed
        $this->runInitializeMethod($method);

        // Invoke the actual message
        $return = $this->invokeMethod(array($this, $this->getMethodName($method)), $data);

        // Invoke the after method
        return $this->runAfterMethod($method, $return);
    }

    /**
     * Boostrap method
     * 
     * Called as the last thing in __construct
     */
    protected function bootstrap() {

    }

    /**
     * Initialize method
     * 
     * Called on every invoke method
     */
    protected function initialize() {

    }

    /**
     * Different ways to invoke the service objects on
     * 
     * Choose your flavor
     * 
     * @param mixed $callback Valid PHP callback
     * @param array $params
     * @return mixed
     */
    protected function invokeMethod($callback, $params = null) {
        switch ($this->invokeMethod) {
            case 'call_user_func':
                return call_user_func($callback, $params);
            case 'call_user_func_array':
                return call_user_func_array($callback, $params);
            default:
                throw new MOC_Api_Exception(sprintf('Invalid invoke method "%s"', $this->invokeMethod));
        }
    }

    /**
     * Executes a validation method for the given method
     * 
     * @param string $method
     * @param $data
     * @return boolean
     */
    protected function runValidateMethodArguments($method, $data) {
        $validateMethod = $this->getMethodName($method, 'validate');
        if (!method_exists($this, $validateMethod)) {
            // Don't warn if we dont enforce validation
            if (!$this->enforceValidation) {
                return true;
            }
            throw new MOC_Api_Exception(sprintf('Method "%s" for "%s" is not implemented by "%s"', $validateMethod, $method, get_class($this)));
        }

        return $this->invokeMethod(array($this, $validateMethod), $data);
    }

    /**
     * Run the corresponding intialize method for the current method call
     * 
     * This method wont complain if the method doesnt exists
     * 
     * @param string $method
     */
    protected function runInitializeMethod($method) {
        $method = $this->getMethodName($method, 'initialize');
        if (method_exists($this, $method)) {
            $this->invokeMethod(array($this, $method));
        }
    }

    /**
     * Run the corresponding after method for the current method call
     * 
     * This methond wont complain if the method doesnt exists
     * 
     * @param string $method
     * @param mixed $data
     * @return mixed
     */
    protected function runAfterMethod($method, $data) {
        $method = $this->getMethodName($method, 'after');
        if (method_exists($this, $method)) {
            $data = $this->invokeMethod(array($this, $method), $data);
        }
        return $data;
    }

    /**
     * Convert an API method to an internal method
     * 
     * - Removes PREFIX from name
     * - Replaces all dots with spaces
     * - UpperCamelCases all words
     * - Replaces all spaces with nothing
     * - Prefixes name with "execute""
     * 
     * @param string $name
     * @return string
     */
    protected function getMethodName($name, $prefix = 'execute') {
        if (!empty($this->apiPrefix)) {
            $name = str_replace($this->apiPrefix, '', $name);
        }
        $name = MOC_Inflector::camelize(str_replace('.', '_', $name));
        return $prefix . $name;
    }
}
