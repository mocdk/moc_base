<?php
/**
 * MOC EID helper
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 13.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */ 
class MOC_EID {
	public static function initFeUser()	{
		self::connectDB();
		self::createTSFE();

		require_once(PATH_t3lib. 'class.t3lib_userauth.php');
		require_once(PATH_tslib. 'class.tslib_feuserauth.php');

		// Initialize FE user:
		$GLOBALS['TSFE']->initFEuser();

		// Return FE user object:
		return $GLOBALS['TSFE']->fe_user;
	}

	public static function connectDB() {
		$GLOBALS['TYPO3_DB']->connectDB();
	}

	public static function createTSFE() {
		global $TYPO3_CONF_VARS;
		$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
		if(!is_a($GLOBALS['TSFE'],$temp_TSFEclassName)) {
			// Include classes necessary for initializing frontend user:
			// We will use tslib_fe to do that:
			require_once(PATH_tslib.'class.tslib_fe.php');
			require_once(PATH_t3lib.'class.t3lib_cs.php');

			// Make new instance of TSFE object
			$GLOBALS['TSFE'] = new $temp_TSFEclassName($TYPO3_CONF_VARS,0,0,true);

		}
	}

	public static function includeTCA() {
		self::createTSFE();
		$GLOBALS['TSFE']->includeTCA();
	}

	public static function cObjOnTSFE() {
		self::initFeUser();
		self::createTSFE();
        
		require_once(PATH_t3lib.'class.t3lib_page.php');
        
		$GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$GLOBALS['TSFE']->initTemplate();
		$GLOBALS['TSFE']->determineId();
		$GLOBALS['TSFE']->getConfigArray();
		require_once(PATH_tslib.'class.tslib_content.php');
		$GLOBALS['TSFE']->newCObj();
	}
    
    /**
     * Get an instance of tslib_pibase
     * 
     * @param boolean $withCObject Should an cObj be attached to the tslib_pibase object
     * @return tslib_pibase
     */ 
    public static function getInstanceOfPiBase($withCObject = true) {
        if (!class_exists('tslib_pibase', false)) {
            require_once PATH_tslib . 'class.tslib_pibase.php';
        }
        
        static $PiBase;
        
        if (empty($PiBase)) {
            $PiBase = new tslib_pibase();
        }
                    
        if ($withCObject && empty($PiBase->cObj)) {
            MOC_EID::cObjOnTSFE();
            $PiBase->cObj = $GLOBALS['TSFE']->cObj;
        }
        
        return $PiBase;
    }
}