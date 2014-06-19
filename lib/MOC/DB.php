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
	 * @param array $tableInformation
	 */
	public static function setTableInformation($tableInformation) {
		self::$tableInformation = $tableInformation;
	}

	/**
	 * @return array
	 */
	public static function getTableInformation() {
		return self::$tableInformation;
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
	 * @param string $unique_field
	 * @return boolean true If successful else an error string
	 */
	public static function insertOrUpdate($table, $insert_data, $update_data = NULL, $unique_field = 'uid') {
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
		//6/3-2011: Mod by JE to fix problem in MySQL < 5.1.12 : the last_insert id is not updated when using this ON DUPLICATE KEY thingie.
		//To fix this, we use the uid=LAST_INSERT_ID(uid) as described in http://dev.mysql.com/doc/refman/5.1/en/insert-on-duplicate.html
		//Unfortunately, this can only be done on columns with a uid field (_mm tables does not have on), and hence we need to ask, costing us an extra query per table
		//There is also a problem, that apparently the trick does not work if nothing in the record changes... I cant seem to duplicate this bug however, seems to work just fine.
		$fields = self::tableFields($table);
		if($fields[$unique_field]) {
			$update_sql .= sprintf(', %s = LAST_INSERT_ID(%s)', $unique_field);
		}
		$query = sprintf('%s ON DUPLICATE KEY UPDATE %s', $insert_sql, $update_sql);
		//print "\n\nQUERY: $query \n\n\n";
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

			// @TODO: Why is this here??? NOTE: Is it specific InnoDB stuff???
	        /*if (self::$tableInformation[$name]['Engine'] !== 'InnoDB') {
	            throw new MOC_DB_Exception(sprintf('Table %s does not have transaction support, its not an InnoDB table. Table type %s does not support transactions', $name, self::$tableInformation[$name]['Engine']));
	        }*/
	    }
	}

	public static function columnType($real) {
		if (is_array($real)) {
			$col = $real['name'];
			if (isset($real['limit'])) {
				$col .= '('.$real['limit'].')';
			}
			return $col;
		}

		$col = str_replace(')', '', $real);
		$limit = self::length($real);
		if (strpos($col, '(') !== false) {
			list($col, $vals) = explode('(', $col);
		}

		if (in_array($col, array('date', 'time', 'datetime', 'timestamp'))) {
			return $col;
		}
		if (($col == 'tinyint' && $limit == 1) || $col == 'boolean') {
			return 'boolean';
		}
		if (strpos($col, 'int') !== false) {
			return 'integer';
		}
		if (strpos($col, 'char') !== false || $col == 'tinytext') {
			return 'string';
		}
		if (strpos($col, 'text') !== false) {
			return 'text';
		}
		if (strpos($col, 'blob') !== false || $col == 'binary') {
			return 'binary';
		}
		if (strpos($col, 'float') !== false || strpos($col, 'double') !== false || strpos($col, 'decimal') !== false) {
			return 'float';
		}
		if (strpos($col, 'enum') !== false) {
			return "enum($vals)";
		}
		return 'text';
	}

	public static function length($real) {
		if (!preg_match_all('/([\w\s]+)(?:\((\d+)(?:,(\d+))?\))?(\sunsigned)?(\szerofill)?/', $real, $result)) {
			trigger_error(__("FIXME: Can't parse field: " . $real, true), E_USER_WARNING);
			$col = str_replace(array(')', 'unsigned'), '', $real);
			$limit = null;

			if (strpos($col, '(') !== false) {
				list($col, $limit) = explode('(', $col);
			}
			if ($limit != null) {
				return intval($limit);
			}
			return null;
		}

		$types = array(
			'int' => 1, 'tinyint' => 1, 'smallint' => 1, 'mediumint' => 1, 'integer' => 1, 'bigint' => 1
		);

		list($real, $type, $length, $offset, $sign, $zerofill) = $result;
		$typeArr = $type;
		$type = $type[0];
		$length = $length[0];
		$offset = $offset[0];

		$isFloat = in_array($type, array('dec', 'decimal', 'float', 'numeric', 'double'));
		if ($isFloat && $offset) {
			return $length.','.$offset;
		}

		if (($real[0] == $type) && (count($real) == 1)) {
			return null;
		}

		if (isset($types[$type])) {
			$length += $types[$type];
			if (!empty($sign)) {
				$length--;
			}
		} elseif (in_array($type, array('enum', 'set'))) {
			$length = 0;
			foreach ($typeArr as $key => $enumValue) {
				if ($key == 0) {
					continue;
				}
				$tmpLength = strlen($enumValue);
				if ($tmpLength > $length) {
					$length = $tmpLength;
				}
			}
		}
		return intval($length);
	}

	/**
	 * Load table meta data information
	 *
	 * @return array
	 */
	public static function loadTableInformation() {
		if (empty(self::$tableInformation)) {
			$resource = $GLOBALS['TYPO3_DB']->sql_query('SHOW TABLE STATUS');
			while ($record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resource)) {
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
