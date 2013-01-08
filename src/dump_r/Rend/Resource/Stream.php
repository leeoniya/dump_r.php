<?php

namespace dump_r\Rend\Resource;
use dump_r\Rend\Resource;

class Stream extends Resource {
	public function get_val($node) {
		$val = $node->ref ? '<*>' : '< >';
		return $val . ' ' . $node->val['uri'];
	}
}