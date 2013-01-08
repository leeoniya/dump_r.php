<?php

namespace dump_r\Type;
use dump_r\Type;

class Array0 extends Type {
/*	// array reference/recusrion detection
	// copy-on-write dooms this :(
	public function chk_ref() {
		if (array_key_exists('*dump_r_ref', $this->raw) && array_key_exists($this->raw['*dump_r_ref'], Type::$dic)) {
			var_dump($this->raw['*dump_r_ref']);
			return true;
		}
		else {
			$this->id = uniqid();
			Type::$dic[$this->id] = true;
			$this->raw['*dump_r_ref'] = $this->id;		// temp set id in array
			return false;
		}
	}
*/
	function get_len() {
		return count($this->nodes);
	}

	function get_nodes() {
		return $this->raw;
	}
}