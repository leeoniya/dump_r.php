<?php

namespace dump_r\Rend;
use dump_r\Rend;

class Boolean extends Rend {
	public function get_val($node) {
		return $node->raw ? 'true' : 'false';
	}
}