<?php

namespace dump_r\Node\_Resource;
use dump_r\Node\_Resource;

class _Stream extends _Resource {
/*
	public function disp_val() {
		$meta = stream_get_meta_data($this->raw);
		return $meta['uri'];
	}
*/
/*
	public function get_nodes($intr = null) {
		return stream_get_meta_data($this->raw);
	}
*/
	public function disp_val() {
		$val = $this->ref ? '<*>' : '< >';
		return $val . ' ' . $this->inter['uri'];
	}
}