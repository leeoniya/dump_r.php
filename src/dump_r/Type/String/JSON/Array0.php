<?php

namespace dump_r\Type\String\JSON;
use dump_r\Type\String\JSON;

class Array0 extends JSON {
	function get_len() {
		return count($this->nodes);
	}
}