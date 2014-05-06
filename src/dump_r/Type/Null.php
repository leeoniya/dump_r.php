<?php

namespace dump_r\Type;
use dump_r\Type;

class Null extends Type {
	function chk_ref() {
		return false;
	}
}