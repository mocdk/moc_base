<?php
/**
 * MOC Uri class
 * 
 * Method related to working with URIs
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 26.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC_Uri {
    /**
     * Construct a valid URI 
     * 
     * @param array $url
     * @param string|array $parts
     * @param null|integer $flags
     * @return string
     */ 
    public static function build($url, $parts = null, $flags = null) {
        if (!function_exists('http_build_url')) {
            throw new MOC_Exception(sprintf('PHP extention "pecl_http" is not installed, cannot use "%s"', __METHOD__));
        }
        
        if (is_null($url)) {
            $url = self::getFull();
        }
        
        if (!empty($parts['query']) && is_array($parts['query'])) {
            $parts['query'] = http_build_query($parts['query']);
        }
        
        $uri = http_build_url($url, $parts, $flags);
        
        //var_dump(compact('uri', 'url','parts','flags'));
        return $uri;
    }
    
    /**
     * Get an aboslute URI to the current script
     * 
     * @param boolean $asArray
     * @return string|array
     */ 
    public static function getFull($asArray = false) {
        $uri = $_SERVER['SCRIPT_URI'] . '?' . $_SERVER['QUERY_STRING'];
        if ($asArray) {
            $uri = parse_url($uri);
        }
        return $uri;
    }
}