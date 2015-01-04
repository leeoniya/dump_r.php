<?php

namespace dump_r\Node\String;
use dump_r\Node\String;

class Binary extends String {
	const BYTES_PER_LINE = 32;

	public function disp_val() {
		$str = bin2hex($this->raw);
		$str = chunk_split($str, 2, ' ');
		$str = chunk_split($str, self::BYTES_PER_LINE * 3, "\n");
		$str = preg_replace('/ +$/m', '', $str);

		return rtrim($str);
	}
}