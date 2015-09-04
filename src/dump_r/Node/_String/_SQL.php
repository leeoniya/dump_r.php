<?php

namespace dump_r\Node\_String;
use dump_r\Node\_String;
use dump_r\Rend;

class _SQL extends _String {
	public function disp_val() {
		if (Rend::$sql_pretty)
			return \SqlFormatter::format($this->raw, false);
		else
			return parent::disp_val();
	}
}