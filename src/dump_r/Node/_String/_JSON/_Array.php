<?php

namespace dump_r\Node\_String\_JSON;
use dump_r\Node\_String\_JSON;

class _Array extends _JSON {
	public function get_len() {
		return count($this->nodes);
	}
}