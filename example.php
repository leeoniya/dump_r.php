<?php
	include 'dump_r.php';
	
	class myObject {}
	
	$obj = new myObject;
	
	$obj->id = 12345;
	$obj->name = 'test string';
	$obj->price = 69.95;
	$obj->address = new stdClass;
	$obj->address->street = '111 Any Sreet';
	$obj->address->zip = 60657;
	$obj->address->city = 'Chicago';
	$obj->kids = array(
		0		=> 'blah',
		'a'		=> null,
		'xxx'	=> new myObject,
	);
	$obj->otherSet = array();
	$obj->isFull = false;
	$obj->food = null;
	$obj->dom = new DOMDocument;
	$obj->xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><root><moo attr=\"myAttribute\">f</moo><moo2>g</moo2><sss>55.9</sss></root>");
	$obj->afile = fopen(__FILE__, 'r');
?>

<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>dump_r()</title>
	
	<link rel="stylesheet" type="text/css" href="dump_r.css" media="all" />
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.6.1.min.js"></script>
	<script type="text/javascript" src="dump_r.js"></script>
</head>
<body>
	<h1>dump_r()</h1>
	<h2>no depth restriction</h2>
	<?php new dump_r($obj); ?>
	<h2>with depth restriction</h2>
	<?php new dump_r($obj, 'root', 1); ?>
</body>
</html>