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
	 * Escape a value for MySQL
	 *
	 * @param string $string
	 * @return string
	 */
	public static function escape($string) {
	    return mysql_real_escape_string($string);
	}
	
	/**
	 * Change TYPO3_DB out with MOC_DB_Logger
	 *
	 * It's interface should be identical, but it logs
	 * all SQL passing through
     *
	 */
	public static function enableLogging() {
		$GLOBALS['TYPO3_DB'] = new MOC_DB_Logger();
		$GLOBALS['TYPO3_DB']->connectDB();
	}

	/**
	 * A wrapper for building INSERT ... ON DUPLICATE KEY UPDATE sql
	 *
	 * @see \error
	 * @see http://dev.mysql.com/doc/refman/5.1/en/insert-on-duplicate.html
	 * @param string $table
	 * @param array $insert_data
	 * @param array|null $update_data
	 * @return boolean true If successfull else an error string
	 */
	public static function insertOrUpdate($table, $insert_data, $update_data = null) {
		// Insert is 100% default (we are just going to append some stuff to it later on)
		$insert_sql = $GLOBALS['TYPO3_DB']->INSERTquery($table, $insert_data);

		// Update is a bit different - we want normal UPDATE statement with a blank "condition"
		// If no update_data is provided, just use the insert_data instead
		$update_sql = $GLOBALS['TYPO3_DB']->UPDATEquery($table, '', $update_data ? $update_data : $insert_data);
		// The "UPDATE $table SET" part is not needed, so lets remove it
		$update_sql = str_replace(sprintf("UPDATE %s", $table), '', $update_sql);
		// Remove all trailing spaces
		$update_sql = trim($update_sql);
		// And then remove the first 3 chars (SET in UPDATE TABLE $table -> SET <-)
		$update_sql = substr($update_sql, 3);

		// Construct the full query
		$query = sprintf('%s ON DUPLICATE KEY UPDATE %s', $insert_sql, $update_sql);

		// And execute it
		$GLOBALS['TYPO3_DB']->sql_query($query);

		// Check for errors
		$error = MOC_DB::error();
		// Return true if no error happened
		return $error === false ? true : $error;
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
	 * Raise a MOC_DB_Exception if there was a error in the last query
	 *
	 */
	public static function raiseExceptionIfError() {
		if ($error = self::error()) {
			MOC_DB::rollbackTransaction();

			throw new MOC_DB_Exception('SQL Error: ' . $error);
		}
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
