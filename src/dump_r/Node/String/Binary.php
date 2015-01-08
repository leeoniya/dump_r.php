<?php

namespace dump_r\Node\String;
use dump_r\Node\String;

class Binary extends String {
	const BYTES_PER_LINE = 32;

	public function disp_val() {
		$str = implode('', array_map(function($byte) {
			// printable ascii chars + space
			if (preg_match('/[ -~]/', $byte))
				return str_pad($byte, 2, ' ', STR_PAD_RIGHT);
			// other common whitespace
			if (preg_match('/[\r\n\t]/', $byte))
				return str_replace(["\t","\r","\n"], ['\t','\r','\n'], $byte);

			return str_pad(dechex(ord($byte)), 2, '0', STR_PAD_LEFT);
		}, str_split($this->raw)));

		return $this->fmt_wrap($str);
	}

	public function disp_val2() {
		$str = bin2hex($this->raw);

		return $this->fmt_wrap($str);
	}

	protected function fmt_wrap($str) {
		$str = chunk_split($str, 2, ' ');
		$str = chunk_split($str, self::BYTES_PER_LINE * 3, "\n");
		$str = preg_replace('/ +$/m', '', $str);

		return rtrim($str);
	}
}