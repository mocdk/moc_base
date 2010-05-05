<?php
MOC_Annotation::load();

/**
 * MOC Annotation class
 * 
 * Wrapper for the most commonly used Addendum features
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 * @since 06.01.2010
 */
class MOC_Annotation {
    /**
     * Get instance of ReflectionAnnotatedClass
     * 
     * @return ReflectionAnnotatedClass
     */
    public static function forClass($class) {
        $Reflection = new ReflectionAnnotatedClass($class);
        return $Reflection;
    }

    /**
     * Get instance of ReflectionAnnotatedMethod
     * 
     * @return ReflectionAnnotatedMethod
     */
    public static function forClassMethod($class, $method) {
        $Reflection = new ReflectionAnnotatedMethod($class, $method);
        return $Reflection;
    }

    /**
     * Get instance of ReflectionAnnotatedProperty
     * 
     * @return ReflectionAnnotatedProperty
     */
    public static function forClassProperty($class, $property) {
        $Reflection = new ReflectionAnnotatedProperty($class, $property);
        return $Reflection;
    }

    /**
     * Add a class to annotation ignore list
     * 
     * This is very usefull for annotation classes
     * as you can avoid circular references
     * 
     * @param string $name
     */
    public static function ignoreClass($name) {
        Addendum::ignore($name);
    }

    /**
     * Clear the annotation cache
     * 
     */
    public static function clearCache() {
        return AnnotationsBuilder::clearCache();
    }

    /**
     * Load addendum classes
     * 
     */
    public static function load() {
        require_once MOC_BASE_LIB_DIR . DIRECTORY_SEPARATOR . 'addendum' . DIRECTORY_SEPARATOR . 'annotations.php';
    }
}
