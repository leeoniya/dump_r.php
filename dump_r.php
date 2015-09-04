<?php

if (!spl_autoload_functions()) {
	require 'lib/PSR4_Loader.php';
	$loader = new Psr4AutoloaderClass;
	$loader->register();
	$loader->addNamespace('dump_r\\', __DIR__ . '/src/dump_r');
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
		$type->class[] = '_Null';
	else if (is_bool($raw))
		$type->class[] = '_Boolean';
	else if (is_int($raw))
		$type->class[] = '_Integer';
	else if (is_float($raw))
		$type->class[] = '_Float';
	else if (is_resource($raw))
		$type->class[] = '_Resource';
	// avoid detecting strings with names of global functions and __invoke-able objects as callbacks
	else if (is_callable($raw) && !(is_object($raw) && !($raw instanceof \Closure)) && !(is_string($raw) && function_exists($raw)))
		$type->class[] = '_Function';
	else if (is_string($raw))
		$type->class[] = '_String';
	else if (is_array($raw))
		$type->class[] = '_Array';
	else if (is_object($raw))
		$type->class[] = '_Object';
	else
		$type->class[] = '_' . gettype($raw);

	return $type;
});

Type::hook('_String', function($raw, Type $type, $path) {
	if ($raw === '')
		return;
//	http://stackoverflow.com/questions/9545336/php-match-control-characters-but-not-whitespace/9545636#9545636
//	http://stackoverflow.com/questions/1497885/remove-control-characters-from-php-string/23066553#23066553
//	http://www.regular-expressions.info/unicode.html#category
//	http://php.net/manual/en/regexp.reference.unicode.php
	$nonprint = preg_match('/[^\PC\s]/u', $raw);
	if ($nonprint == 1 || $nonprint === false)
		$type->class[] = '_Binary';
	else if (strlen($raw) > 5 && preg_match('#[:/-]#', $raw) && ($ts = strtotime($raw)) !== false) {
		$type->class[] = '_Datetime';
		$type->inter = $ts;
	}
	// _SQL
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
		$type->class[] = '_SQL';

	// _JSON
	else if ($raw{0} == '{' && $json = json_decode($raw)) {
		$type->class[] = '_JSON\_Object';
		$type->inter = $json;
	}
	else if ($raw{0} == '[' && $json = json_decode($raw)) {
		$type->class[] = '_JSON\_Array';
		$type->inter = $json;
	}
	// jsonH

	// _XML
	else if (substr($raw, 0, 5) == '<?xml') {
		// strip namespaces
		$raw = preg_replace('/<(\/?)[\w-]+?:/', '<$1', preg_replace('/\s+xmlns:.*?=".*?"/', '', $raw));

		if ($xml = simplexml_load_string($raw)) {
			$type->class[] = '_XML';
			$type->inter = $xml;
		}
		// _XML\\_Array
		// _XML\\_Object
	}

	return $type;
});

Type::hook('_Resource', function($raw, Type $type, $path) {
	$kind = get_resource_type($raw);		// this is valuable for other resources

	switch ($kind) {
		case 'stream':
			$meta = stream_get_meta_data($raw);
			$type->class[] = '_Stream';
			$type->inter = $meta;
	}

	return $type;
});
