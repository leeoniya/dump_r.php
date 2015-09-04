<?php

namespace dump_r\Node\_String;
use dump_r\Node\_String;
use dump_r\Rend;

class _JSON extends _String {
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