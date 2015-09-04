<?php

namespace dump_r\Node\_String;
use dump_r\Node\_String;

class _Binary extends _String {
	const BYTES_PER_LINE = 32;

	public function disp_val() {
		if (extension_loaded('mbstring'))
			$chars = self::mb_split($this->raw);
		else {
			$chars = preg_split('/(?<!^)(?!$)/u', $this->raw);	// buggy
			if (strlen($chars[0]) > 2)
				trigger_error("Binary string may have been improperly split; 'mbstring' extension is not enabled");
		}

		$cells = array_map(function($chr) {
			// whitespace, excluding space
			if (preg_match('/[\t\r\n\v\f]/u', $chr))
				return str_replace(["\t","\r","\n","\v","\f"], ['\t','\r','\n','\v','\f'], $chr);
			// printable chars + space
			if (preg_match('/\P{C}/u', $chr))
				return str_pad($chr, 2, ' ', STR_PAD_RIGHT);

			return str_pad(dechex(ord($chr)), 2, '0', STR_PAD_LEFT);
		}, $chars);

		return $this->fmt_wrap($cells);
	}

	public function disp_val2() {
		$str = unpack('H*', $this->raw);
		return $this->fmt_wrap(str_split($str[1], 2));
	}

	protected function fmt_wrap($cells) {
		$out = [];
		$line = [];
		$i = -1;
		$len = count($cells);
		while (++$i < $len) {
			if (($i+1) % self::BYTES_PER_LINE == 0) {
				$out[] = implode(' ', $line);
				$line = [];
			}
			$line[] = $cells[$i];
		}

		if (!empty($line))
			$out[] = implode(' ', $line);

		return implode("\n", $out);
	}

	public static function mb_split($string) {
		$strlen = mb_strlen($string);
		while ($strlen) {
			$array[] = mb_substr($string,0,1,"UTF-8");
			$string = mb_substr($string,1,$strlen,"UTF-8");
			$strlen = mb_strlen($string);
		}
		return $array;
	}
}