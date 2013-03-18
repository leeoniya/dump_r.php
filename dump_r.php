<?php

if (!spl_autoload_functions()) {
	require 'lib/SplClassLoader.php';
	$classLoader = new SplClassLoader('dump_r', __DIR__ . '/src');
	$classLoader->register();
}

use dump_r\Core;

if (!function_exists('dump_r')) {
	function dump_r($raw, $ret = false, $html = true, $depth = 1e3, $expand = 1e3) {
		return Core::dump_r($raw, $ret, $html, $depth, $expand);
	}
}
