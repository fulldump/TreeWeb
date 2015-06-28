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

import('core.TreeScript');

function recursive_compare_rec($a, $b) {
	if (gettype($a) == gettype($b)) {
		if (is_array($a)) {
			foreach ($a as $k=>$v) {
				if (!array_key_exists($k, $b) || !recursive_compare_rec($v, $b[$k])) {
					return false;
				}
			}
			foreach ($b as $k=>$v) {
				if (!array_key_exists($k, $a) || !recursive_compare_rec($a[$k], $v)) {
					return false;
				}
			}
			return true;
		} else {
			return $a == $b;
		}
	}
	return false;
}

function deep_equal($t, $a, $b) {
	if (False === recursive_compare_rec($a, $b)) {
		$t->Error("This:\n".print_r($a, true)."\nShould be equals to: \n".print_r($b, true));
	}
}

Tests::add('Empty code', function(Test $t) {
	$code = '';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>''
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('Comment (one line)', function(Test $t) {
	$code = ' texto uno [[ this is a comment ]] texto dos';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'comment',
			'data'=> ' this is a comment '
		),
		array(
			'type'=>'text',
			'data'=>' texto dos'
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('Comment (with tab char)', function(Test $t) {
	$code = ' texto uno [[		this is a comment ]] texto dos';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'comment',
			'data'=> '		this is a comment '
		),
		array(
			'type'=>'text',
			'data'=>' texto dos'
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('Comment (multiple line)', function(Test $t) {
	$code = ' texto uno [[
this
is
a
comment
]] texto dos';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'comment',
			'data'=> '
this
is
a
comment
'
		),
		array(
			'type'=>'text',
			'data'=>' texto dos'
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('Comment (empty)', function(Test $t) {
	$code = ' texto uno [[]] texto dos';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'comment',
			'data'=> ''
		),
		array(
			'type'=>'text',
			'data'=>' texto dos'
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('Comment (unexpected end)', function(Test $t) {
	$code = ' text one [[ unexpected end of file';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' text one '
		),
		array(
			'type'=>'comment',
			'data'=> ' unexpected end of file'
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('One tag', function(Test $t) {
	$code = ' texto uno [[MI_ITEM]] texto dos';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'tag',
			'data'=> array(),
			'name'=>'MI_ITEM',
			'flags'=>array(),
		),
		array(
			'type'=>'text',
			'data'=>' texto dos'
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('One tag (blanks)', function(Test $t) {
	$code = ' texto uno [[MI_ITEM   
   	]] texto dos';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'tag',
			'data'=> array(),
			'name'=>'MI_ITEM',
			'flags'=>array(),
		),
		array(
			'type'=>'text',
			'data'=>' texto dos'
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('One tag (unexpected end)', function(Test $t) {
	$code = ' texto uno [[MI_ITEM';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'tag',
			'data'=> array(),
			'name'=>'MI_ITEM',
			'flags'=>array(),
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('Flags (no end)', function(Test $t) {
	$code = ' texto uno [[MI_ITEM flag]] texto dos ';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'tag',
			'data'=> array(),
			'name'=>'MI_ITEM',
			'flags'=>array(
				'flag',
			),
		),
		array(
			'type'=>'text',
			'data'=>' texto dos '
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('Flags (with space)', function(Test $t) {
	$code = ' texto uno [[MI_ITEM uno dos tres ]] texto dos ';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'tag',
			'data'=> array(),
			'name'=>'MI_ITEM',
			'flags'=>array(
				'uno',
				'dos',
				'tres'
				),
		),
		array(
			'type'=>'text',
			'data'=>' texto dos '
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('Flags (diverse)', function(Test $t) {
	$code = ' texto uno [[MI_ITEM 
	:uno 
	dos 	
	tres"
	"cuatro
	$cinco 	 ]] texto dos ';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'tag',
			'data'=> array(),
			'name'=>'MI_ITEM',
			'flags'=>array(
				':uno',
				'dos',
				'tres"',
				'"cuatro',
				'$cinco',
				),
		),
		array(
			'type'=>'text',
			'data'=>' texto dos '
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('Attributes (equal)', function(Test $t) {
	$code = ' texto uno [[MI_ITEM atributo=valor ]] texto dos ';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'tag',
			'data'=> array(
				'atributo'=>'valor'
			),
			'name'=>'MI_ITEM',
			'flags'=>array(),
		),
		array(
			'type'=>'text',
			'data'=>' texto dos '
		),
	);
	$result = TreeScript::getParse($code);
	$t->log(print_r($result, true));
	deep_equal($t, $reference, $result);
});

Tests::add('Attributes (colon)', function(Test $t) {
	$code = ' texto uno [[MI_ITEM atributo:valor ]] texto dos ';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'tag',
			'data'=> array(
				'atributo'=>'valor'
			),
			'name'=>'MI_ITEM',
			'flags'=>array(),
		),
		array(
			'type'=>'text',
			'data'=>' texto dos '
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('Attributes (no end)', function(Test $t) {
	$code = ' texto uno [[MI_ITEM atributo=valor]] texto dos ';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'tag',
			'data'=> array(
				'atributo'=>'valor'
			),
			'name'=>'MI_ITEM',
			'flags'=>array(),
		),
		array(
			'type'=>'text',
			'data'=>' texto dos '
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('Attributes (spaces)', function(Test $t) {
	$code = ' texto uno [[MI_ITEM atributo    =   valor]] texto dos ';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'tag',
			'data'=> array(
				'atributo'=>'valor'
			),
			'name'=>'MI_ITEM',
			'flags'=>array(),
		),
		array(
			'type'=>'text',
			'data'=>' texto dos '
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('Attributes (single quotes)', function(Test $t) {
	$code = ' texto uno [[MI_ITEM atributo = \'Habia una vez\']] texto dos ';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'tag',
			'data'=> array(
				'atributo'=>'Habia una vez'
			),
			'name'=>'MI_ITEM',
			'flags'=>array(),
		),
		array(
			'type'=>'text',
			'data'=>' texto dos '
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('Attributes (double quotes)', function(Test $t) {
	$code = ' texto uno [[MI_ITEM atributo = "Habia una vez" ]] texto dos ';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'tag',
			'data'=> array(
				'atributo'=>'Habia una vez'
			),
			'name'=>'MI_ITEM',
			'flags'=>array(),
		),
		array(
			'type'=>'text',
			'data'=>' texto dos '
		),
	);
	$result = TreeScript::getParse($code);
	deep_equal($t, $reference, $result);
});

Tests::add('Attributes (escaped chars)', function(Test $t) {
	$code = ' texto uno [[MI_ITEM
		simple = "aa \' aa"
		doble = \'bb " bb\'
		barra = \'cc \\ cc\'
		]] texto dos ';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'tag',
			'data'=> array(
				'simple'=>'aa \' aa',
				'doble'=>'bb " bb',
				'barra'=>'cc \\ cc'
			),
			'name'=>'MI_ITEM',
			'flags'=>array(),
		),
		array(
			'type'=>'text',
			'data'=>' texto dos '
		),
	);

	$result = TreeScript::getParse($code);
	$t->log(print_r($result,true));
	deep_equal($t, $reference, $result);
});

Tests::add('Attributes (Combined 1)', function(Test $t) {
	$code = ' texto uno [[MI_ITEM
	    flag_1
	    a=b
	    flag_2
	    flag_3
	    c= d
	    flag_4
	    e =f
		]] texto dos ';
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' texto uno '
		),
		array(
			'type'=>'tag',
			'data'=> array(
				'a'=>'b',
				'c'=>'d',
				'e'=>'f',
			),
			'name'=>'MI_ITEM',
			'flags'=>array(
				'flag_1',
				'flag_2',
				'flag_3',
				'flag_4',
			),
		),
		array(
			'type'=>'text',
			'data'=>' texto dos '
		),
	);

	$result = TreeScript::getParse($code);
	$t->log(print_r($result, true));
	deep_equal($t, $reference, $result);
});

Tests::add('Attributes (Combined 2)', function(Test $t) {
	$code = <<<FUCK
 1 [[MI_ITEM 
	 	a=''
	 	b="'"
		c="function(\$var='', \$ver='') {
			return \$var.\$ver;
		}"
	]] 2 
FUCK;
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' 1 '
		),
		array(
			'type'=>'tag',
			'data'=> array(
				'a'=>'',
				'b'=>'\'',
				'c'=>"function(\$var='', \$ver='') {
			return \$var.\$ver;
		}",
			),
			'name'=>'MI_ITEM',
			'flags'=>array(
			),
		),
		array(
			'type'=>'text',
			'data'=>' 2 '
		),
	);

	$result = TreeScript::getParse($code);
	$t->log(print_r($result, true));
	deep_equal($t, $reference, $result);
});

Tests::add('Attributes (repeated keys)', function(Test $t) {
	$code = <<<FUCK
 1 [[MI_ITEM 
	 	a='uno'
	 	a="dos"
	]] 2 
FUCK;
	$reference = array(
		array(
			'type'=>'text',
			'data'=>' 1 '
		),
		array(
			'type'=>'tag',
			'data'=> array(
				'a'=>'dos',
			),
			'name'=>'MI_ITEM',
			'flags'=>array(
			),
		),
		array(
			'type'=>'text',
			'data'=>' 2 '
		),
	);

	$result = TreeScript::getParse($code);
	$t->log(print_r($result, true));
	deep_equal($t, $reference, $result);
});

Tests::add('Multibyte', function(Test $t) {
	$code = <<<FUCK
ú [[MI_ITEM]] ú 1234567890
FUCK;

	$reference = array (
		0 => array(
			'type' => 'text',
			'data' => 'ú ',
		),
		1 => array(
			'type' => 'tag',
			'data' => array(),
			'name' => 'MI_ITEM',
			'flags' => array(),
		),
		2 => array(
			'type' => 'text',
			'data' => ' ú 1234567890',
		),
	);

	$result = TreeScript::getParse($code);
	$t->log(print_r($result, true));
	deep_equal($t, $reference, $result);
});

Tests::add('[[noparse]]', function(Test $t) {
	$t->log(time());

	$code = <<<FUCK

[[NAME1 attribute:value]]

[[noparse]]

[[NAME1 attribute:value]]

FUCK;

	$reference = array(
		0 => array (
			'type' => 'text',
			'data' => "\n",
		),
		1 => array (
			'type' => 'tag',
			'data' => array (
				'attribute' => 'value',
			),
			'name' => 'NAME1',
			'flags' => array (
			),
		),
		2 => array (
			'type' => 'text',
			'data' => "\n\n",
		),
		3 => array (
			'type' => 'noparse',
			'data' => array (
			),
			'name' => 'noparse',
			'flags' => array (
			),
		),
		4 => array (
			'type' => 'text',
			'data' => "\n\n[[NAME1 attribute:value]]\n",
		),
	);

	$result = TreeScript::getParse($code);
	$t->log(var_export($result, true));
	deep_equal($t, $reference, $result);
});

Tests::add('[[noparse a:b]]', function(Test $t) {
	$t->log(time());

	$code = <<<FUCK

[[NAME1 attribute:value]]

[[noparse a:b]]

[[NAME1 attribute:value]]

FUCK;

	$reference = array(
		0 => array (
			'type' => 'text',
			'data' => "\n",
		),
		1 => array (
			'type' => 'tag',
			'data' => array (
				'attribute' => 'value',
			),
			'name' => 'NAME1',
			'flags' => array (
			),
		),
		2 => array (
			'type' => 'text',
			'data' => "\n\n",
		),
		3 => array (
			'type' => 'noparse',
			'data' => array (
				'a' => 'b',
			),
			'name' => 'noparse',
			'flags' => array (
			),
		),
		4 => array (
			'type' => 'text',
			'data' => "\n\n[[NAME1 attribute:value]]\n",
		),
	);

	$result = TreeScript::getParse($code);
	$t->log(var_export($result, true));
	deep_equal($t, $reference, $result);
});

Tests::add('[[noparse a:b e]]', function(Test $t) {
	$t->log(time());

	$code = <<<FUCK

[[NAME1 attribute:value]]

[[noparse a:b e]]

[[NAME1 attribute:value]]

FUCK;

	$reference = array(
		0 => array (
			'type' => 'text',
			'data' => "\n",
		),
		1 => array (
			'type' => 'tag',
			'data' => array (
				'attribute' => 'value',
			),
			'name' => 'NAME1',
			'flags' => array (
			),
		),
		2 => array (
			'type' => 'text',
			'data' => "\n\n",
		),
		3 => array (
			'type' => 'noparse',
			'data' => array (
				'a' => 'b',
			),
			'name' => 'noparse',
			'flags' => array (
				0 => 'e',
			),
		),
		4 => array (
			'type' => 'text',
			'data' => "\n\n[[NAME1 attribute:value]]\n",
		),
	);

	$result = TreeScript::getParse($code);
	$t->log(var_export($result, true));
	deep_equal($t, $reference, $result);
});

