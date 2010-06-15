<?php
/**
 * MOC Configuration class
 *
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 13.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC_Configuration implements ArrayAccess, Countable, Serializable, IteratorAggregate {

    /**
     * Data storage
     *
     * @var array
     */
    protected $data = array();

    /**
     * Initalize object with data
     *
     * @param array $data
     */
    public function __construct($data = array()) {
        if (empty($data)) {
            $data = array();
        }
        $this->data = $data;
    }

    /**
     * Used to read information stored in the configuration instance.
     *
     * @param string $var Variable to obtain
     * @param mixed $default
     * @return string value of $var
     */
    public function get($var = null, $default = null) {
        $name = $this->__configVarNames($var);

        if (empty($name)) {
            return $this->data;
        }

        switch (count($name)) {
            case 5:
                if (isset($this->data[$name[0]][$name[1]][$name[2]][$name[3]][$name[4]])) {
                    return $this->data[$name[0]][$name[1]][$name[2]][$name[3]][$name[4]];
                }
                break;
            case 4:
                if (isset($this->data[$name[0]][$name[1]][$name[2]][$name[3]])) {
                    return $this->data[$name[0]][$name[1]][$name[2]][$name[3]];
                }
                break;
            case 3:
                if (isset($this->data[$name[0]][$name[1]][$name[2]])) {
                    return $this->data[$name[0]][$name[1]][$name[2]];
                }
                break;
            case 2:
                if (isset($this->data[$name[0]][$name[1]])) {
                    return $this->data[$name[0]][$name[1]];
                }
                break;
            case 1:
                if (isset($this->data[$name[0]])) {
                    return $this->data[$name[0]];
                }
                break;
            default:
                throw new MOC_Configuration_Exception(sprintf('Unable to get the key. Depth is invalid ("%s")', count($name)));
        }

        return $default;
    }

    /**
     * Get the RAW data array
     *
     * @return array
     */
    public function getAll() {
        return $this->data;
    }

    /**
     * Check if a key exists
     *
     * @param string $key
     */
    public function check($key) {
        if (!is_array($key)) {
            $key = array($key);
        }

        $exists = true;

        foreach ($key as $field) {
            $name = $this->__configVarNames($field);
            switch (count($name)) {
                case 1:
                    $exists = $exists && array_key_exists($name[0], $this->data);
                    break;
                case 2:
                    $exists = $exists && (array_key_exists($name[0], $this->data) && is_array($this->data[$name[0]]) && array_key_exists($name[1], $this->data[$name[0]]));
                    break;
                case 3:
                    $exists = $exists && (array_key_exists($name[0], $this->data) && is_array($this->data[$name[0]]) && array_key_exists($name[1], $this->data[$name[0]]) && is_array($this->data[$name[0]][$name[1]]) && array_key_exists($name[2], $this->data[$name[0]][$name[1]]));
                    break;
                case 4:
                    $exists = $exists && (array_key_exists($name[0], $this->data) && is_array($this->data[$name[0]]) && array_key_exists($name[1], $this->data[$name[0]]) && is_array($this->data[$name[0]][$name[1]]) && array_key_exists($name[2], $this->data[$name[0]][$name[1]]) &&
                        is_array($this->data[$name[0]][$name[1]][$name[2]]) && array_key_exists($name[3], $this->data[$name[0]][$name[1]][$name[2]]));
                    break;
                case 5:
                    $exists = $exists && (array_key_exists($name[0], $this->data) && is_array($this->data[$name[0]]) && array_key_exists($name[1], $this->data[$name[0]]) && is_array($this->data[$name[0]][$name[1]]) && array_key_exists($name[2], $this->data[$name[0]][$name[1]]) &&
                        is_array($this->data[$name[0]][$name[1]][$name[2]]) && array_key_exists($name[3], $this->data[$name[0]][$name[1]][$name[3]]) && is_array($this->data[$name[0]][$name[1]][$name[2]][$name[3]]) && array_key_exists($name[4], $this->data[$name[0]][$name[1]][$name[2]][$name[3]]));
                    break;
                default:
                    throw new MOC_Configuration_Exception(sprintf('Unable to check if key exists. Depth is invalid ("%s")', count($name)));
            }
        }

        return $exists;
    }

    /**
     * Delete a configuration key
     *
     * @param string $key
     */
    public function delete($key) {
        if (!$this->check($key)) {
            return false;
        }

        $name = $this->__configVarNames($key);
        switch (count($name)) {
            case 1:
                if (array_key_exists($name[0], $this->data)) {
                    unset($this->data[$name[0]]);
                    return true;
                }
                break;
            case 2:
                if (array_key_exists($name[0], $this->data) && is_array($this->data[$name[0]]) && array_key_exists($name[1], $this->data[$name[0]])) {
                    unset($this->data[$name[0]][$name[1]]);
                    return true;
                }
                break;
            case 3:
                if (array_key_exists($name[0], $this->data) && is_array($this->data[$name[0]]) && array_key_exists($name[1], $this->data[$name[0]]) && is_array($this->data[$name[0]][$name[1]]) && array_key_exists($name[2], $this->data[$name[0]][$name[1]])) {
                    unset($this->data[$name[0]][$name[1]][$name[2]]);
                    return true;
                }
                break;
            case 4:
                if (array_key_exists($name[0], $this->data) && is_array($this->data[$name[0]]) && array_key_exists($name[1], $this->data[$name[0]]) && is_array($this->data[$name[0]][$name[1]]) && array_key_exists($name[2], $this->data[$name[0]][$name[1]]) && array_key_exists
                    ($name[2], $this->data[$name[0]][$name[1]][$name[2]])) {
                    unset($this->data[$name[0]][$name[1]][$name[2]][$name[3]]);
                    return true;
                }
                break;
            case 5:
                if (array_key_exists($name[0], $this->data) && is_array($this->data[$name[0]]) && array_key_exists($name[1], $this->data[$name[0]]) && is_array($this->data[$name[0]][$name[1]]) && array_key_exists($name[2], $this->data[$name[0]][$name[1]]) && array_key_exists
                    ($name[2], $this->data[$name[0]][$name[1]][$name[2]]) && array_key_exists($name[2], $this->data[$name[0]][$name[1]][$name[2]][$name[3]])) {
                    unset($this->data[$name[0]][$name[1]][$name[2]][$name[3]][$name[4]]);
                    return true;
                }
                break;
            default:
                throw new MOC_Configuration_Exception(sprintf('Unable to delete the key. Depth is invalid ("%s")', count($name)));
        }
        return false;
    }

    /**
     * Check if a configuration key is present, if not, throw an MOC_Configuration_Exception
     *
     * @param string $key
     */
    public function checkKeyPresence($key) {
        if (!$this->check($key)) {
            throw new MOC_Configuration_Exception(sprintf('Required configuration setting "%s" is missing', $key));
        }
    }

    /**
     * Validate a configuration key
     *
     * @param string $key
     * @param string|array $method
     * @return boolean
     */
    public function validate($key, $method) {
        if (!$this->check($key)) {
            return false;
        }

		if (!is_callable($method)) {
			return false;
		}

        return call_user_func($method, $this->get($key));
    }

    /**
     * Used to store a dynamic variable in the Configure instance.
     *
     * @param array $config Name of var to write
     * @param mixed $value Value to set for var
     */
    public function set($config, $value = null) {
        if (!is_array($config)) {
            $config = array($config => $value);
        }
        foreach ($config as $names => $value) {
            $name = $this->__configVarNames($names);

            switch (count($name)) {
                case 7:
                    $this->data[$name[0]][$name[1]][$name[2]][$name[3]][$name[4]][$name[5]][$name[6]] = $value;
                    break;
                case 6:
                    $this->data[$name[0]][$name[1]][$name[2]][$name[3]][$name[4]][$name[5]] = $value;
                    break;
                case 5:
                    $this->data[$name[0]][$name[1]][$name[2]][$name[3]][$name[4]] = $value;
                    break;
                case 4:
                    $this->data[$name[0]][$name[1]][$name[2]][$name[3]] = $value;
                    break;
                case 3:
                    $this->data[$name[0]][$name[1]][$name[2]] = $value;
                    break;
                case 2:
                    $this->data[$name[0]][$name[1]] = $value;
                    break;
                case 1:
                    $this->data[$name[0]] = $value;
                    break;
                default:
                	print "TEST";
                	print_r($name);
                    throw new MOC_Configuration_Exception(sprintf('Unable to set the value. Depth is invalid ("%s")', count($name)));
            }
        }
    }

    /**
     * Set value of $path if its not defined already
     *
     * @param mixed $path
     * @param mixed $value
     */
    public function setDefaultIfEmpty($path, $value) {
        if ($this->check($path)) {
            return false;
        }
        $this->set($path, $value);
    }

    /**
     * Filter a simple array on $itemKey if value not in $allowed
     *
     * @param string $path The path to the array
     * @param string $itemKey The key to use for comparison
     * @param mixed $allowed List of items that the value of $itemKey must match
     * @return integer Number of records filtered
     */
    public function filterByValue($path, $itemKey, $allowed) {
        if (!is_array($allowed)) {
            $allowed = array($allowed);
        }

        $filtered = 0;

		if (empty($path)) {
			$pathValues = $this->getAll();
		}

		$pathValues = $this->get($path);
		if (empty($pathValues)) {
			return 0;
		}

        foreach ($pathValues as $key => $value) {
            if (false === array_search($value[$itemKey], $allowed)) {
                $this->delete($path . '.' . $key);
                $filtered++;
                continue;
            }
        }

        return $filtered;
    }

    /**
     * Extract a list of keys from current object and return them in a new
     * MOC_Configuration object
     *
     * @param array $keys
     * @return MOC_Configuration
     */
    public function extractKeys($keys) {
        $Configuration = new MOC_Configuration();
        foreach ($keys as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
            }

            $Configuration->set($key, $this->get($value));
        }
        return $Configuration;
    }

    /**
     * Merge an array of settings into the current setup
     *
     * @param array $settings
     */
    public function merge($settings) {
        if (empty($settings) || !is_array($settings)) {
            throw new MOC_Configuration_Exception('Unabale to merge settings, invalid data provided');
        }

        $this->data = MOC_Array::merge($this->data, $settings);
    }

    /**
     * Load configuration data from globals ExtConf
     *
     * @param string $extKey
     * @param boolean $return If true the settings will be returned, else its merged into current settings
     */
    public function loadFromExtConf($extKey, $return = false) {
        global $TYPO3_CONF_VARS, $TYPO3_LOADED_EXT;

        // Check if EXT_CONF_KEY exists in the TYPO3_CONF_VARS array
        if (!array_key_exists($extKey, $TYPO3_CONF_VARS['EXT']['extConf'])) {
            throw new MOC_Configuration_Exception(sprintf('Missing extConf key "%s"', $extKey));
        }

        // Read the EXT_CONF_KEY from TYPO3_CONF_VARS - also unserialize the data
        $config = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$extKey]);
        if (empty($config) || !is_array($config)) {
            throw new MOC_Configuration_Exception(sprintf('Missing configuration data for extConf "%s"', $extKey));
        }

        if ($return) {
            return $config;
        }

        $this->set($config);
    }

    /**
     * Build a path key from an array
     *
     * @param array $pathKeys
     * @return string
     */
    public function buildPath($pathKeys) {
        return join('.', $pathKeys);
    }

    /**
     * Check if offset exists
     *
     * @see ArrayAccess
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return $this->check($offset);
    }

    /**
     * Get offset value
     *
     * @see ArrayAccess
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->get($offset);
    }

    /**
     * Set offset value
     *
     * @see ArrayAccess
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {
        $this->set($offset, $value);
    }

    /**
     * Unset offset
     *
     * @see ArrayAccess
     * @param mixed $offset
     */
    public function offsetUnset($offset) {
        $this->delete($offset);
    }

    /**
     * Count elements of an object
     *
     * @see Countable
     * @return integer
     */
    public function count() {
        return sizeof($this->data);
    }

    /**
     * Serialize data
     *
     * @see Serializable
     * @return array
     */
    public function serialize() {
        return serialize($this->data);
    }

    /**
     * Unserialize data
     *
     * @see Serializable
     * @param string $data
     */
    public function unserialize($data) {
        $this->data = unserialize($data);
    }

    /**
     * Get itrator to traversable
     *
     * @see IteratorAggregate
     * @return array
     */
    public function getIterator() {
        return new ArrayIterator($this);
    }

    /**
     * Called when object is cloned
     *
     */
    public function __clone() {

    }

    /**
     * Checks $name for dot notation to create dynamic Configure::$var as an array when needed.
     *
     * @param mixed $name Name to split
     * @return array Name separated in items through dot notation
     */
    protected function __configVarNames($name) {
        if (is_string($name)) {
            if (strpos($name, ".")) {
				// Prevent double dots
				$name = str_replace('..', '.', $name);
				// Prevent trailing dots
				$name = trim(trim($name, '.'));
				// Return array path
				return explode(".", $name);
            }
            return array($name);
        }
        return $name;
    }
}
