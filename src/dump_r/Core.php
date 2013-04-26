<?php

namespace dump_r;
use dump_r\Type, dump_r\Rend;

class Core {
	public static function dump_r($raw, $ret = false, $html = true, $depth = 1e3, $expand = 1e3) {
		$root = Type::fact($raw, $depth);

		// get the input arg passed to the function
		$src = debug_backtrace();
		$idx = strpos($src[0]['file'], 'dump_r.php') ? 1 : 0;
		$src = (object)$src[$idx];
		$file = file($src->file);
		$line = $file[$src->line - 1];
		preg_match('/dump_r\((.+?)(?:,|\)(;|\?>))/', $line, $m);
		$key = $m[1];

		if (PHP_SAPI == 'cli' || !$html)
			$out = Rend::text0($src->file, $src->line, $key, $root);
		else
			$out = Rend::html0($src->file, $src->line, $key, $root, $expand);

		if ($ret)
			return $out;

		echo $out;
	}
}

// typenode classification
Type::hook('*', function($raw) {
	if (is_null($raw))
		return 'Null';
	if (is_bool($raw))
		return 'Boolean';
	if (is_int($raw))
		return 'Integer';
	if (is_float($raw))
		return 'Float';
	if (is_resource($raw))
		return 'Resource';
	// avoid detecting strings with names of global functions and __invoke-able objects as callbacks
	if (is_callable($raw) && !(is_object($raw) && !($raw instanceof \Closure)) && !(is_string($raw) && function_exists($raw)))
		return 'Function0';	// lang construct
	if (is_string($raw))
		return 'String';
	if (is_array($raw))
		return 'Array0';	// lang construct
	if (is_object($raw))
		return 'Object';

	return gettype($raw);
});

// renderer classification
Rend::hook('*', function($node) {
	$class = explode('\\', get_class($node));
	$class = array_slice($class, 2);
	return implode('\\', $class);
});

Type::hook('String', function($raw) {
	if ($raw === '') return;

	if (strlen($raw) > 5 && preg_match('#[:/-]#', $raw) && ($ts = strtotime($raw)) !== false)
		return array('Datetime', $ts);

	// SQL
	if (strpos($raw, 'SELECT')   === 0 ||
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
	) return 'SQL';

	// JSON
	if ($raw{0} == '{' && $json = json_decode($raw))
		return array('JSON\\Object', $json);
	if ($raw{0} == '[' && $json = json_decode($raw))
		return array('JSON\\Array0', $json);
	// jsonH

	// XML
	if (substr($raw, 0, 5) == '<?xml') {
		// strip namespaces
		$raw = preg_replace('/<(\/?)[\w-]+?:/', '<$1', preg_replace('/\s+xmlns:.*?=".*?"/', '', $raw));

		if ($xml = simplexml_load_string($raw))
			return array('XML', $xml);
		// XML\Array0
		// XML\Object
	}
});

Type::hook('Resource', function($raw, $intr = null) {
	$type = get_resource_type($raw);		// this is valuable for other resources

	switch ($type) {
		case 'stream':
			$meta = stream_get_meta_data($raw);
			return array('Stream', $meta);
	}
});