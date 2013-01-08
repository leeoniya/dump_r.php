<?php

namespace dump_r\Rend\String;
use dump_r\Rend, dump_r\Rend\String;

class XML extends String {
	public function get_val($node) {
		if (Rend::$xml_pretty) {
			$dom = dom_import_simplexml($node->val)->ownerDocument;
			$dom->formatOutput = true;
			return trim($dom->saveXML());
		}
		else
			return parent::get_val($node);
	}
}