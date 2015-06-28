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

import('core.Config');
import('core.Node');

class Router {

	public static $filename = 'router.json';
	public static $root = null;

	public static $url;
	public static $parts;
	public static $parameters;
	public static $language;
	public static $node;

	private function __construct() {}

	public static function setUrl($url) {
		self::load();

		// TODO: [OPTIONAL] save previous state in $history

		// Reset all static vars
		self::$url = $url;
		self::$parts = array();
		self::$parameters = array();
		self::$language = '';
		self::$node = null;

		// Calculate vars
		self::_preprocess_url();
		self::_extract_language();
		self::_search_node();
		self::_apply_permissions();
	}

	public static function export() {
		return '<?php '
		.'Router::load();'
		// .'Router::$root = new Node();'
		// .'Router::$root->fromArray('.var_export(self::$root->toArray(), true).');'
		.'Router::$node = Router::$root->getById('.var_export(self::$node->id, true).');'
		.'Router::$url = '.var_export(self::$url, true).';'
		.'Router::$parts = '.var_export(self::$parts, true).';'
		.'Router::$parameters = '.var_export(self::$parameters, true).';'
		.'Router::$language = '.var_export(self::$language, true).';'
		.'?>';
	}

	public static function getNodeUrl($node, $language=null) {
		if (null === $language) {
			$language = self::$language;
		}

		$parts = array();

		$default_page_id = Config::get('DEFAULT_PAGE');

		$current = $node;
		while (null !== $current->parent && $default_page_id != $current->id) {
			$parts[] = $current->key;
			$current = $current->parent;
		}

		$url = '/'.implode('/', array_reverse($parts));

		if ($language != Config::get('DEFAULT_LANGUAGE')) {
			$url = '/'.$language.$url;
		}

		$query = http_build_query($_GET);
		if ('' != $query) {
			$url .= '?'.$query;
		}

		return $url;
	}

	private static function _preprocess_url() {
		// Parse url
		$parse = parse_url('http://dummy:80'.self::$url);
		$path = $parse['path'];
		$query = @$parse['query'];

		// Split by '/'
		self::$parts = explode('/', $path);


		// Remove first if empty
		if (count(self::$parts) && '' === self::$parts[0]) {
			array_shift(self::$parts);
		}

		// Remove last if empty
		if (count(self::$parts) && '' === end(self::$parts)) {
			array_pop(self::$parts);
		}

		// Decode url parts
		foreach (self::$parts as $i=>$part) {
			self::$parts[$i] = rawurldecode($part);
		}
	}

	private static function _extract_language() {
		$default_language = Config::get('DEFAULT_LANGUAGE');
		$available_languages = explode(',', Config::get('AVAILABLE_LANGUAGES'));
		$tentative_language = self::$parts[0];
		if ($tentative_language != $default_language && in_array($tentative_language, $available_languages)) {
			array_shift(self::$parts);
			self::$language = $tentative_language;
		} else {
			self::$language = $default_language;
		}
	}

	private static function _select_starting_node() {
		$default_node = self::$root->getById(Config::get('DEFAULT_PAGE'));

		// Only to fix the extreme case with corrupted configuration
		if (null === $default_node) {
			return self::$root;
		}

		// If requested path is '/'
		if (!count(self::$parts)) {
			return $default_node;
		}

		// Match root level first
		$part_0 = self::$parts[0];
		$default_node_id = $default_node->id;
		foreach (self::$root->children as $k=>$child) {
			if ($k == $part_0 && $child->id != $default_node_id) {
				return self::$root;
			}
		}

		return $default_node;
	}

	private static function _search_node() {
		self::$node = self::_select_starting_node();

		foreach (self::$parts as $part) {

			$found = false;
			foreach (self::$node->children as $key=>$node) {
				if (self::_is_parameter($key)) {
					self::$parameters[$key] = $part;
					$found = true;
					break;
				} else if ($key == $part) {
					$found = true;
					break;
				}
			}
			
			if ($found) {
				self::$node = $node;
				array_shift(self::$parts);
			} else {
				break;
			}

		}
	}

	private static function _have_access() {
		$permissions = self::$node->getProperty('permissions');
		if (null == $permissions ) {
			return false;
		}

		$permissions = json_decode($permissions, true);
		if (false === $permissions) {
			return false;
		}

		if (in_array('*', $permissions)) {
			return true;
		}

		$user = Session::getUser();
		if (null === $user) {
			return false;
		}

		$groups = json_decode($user->getGroups(), true);
		if (false === $groups) {
			return false;
		}

		if (in_array('*', $groups)) {
			return true;
		}

		if (count(array_intersect($permissions, $groups))) {
			return true;
		}

		return false;
	}

	private static function _apply_permissions() {
		if ( !self::_have_access() ) {
			http_response_code(403);
			self::$node = Router::$root->getById(Config::get('403_PAGE'));
		}
	}

	private static function _is_parameter($part) {
		return '{' == mb_substr($part, 0, 1) && mb_substr($part, -1, 1) == '}';
	}

	private static function _node_match($node, $key) {
		if (null === $node) {
			return false;
		}

		foreach ($node->children as $k=>$child) {
			if ($k == $key || self::_is_parameter($k)) {
				return true;
			}
		}

		return false;
	}

	public static function load() {
		if (null !== self::$root) {
			return;
		}

		// // OLD LOAD
		// self::$root = new Node();

		// $content = @file_get_contents(self::$filename);
		// $data = json_decode($content, true);
		// if (null !== $data) {
		// 	self::$root->fromArray($data);
		// }

		// NEW LOAD
		@include(self::$filename);
	}

	public static function save() {
		// // OLD SAVE
		// file_put_contents(
		// 	self::$filename,
		// 	json_encode(self::$root->toArray(), Config::get('JSON_ENCODE_OPTIONS')));

		// NEW SAVE
		$output = '<?php Router::$root = new Node(); Router::$root->fromArray('.var_export(self::$root->toArray(), true).');';
		file_put_contents(self::$filename,$output);
		file_put_contents(self::$filename, php_strip_whitespace(self::$filename));
	}

	public static function toString() {
		return '<pre>'
			.print_r(self::$url, true)."\n"
			.print_r(self::$parts, true)."\n"
			.print_r(self::$parameters, true)."\n"
			.print_r(self::$language, true)."\n"
			.self::$node."\n"
			.'</pre>';
	}

}
