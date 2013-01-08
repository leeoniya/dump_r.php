<?php

namespace dump_r\Rend;
use dump_r\Rend;

class Array0 extends Rend {
	public function get_val($node) {
		return $node->ref ? '[*]' : '[ ]';
	}
}