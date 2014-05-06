<?php

namespace dump_r\Type;
use dump_r\Type;

class String extends Type {
	function chk_ref() {
		return false;
	}

	function get_len() {
		return strlen($this->raw);
	}
}