<?php
	include 'dump_r.php';

	class lib {
		public static function myFn() {}
	}

	class myObject {
		public		$cow = 999;
		protected	$moo = 666;
		private		$cat = 555;
		static		$dog = 444;
	}

	$obj = new myObject;

	$obj->id = 12345;
	$obj->name = 'test string';
	$obj->name2 = '';
	$obj->name3 = '0';
	$obj->name4 = 'false';
	$obj->name5 = 'null';
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

	$obj->call_anon = function($a) {};
	$obj->call_static = 'lib::myFn';
	$obj->call_array = array('lib' ,'myFn');
	$obj->notfn = 'pi';		// global functions are excluded in favor of not mis-interpreting strings

	$sub = new stdClass;
	$sub->a = 'moo';
	$sub->b = false;
	$sub->c = array('hello', 'world', 2.98);
	$sub->d = "75";

	$obj->json_str_arr = json_encode(array(true,false,null,$sub));
	$obj->json_str_obj = json_encode($sub);
	$obj->xml_str = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><root><moo attr=\"myAttribute\">f</moo><moo2>g</moo2><sss>55.9</sss></root>";
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>dump_r()</title>

	<link rel="stylesheet" href="dump_r.css" media="all">
	<script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
	<script src="dump_r.js"></script>
</head>
<body>
	<h1>dump_r()</h1>
	<h2>no depth restriction</h2>
	<?php dump_r($obj); ?>
	<h2>with depth restriction</h2>
	<?php dump_r($obj, 1); ?>
</body>
</html>