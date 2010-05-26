<?php
/**
 * Common direct db actions
 *
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 08.04.2010 
 */
class MOC_DB {
	
	/**
	  * List of all fields for a table
	 * 
	 * @var array
	 */
	protected static $tableFieldCache = array();
	
	/**
	 * List of table meta data
	 *
	 * @var array
	 */
	protected static $tableInformation = array();
	
	/**
	 * Get a list of fields in a table
	 * 
	 * @return array
	 */
	public static function tableFields($table) {
		if (!array_key_exists($table, self::$tableFieldCache)) {
			self::$tableFieldCache[$table] = $GLOBALS['TYPO3_DB']->admin_get_fields($table);
		}
		return self::$tableFieldCache[$table];
	}
	
	/**
	 * Remove keys from $data that is not present as a column in $table
	 *
	 * @param string $table
	 * @param array $data
	 * @return array 
	 */
	public static function saveFields($table, $data) {
		$fields = self::tableFields($table);
		return array_intersect_key($data, $fields);
	}
	
	/**
	 * Begin a database transaction
	 *
	 */
	public static function beginTransaction() {
		$GLOBALS['TYPO3_DB']->sql_query('BEGIN');
	}
	
	/**
	 * Rollback a database transaction
	 *
	 */ 
	public static function rollbackTransaction() {
		$GLOBALS['TYPO3_DB']->sql_query('ROLLBACK');
	}
	
	/**
	 * Commit a database transaction
	 *
	 */
	public static function commitTransaction() {
		$GLOBALS['TYPO3_DB']->sql_query('COMMIT');
	}
	
	/**
	 * Check a list of tables for transaction support
	 *
	 * The reason you would want to check them up front is that any Create, Update and Delete
	 * actions on non-innodb tables will commit any active transactions, and thus your
	 * concurrency would be lost
	 *
	 * Throws an MOC_DB_Exception if a table isnt transactional 
	 *
	 * @param array|string $names
	 */
	public static function checkTransactionSupport($names) {
	    if (!is_array($names)) {
	        $names = array($names);
	    }
	    
	    self::loadTableInformation();
	    
	    foreach ($names as $name) {
	        if (!array_key_exists($name, self::$tableInformation)) {
	            throw new MOC_DB_Exception(sprintf('Cant check table %s for transaction support, its not present in self::$tableInformation. (Maybe the table doesnt exist)', $name));
	        }
	        
	        if (self::$tableInformation[$name]['Engine'] !== 'InnoDB') {
	            throw new MOC_DB_Exception(sprintf('Table %s does not have transaction support, its not an InnoDB table. Table type %s does not support transactions', $name, self::$tableInformation[$name]['Engine']));
	        }
	    }
	}
	
	/**
	 * Load table meta data information
	 *
	 * @return array
	 */
	public static function loadTableInformation() {
	    if (empty(self::$tableInformation)) {
	        $resource = $GLOBALS['TYPO3_DB']->sql_query('SHOW TABLE STATUS');
            while ($record = mysql_fetch_array($resource)) {
                self::$tableInformation[$record['Name']] = $record;
            }    
	    }
	    return self::$tableInformation;
	}
	
	/**
	 * Check if the last query raised an SQL error
	 *
	 * @return string|boolean
	 */
	public static function error() {
		$error = $GLOBALS['TYPO3_DB']->sql_error();
		if (empty($error)) {
			return false;
		}
		return $error;
	}
	
	/**
	 * Get the uid for the last Insert operation
	 *
	 * @return null|integer
	 */
	public static function insertId() {
		return $GLOBALS['TYPO3_DB']->sql_insert_id();
	}
	
	/**
	 * Get information about how many affected rows the last query involed
	 *  
	 * @return integer
	 */
	public static function affectedRows() {
		return $GLOBALS['TYPO3_DB']->sql_affected_rows();
	}
	
	/**
	 * Suppress HTML errors in case of SQL errors
	 *
	 * Mostly useful in CLI scripts
	 * 
	 */
	public static function suppressHTMLErrors() {
		$GLOBALS['TYPO3_DB']->debugOutput = 0;
	}
}