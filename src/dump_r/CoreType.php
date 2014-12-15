<?php

namespace dump_r;

// helper class for returning from Type::hook functions
// class must have implemented Type and Rend classes implemented in the system
class CoreType {
	public $class = null;
	public $intr = null;

	function __construct($class = null, $intr = null) {
		$this->class = $class;
		$this->intr = $intr;
	}
}