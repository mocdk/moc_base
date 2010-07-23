<?php
/**
 * MOC Autoload class.
 *
 * Can scan any number of directories for files
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 09.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC_Autoload {

    /**
     * List of paths to scan when looking for class files
     *
     * @var array
     */
    private static $paths = array();

    /**
     * Add a path to the list of paths to scan
     *
     * Provides a delegate class that converts class name to file name
     *
     * @param string $path
     * @param MOC_Autoload_Interface $delegate
     */
    public static function addPath($path, MOC_Autoload_Interface $delegate) {
        // Check path exists
        if (!is_dir($path)) {
            throw new MOC_Autoload_Exception(sprintf('Path "%s" does not exists', $path));
        }

        // Get an absolute path
        $path = realpath($path);

        // Append to search paths
        self::$paths[$path] = $delegate;

        set_include_path(get_include_path() . PATH_SEPARATOR . $path);
    }

    /**
     * Easy wrapper for adding a plugin to the autoload path
     * 
     * @param string $extKey The extension key of the LOADED extension
     * @param null|MOC_Autoload_Interface The delegate that transform class names to paths
     */
    public function addPlugin($extKey, $delegate = null) {
        if (!t3lib_extMgm::isLoaded($extKey, false)) {
            throw new MOC_Autoload_Exception(sprintf('Extension "%s" is not loaded', $extKey));
        }
        
        if (empty($delegate) || !($delegate instanceof MOC_Autoload_Interface)) {
            $delegate = new MOC_Autoload_Pear();
        }

        $path = t3lib_extMgm::extPath($extKey, $delegate->getBaseFolder());
        if (!file_exists($path)) {
            throw new MOC_Autoload_Exception(sprintf('Extension "%s" does not have a "%s" directory (%s)', $extKey, $delegate->getBaseFolder(), $path));
        }

        self::addPath($path, $delegate);
    }

    /**
     * Get a list of all paths the autloader is scanning for classes
     * 
     * @return array
     */
    public static function getPaths() {
        return array_keys(self::$paths);
    }

    /**
     * Scan all search paths for the class name
     *
     * @param string $className
     */
    public static function includeClass($className) {
        // Check if class already exists
        if (class_exists($className, false)) {
            return true;
        }

        // Iterate our search paths
        foreach (self::$paths as $path => $delegate) {
            $filePath = $path . DIRECTORY_SEPARATOR . $delegate->getPath($className);
            if (is_file($filePath)) {
                require $filePath;
                return true;
            }
        }

        return false;
    }

    /**
     * We never want to create an instance of this class
     *
     */
    protected function __construct() {

    }
}
