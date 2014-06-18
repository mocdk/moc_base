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
		$temp_TSFEclassName = 'TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController';
		if(!is_a($GLOBALS['TSFE'],$temp_TSFEclassName)) {
			// Make new instance of TSFE object
			$GLOBALS['TSFE'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($temp_TSFEclassName, $TYPO3_CONF_VARS, 0, 0, TRUE);
		}
	}

	public static function includeTCA() {
		self::createTSFE();
		$GLOBALS['TSFE']->includeTCA();
	}

	public static function cObjOnTSFE() {
		self::initFeUser();
		self::createTSFE();

		$GLOBALS['TSFE']->sys_page = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('\TYPO3\CMS\Frontend\Page\PageRepository');
		$GLOBALS['TSFE']->initTemplate();
		$GLOBALS['TSFE']->determineId();
		$GLOBALS['TSFE']->getConfigArray();
		$GLOBALS['TSFE']->newCObj();
	}

	/**
	 * Get an instance of tslib_pibase
	 * 
	 * @param boolean $withCObject Should an cObj be attached to the \TYPO3\CMS\Frontend\Plugin\AbstractPlugin object
	 * @return \TYPO3\CMS\Frontend\Plugin\AbstractPlugin
	 */ 
	public static function getInstanceOfPiBase($withCObject = TRUE) {
		static $PiBase;

		if (empty($PiBase)) {
			$PiBase = new \TYPO3\CMS\Frontend\Plugin\AbstractPlugin();
		}

		if ($withCObject && empty($PiBase->cObj)) {
			MOC_EID::cObjOnTSFE();
			$PiBase->cObj = $GLOBALS['TSFE']->cObj;
		}

		return $PiBase;
	}
}