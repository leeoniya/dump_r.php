<?php

namespace dump_r\Node\Resource;
use dump_r\Node\Resource;

class Stream extends Resource {
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