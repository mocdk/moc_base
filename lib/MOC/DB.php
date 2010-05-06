<?php
/**
 * Common direct db actions
 *
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 08.04.2010 
 */
class MOC_DB {
	
	/**
	  * Table field cache
	 * 
	 * @var array
	 */
	protected static $tableCache = array();
	
	/**
	 * Get a list of fields in a table
	 * 
	 * @return array
	 */
	public static function tableFields($table) {
		if (!array_key_exists($table, self::$tableCache)) {
			self::$tableCache[$table] = $GLOBALS['TYPO3_DB']->admin_get_fields($table);
		}
		return self::$tableCache[$table];
	}
	
	public static function saveFields($table, $data) {
		$fields = self::tableFields($table);
		return array_intersect_key($data, $fields);
	}
	
	public static function beginTransaction() {
		$GLOBALS['TYPO3_DB']->sql_query('BEGIN');
	}
	
	public static function rollbackTransaction() {
		$GLOBALS['TYPO3_DB']->sql_query('ROLLBACK');
	}
	
	public static function commitTransaction() {
		$GLOBALS['TYPO3_DB']->sql_query('COMMIT');
	}
	
	public static function error() {
		$error = $GLOBALS['TYPO3_DB']->sql_error();
		if (empty($error)) {
			return false;
		}
		return $error;
	}
	
	public static function insertId() {
		return $GLOBALS['TYPO3_DB']->sql_insert_id();
	}
	
	public static function affectedRows() {
		return $GLOBALS['TYPO3_DB']->sql_affected_rows();
	}
	
	public static function suppressHTMLErrors() {
		$GLOBALS['TYPO3_DB']->debugOutput = 0;
	}
}