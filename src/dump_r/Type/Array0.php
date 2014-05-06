<?php

namespace dump_r\Type;
use dump_r\Type;

class Array0 extends Type {
	// TODO: fix array recursion detection, since === actually compares array contents, not mem pointers
	// copy-on-write also dooms temp tagging :(
	function chk_ref() {
		return false;
	}

	function get_len() {
		return count($this->nodes);
	}

	function get_nodes() {
		return $this->raw;
	}
}