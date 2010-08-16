<?php
/**
 * Overwrite most functions in t3lib_DB to provide logging facility
 *
 * May not work with all versions of TYPO3
 *
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 16.08.2010
 */
class MOC_DB_Logger extends t3lib_DB {
	protected $queries = array();

	function getAllQueriesRaw() {
		return $this->queries;
	}

    function getAllQueries() {
	    $lines = array();
		foreach($this->queries as $i => $query) {
			$lines[] = '-- ';
			$lines[] = '-- Query #' . ($i+1);
			$lines[] = '-- ';
			$lines[] = $query . ';';
			$lines[] = '';
		}
		$lines[] = '';
		return join("\n", $lines);
	}

	function writeQueryLog($file = null) {
		if (empty($file)) {
			$file = PATH_site . 'typo3temp/query_logs/' . time() . '.sql';
		} elseif (false === strpos($file, DIRECTORY_SEPARATOR)) {
			$file = PATH_site . 'typo3temp/query_logs/' . $file;
		}

		$dir = dirname($file);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

		file_put_contents($file, $this->getAllQueries());
		return "Wrote query log to $file";
	}

	function INSERTquery() {
		$args = func_get_args();
		$query = call_user_func_array(array('parent', 'INSERTquery'), $args);

		$this->queries[] = $query;

		return $query;
	}

	public function INSERTmultipleRows() {
		$args = func_get_args();
		$query = call_user_func_array(array('parent', 'INSERTmultipleRows'), $args);

		$this->queries[] = $query;

		return $query;
	}

	function UPDATEquery() {
		$args = func_get_args();
		$query = call_user_func_array(array('parent', 'UPDATEquery'), $args);

		$this->queries[] = $query;

		return $query;
	}

	function DELETEquery() {
		$args = func_get_args();
		$query = call_user_func_array(array('parent', 'DELETEquery'), $args);

		$this->queries[] = $query;

		return $query;
	}

	function SELECTquery() {
		$args = func_get_args();
		$query = call_user_func_array(array('parent', 'SELECTquery'), $args);

		$this->queries[] = $query;

		return $query;
	}

	function listQuery() {
		$args = func_get_args();
		$query = call_user_func_array(array('parent', 'listQuery'), $args);

		$this->queries[] = $query;

		return $query;
	}

	function searchQuery() {
		$args = func_get_args();
		$query = call_user_func_array(array('parent', 'searchQuery'), $args);

		$this->queries[] = $query;

		return $query;
	}

	function sql($db, $query) {
		$this->queries[] = $query;
		return parent::sql($db, $query);
	}

	function sql_query($query) {
		$this->queries[] = $query;
		return parent::sql_query($query);
	}

	function admin_query($query) {
		$this->queries[] = $query;
		return parent::$query;
	}
}