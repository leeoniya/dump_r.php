<style>
	.dump_r li.marked > .lbl {
		background: yellow;
	}
</style>

<?php

require '../dump_r.php';

use dump_r\Type;

$stuff = [
	'imgs/img_1771.jpg',
	'data/people.csv',
	[
		'a' => [1,2,3.5],
		'b' => [4,5,6],
		'c' => [7,8,[11,12,13]],
		'xxx'	=> true,
		'yyy'	=> 'something',
	],
	[
		['a' => 1, 'b' => 3],
		['a' => 2, 'b' => 4],
	],
	"\x04\x00\xa0\x00\x32\x42\x00\xa0\xff\xff\xff\xff",
];


// exif data
Type::hook('_String', function($raw, Type $type, $path) {
	// match path-esque strings (containing '/' or '\') trailed by an
	// EXIF-capable image extension, then verify this file actually exists
	if (preg_match('#[\/]+.+\.(jpe?g|tiff?)$#', $raw) && is_file($raw)) {
		$nodes = $exif = exif_read_data($raw, 0, true);
		$len = $exif['COMPUTED']['Width'] . 'x' . $exif['COMPUTED']['Height'];

		$type->types	= ['image'];
		$type->nodes	= ['EXIF' => $nodes['EXIF']];
		$type->length	= $len;

		return $type;
	}
});


// csv records
Type::hook('_String', function($raw, Type $type, $path) {
	if (preg_match('#[\/]+.+\.csv$#', $raw) && is_file($raw)) {

		$type->types	= ['csv'];
		$type->nodes	= csv2array($raw);
		$type->length	= count($type->nodes);

		return $type;
	}
});


function csv2array($file) {
	$csv = [];
	$rows = array_map('str_getcsv', file($file));
	$header = array_shift($rows);
	foreach ($rows as $row)
		$csv[] = array_combine($header, $row);

	return $csv;
}

// prevent arrays keyed under 'c' from dumping sub-nodes
Type::hook('_Array', function($raw, Type $type, $path) {
	if (end($path) === 'c')
		$type->depth = 1;

	return $type;
});

// prevent anything keyd under 'xxx' from dumping
Type::hook('*', function($raw, Type $type, $path) {
	if (end($path) === 'xxx')
		return false;
});

// tag specific keys with addl rend classes
Type::hook('*', function($raw, Type $type, $path) {
	if (end($path) === 'yyy') {
		$type->classes[] = 'marked';
	}

	return $type;
});

dump_r($stuff);