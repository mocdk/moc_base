<?php
/**
 * MOC Misc class
 * 
 * Junk yard for stuff that doen't belong anywhere
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 26.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */ 
class MOC_Misc {
	
	/**
	 * Disable HTTP Cache by sending the required headers
 	 *
  	 */
	public static function disableHTTPCache() {
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	}
	
    /**
     * Strip all potential dangerous paths from a string
     * 
     * We don't want to publish paths on our server to strangers 
     * 
     * PATH_site => $site
     * PATH_t3lib => $t3lib
     * PATH_typo3 => $typo3
     * 
     * @param string $str
     * @return string
     */ 
    public static function stripPaths($str) {
        $str = str_replace(realpath(PATH_site), '$site', $str);
        $str = str_replace(realpath(PATH_t3lib), '$t3lib', $str);
        $str = str_replace(realpath(PATH_typo3), '$typo3', $str); 
        $str = str_replace(realpath('/usr/local/src/typo3/'), '$shared', $str);
        
        return $str;
    }
    
    /**
     * Check if a string can be evaluated to boolean 
     * 
     * @param mixed $str
     * @param array $additionalTrueValues List of additional values that should evaluate to true
     * @param boolean $default Default boolean return value
     * @return boolean TRUE if the $str exists in $trueList
     */ 
    public static function evaluateBoolean($str, $additionalTrueValues = array(), $default = false) {
        $trueList = array(true, 1, '1', 'y', 'yes', 'true', 'ja', 'on');
        
        // Merge additional true values if needed
        if (is_array($additionalTrueValues) && !empty($additionalTrueValues)) {
            $trueList = array_merge($trueList, $additionalTrueValues);
        }
    
        // Check if $str can be evaluated to true
        if (false !== array_search($str, $trueList, true)) {
            return true;
        }
        
        // Or return default value
        return $default;
    }
}