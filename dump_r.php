<?php

if (!spl_autoload_functions()) {
	require 'lib/SplClassLoader.php';
	$classLoader = new SplClassLoader('dump_r', __DIR__ . '/src');
	$classLoader->register();
}

use dump_r\Core;
use dump_r\Type;

if (!function_exists('dump_r')) {
	function dump_r($raw, $ret = false, $html = true, $depth = 1e3, $expand = 1e3) {
		return Core::dump_r($raw, $ret, $html, $depth, $expand);
	}
}

// typenode classification
Type::hook('*', function($raw, Type $type, $path) {
	if (is_null($raw))
		$type->class[] = 'Null';
	else if (is_bool($raw))
		$type->class[] = 'Boolean';
	else if (is_int($raw))
		$type->class[] = 'Integer';
	else if (is_float($raw))
		$type->class[] = 'Float';
	else if (is_resource($raw))
		$type->class[] = 'Resource';
	// avoid detecting strings with names of global functions and __invoke-able objects as callbacks
	else if (is_callable($raw) && !(is_object($raw) && !($raw instanceof \Closure)) && !(is_string($raw) && function_exists($raw)))
		$type->class[] = 'Function0';	// lang construct
	else if (is_string($raw))
		$type->class[] = 'String';
	else if (is_array($raw))
		$type->class[] = 'Array0';	// lang construct
	else if (is_object($raw))
		$type->class[] = 'Object';
	else
		$type->class[] = gettype($raw);

	return $type;
});

Type::hook('String', function($raw, Type $type, $path) {
	if ($raw === '')
		return;
//	http://stackoverflow.com/questions/9545336/php-match-control-characters-but-not-whitespace/9545636#9545636
//	http://stackoverflow.com/questions/1497885/remove-control-characters-from-php-string/23066553#23066553
//	http://www.regular-expressions.info/unicode.html#category
//	http://php.net/manual/en/regexp.reference.unicode.php
	$nonprint = preg_match('/[^\PC\s]/u', $raw);
	if ($nonprint == 1 || $nonprint === false)
		$type->class[] = 'Binary';
	else if (strlen($raw) > 5 && preg_match('#[:/-]#', $raw) && ($ts = strtotime($raw)) !== false) {
		$type->class[] = 'Datetime';
		$type->inter = $ts;
	}
	// SQL
	else if (
		strpos($raw, 'SELECT')   === 0 ||
		strpos($raw, 'INSERT')   === 0 ||
		strpos($raw, 'UPDATE')   === 0 ||
		strpos($raw, 'DELETE')   === 0 ||
		strpos($raw, 'BEGIN')    === 0 ||
		strpos($raw, 'COMMIT')   === 0 ||
		strpos($raw, 'ROLLBACK') === 0
		/* sql_extended
		strpos($raw, 'CREATE')   === 0 ||
		strpos($raw, 'DROP')     === 0 ||
		strpos($raw, 'TRUNCATE') === 0 ||
		strpos($raw, 'ALTER')    === 0 ||
		strpos($raw, 'DESCRIBE') === 0 ||
		strpos($raw, 'EXPLAIN')  === 0 ||
		strpos($raw, 'SHOW')     === 0 ||
		strpos($raw, 'GRANT')    === 0 ||
		strpos($raw, 'REVOKE')   === 0
		*/
	)
		$type->class[] = 'SQL';

	// JSON
	else if ($raw{0} == '{' && $json = json_decode($raw)) {
		$type->class[] = 'JSON\Object';
		$type->inter = $json;
	}
	else if ($raw{0} == '[' && $json = json_decode($raw)) {
		$type->class[] = 'JSON\Array0';
		$type->inter = $json;
	}
	// jsonH

	// XML
	else if (substr($raw, 0, 5) == '<?xml') {
		// strip namespaces
		$raw = preg_replace('/<(\/?)[\w-]+?:/', '<$1', preg_replace('/\s+xmlns:.*?=".*?"/', '', $raw));

		if ($xml = simplexml_load_string($raw)) {
			$type->class[] = 'XML';
			$type->inter = $xml;
		}
		// XML\Array0
		// XML\Object
	}

	return $type;
});

Type::hook('Resource', function($raw, Type $type, $path) {
	$kind = get_resource_type($raw);		// this is valuable for other resources

	switch ($kind) {
		case 'stream':
			$meta = stream_get_meta_data($raw);
			$type->class[] = 'Stream';
			$type->inter = $meta;
	}

	return $type;
});
