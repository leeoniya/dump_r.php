<?php

namespace dump_r\Node;
use dump_r\Node;

class _Function extends Node {
	public function get_sub() {
		$sub = [];

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

	public function disp_val() {
		$val = $this->ref ? '(*)' : '( )';
		$val .= ' ';

		switch ($this->sub[0]) {
			case 'closure':
				$val .= '<closure>';
				break;
			case 'instance':
				$val .= "<obj>,{$this->raw[1]}";
				break;
			case 'static':
				$val .= is_string($this->raw) ? $this->raw : implode(',', $this->raw);
		}

		return $val;
/*
		if (is_array($this->raw))
			$reflector = new \ReflectionMethod($this->raw[0], $this->raw[1]);
		else if (is_string($this->raw)) {
			$call = explode('::', $this->raw);
			$reflector = new \ReflectionMethod($call[0], $call[1]);				// ReflectionFunction
		}
		else if ($this->raw instanceof \Closure) {
			$objReflector = new \ReflectionObject($this->raw);
			$reflector    = $objReflector->getMethod('__invoke');
		}

		$params = $reflector->getParameters();
*/
	}
}