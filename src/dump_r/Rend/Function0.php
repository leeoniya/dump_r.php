<?php

namespace dump_r\Rend;
use dump_r\Rend;

class Function0 extends Rend {
	public function get_val($node) {
		$val = $node->ref ? '(*)' : '( )';
		$val .= ' ';

		switch ($node->sub[0]) {
			case 'closure':
				$val .= '<closure>';
				break;
			case 'instance':
				$val .= "<obj>,{$node->raw[1]}";
				break;
			case 'static':
				$val .= is_string($node->raw) ? $node->raw : implode(',', $node->raw);
		}

		return $val;
	}
}