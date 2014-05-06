<?php

namespace dump_r\Type;
use dump_r\Type, dump_r\Core;

class Array0 extends Type {
	static $ref_key = '__ref_uid';

	function chk_ref() {
		if (array_key_exists(self::$ref_key, $this->raw)) {
			$this->id = $this->raw[self::$ref_key];
			return true;
		}

		$this->id = Core::rand_str(16);
		$this->raw[self::$ref_key] = $this->id;
		Type::$dic[$this->id] = &$this->raw;

		return false;
	}

	function get_len() {
		return count($this->nodes);
	}

	function get_nodes() {
		return array_slice($this->raw, 0, count($this->raw) - 1);
	}
}