<?php

namespace dump_r\Type\String;
use dump_r\Type\String;

class JSON extends String {
	function get_nodes() {
		return (array)$this->val;
	}
}