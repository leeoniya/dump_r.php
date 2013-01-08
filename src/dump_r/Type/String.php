<?php

namespace dump_r\Type;
use dump_r\Type;

class String extends Type {
	function get_len() {
		return strlen($this->raw);
	}
}