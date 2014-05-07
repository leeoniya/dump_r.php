<?php

namespace dump_r\Rend;
use dump_r\Rend;

class String extends Rend {
	public function html_css_class($node, $vis, $expand) {
		$class = parent::html_css_class($node, $vis, $expand);

		if (preg_match('/\t| /m', substr($node->raw, 0, 1)))
			$class[] = 'leadspc';
		if (preg_match('/\t| /m',  substr($node->raw, -1)))
			$class[] = 'trailspc';

		return $class;
	}
}