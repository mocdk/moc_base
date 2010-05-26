<?php
/**
 * Class to construct SQL conditions from a PHP array
 *
 * Loosely based on my work at the CakePHP project
 *
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 26.05.2010 
 */
class MOC_DB_Condition {
    const START_QUOTE = '`';
	
	const END_QUOTE = '`';
    
    public static $QUOTE_FIELDS = true;
    
	protected static $sqlOps = array('like', 'ilike', 'or', 'not', 'in', 'between', 'regexp', 'similar to');
	
    /**
     * Creates a WHERE clause by parsing given conditions data.  If an array or string
     * conditions are provided those conditions will be parsed and quoted.  If a boolean
     * is given it will be integer cast as condition.  Null will return 1 = 1.
     *
     * @param mixed $conditions Array or string of conditions, or any value.
     * @param boolean $quoteValues If true, values should be quoted
     * @param boolean $where If true, "WHERE " will be prepended to the return value
     * @return string SQL fragment
     * @access public
     */
    public static function build($conditions, $quoteValues = true, $where = true) {
        $clause = $out = '';
        if ($where) {
            $clause = ' WHERE ';
    	}

    	if (is_array($conditions) && !empty($conditions)) {
    		$out = self::conditionKeysToString($conditions, $quoteValues);
    		if (empty($out)) {
    		    return $clause . ' 1 = 1';
        	}
        	return $clause . implode(' AND ', $out);
    	}
    	if ($conditions === false || $conditions === true) {
    		return $clause . (int)$conditions . ' = 1';
    	}

    	if (empty($conditions) || trim($conditions) == '') {
    		return $clause . '1 = 1';
    	}

    	$clauses = '/^WHERE\\x20|^GROUP\\x20BY\\x20|^HAVING\\x20|^ORDER\\x20BY\\x20/i';
    	if (preg_match($clauses, $conditions, $match)) {
    		$clause = '';
    	}
    	if (trim($conditions) == '') {
    		$conditions = ' 1 = 1';
    	} else {
    		$conditions = self::quoteFields($conditions);
    	}
    	return $clause . $conditions;
    }
	
    /**
     * Creates a WHERE clause by parsing given conditions array.  Used by DboSource::conditions().
     *
     * @param array $conditions Array or string of conditions
     * @param boolean $quoteValues If true, values should be quoted
     * @return string SQL fragment
     * @access private
     */
	private static function conditionKeysToString($conditions, $quoteValues = true) {
        $c = 0;
		$out = array();
		$data = $columnType = null;
		$bool = array('and', 'or', 'not', 'and not', 'or not', 'xor', '||', '&&');

		foreach ($conditions as $key => $value) {
			$join = ' AND ';
			$not = null;

			if (is_array($value)) {
				$valueInsert = (!empty($value) && (substr_count($key, '?') == count($value) || substr_count($key, ':') == count($value)));
			}

			if (is_numeric($key) && empty($value)) {
				continue;
			} elseif (is_numeric($key) && is_string($value)) {
				$out[] = $not . self::quoteFields($value);
			} elseif ((is_numeric($key) && is_array($value)) || in_array(strtolower(trim($key)), $bool)) {
				if (in_array(strtolower(trim($key)), $bool)) {
					$join = ' ' . strtoupper($key) . ' ';
				} else {
					$key = $join;
				}
				$value = self::conditionKeysToString($value, $quoteValues);
				if (strpos($join, 'NOT') !== false) {
					if (strtoupper(trim($key)) == 'NOT') {
						$key = 'AND ' . trim($key);
					}
					$not = 'NOT ';
				}

				if (empty($value[1])) {
					if ($not) {
						$out[] = $not . '(' . $value[0] . ')';
					} else {
						$out[] = $value[0] ;
					}
				} else {
					$out[] = '(' . $not . '(' . implode(') ' . strtoupper($key) . ' (', $value) . '))';
				}

			} else {
				if (is_object($value) && isset($value->type)) {
					if ($value->type == 'identifier') {
						$data .= self::name($key) . ' = ' . self::name($value->value);
					} elseif ($value->type == 'expression') {
						if (is_numeric($key)) {
							$data .= $value->value;
						} else {
							$data .= self::name($key) . ' = ' . $value->value;
						}
					}
				} elseif (is_array($value) && !empty($value) && !$valueInsert) {
					$keys = array_keys($value);
					if (array_keys($value) === array_values(array_keys($value))) {
						$count = count($value);
						if ($count === 1) {
							$data = self::quoteFields($key) . ' = (';
						} else {
							$data = self::quoteFields($key) . ' IN (';
						}
						if ($quoteValues) {
							$data .= implode(', ', self::value($value, $columnType));
						}
						$data .= ')';
					} else {
						$ret = self::conditionKeysToString($value, $quoteValues);
						if (count($ret) > 1) {
							$data = '(' . implode(') AND (', $ret) . ')';
						} elseif (isset($ret[0])) {
							$data = $ret[0];
						}
					}
				} elseif (is_numeric($key) && !empty($value)) {
					$data = self::quoteFields($value);
				} else {
					$data = self::parseKey(trim($key), $value);
				}

				if ($data != null) {
					$out[] = $data;
					$data = null;
				}
			}
			$c++;
		}
		return $out;
	}
	
	/**
     * Extracts a Model.field identifier and an SQL condition operator from a string, formats
     * and inserts values, and composes them into an SQL snippet.
     *
     * @param string $key An SQL key snippet containing a field and optional SQL operator
     * @param mixed $value The value(s) to be inserted in the string
     * @return string
     */
    private static function parseKey($key, $value) {    		
    		$operatorMatch = '/^((' . implode(')|(', self::$sqlOps);
    		$operatorMatch .= '\\x20)|<[>=]?(?![^>]+>)\\x20?|[>=!]{1,3}(?!<)\\x20?)/is';
    		$bound = (strpos($key, '?') !== false || (is_array($value) && strpos($key, ':') !== false));

    		if (!strpos($key, ' ')) {
    			$operator = '=';
    		} else {
    			list($key, $operator) = explode(' ', trim($key), 2);

    			if (!preg_match($operatorMatch, trim($operator)) && strpos($operator, ' ') !== false) {
    				$key = $key . ' ' . $operator;
    				$split = strrpos($key, ' ');
    				$operator = substr($key, $split);
    				$key = substr($key, 0, $split);
    			}
    		}

            $virtual = false;
    		$type = null;

    		$null = ($value === null || (is_array($value) && empty($value)));

    		if (strtolower($operator) === 'not') {
    			$data = self::conditionKeysToString(array($operator => array($key => $value)), true);
    			return $data[0];
    		}

    		$value = self::value($value, $type);
    		if (!$virtual && $key !== '?') {
    			$isKey = (strpos($key, '(') !== false || strpos($key, ')') !== false);
    			$key = $isKey ? self::quoteFields($key) : self::name($key);
    		}

    		if ($bound) {
    			return MOC_String::insert($key . ' ' . trim($operator), $value);
    		}

    		if (!preg_match($operatorMatch, trim($operator))) {
    			$operator .= ' =';
    		}
    		$operator = trim($operator);

    		if (is_array($value)) {
    			$value = implode(', ', $value);

    			switch ($operator) {
    				case '=':
    					$operator = 'IN';
    				break;
    				case '!=':
    				case '<>':
    					$operator = 'NOT IN';
    				break;
    			}
    			$value = "({$value})";
    		} elseif ($null) {
    			switch ($operator) {
    				case '=':
    					$operator = 'IS';
    				break;
    				case '!=':
    				case '<>':
    					$operator = 'IS NOT';
    				break;
    			}
    		}
    		if ($virtual) {
    			return "({$key}) {$operator} {$value}";
    		}
    		return "{$key} {$operator} {$value}";
    	}
	
    /**
     * Returns a quoted name of $data for use in an SQL statement.
     * Strips fields out of SQL functions before quoting.
     *
     * @param string $data
     * @return string SQL field
     */
     private static function name($data) {
		if ($data === '*') {
			return '*';
		}
		if (is_array($data)) {
			foreach ($data as $i => $dataItem) {
				$data[$i] = self::name($dataItem);
			}
			return $data;
		}
		$data = trim($data);
		if (!self::$QUOTE_FIELDS) {
		    return $data;
		}
		if (preg_match('/^[\w-]+(\.[\w-]+)*$/', $data)) { // string, string.string
			if (strpos($data, '.') === false) { // string
				return self::START_QUOTE . $data . self::END_QUOTE;
			}
			$items = explode('.', $data);
			return self::START_QUOTE . implode(self::START_QUOTE . '.' . self::END_QUOTE, $items) . self::END_QUOTE;
		}
		if (preg_match('/^[\w-]+\.\*$/', $data)) { // string.*
			return self::START_QUOTE . str_replace('.*', self::END_QUOTE . '.*', $data);
		}
		if (preg_match('/^([\w-]+)\((.*)\)$/', $data, $matches)) { // Functions
			return $matches[1] . '(' . self::name($matches[2]) . ')';
		}
		if (preg_match('/^([\w-]+(\.[\w-]+|\(.*\))*)\s+' . preg_quote("") . '\s*([\w-]+)$/', $data, $matches)) {
			return preg_replace('/\s{2,}/', ' ', self::name($matches[1]) . ' ' . '' . ' ' . self::name($matches[3]));
		}
		return $data;
	}
	
    /**
     * Prepares a value, or an array of values for database queries by quoting and escaping them.
     *
     * @param mixed $data A value or an array of values to prepare.
     * @param string $column The column into which this data will be inserted
     * @param boolean $read Value to be used in READ or WRITE context
     * @return mixed Prepared value or array of values.
     */
    private static function value($data, $column = null, $read = true) {
        if (is_array($data) && !empty($data)) {
		 	return array_map(
				array('MOC_DB_Condition', 'value'),
				$data, array_fill(0, count($data), $column), array_fill(0, count($data), $read)
			);
		} elseif (is_object($data) && isset($data->type)) {
			if ($data->type == 'identifier') {
				return self::name($data->value);
			} elseif ($data->type == 'expression') {
				return $data->value;
			}
		}
		
		if ($data === null || (is_array($data) && empty($data))) {
        	return 'NULL';
		}
		if ($data === '' && $column !== 'integer' && $column !== 'float' && $column !== 'boolean') {
			return  "''";
		}
		
		if (empty($column)) {
			$column = self::introspectType($data);
		}

		switch ($column) {
			case 'boolean':
				return self::boolean((bool)$data);
			break;
			case 'integer':
			case 'float':
				if ($data === '') {
					return 'NULL';
				}
				if ((is_int($data) || is_float($data) || $data === '0') || (
					is_numeric($data) && strpos($data, ',') === false &&
					$data[0] != '0' && strpos($data, 'e') === false)) {
						return $data;
					}
			default:
				$data = "'" . mysql_real_escape_string($data) . "'";
			break;
		}
		return $data;
	}
	
	/**
     * Guesses the data type of an array
     *
     * @param string $value
     * @return void
     */
    private static function introspectType($value) {
    	if (!is_array($value)) {
			if ($value === true || $value === false) {
				return 'boolean';
			}
			if (is_float($value) && floatval($value) === $value) {
				return 'float';
			}
			if (is_int($value) && intval($value) === $value) {
				return 'integer';
			}
			if (is_string($value) && strlen($value) > 255) {
				return 'text';
			}
			return 'string';
		}

		$isAllFloat = $isAllInt = true;
		$containsFloat = $containsInt = $containsString = false;
		foreach ($value as $key => $valElement) {
			$valElement = trim($valElement);
			if (!is_float($valElement) && !preg_match('/^[\d]+\.[\d]+$/', $valElement)) {
				$isAllFloat = false;
			} else {
				$containsFloat = true;
				continue;
			}
			if (!is_int($valElement) && !preg_match('/^[\d]+$/', $valElement)) {
				$isAllInt = false;
			} else {
				$containsInt = true;
				continue;
			}
			$containsString = true;
		}

		if ($isAllFloat) {
			return 'float';
		}
		if ($isAllInt) {
			return 'integer';
		}

		if ($containsInt && !$containsString) {
			return 'integer';
		}
		return 'string';
	}
	
    /**
     * Translates between PHP boolean values and Database (faked) boolean values
     *
     * @param mixed $data Value to be translated
     * @return mixed Converted boolean value
     */
    private static function boolean($data) {
    	if ($data === true || $data === false) {
    		if ($data === true) {
    			return 1;
    		}
    		return 0;
    	} else {
    		return !empty($data);
    	}
    }
    
	/**
     * Quotes Model.fields
     *
     * @param string $conditions
     * @return string or false if no match
     */
    private static function quoteFields($conditions) {
    	$start = $end  = null;
	    $original = $conditions;
     
	    $start = $end = preg_quote('`');
	    $conditions = str_replace(array($start, $end), '', $conditions);
	    $conditions = preg_replace_callback('/(?:[\'\"][^\'\"\\\]*(?:\\\.[^\'\"\\\]*)*[\'\"])|([a-z0-9_' . $start . $end . ']*\\.[a-z0-9_' . $start . $end . ']*)/i', array('MOC_DB_Condition', '__quoteMatchedField'), $conditions);
	    if ($conditions !== null) {
	    	return $conditions;
	    }
	    return $original;
	}
	
	/**
     * Auxiliary function to quote matches `Model.fields` from a preg_replace_callback call
     *
     * @param string matched string
     * @return string quoted strig
     * @access private
     */
    protected static function __quoteMatchedField($match) {
        if (is_numeric($match[0])) {
    		return $match[0];
    	}
    	return self::name($match[0]);
    }
}