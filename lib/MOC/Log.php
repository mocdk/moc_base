<?php
/**
 * MOC Log class
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC_Log {
    const EMERG = LOG_EMERG;

    const ALERT = LOG_ALERT;

    const CRITICAL = LOG_CRIT;

    const ERROR = LOG_ERR;

    const WARNING = LOG_WARNING;

    const NOTICE = LOG_NOTICE;

    const INFO = LOG_INFO;

    const DEBUG = LOG_DEBUG;

    /**
     * The log level
     * 
     * @var integer
     */
    protected static $logLevel;

    /**
     * The list of adapters registered
     * 
     * @var array
     */
    protected static $adapters = array();

    /**
     * Add an log adapter to the registry
     * 
     * @param object|string $name Either an object instance or a string class name
     * @param array $options Optional options to the adapter
     */
    public static function registerAdapter($name, $options = array()) {
		if (empty(self::$adapters)) {
			self::initialize();
		}
		
        // Resolve the adapter class
        $className = self::resolveAdapter($name);

		// Make sure we don't add duplicate adapters
		if (self::hasAdapter($className)) {
	    	throw new MOC_Log_Exception(sprintf('An adapter with the class "%s" has already been registered', $className));
		}
		
		// Create the adapter object
		$Adapter = new $className();
		
	    // Make sure object implements our interface
		if (!($Adapter instanceof MOC_Log_Adapter_Interface)) {
	    	throw new MOC_Log_Exception(sprintf('Object "%s" must implement MOC_Log_Adapter_Interface', get_class($Adapter)));
		}
		
		// Push options to the adapter
		$Adapter->setOptions($options);

		// Run initialize if it exists
		if (method_exists($Adapter, 'initialize')) {
			$Adapter->initialize();
		}
		
        // Add to adapter stack
        self::$adapters[$className] = $Adapter;
    }

	/**
	 * Check if an adapter has been registered
	 * 
	 * @param string|object $name
	 * @return boolean
	 */
	public static function hasAdapter($name) {
		$className = self::resolveAdapter($name);
		return array_key_exists($className, self::$adapters);
	}
	
	/**
	 * Get the registered instance of an adapter 
	 * 
	 * @param string|object $name
	 * @return MOC_Log_Adapter_Interface
	 */
	public static function getAdapter($name) {
		$className = self::resolveAdapter($name);
		if (!self::hasAdapter($className)) {
			throw new MOC_Log_Exception(sprintf('No registered adapter with class name: %s', self::resolveAdapter($className)));
		}
		return self::$adapters[$className];
	}
	
    /**
     * Remove a log adapter from the registry
     * 
     * @param object|string Either an object instance or a string class name
     */
    public static function unregisterAdapter($name) {
        // Resolve the adapter class
        $className = self::resolveAdapter($name);

        // Check if the adapter is registered
        if (!array_key_exists($className, self::$adapters)) {
            return false;
        }

        // Remove the adapter
        self::$adapters[$className] = null;
        unset(self::$adapters[$className]);

        return true;
    }

    /**
     * Add a new log event
     * 
     * @param integer $severity LOG_* or MOC_LOG constants
     * @param string $message
     * @param string $ext_key
     * @param mixed $additional_info String or variable that can be serialized
     */
    public static function add($severity, $message, $ext_key = null, $additional_info = null) {
        // Don't do anything if we arent in the right log level
        if (self::getLogLevel() < $severity) {
            return false;
        }

        // Validate log level
        self::validateLogLevel($severity);

        // Push to the adapters
        foreach (self::$adapters as $Adapter) {
            $Adapter->add($severity, $message, $ext_key, $additional_info);
        }
    }

 	/**
     * Add an exception to log table
     * 
     * @param integer $severity LOG_* or MOC_LOG constants
     * @param Exception $Exception
     * @param string $ext_key
     */
    public static function addException($severity, exception $Exception, $ext_key = null) {
        return self::add($severity, $Exception->getMessage(), $ext_key, $Exception->getTraceAsString());
    }

    /**
     * Change the log level 
     * 
     * @param integer $level LOG_* or MOC_LOG constants
     */
    public static function setLogLevel($level) {
	    self::validateLogLevel($level);
        self::$logLevel = $level;
    }

    /**
     * Get the current log level
     * 
     * If no current log level is defined
     * Try to get it from console if available
     * 
     * @return integer
     */
    public static function getLogLevel() {
        return self::$logLevel;
    }

    /**
     * Validate if a log level is valid
     * 
     * @param integer $level LOG_* or MOC_LOG constants
     */
    protected static function validateLogLevel($level) {
        // Validate severity
        if (!is_numeric($level) || ($level < self::EMERG || $level > self::DEBUG)) {
            throw new MOC_Log_Exception(sprintf('Invalid severity value (%d). Must be integer and be between LOG_EMERG (%d) and LOG_DEBUG (%d).', $level, self::EMERG, self::DEBUG));
        }
    }

    /**
     * Resolve a string or object instance to an adapter class name
     * 
     * @param object|string $name Either an object instance or a string class name
     * @return string Classname of the adapter
     */
    protected static function resolveAdapter($name, $resolveToObject = true) {
        // Check if property is a object and it's a valid one too
        if (is_object($name)) {
            $className = get_class($name);
        }
        // If no underscore is in the class name, try to prepend MOC_LOG_Adapter
        elseif (false === strstr($name, '_')) {
            $className = 'MOC_Log_Adapter_' . ucfirst($name);
        }
        // Let's asume the user knows what he/she is doing (Brave!)
        else {
            $className = $name;
        }

        // Validate data
        if (!class_exists($className)) {
            throw new MOC_Log_Exception(sprintf('Unable to find adapter class "%s"', $className));
        }

		return $className;
    }

	/**
	 * Initialize defaults 
	 * 
	 */
	protected static function initialize() {		
		// Log level
		if (is_null(self::$logLevel)) {
			$Opts = new Zend_Console_Getopt(array(
		 		'moc_log_level|log_level=s' => 'The log level',
			));
			
	        // Check if we can use getopt (console)
			$level = $Opts->getOption('moc_log_level');
		    // Do we have a getopt parameter "d"
			if (!empty($level)) {
			    $constant_name = sprintf('LOG_%s', strtoupper($level));

			    if (!defined($constant_name)) {
			        throw new MOC_Log_Exception(sprintf('Invalid log level constant "%s"', $constant_name));
			    }

			    self::setLogLevel(constant($constant_name));
			}

            // Always default to LOG_WARNING
            if (empty(self::$logLevel)) {
                self::setLogLevel(LOG_WARNING);
            }
        }
	}

    /**
     * This is a static class, your not allowed to make instances!
     * 
     */
    protected function __construct() {

    }
}