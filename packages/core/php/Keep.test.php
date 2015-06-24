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

define('filename', '.keep_test');

function change_filename() {
	Keep::$filename = filename;
}

function clean_file() {
	if (file_exists(filename)) {
		unlink(filename);
	}
}

Tests::add('Read default value', function(Test $t) {
	change_filename();
	clean_file();

	if (null !== Keep::read('key')) {
		$t->error('Unexisting value should return null');
	}

});

Tests::add('Write something', function(Test $t) {
	change_filename();
	clean_file();

	$value = 'something';

	Keep::write('key', $value);

	if (file_get_contents(filename) != 'a:1:{s:3:"key";s:9:"something";}') {
		$t->error('A file should be written with php serialization format.');
	}

	clean_file();
});


Tests::add('Inverse operations', function(Test $t) {
	change_filename();

	$value = 'something';

	Keep::write('key', $value);

	if ($value !== Keep::read('key')) {
		$t->error('read should return the same value as written');
	}

	clean_file();
});

Tests::add('Remove key', function(Test $t) {
	change_filename();

	$value = 'something';

	Keep::write('key', $value);

	Keep::remove('key');

	if (file_get_contents(filename) != 'a:0:{}') {
		$t->error('Remove should drop information from file.');
	}

	clean_file();
});

Tests::add('Remove only one key', function(Test $t) {
	change_filename();


	$value_a = 'value a';
	Keep::write('a', $value_a);

	$value_b = 'value b';
	Keep::write('b', $value_b);

	Keep::remove('a');

	if (file_get_contents(filename) != 'a:1:{s:1:"b";s:7:"value b";}') {
		$t->error('Remove should drop only affected key from file.');
	}

	clean_file();
});

Tests::add('Reuse file reads', function(Test $t) {
	change_filename();
	clean_file();

	$filedata = 'a:2:{s:1:"b";s:7:"value b";s:1:"a";s:7:"value a";}';
	file_put_contents(filename, $filedata);

	Keep::read('a');

	clean_file();

	if ('value b' !== Keep::read('b')) {
		$t->error('read should open the file once');
	}

});


