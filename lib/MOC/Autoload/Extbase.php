<?php
/**
 * Looks for files in the PEAR Framework file/class style
 *
 * @example class MOC_Autoload_Pear => MOC/Autoload/Pear.php
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 13.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC_Autoload_Extbase implements MOC_Autoload_Interface {

    /**
     * Convert underscores to DIRECTORY_SEPARATOR
     *
     * @param string $className
     */
    public function getPath($className) {
        $str = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        return $str;
    }

	public function getBaseFolder() {
		return 'Classes';
	}
}
