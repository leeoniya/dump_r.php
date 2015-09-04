<?php

namespace dump_r\Node;
use dump_r\Node;

class _Null extends Node {
	public function chk_ref() {
		return false;
	}

	public function disp_val() {
		return 'null';
	}
}