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

/*
 * The main target of `Keep` is to persist small amounts of information and
 * saving disk accesses.
 *
 * Typical usage:

	class Config {

		public static $data = null;

		public static function read() {
			if (null !== self::$data) {
				return;
			}
			self::$data = Keep::read(__CLASS__);
			if (null === self::$data) {
				// Default values for my class
				self::$data = array(
					'default' => 'value',
				);
			}
		}

		public static function write() {
			Keep::write(__CLASS__, self::$data);
		}

	}

 * Implementation details: The ugliest thing is that `$filename` property
 * is public, the reason is only for testing purposes.
 * 
 * Improvements: Detect what is the current file it is being called from
 * to avoid passing a key.
*/

class Keep {

	public static $filename = '.keep';
	private static $data = null;

	public static function read($key) {
		self::load_data();
		if (isset(self::$data[$key]))
			return self::$data[$key];
		return null;
	}

	public static function write($key, &$data) {
		self::load_data();
		self::$data[$key]=$data;
		self::save_data();
	}

	public static function remove($key) {
		self::load_data();
		unset(self::$data[$key]);
		self::save_data();
	}

	private static function load_data() {
		if (self::$data == null) {
			if (file_exists(self::$filename)) {
				self::$data = unserialize(file_get_contents(self::$filename));
			} else {
				self::$data = array();
			}
		}
	}

	private static function save_data() {
		file_put_contents(self::$filename,serialize(self::$data));
		chmod(self::$filename, 0666);
	}

}