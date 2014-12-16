<?php

require '../dump_r.php';

use dump_r\Type;
use dump_r\UserType;

$stuff = [
	'imgs/img_1771.jpg',
	'data/people.csv',
];

// exif data
Type::hook('String', function($raw, $type) {
	// match path-esque strings (containing '/' or '\') trailed by an
	// EXIF-capable image extension, then verify this file actually exists
	if (preg_match('#[\/]+.+\.(jpe?g|tiff?)$#', $raw) && is_file($raw)) {
		$nodes = $exif = exif_read_data($raw, 0, true);
		$len = $exif['COMPUTED']['Width'] . 'x' . $exif['COMPUTED']['Height'];

		return new UserType(['image'], ['EXIF' => $nodes['EXIF']], $len);
	}
});

// csv records
Type::hook('String', function($raw, $type) {
	if (preg_match('#[\/]+.+\.csv$#', $raw) && is_file($raw)) {
		$nodes = csv2array($raw);
		$len = count($nodes);
		return new UserType(['csv'], $nodes, $len);
	}
});

function csv2array($file) {
	$csv = array();
	$rows = array_map('str_getcsv', file($file));
	$header = array_shift($rows);
	foreach ($rows as $row)
		$csv[] = array_combine($header, $row);

	return $csv;
}

dump_r($stuff);