<?php

namespace dump_r\Type;
use dump_r\Type;

class Resource extends Type {
/*
	public function get_id() {
		return intval($this->raw);
	}
*/
	public function chk_ref() {
/*
		$this->id = intval($this->raw);

		if (array_key_exists($this->id, Type::$dic))
			return true;
		else
			Type::$dic[$this->id] = $this->id;
*/
		return false;
	}
}
