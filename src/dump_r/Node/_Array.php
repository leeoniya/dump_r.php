<?php

namespace dump_r\Node;
use dump_r\Node, dump_r\Core, dump_r\Type;

class _Array extends Node {
	static $ref_key = '__ref_uid';

	public function chk_ref() {
		if (array_key_exists(self::$ref_key, $this->raw)) {
			$this->id = $this->raw[self::$ref_key];
			return true;
		}

		$this->id = Core::rand_str(16);
		$this->raw[self::$ref_key] = $this->id;
		Type::$dic[$this->id] = &$this->raw;

		return false;
	}

	public function get_len() {
		$len = count($this->nodes);

		if (isset($this->raw[self::$ref_key]))
			$len -= 1;

		return $len;
	}

	public function get_nodes() {
		return $this->raw;
	}

	public function disp_val() {
		return $this->ref ? '[*]' : '[ ]';
	}
}