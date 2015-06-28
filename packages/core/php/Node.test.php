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
 *  Root
 *		A
 *			B
 *				C
 *				D
 *			E
 *				F
 *				G
 */
function buildBasicHierarchy() {
	$root = new Node();

	$a = new Node();	$root->append('a', $a);
		$b = new Node();	$a->append('b', $b);
			$c = new Node();	$b->append('c', $c);
			$d = new Node();	$b->append('d', $d);
		$e = new Node();	$a->append('e', $e);
			$f = new Node();	$e->append('f', $f);
			$g = new Node();	$e->append('g', $g);

	return $root;
}

function checkLinked($a, $b) {
	$key = md5(microtime());
	$value = 'CHECK_LINKED';
	$a->properties[$key] = $value;
	return $b->properties[$key] == $value;
}

function checkFatherhood($node) {
	foreach ($node->children as $C=>$child) {
		if ($child->parent->id != $node->id || !checkFatherhood($child)) {
			return false;
		}
	}
	return true;
}

function checkHasParent(&$t, $expected, $a, $b, $name_a='a', $name_b='b') {

	$not = $expected ? '' : ' NOT';

	if ($expected === $a->hasParent($b)) {
		$t->log("$name_a has$not parent $name_b\t\tOK");
	} else {
		$t->error("$name_a has$not parent $name_b\t\tERROR");
	}

}

import('core.Node');

Tests::add('Remove root node', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();

	// Run
	$result = $root->remove();

	// Check
	if ($result !== false) {
		$t->error("Result should be 'false'");
	}

	// Print
	$t->log($root);
});

Tests::add('Remove existing node', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();
	$b = $root->get('a/b');

	// Run
	$result = $b->remove();

	// Check
	if ($result !== true) {
		$t->error("Remove must return 'true' if success");
	}

	if ($b->parent != null) {
		$t->error("Removed-node's parent must be null");
	}

	if (!checkFatherhood($b)) {
		$t->error("Removed-node's fatherhood fails");
	}

	if (!checkFatherhood($root)) {
		$t->error("Root-node's fatherhood fails");
	}

	// Print
	$t->log($b);
	$t->log($root);
});

Tests::add('Check basic-hierarchy fatherhood', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();

	// Run & check
	if (!checkFatherhood($root)) {
		$t->error("BasicHierarachy has incoherences");
	}

	// Print
	$t->log($root);
});

Tests::add('Get node', function(Test $t) {
	// Prepare environment
	$root = buildBasicHierarchy();

	// Run test
	$e = $root->get('a/e');
	
	// Check
	if ($e->id != $root->children['a']->children['e']->id) {
		$t->error("The node is not the same");
	}

	if (!checkLinked($e, $root->children['a']->children['e'])) {
		$t->error("Nodes not linked");
	}

	// Print result
	$t->log($e);
	$t->log($root);
});

Tests::add('Append new node', function(Test $t) {
	// Prepare environment
	$root = new Node();
	$page1 = new Node();

	// Run test
	$root->append('page1', $page1);

	// Check

	if ($page1 != $root->children['page1']) {
		$t->error("page1 should be root->children['page1']");
	}
	if ('page1' != $page1->key) {
		$t->error("page1->key should be 'page1'");
	}

	// Print result
	$t->log($root);
});

Tests::add('Append new deep node', function(Test $t) {
	// Prepare environment
	$root = new Node();

	$page1 = new Node();
	$root->append('page1', $page1);

	$page2 = new Node();
	$page1->append('page2', $page2);

	// Run test
	$pageN = new Node();
	$root->append('page1/page2/pageN', $pageN);

	// Check
	if (null !== $root->key) {
		$t->error("root->key should be null");
	}
	if ('page1' !== $page1->key) {
		$t->error("page1->key should be page1");
	}
	if ('page2' !== $page2->key) {
		$t->error("page2->key should be page2");
	}

	// Print
	$t->log($root);
});

Tests::add('Append cyclic node', function(Test $t) {
	// Prepare environment
	$root = buildBasicHierarchy();
	$b = $root->get('a/b');

	// Run
	$result = $b->append('root', $root);

	// Check
	if (false !== $result) {
		$t->error("append() must return false, because the node to insert is a parent");
	}
	if ($root->key !== null) {
		$t->error("root->key must be null");
	}

	// Print
	$t->log($root);
});

Tests::add('Append existing node', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();
	$c = $root->get('a/b/c');
	$e = $root->get('a/e');

	// Run
	$c->append('e', $e);

	// Check
	if (!checkLinked($e, $c->get('e'))) {
		$t->error("Error inserting node 'e' below 'b'");
	}
	if (!checkLinked($e->parent, $c)) {
		$t->error("Parent of node 'e' MUST be 'c'");
	}
	if (null !== $root->get('a/e')) {
		$t->error("Node 'e' was NOT removed from 'a'");
	}
	if ($e->key !== 'e') {
		$t->error("e->key must be 'e'");
	}

	// Print
	$t->log($root);
});

Tests::add('Has parent', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();
	$a = $root->get('a');
	$b = $root->get('a/b');
	$c = $root->get('a/b/c');
	$e = $root->get('a/e');


	// Check

	// Should return true
	checkHasParent($t, true, $root, $root, '?', '?');

	checkHasParent($t, true, $a, $a, 'a', 'a');
	checkHasParent($t, true, $a, $root, 'a', '?');

	checkHasParent($t, true, $b, $b, 'b', 'b');
	checkHasParent($t, true, $b, $a, 'b', 'a');
	checkHasParent($t, true, $b, $root, 'b', '?');

	checkHasParent($t, true, $c, $c, 'c', 'c');
	checkHasParent($t, true, $c, $b, 'c', 'b');
	checkHasParent($t, true, $c, $a, 'c', 'a');
	checkHasParent($t, true, $c, $root, 'c', '?');

	checkHasParent($t, true, $e, $e, 'e', 'e');
	checkHasParent($t, true, $e, $a, 'e', 'a');
	checkHasParent($t, true, $e, $root, 'e', '?');

	// Should return false

	checkHasParent($t, false, $root, $a, '?', 'a');
	checkHasParent($t, false, $root, $b, '?', 'b');
	checkHasParent($t, false, $root, $c, '?', 'c');
	checkHasParent($t, false, $root, $e, '?', 'e');

	checkHasParent($t, false, $a, $b, 'a', 'b');
	checkHasParent($t, false, $a, $c, 'a', 'c');
	checkHasParent($t, false, $a, $e, 'a', 'e');

	checkHasParent($t, false, $b, $c, 'b', 'c');
	checkHasParent($t, false, $b, $e, 'b', 'e');

	checkHasParent($t, false, $c, $e, 'c', 'e');

	checkHasParent($t, false, $e, $b, 'e', 'b');
	checkHasParent($t, false, $e, $c, 'e', 'c');

	// Print
	$t->log($root);
});

Tests::add('Insert before null node', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();

	// Run
	$result = $root->insertBefore('my-key', null);

	// Check
	if (false !== $result) {
		$t->error("result must be false");
	}

	// Print
	$t->log($root);
});

Tests::add('Insert before null parent node', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();
	$z = new Node();

	// Run
	$result = $root->insertBefore('my-key', $z);

	// Check
	if (false !== $result) {
		$t->error("result must be false -> z does not have brother");
	}
	if (null !== $z->key) {
		$t->error("z->key must be null");
	}

	// Print
	$t->log($root);
});

Tests::add('Insert before existing key', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();
	$a = $root->get('a');

	// Run
	$result = $a->insertBefore('a', $a);

	// Check
	if (false !== $result) {
		$t->error("result must be false -> key already exists");
	}

	// Print
	$t->log($root);
});

Tests::add('Insert before OK', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();
	$a = $root->get('a');
	$z = new Node();

	// Run
	$result = $z->insertBefore('z', $a);

	// Check
	if ($z->parent->id != $a->parent->id) {
		$t->error("Parent is not correct");
	}
	if ('z' !== $z->key) {
		$t->error("z->key must be 'z'");
	}

	// Print
	$t->log($root);
});

Tests::add('Insert before the same', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();
	$a = $root->get('a');

	// Run
	$result = $a->insertBefore('a2', $a);

	// Check
	if ($a->parent->id != $root->id) {
		$t->error("Parent is not correct");
	}

	// Check
	if ($root->get('a') !== null) {
		$t->error("Key 'a' should NOT exist");
	}

	if ($root->get('a2') === null) {
		$t->error("Key 'a2' should exist");
	}

	if ('a2' !== $a->key) {
		$t->error("a->key should be 'a2'");
	}

	// Print
	$t->log($root);
});

Tests::add('Insert after OK', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();
	$a = $root->get('a');
	$z = new Node();

	// Run
	$result = $z->insertAfter('z', $a);

	// Check
	if ($z->parent->id != $a->parent->id) {
		$t->error("Parent is not correct");
	}
	if ('z' !== $z->key) {
		$t->error("z->key must be 'z'");
	}

	// Print
	$t->log($root);

});

Tests::add('Insert e before d', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();
	$e = $root->get('a/e');
	$d = $root->get('a/b/d');

	// Run
	$e->insertBefore('e', $d);

	// Check
	if (!checkLinked($e->parent, $d->parent)) {
		$t->error("The parent of 'e' is not correct");
	}
	if (!checkLinked($e, $root->get('a/b/e'))) {
		$t->error("'e' has not been inserted correctly");
	}

	if (null !== $root->get('a/e')) {
		$t->error("'e' has not been removed from origin");
	}

	if ('e' !== $e->key) {
		$t->error("e->key must be 'e'");
	}

	// Print
	$t->log($root);

});

Tests::add('Insert e after d', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();
	$e = $root->get('a/e');
	$d = $root->get('a/b/d');

	// Run
	$e->insertAfter('e', $d);

	// Check
	if (!checkLinked($e->parent, $d->parent)) {
		$t->error("The parent of 'e' is not correct");
	}
	if (!checkLinked($e, $root->get('a/b/e'))) {
		$t->error("'e' has not been inserted correctly");
	}
	if (null !== $root->get('a/e')) {
		$t->error("'e' has not been removed from origin");
	}
	if ('e' !== $e->key) {
		$t->error("e->key must be 'e'");
	}

	// Print
	$t->log($root);
});

Tests::add('Append parent - check destruction', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();
	$a = $root->get('a');
	$b = $root->get('a/b');

	// Run
	$result = $b->append('new_a', $a);

	// Check
	if (false !== $result) {
		$t->error("Must return false");
	}

	// Print
	$t->log($root);
});

Tests::add('Insert before - check destruction', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();
	$a = $root->get('a');
	$b = $root->get('a/b');

	// Run
	$result = $a->insertBefore('new_a', $b);

	// Check
	if (false !== $result) {
		$t->error("Must return false");
	}

	// Print
	$t->log($root);
});

Tests::add('Insert after - check destruction', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();
	$a = $root->get('a');
	$b = $root->get('a/b');

	// Run
	$result = $a->insertAfter('new_a', $b);

	// Check
	if (false !== $result) {
		$t->error("Must return false");
	}

	// Print
	$t->log($root);
});

Tests::add('fromArray toArray', function(Test $t) {
	// Prepare
	$json = '{"id":"c170ad596fb6f987f0b34bf58099ca63","properties":[],"children":{"a":{"id":"2d63f222710222663373b0692b273103","properties":[],"children":{"b":{"id":"dc3a2e4652f068bad6f78e71920483f8","properties":[],"children":{"c":{"id":"63f5f8032383f518381541e2053b9213","properties":[],"children":[]},"d":{"id":"1f3b9cf313b126f9ed03739730d58eed","properties":[],"children":[]}}},"e":{"id":"5049e43d9fd9576aa0e715d9bd8dea20","properties":[],"children":{"f":{"id":"f8a45a93ed68521a7d4c23b7918b13b3","properties":[],"children":[]},"g":{"id":"a95c67d9ebdbc4f3fb175242fb1307eb","properties":[],"children":[]}}}}}}}';
	$array = json_decode($json, true);

	// Run
	$root = new Node();
	$root->fromArray($array);
	$result = $root->toArray();

	$result_json = json_encode($result);

	// Check
	if ($json !== $result_json) {
		$t->error("Serialization fail");
	}

	// Print
	$t->log("$json\n$result_json");

});

Tests::add('get property', function(Test $t) {
	// Prepare
	$hash_red = md5(microtime());
	$hash_blue = md5(microtime());
	$hash_pink = md5(microtime());

	$root = buildBasicHierarchy();
	$a = $root->get('a');
	$b = $root->get('a/b');
	$c = $root->get('a/b/c');
	$d = $root->get('a/b/d');
	$e = $root->get('a/e');
	$f = $root->get('a/e/f');
	$g = $root->get('a/e/g');

	$root->properties['red'] = $hash_red;
	$b->properties['blue'] = $hash_red;
	$e->properties['red'] = $hash_pink;

	// Run & check
	if (false
		|| $root->getProperty('red') !== $hash_red
		|| $a->getProperty('red') !== $hash_red
		|| $b->getProperty('red') !== $hash_red
		|| $c->getProperty('red') !== $hash_red
		|| $d->getProperty('red') !== $hash_red
	) {
		$t->error("red must be hash_red in nodes root, a, b, c, d");
	}

	if (false
		|| $e->getProperty('red') !== $hash_pink
		|| $f->getProperty('red') !== $hash_pink
		|| $g->getProperty('red') !== $hash_pink
	) {
		$t->error("red must be hash_pink in nodes e, f, g");
	}

	if (false
		|| $root->getProperty('blue') !== null
		|| $a->getProperty('blue') !== null
		|| $e->getProperty('blue') !== null
		|| $f->getProperty('blue') !== null
		|| $g->getProperty('blue') !== null
	) {
		$t->error("blue must be null in nodes root, a, e, f, g");
	}

	if (false
		|| $a->getProperty('blue') !== null
		|| $e->getProperty('blue') !== null
		|| $f->getProperty('blue') !== null
		|| $g->getProperty('blue') !== null
	) {
		$t->error("blue must be hash_blue in nodes b,c,d");
	}

	// Print
	$t->log($root);
});

Tests::add('get inherited properties', function(Test $t) {
	// Prepare
	$hash_red = md5(microtime());
	$hash_green = md5(microtime());
	$hash_blue = md5(microtime());
	$hash_black = md5(microtime());

	$root = buildBasicHierarchy();
	$a = $root->get('a');
	$b = $root->get('a/b');
	$c = $root->get('a/b/c');

	$root->properties['red'] = $hash_red;
	$a->properties['green'] = $hash_green;
	$b->properties['blue'] = $hash_blue;
	$c->properties['red'] = $hash_black;

	// Run
	$result = $c->getInheritedProperties();

	// Check
	if (array_key_exists('red', $result)) {
		$t->error("'red' is not an inherited property because has been redefined");
	}

	if ($result['blue'] !== $hash_blue) {
		$t->error("'blue' must be an inherited property");
	}

	if ($result['green'] !== $hash_green) {
		$t->error("'green' must be an inherited property");
	}

	// Print
	$t->log(print_r($result, true));
	$t->log($root);
});

Tests::add('Get by id - base case', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();

	// Run
	$node = $root->getById($root->id);

	// Check
	if ($node !== $root) {
		$t->error("returned node must be the root node");
	}

	// Print
	$t->log("Node id: ".$node->id);
	$t->log($root);

});

Tests::add('Get by id - recursive case', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();

	// Run
	$node = $root->getById($root->children['a']->id);

	// Check
	if ($node !== $root->children['a']) {
		$t->error("returned node must be the 'a' node");
	}

	// Print
	$t->log($node->id);
	$t->log($root);
});

Tests::add('Get by id - unexisting node', function(Test $t) {
	// Prepare
	$root = buildBasicHierarchy();

	// Run
	$node = $root->getById('unexisting id');

	// Check
	if ($node !== null) {
		$t->error("returned node must be null");
	}

	// Print
	$t->log($root);
});
