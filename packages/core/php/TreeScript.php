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
 * TreeScript
 * Description: Parse TreeWeb tags
 * Typical use:

	$tokens = TreeScript::getParse($code);
 
 *
*/

class TreeScript {

	public $code = '';
	public $code_length = 0;
	public $errors = array();
	public $i = 0;
	public $tokens = array();
	public $token = array(); // Last token
	public $attribute = '';
	public $value = '';
	public $noparse = false;
	public $encoding = '';

	public function __construct(&$code, $encoding='UTF-8') {
		$this->encoding = $encoding;

		$this->code = &$code;
		$this->code_length = mb_strlen($code, $this->encoding);
	}

	public function parse() {
		$this->i = 0; // Initialize position
		$this->noparse = false;
		$this->tokens = array();
		$this->textScope();
	}

	private function textScope() {
		if ($this->noparse) {
			$find = false;
		} else {
			$find = $p = mb_strpos($this->code,'[[',$this->i, $this->encoding);
		}

		if ($find===false) {
			$p = $this->code_length;
		}
		$this->tokens[] = array(
			'type'=>'text',
			'data'=>mb_substr($this->code,$this->i, $p-$this->i, $this->encoding),
		);

		$this->i = $p+2;
		if (!$this->isEnd()) {
			$this->token = array(
				'type'=>'tag',
				'data'=>array(),
				'name'=>'',
				'flags'=>array(),
			);
			$this->tagStart();
		}
	}

	private function tagStart() {
		$c = mb_substr($this->code, $this->i, 1, $this->encoding);
		$cc = mb_substr($this->code, $this->i, 2, $this->encoding);

		if ($c == ' ' || $c == "\n" || $c == "\t" || $cc == ']]') {
			$this->comment();
		} else {
			$this->token['name'] = $c;
			$this->i++;
			$this->tagName();
		}
	}

	private function tagName() {
		$c = mb_substr($this->code, $this->i, 1, $this->encoding);
		$cc = mb_substr($this->code, $this->i, 2, $this->encoding);

		if ($c == ' ' || $c == "\n" || $c == "\t") {
			$this->checkNoparse();
			$this->i++;
			$this->tagAttributeStart();
		} else if ($cc == ']]') {
			$this->checkNoparse();
			$this->tokens[] = $this->token;
			$this->i += 2;
			$this->textScope();
		} else if ($this->isEnd()) {
			$this->errors[] = 'Early end of file. Expected ]] ';
			$this->tokens[] = $this->token;
		} else {
			$this->token['name'] .= $c;
			$this->i++;
			$this->tagName();
		}
	}

	private function tagAttributeStart() {
		$c = mb_substr($this->code, $this->i, 1, $this->encoding);
		$cc = mb_substr($this->code, $this->i, 2, $this->encoding);

		if ($c == ' ' || $c == "\n" || $c == "\t") {
			$this->i++;
			$this->tagAttributeStart();
		} else if ($cc == ']]') {
			$this->tokens[] = $this->token;
			$this->i += 2;
			$this->textScope();
		} else if ($this->isEnd()) {
			$this->errors[] = 'Early end of file. Expected ]] ';
			$this->tokens[] = $this->token;
		} else {
			$this->attribute = $c;
			$this->i++;
			$this->tagAttribute(false);
		}
	}

	private function tagAttribute($space=false) {
		$c = mb_substr($this->code, $this->i, 1, $this->encoding);
		$cc = mb_substr($this->code, $this->i, 2, $this->encoding);

		if ($c == ' ' || $c == "\n" || $c == "\t") {
			$this->i++;
			$this->tagAttribute(true);
		} else if ($c == ':' || $c == '=') {
			$this->i++;
			$this->tagValueStart();
		} else if ($cc == ']]') {
			$this->token['flags'][] = $this->attribute;
			$this->tagAttributeStart();
		} else if ($this->isEnd()) {
			$this->errors[] = 'Early end of file. Expected ]] ';
			$this->tokens[] = $this->token;
		} else {
			if ($space) {
				$this->token['flags'][] = $this->attribute;
				$this->attribute = '';
			}
			$this->attribute .= $c;
			$this->i++;
			$this->tagAttribute(false);
		}
	}

	private function tagValueStart() {
		$c = mb_substr($this->code, $this->i, 1, $this->encoding);
		$cc = mb_substr($this->code, $this->i, 2, $this->encoding);

		$this->value = '';

		if ($c == ' ' || $c == "\n" || $c == "\t") {
			$this->i++;
			$this->tagValueStart();
		} else if ($cc == ']]') {
			// $this->tagAttribute();
			$this->tokens[] = $this->token;
			$this->i += 2;
			$this->textScope();
		} else if ($c == '"' || $c == "'") {
			$this->i++;
			$this->tagValueQuotes($c);
		} else if ($this->isEnd()) {
			$this->errors[] = 'Early end of file. Expected value or ]].';
			$this->i++;
			$this->$tagValueEscape();
		} else {
			$this->value = $c;
			$this->i++;
			$this->tagValue();
		}
	}

	private function tagValue() {
		$c = mb_substr($this->code, $this->i, 1, $this->encoding);
		$cc = mb_substr($this->code, $this->i, 2, $this->encoding);

		if ($c == ' ' || $c == "\n" || $c == "\t") {
			$this->token['data'][$this->attribute] = $this->value;
			$this->i++;
			$this->tagAttributeStart();
		} else if ($cc==']]') {
			$this->token['data'][$this->attribute] = $this->value;
			$this->tagValueStart();
		} else {
			$this->value .= $c;
			$this->i++;
			$this->tagValue();
		}
		
	}

	private function tagValueQuotes(&$quote) {
		$c = mb_substr($this->code, $this->i, 1, $this->encoding);
		$cc = mb_substr($this->code, $this->i, 2, $this->encoding);

		if ($c == $quote) {
			$this->i++;
			$this->token['data'][$this->attribute] = $this->value;
			$this->tagAttributeStart();
		} else if ($cc == '\\\\' || $cc == '\\"' || $cc == '\\\'') {
			$this->i++;
			$d = mb_substr($this->code, $this->i, 1, $this->encoding);
			$this->value .= $d;
			$this->i++;
			$this->tagValueQuotes($quote);
		} else {
			$this->value .= $c;
			$this->i++;
			$this->tagValueQuotes($quote);
		}
	}

	private function checkNoparse() {
		if ('noparse' == $this->token['name']) {
			$this->noparse = true;
			$this->token['type'] = 'noparse';
		}
	}

	private function comment() {
		$find = $p = mb_strpos($this->code,']]',$this->i, $this->encoding);
		if ($find === false) {
			$p = $this->code_length;
		}
		$this->tokens[] = array(
			'type'=>'comment',
			'data'=>mb_substr($this->code,$this->i, $p-$this->i, $this->encoding),
		);
		$this->i = $p+2;
		if ($find) {
			$this->textScope();
		}
	}

	private function isEnd() {
		return $this->i >= $this->code_length;
	}

	public static function getParse(&$code, $encoding='UTF-8') {
		$treescript = new TreeScript($code, $encoding);
		$treescript->parse();
		return $treescript->tokens;
	}

}


