<?php
/**
 * MOC Api Loader Registry class
 * 
 * Map between a function and its handler class
 * 
 * @author Christian Winther
 * @since 07.01.2010
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC_Api_Loader_Registry {
    /**
     * Map with function > class mappings
     * 
     * @var array
     */
    protected static $map = array();

    /**
     * Register a mapping between a function and a class
     * 
     * @param string|array $function
     * @param string $class
     */
    public static function register($function, $class = null) {
        if (is_array($function)) {
            foreach ($function as $k => $v) {
                self::register($k, $v);
            }
            return;
        }

        if (array_key_exists($function, self::$map)) {
            throw new MOC_Api_Loader_Exception(sprintf('Function "%s" has already been registered', $prefix));
        }

        if (empty($class)) {
            throw new MOC_Api_Loader_Exception('Missing class name in register');
        }

        self::$map[$function] = $class;
    }

    /**
     * Unregister a mapping between a function and a class
     * 
     * @param string $function
     */
    public static function unregister($function) {
        if (!array_key_exists($function, $class)) {
            throw new MOC_Api_Loader_Exception(sprintf('Unable to unregister function "%s". It has not been registred', $function));
        }
        unset(self::$map[$function]);
    }

    /**
     * Attempt to create a object instance of a class mapped by a function
     * 
     * @param string $function
     * @return object
     */
    public static function getClassInstance($function) {
        if (empty(self::$map)) {
            throw new MOC_Api_Loader_Exception('No function has been registered in the map');
        }

        if (empty($function) || !is_string($function)) {
            throw new MOC_Api_Loader_Exception('Missing or invalid function name. Must be a string');
        }

        if (!array_key_exists($function, self::$map)) {
            throw new MOC_Api_Loader_Exception(sprintf('There is no mapping for function "%s"', $function));
        }

        $className = self::$map[$function];
        if (!class_exists($className)) {
            throw new MOC_Api_Loader_Exception(sprintf('Mapped class "%s" from function "%s" does not exists', $className, $function));
        }

        $Object = new $className();
        return $Object;
    }
}
