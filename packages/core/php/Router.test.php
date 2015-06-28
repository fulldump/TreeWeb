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

function build_basic_hierarchy() {
	$root = new Node();

	$a = new Node();	$root->append('a', $a);
		$b = new Node();	$a->append('b', $b);
			$c = new Node();	$b->append('c', $c);
			$d = new Node();	$b->append('d', $d);
		$e = new Node();	$a->append('e', $e);
			$f = new Node();	$e->append('f', $f);
			$g = new Node();	$e->append('g', $g);

	$a->properties['permissions'] = '["*"]';

	return $root;
}

function get_default_language() {
	$default_language = Config::get('DEFAULT_LANGUAGE');
	return $default_language;
}

function get_not_default_language() {
	$default_language = get_default_language();
	$available_languages = explode(',', Config::get('AVAILABLE_LANGUAGES'));

	// Find other language different from the default one
	$other_language = '';
	foreach ($available_languages as $al) {
		if ($al != $default_language) {
			$other_language = $al;
		}
	}
	return $other_language;	
}

function get_not_existing_language() {
	$available_languages = explode(',', Config::get('AVAILABLE_LANGUAGES'));
	return implode('', $available_languages);
}

import('core.Router');

Tests::add('Get default language', function(Test $t) {
	// Prepare
	Router::$root = build_basic_hierarchy();
	$default_language = get_default_language();

	// Run
	$router = Router::setUrl("/$default_language/example/path");

	// Check
	if ($default_language != Router::$language) {
		$t->error("Default language does not match");
	}
	if (Router::$parts[0] != $default_language) {
		$t->error("Url must not be consumed");
	}

	// Print
	$t->log(Router::toString());
});

Tests::add('Get not default language', function(Test $t) {
	// Prepare
	Router::$root = build_basic_hierarchy();
	$not_default_language = get_not_default_language();

	// Run
	$router = Router::setUrl("/$not_default_language/example/path");

	// Check
	if ($not_default_language != Router::$language) {
		$t->error("Language does not match");
	}
	if (Router::$parts[0] != 'example') {
		$t->error("Url must be consumed");
	}

	// Print
	$t->log(Router::toString());
});

Tests::add('Get not existing language', function(Test $t) {
	// Prepare
	Router::$root = build_basic_hierarchy();
	$default_language = get_default_language();
	$not_existing_language = get_not_existing_language();

	// Run
	$router = Router::setUrl("/$not_existing_language/example/path");

	// Check
	if ($default_language != Router::$language) {
		$t->error("Language does not match");
	}

	if (Router::$parts[0] != $not_existing_language) {
		$t->error("Url must NOT be consumed");
	}

	// Print
	$t->log(Router::toString());
});

Tests::add('Get existing node', function(Test $t) {
	// Prepare
	Router::$root = build_basic_hierarchy();

	// Run
	$router = Router::setUrl('/a/b/c');

	// Check
	if (0 != count(Router::$parts)) {
		$t->error("All parts should be consumed");
	}

	if (Router::$node->id != Router::$root->get('a/b/c')->id) {
		$pass = false;
		echo "Returned node is not the correct one\n";
	}

	// Print
	$t->log(Router::$node);
	$t->log(Router::toString());
});

Tests::add('Combined test - get language and node', function(Test $t) {
	// Prepare
	Router::$root = build_basic_hierarchy();
	$not_default_language = get_not_default_language();

	// Run
	$router = Router::setUrl("/$not_default_language/a/b");

	// Check
	if (0 != count(Router::$parts)) {
		$t->error("All parts must be consumed");
	}

	if (Router::$node->id != Router::$root->get('a/b')->id) {
		$t->error("Returned node is not the correct one");
	}

	if ($not_default_language != Router::$language) {
		$t->error("Language does not match");
	}

	// Print
	$t->log(Router::toString());
});

Tests::add('Get parametrized', function(Test $t) {
	// Prepare
	Router::$root = $root = build_basic_hierarchy();
	$a = $root->get('a');
	$e = $root->get('a/e');
	$e->remove();
	$a->append('{parameter}', $e);

	// Run
	$router = Router::setUrl("/a/my-parameter/f");

	// Check
	if (0 != count(Router::$parts)) {
		$t->log("All parts must be consumed");
	}

	if (1 != count(Router::$parameters)) {
		$t->log("Must be 1 parameter");
	}

	if (Router::$parameters['{parameter}'] !== 'my-parameter') {
		$t->log("parameters['{parameter}'] MUST BE 'my-parameter'");
	}

	// Print
	$t->log($root);
	$t->log(Router::toString());
});
