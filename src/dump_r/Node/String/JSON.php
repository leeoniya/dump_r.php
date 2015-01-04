<?php

namespace dump_r\Node\String;
use dump_r\Node\String;
use dump_r\Rend;

class JSON extends String {
	public function get_nodes() {
		return (array)$this->inter;
	}

	public function disp_val() {
		if (Rend::$json_pretty)
			return json_encode($this->inter, JSON_PRETTY_PRINT);
		else
			return parent::disp_val();
	}
}