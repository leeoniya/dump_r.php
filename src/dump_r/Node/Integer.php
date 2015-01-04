<?php

namespace dump_r\Node;
use dump_r\Node;

class Integer extends Node {
	public function chk_ref() {
		return false;
	}
}