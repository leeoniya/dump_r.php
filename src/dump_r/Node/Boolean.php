<?php

namespace dump_r\Node;
use dump_r\Node;

class Boolean extends Node {
	public function chk_ref() {
		return false;
	}

	public function disp_val() {
		return $this->raw ? 'true' : 'false';
	}
}