<?php

/*
	TreeWeb

	Copyright 2015 Gerardo Óscar Jiménez Tornos <gerardooscarjt@gmail.com>

	This file is part of TreeWeb.

	TreeWeb is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	TreeWeb is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with TreeWeb.  If not, see <http://www.gnu.org/licenses/>.
*/

import('core.Keep');

class Config {

	private static $_data = null;
	private static $dir_config = 'config';
	private static $default_type = 'STRING';


	public static function getKeys() {
		$keys = array();

		$path = self::$dir_config.'/';
		$d = dir($path);
		while (false !== ($entry = $d->read())) {
			$entry = pathinfo ($entry);
			if ('json' == $entry['extension']) {
				$keys[] = $entry['filename'];
			}
		}
		$d->close();

		return $keys;
	}


	/**
	 * Get a value from configuration
	 *
	 * Returns the value of a key. By default returns local value, but if it
	 * does not exists, it returns the global one. If global does not exists
	 * it returns default value.
	 * If $global == true, dont seek in local.
	 *
	 * @param 	string 		$key 		UPPERCASE_KEY
	 * @param 	boolean		$global		From global scope
	 * @return 	mixed 		The stored value
	 * 
	*/
	public static function get($key, $global=true) {
		null != self::$_data || self::load_data();

		$k = $key;

		if (!$global) {
			// Check in local
			$host = $_SERVER['HTTP_HOST'];
			if (array_key_exists($host, self::$_data)) {
				if (array_key_exists($k, self::$_data[$host])) {
					return self::$_data[$host][$k];
				}
			}
		}

		// Check in global
		if (array_key_exists($k, self::$_data['global'])) {
			return self::$_data['global'][$k];
		} else {
			$model = self::load($key);
			if (false == $model) {
				return '';													// Problem loading model
			}
			$value = $model['default'];
			self::set($key, $value, true);
			return $value;
		}
	}

	/**
	 * Set a value to configuration
	 *
	 * Set a value to a key. By default, store values in local.
	 * If $global==true the value is stored in global.
	 *
	 * Error codes:
	 * 		0 	All OK
	 * 		1 	ERROR, key does not exists
	 * 		2 	ERROR, cant load model
	 * 		3 	ERROR, type missmatch
	 * 		4 	Error, cant store data
	 *
	 * @param 	string 		$key 		UPPERCASE_KEY
	 * @param 	string 		$value 		The value
	 * @param 	boolean		$global		From global scope
	 * @return 	integer 	Error codes
	 * 
	*/
	public static function set($key, $value, $global=true) {

		if (!self::exists($key)) {
			return 1;														// ERROR, key does not exists
		}

		$model = self::load($key);
		if (false == $model) {
			return 2;														// ERROR, cant load model
		}

		$type = $model['type'];
		if (!self::check($value, $type)) {
			return 3;														// ERROR, type missmatch
		}

		null != self::$_data || self::load_data();							// Load data if needed

		$k = $key;

		if ($global) {
			self::$_data['global'][$k] = $value;
		} else if (false === $global) {
			$host = $_SERVER['HTTP_HOST'];
			self::$_data[$host][$k] = $value;
		}

		if (!self::store_data()) {
			return 4;														// Error, cant store data
		}

		return 0;
	}

	/**
	 * Restore a value
	 *
	 * Restore the default value for a key if $global is true or inherit
	 * from global if $global is false.
	 *
	 * Error codes:
	 * 		0 	All OK
	 * 		1 	ERROR, key does not exists
	 * 		2 	Error, cant store data
	 *
	 * @param 	string 		$key 		UPPERCASE_KEY
	 * @param 	boolean		$global		From global scope
	 * @return 	integer 	Error codes
	 * 
	*/
	public static function restore($key, $global=true) {
		if (!self::exists($key)) {
			return 1;														// ERROR, key does not exists
		}

		null != self::$_data || self::load_data();							// Load data if needed

		if (true === $global) {
			unset(self::$_data['global'][$key]);
		} else if (false === $global) {
			$host = $_SERVER['HTTP_HOST'];
			if (array_key_exists($host, self::$_data)) {
				unset(self::$_data[$host][$key]);
			} else {
				return 0;													// Value does not recorded
			}
		}

		if (!self::store_data()) {
			return 2;														// Error, cant store data
		}			

		return 0;

	}

	/**
	 * Check if a key exists
	 *
	 * @param 	string 		$key 		UPPERCASE_KEY
	 * @return 	boolean 	true if exists, false otherwise
	 * 
	*/
	public static function exists($key) {
		$file = self::getPath($key);
		return file_exists($file);
	}

	/**
	 * Get path where config file is/will be stored
	 * 
	 * TODO: VERY IMPORTANT sanitize key or return null
	 *
	 * @param 	string 		$key 		UPPERCASE_KEY
	 * @return 	string 		path where config file is stored
	*/
	public static function getPath($key) {
		return self::$dir_config.'/'.$key.'.json';
	}

	/**
	 * Creates a configuration key
	 *
	 * The default type is string
	 * Error codes:
	 * 		0 	All ok
	 * 		2 	Key already exists
	 * 		3 	Error, cant store model
	 *		
	 *
	 * @param 	string 		$key 		UPPERCASE_KEY
	 * @return 	integer 	Error code (0 is no error)
	*/
	public static function create($key) {

		if (self::exists($key)) {
			return 2;
		}

		$model = array(
			'name'=>$key,
			'type'=>self::$default_type,
			'description'=>'Please programmer, complete this description',
			'default'=>'Please programmer, complete default value',
			'callback_before'=>null,
			'callback_after'=>null,
		);

		if (!self::store($key, $model)) {
			return 3;
		}

		return 0;
	}

	public static function remove($key) {
		return 12;
	}

	/**
	 * Set type
	 *
	 * Set type and reset default value to be correct.
	 * Available types are:
	 * 		NUMBER 		Real numbers
	 *		STRING 		String
	 *		MD5 		MD5 hash. Default value is md5('')
	 *		EMAIL 		List of emails separated by comma.
	 *		BOOLEAN 	Bool. Default value is false.
	 * 		{list}		A comma-separated list of values.
	 * Items in a list are automatically trimmed. The list must contain
	 * at least two items.
	 * For example, this are valid lists:
	 * 		"one, two, thre", "   my first option   , my second one   "
	 * Error codes:
	 * 		0	All ok
	 * 		1	Error, cant load model
	 * 		2	Error, type mismatch
	 * 		3	Error, cant save model
	 *
	 * @param 	string 		$key 		UPPERCASE_KEY
	 * @param 	string 		$type 		NUMBER,STRING,MD5,EMAIL,BOOL
	 *									or {list}
	 * @return 	string 		The type or empty string if error
	*/
	public static function setType($key, $type) {
		$model = self::load($key);

		if (false === $model) {
			return 1;
		}

		$TYPE = strtoupper($type);
		switch ($TYPE) {
			case 'NUMBER':
				$model['default'] = (float) $model['default'];
				break;
			case 'STRING':
				$model['default'] = (string) $model['default'];
				break;
			case 'MD5':
				$model['default'] = md5('');
				break;
			case 'EMAIL':
				$model['default'] = 'email@example.com';
				break;
			case 'BOOLEAN':
				$model['default'] = false;
				break;
			default:
				// Treat this as a list, so, this must have at least
				// two values
				$list = array_map('trim', explode(',', $TYPE));
				if (count($list)>1) {
					$model['default'] = $list[0];
					$TYPE = implode(', ', $list);
				} else {
					return 2;
				}
				break;
		}

		$model['type'] = $TYPE;

		self::restore($key, true);
		self::restore($key, false);


		if (!self::store($key, $model)) {
			return 3;
		}

		return 0;
	}

	/**
	 * Get type
	 *
	 * @param 	string 		$key 		UPPERCASE_KEY
	 * @return 	string 		The type or empty string if error
	*/
	public static function getType($key) {
		$model = self::load($key);

		if (false === $model) {
			return '';
		}

		return $model['type'];
	}

	/**
	 * Set description
	 *
	 * Error codes:
	 * 		0		All ok
	 * 		1		Error, cant load model
	 * 		2		Error, cant store model
	 *
	 * @param 	string 		$key 		UPPERCASE_KEY
	 * @return 	integer		Error code
	*/
	public static function setDescription($key, $description) {
		$model = self::load($key);

		if (false === $model) {
			return 1;
		}

		$model['description'] = $description;

		if (!self::store($key, $model)) {
			return 2;
		}

		return 0;
	}

	/**
	 * Get description
	 *
	 * @param 	string 		$key 		UPPERCASE_KEY
	 * @return 	string 		The type or empty string if error
	*/
	public static function getDescription($key) {
		$model = self::load($key);

		if (false === $model) {
			return '';
		}

		return $model['description'];
	}

	/**
	 * Set default
	 *
	 * Error codes:
	 * 		0		All ok
	 * 		1		Error, cant load model
	 * 		2		Error, is not valid value
	 * 		3		Error, cant store model
	 *
	 * @param 	string 		$key 		UPPERCASE_KEY
	 * @param 	string 		$value 		Default value
	 * @return 	integer		Error code
	*/
	public static function setDefault($key, $value) {
		$model = self::load($key);

		if (false === $model) {
			return 1;
		}

		$type = $model['type'];
		if (!self::check($value, $type)) {
			return 2;
		}

		$model['default'] = $value;

		if (!self::store($key, $model)) {
			return 3;
		}

		return 0;
	}

	/**
	 * Get default
	 *
	 * Get default value, used when is unset.
	 *
	 * @param 	string 		$key 		UPPERCASE_KEY
	 * @return 	string 		The type or empty string if error
	*/
	public static function getDefault($key) {
		$model = self::load($key);

		if (false === $model) {
			return '';
		}

		return $model['default'];
	}

	/**
	 * TODO: next version
	*/
	public static function setCallbackBefore($key, $callback) {
		return 19;
	}

	/**
	 * TODO: next version
	*/
	public static function getCallbackBefore($key) {
		return 20;
	}

	/**
	 * TODO: next version
	*/
	public static function setCallbackAfter($key, $callback) {
		return 21;
	}

	/**
	 * TODO: next version
	*/
	public static function getCallbackAfter($key, $callback) {
		return 22;
	}

	/////////////////////////////// PRIVATE ////////////////////////////////

	// load data
	private static function load_data() {
		if (null == self::$_data) {
			self::$_data = Keep::read(__CLASS__);
			if (self::$_data===null) {
				self::$_data = array(
					'global'=>array(),
				);
			}
		}
	}

	// store data
	private static function store_data() {
		Keep::write(__CLASS__, self::$_data);
		return true;
	}

	// load model
	// TODO: !! error: must check if file exists before access to it!!!!!!
	private static function load($key) {
		$data = file_get_contents(self::getPath($key));
		if (false == $data) {
			return false;
		}

		return json_decode($data, true);
	}

	// store model
	private static function store($key, &$model) {
		$path = self::getPath($key);

		$result = file_put_contents(
			$path,
			json_encode($model, JSON_PRETTY_PRINT)
		);

		chmod($path, 0777);

		return $result;
	}

	private static function check(&$value, $type) {
		switch ($type) {
			case 'NUMBER':
				$value = str_replace(',', '.', $value);
				return true===settype($value, 'float');
			case 'STRING':
				return true===settype($value, 'string');
			case 'MD5':
				return !empty($value) && preg_match('/^[a-f0-9]{32}$/', $value);
			case 'EMAIL':
				$list = explode(',', $value);
				foreach ($list as $L=>$l) {
					$list[$L] = trim($l);
					if (!filter_var($list[$L], FILTER_VALIDATE_EMAIL)) {
						return false;
					}
				}
				$value = implode(', ', $list);
				return true;
			case 'BOOLEAN':
				if (       $value === true  || $value == 'true'  || $value == 1 || $value == '1' || $value == 'on'  || $value == 'yes') {
					$value = true;
					return true;
				} elseif  ($value === false || $value == 'false' || $value == 0 || $value == '0' || $value == 'off' || $value == 'no' ) {
					$value = false;
					return true;
				}
				return false;
			default:
				// Treat this as a list, so, this must have at least two values
				$list = array_map('trim', explode(',', $type));
				return count($list) > 1 && in_array($value, $list);
		}
		return false;
	}

}
