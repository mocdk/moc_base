<?php
/**
 * MOC Api Loader Prefix class
 * 
 * Try to find a map a function name to a class
 * 
 * The user provides a list of class prefixes to prefix the method name with
 * 
 * A function name need to follow one of two nameing conventions
 * 
 * a) PEAR style Class_Name
 * b) camcelCase 
 * 
 * @author Christian Winther
 * @since 07.01.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC_Api_Loader_Prefix {
    protected static $prefixes = array();

    /**
     * Register a class prefix for the mapping
     * 
     * @param string
     */
    public static function registerPrefix($prefix) {
        if (false !== array_search($prefix, self::$prefixes)) {
            throw new MOC_Api_Loader_Exception(sprintf('Prefix "%s" has already been registered', $prefix));
        }
        self::$prefixes[] = $prefix;
    }

    /**
     * Unregister a class prefix
     * 
     * @param string
     */
    public static function unregisterPrefix($prefix) {
        $key = array_search($prefix, self::$prefixes);
        if (false === $key) {
            throw new MOC_Api_Loader_Exception(sprintf('Unable to unregister prefix "%s". It has not been registred', $prefix));
        }
        unset(self::$prefixes[$key]);
    }

    /**
     * Attempt to create a object instance of a class mapped by a method
     * 
     * @param string $function
     * @return object
     */
    public static function getClassInstance($function) {
        if (empty(self::$prefixes)) {
            throw new MOC_Api_Loader_Exception('No class prefixes has been registered');
        }

        if (empty($function) || !is_string($function)) {
            throw new MOC_Api_Loader_Exception('Missing or invalid method name. Must be a string');
        }

        $methodAsClass = MOC_Inflector::underscore(MOC_Inflector::camelize($function));
        foreach (self::$prefixes as $prefix) {
            $className = $prefix . $methodAsClass;
            if (!class_exists($className)) {
                continue;
            }
            $Object = new $className;
            return $Object;
        }

        throw new MOC_Api_Loader_Exception(sprintf('Unable to find a class for method "%s" (%s)', $function, $methodAsClass));
    }
}
