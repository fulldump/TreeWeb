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

class Node {

	public $id = null;
	public $key = null;
	public $parent = null;
	public $properties = array();
	public $children = array();

	public function __construct() {
		$this->id = md5(microtime());
	}

	private function __toStringRec($deep=0, $name='?') {
		$indentation = str_repeat('    ', $deep);
		$this_id = $this->id;
		$parent_id = $this->parent == null ? 'null' : $this->parent->id;
		$properties = ' PROPERTIES'.json_encode($this->properties);


		$result = "$indentation $name: ID:$this_id PARENT_ID:$parent_id $properties\n";
		foreach ($this->children as $C=>$child) {
			$result .= $child->__toStringRec($deep+1, $C);
		}

		return $result;
	}

	public function __toString() {
		return $this->__toStringRec();
	}

	public function get($key) {
		if (!is_array($key)) {
			$key = explode('/', $key);
		}

		$top = array_shift($key);
		if (array_key_exists($top, $this->children)) {
			if (count($key)) {
				return $this->children[$top]->get($key);
			} else {
				return $this->children[$top];
			}
		}

		return null;
	}

	public function getById($id) {
		// Is this node?
		if ($id == $this->id) {
			return $this;
		}

		// Search children
		foreach ($this->children as $children) {
			$node = $children->getById($id);
			if ($node !== null) {
				return $node;
			}
		}

		return null;
	}

	public function insertBefore($key, $node) {

		if (null === $node) {
			return false;
		}

		if (null === $node->parent) {
			return false;
		}

		if (array_key_exists($key, $node->parent->children)) {
			return false;
		}

		if ($node->hasParent($this)) {
			return false;
		}

		$node_parent = $node->parent;

		$new_children = array();
		foreach($node->parent->children as $C=>$child) {
			if ($child->id == $node->id) {
				$new_children[$key] = $this;
			}
			if ($child->id != $this->id) {
				$new_children[$C] = &$node->parent->children[$C];
				$this->remove();
			}
		}

		$this->parent = $node_parent;
		$node_parent->children = $new_children;
		$this->key = $key;
		return true;
	}

	public function insertAfter($key, $node) {

		if (null === $node) {
			return false;
		}

		if (null === $node->parent) {
			return false;
		}

		if (array_key_exists($key, $node->parent->children)) {
			return false;
		}

		if ($node->hasParent($this)) {
			return false;
		}

		$new_children = array();
		foreach($node->parent->children as $C=>$child) {
			if ($child->id != $this->id) {
				$new_children[$C] = &$node->parent->children[$C];
				$this->remove();
			}
			if ($child->id == $node->id) {
				$new_children[$key] = $this;
			}
		}

		$this->parent = $node->parent;
		$node->parent->children = $new_children;
		$this->key = $key;
		return true;

	}

	public function append($key, Node $node) {
		// Sanitize
		if (false !== strpos($key, '/')) {
			return false;
		}

		if (array_key_exists($key, $this->children)) {
			return false;
		}

		if ($this->hasParent($node)) {
			return false;
		}

		$node->remove();
		$this->children[$key] = $node;
		$node->parent = $this;
		$node->key = $key;
		return true;
	}

	public function remove() {
		if ($this->parent == null) {
			return false;
		}

		foreach($this->parent->children as $C=>$child) {
			if ($child->id == $this->id) {
				unset($this->parent->children[$C]);
				break;
			}
		}

		$this->parent = null;

		return true;
	}

	public function hasParent($node) {
		if ($node === null) {
			return false;
		}

		$current = $this;
		while ($current !== null) {
			if ($node->id == $current->id) {
				return true;
			}
			$current = $current->parent;
		}

		return false;
	}

	public function toArray() {
		// Calculate children
		$children = array();
		foreach ($this->children as $C => $child) {
			$children[$C] = $child->toArray();
		}

		// Returl all
		return array(
			'id' => $this->id,
			'properties' => $this->properties,
			'children' => $children,
		);
	}

	public function fromArray($array) {
		$this->id = $array['id'];
		$this->properties = $array['properties'];
		$this->children = array();

		foreach ($array['children'] as $C=>$c) {
			$child = new Node();
			$child->key = $C;
			$child->parent = $this;
			$child->fromArray($c);
			$this->children[$C] = $child;
		}
	}

	public function getProperty($key) {

		$current = $this;
		while (null !== $current) {
			if (array_key_exists($key, $current->properties)) {
				return $current->properties[$key];
			}
			$current = $current->parent;
		}

		return null;
	}

	public function getInheritedProperties() {
		$used = array_keys($this->properties);

		$result = array();

		$current = $this->parent;
		while (null !== $current) {
			foreach($current->properties as $P=>$p) {
				if (!in_array($P, $used)) {
					$used[] = $P;
					$result[$P] = $p;
				}
			}
			$current = $current->parent;
		}

		return $result;
	}

	// TODO: Tests for this
	public function getKey() {
		if (null == $this->parent) {
			return null;
		}

		foreach ($this->parent->children as $key=>$child) {
			if ($this->id == $child->id) {
				return $key;
			}
		}

		return null;
	}

}
