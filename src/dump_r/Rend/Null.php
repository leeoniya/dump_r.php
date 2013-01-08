<?php

namespace dump_r\Rend;
use dump_r\Rend;

class Null extends Rend {
	public function get_val($node) {
		return 'null';
	}
}