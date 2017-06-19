<?php

namespace dump_r;
use dump_r\Type;
use dump_r\Node;

class Core {
	public static function dump_r($file, $line, $key, &$raw, $ret = false, $html = true, $depth = 1e3, $expand = 1e3) {
		$root = Type::fact($raw, [], $depth);

		// remove array recursion detection keys from orig
		foreach(Type::$dic as $key2 => &$raw_ref)
			if (is_array($raw_ref))
				unset($raw_ref[Node\_Array::$ref_key]);


		self::cleanArrRefTags($root);

		Type::$dic = [];

		if (PHP_SAPI == 'cli' || !$html)
			$out = $root->text0($file, $line, $key);
		else
			$out = $root->html0($file, $line, $key, $expand);

		if ($ret)
			return $out;

		echo $out;
	}

	public static function rand_str($chars = 8) {
		$letters = 'abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		return substr(str_shuffle($letters), 0, $chars);
	}

	public static function cleanArrRefTags(&$node) {
		if ($node instanceof Node\_Array)
			unset($node->nodes[Node\_Array::$ref_key]);

		if ($node->nodes)
			foreach ($node->nodes as &$node)
				self::cleanArrRefTags($node);
	}
}