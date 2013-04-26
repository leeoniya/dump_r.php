<?php

namespace dump_r\Rend\String;
use dump_r\Rend, dump_r\Rend\String;

class SQL extends String {
	public function get_val($node) {
		if (Rend::$sql_pretty)
			return \SqlFormatter::format($node->raw, false);
		else
			return parent::get_val($node);
	}
}