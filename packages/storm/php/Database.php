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

/*
 * Database tries to abstract a database connection.
 *
 * Typical usage:

 	import('storm.Database');

	// Only the first time:
	Database::configure('localhost', 'my_db', 'root', '123456');

	// Perform a query:
	$cursor = Database::sql("SELECT * FROM Users");

	// Perform several queries (return the result for the last one):
	$cursor = Database::sql(array(
		"INSERT INTO Users (Name) VALUES ('Fulanitez')",
		"INSERT INTO Users (Name) VALUES ('Fulanitez')",
		"SELECT * FROM Users",
	));

	// Escape values
	$search = Database::escape($_POST['search']);
	$cursor = Database::sql("SELECT * FROM Users WHERE Name Like '%$search%'");

 *
*/

class Database {

	static $log = array();
	static $link = null;
	static $n = 0;
	static $config = null;
	
	/**
	 * Disable instantiation methods
	*/
	private function __construct() {}
	private function __clone() {}
	public function __destruct() {
		if (is_resource($this->link) )
			mysqli_close($this->link);
	}

	private static function connect() {
		if (null !== self::$link) {
			return;
		}

		self::load_config();
		self::$link = new mysqli(
			self::$config['HOST'],
			self::$config['USER'],
			self::$config['PASSWORD'],
			self::$config['DATABASE']
		);

		if (self::$link->connect_errno) {
			self::$link = null;
			return;
		}

		self::$link->set_charset('utf8');
	}

	public static function escape($param) {
		self::connect();
		return self::$link->real_escape_string($param);
	}

	public static function getInsertId() {
		self::connect();
		return self::$link->insert_id;
	}

	public static function getAffectedRows() {
		self::connect();
		return self::$link->affected_rows;
	}

	public static function sql($sql) {
		self::connect();

		if (!is_array($sql)) {
			$sql = array($sql);
		}

		foreach ($sql as $s) {
			$result = self::one_sql($s);
		}

		return $result;
	}

	private static function one_sql($sql) {
		if (Config::get('DATABASE_LOG_ENABLED')) {
			self::$log[] = $sql;
		}

		$result = self::$link->query($sql);
		
		if (self::$link->errno) {
			self::$log[] = self::$link->error;
		}

		self::$n++;

		return $result;
	}
	
	public static function getN() {
		return self::$n;
	}
	
	public static function configure($HOST, $DATABASE, $USER, $PASSWORD) {
		$link = new mysqli($HOST, $USER, $PASSWORD, $DATABASE);

		if ($link->connect_errno) {
			return;
		}

		self::load_config();
		self::$config['HOST'] = $HOST;
		self::$config['DATABASE'] = $DATABASE;
		self::$config['USER'] = $USER;
		self::$config['PASSWORD'] = $PASSWORD;
		self::store_config();
	}

	// Implements Keepable
	public static function load_config() {
		if (self::$config == null) {
			self::$config = Keep::read(__CLASS__);
			if (self::$config===null) {
				self::$config = array(
					'HOST' => 'unconfigured host',
					'USER' => 'unconfigured user',
					'PASSWORD' => 'unconfigured password',
					'DATABASE' => 'unconfigured database',
				);
			}
		}
	}

	// Implements Keepable
	public static function store_config() {
		Keep::write(__CLASS__, self::$config);
	}
}
