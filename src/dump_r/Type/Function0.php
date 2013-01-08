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
/*
		if ($this->raw instanceof \Closure)
			$this->id = spl_object_hash($this->raw);
		else if (is_string($this->raw) || (is_array($this->raw) && is_string($this->raw[0])))
			$this->id = is_string($this->raw) ? $this->raw : implode('::', $this->raw);
		else if (is_array($this->raw))
			$this->id = spl_object_hash($this->raw[0]) . '::' . $this->raw[1];


		if (array_key_exists($this->id, Type::$dic))
			return true;
		else
			Type::$dic[$this->id] = $this->id;
*/
		return false;
	}
}