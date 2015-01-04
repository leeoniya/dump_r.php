<?php

namespace dump_r\Node\String\JSON;
use dump_r\Node\String\JSON;

class Array0 extends JSON {
	public function get_len() {
		return count($this->nodes);
	}
}