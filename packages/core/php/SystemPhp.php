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

/**
 * Class: SystemComponent
 * Location: class/storm/SystemComponent.class.php
 * Date: Sun, 28 Aug 2011 02:03:33 +0200
 * Author: gerardooscarjt@gmail.com
 * Typical use:
 * $my_component = SystemComponent::INSERT('my_component');
 * $my_component->setPHP('<? echo 'Hello world'; ?>');
 * $my_component->setCSS('* { color: red; }');
 * $my_component->setJS('alert(99)');
 * ...
*/
	
class SystemPhp {

	protected static $dir_base = 'php';
	
	protected static $data = array();
	protected $name = '';
	protected $php = null;
	
	public function __construct($name) {
		$this->name = $name;
	}

	public static function SELECT() {
		$select = array();

		$path = self::$dir_base.'/';
		$d = dir($path);
		while (false !== ($entry = $d->read())) {
			$entry = pathinfo ($entry);
			if ('php' == $entry['extension']) {
				$select[] = $entry['filename'];
			}
		}
		$d->close();

		return $select;
	}
	
	/**
	 * VULNERABILITY: User could create files outside the folder 'component/'
	*/
	public static function INSERT($name) {
		$filename = self::$dir_base.'/'.$name.'.php';

		if (file_exists($filename)) {
			return null;
		} else {
			file_put_contents($filename, '<?php echo(\'Your PHP here\'); ?>'); chmod($filename, 0777);
			return self::get($name);
		}
	}
	
	public function DELETE($physical=true) {
		// TODO: check if this is being used
		// TODO: remove
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getPHP() {
		if (null === $this->php) {
			$this->php = file_get_contents(self::$dir_base.'/'.$this->name.'.php');
		}
		return $this->php;
	}
	
	public function setPHP($code) {
		return file_put_contents(self::$dir_base.'/'.$this->name.'.php', $code);
	}

	public static function get($name) {
		if (!array_key_exists($name, self::$data)) {
			$filename = self::$dir_base.'/'.$name.'.php';
			if (file_exists($filename)) {
				self::$data[$name] = new SystemPhp($name);
			} else {
				return null;
			}
		}
		return self::$data[$name];
	}

	public function getFilename() {
		return self::$dir_base.'/'.$this->name.'.php';
	}

}
