<?php

if (!spl_autoload_functions()) {
	require 'lib/SplClassLoader.php';
	$classLoader = new SplClassLoader('dump_r', __DIR__ . '/src');
	$classLoader->register();
}

use dump_r\Core;

if (!function_exists('dump_r')) {
	function dump_r($raw, $depth = 1000, $expand = 1000, $ret = false) {
		return Core::dump_r($raw, $depth, $expand, $ret);
	}
}
