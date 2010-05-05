<?php
/**
 * MOC Registry class
 * 
 * A global static registry for objects and values 
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 28.01.2010
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC_Registry {
    /**
     * Items in the registry
     * 
     * @var array
     */
    protected static $items = array();

    /**
     * Add a key / value pair to the registry
     * 
     * @param string $name
     * @param mixed $value
     */
    public static function set($name, $value) {
        self::$items[$name] = $value;
    }

    /**
     * Get a value by its name
     * 
     * @param string $name
     * @return mixed
     */
    public static function get($name) {
        if (!array_key_exists($name, self::$items)) {
            return null;
        }
        return self::$items[$name];
    }
}