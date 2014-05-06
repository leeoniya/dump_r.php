<?php

namespace dump_r\Type;
use dump_r\Type;

class Function0 extends Type {
	public function get_sub() {
		$sub = array();

		if (is_string($this->raw))
			$sub[] = 'static';
		else if (is_array($this->raw)) {
			if (is_string($this->raw[0]))
				$sub[] = 'static';
			else
				$sub[] = 'instance';
		}
		else if ($this->raw instanceof \Closure) {
			$sub[] = 'closure';
		}

		return $sub;
	}

	public function chk_ref() {
		if ($this->raw instanceof \Closure)
			return parent::chk_ref();

		return false;
	}
}