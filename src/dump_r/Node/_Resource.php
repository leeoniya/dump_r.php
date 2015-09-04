<?php

namespace dump_r\Node;
use dump_r\Node;

class _Resource extends Node {
/*
	public function get_id() {
		return intval($this->raw);
	}
*/
	public function disp_val() {
		return $this->ref ? '<*>' : '< >';
	}
}
