<?php

namespace dump_r;

// helper class for returning from Type::hook functions
// class must have implemented Type and Rend classes implemented in the system
class UserType {
	public $sub = null;
	public $nodes = null;
	public $len = null;

	// params must be the expected results of matching
	// get_* methods of a fully implemented type
	function __construct(Array $sub = null, Array $nodes = null, $len = null) {
		$this->sub = $sub;
		$this->nodes = $nodes;
		$this->len = $len;
	}
}