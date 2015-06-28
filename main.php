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
 * How to use `generate_import`:

	// statically loaded files:
	$statics = array(
	'core.config',
	'utils.json',
	'utils.dom',
	);

	// Generate importer:
	$import = generate_import($statics);

	// Import files:
	$import('utils.date');

 * NOTICE: $import should be a global variable
 *
 * TODO: performance analysis
 *
 * TODO: static import with [[import package.a.b.c.file]]
 *
 * DESIGN NOTES: $statics is not accesible outside generate_import scope.
 * `$import` name is a convention, anyone can replace it (this is good and bad
 * at the same time). Other approach to avoid overwritting could be a function
 * called `import` with `$imported` as a static variable, and an optional
 * parameter to indicate whether import or only mark as imported.
*/

function generate_import($imported=array()) {
	return function ($chain) use (&$imported) {
		if (in_array($chain, $imported)) {
			return;
		}
		array_push($imported, $chain);

		$parts = explode('.', $chain);
		$file = array_pop($parts);

		$path = '';
		foreach ($parts as $part) {
			$path .= "packages/$part/";
		}

		require $path . "php/$file.php";
	};
}

function import($chain) {
	static $import93473a7344419b15c4219cc2b6c64c6f = null;
	if (null === $import93473a7344419b15c4219cc2b6c64c6f) {
		$import93473a7344419b15c4219cc2b6c64c6f = generate_import();
	}
	return $import93473a7344419b15c4219cc2b6c64c6f($chain);
}

import('core.Router');
import('core.ControllerPhp');

function go() {
	$url = $_SERVER['REQUEST_URI'];

	$hash = md5($url);

	if (!(@include('cache/'.$hash))) {
		Router::setUrl($url);

		switch( Router::$node->getProperty('type')) {
			case 'page':
			case 'root':
				ControllerPage::compile();
				break;
			case 'php':
				ControllerPhp::compile();
				break;
		}
	}
}
