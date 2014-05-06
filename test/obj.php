<?php

class lib0 {
	public static function myFn() {}
}

class lib extends lib0 {
	public static function myFn() {}
}

class Account {
	function __construct($props) {
		foreach($props as $k => $v) {
			$this->$k = $v;
		}
	}
}

class myObject {
	public		$cow_publ = 999;
	protected	$moo_prot = 666;
	private		$cat_priv = 555;
	static		$dog_stat = 444;

	protected	$self;

	public function  __construct() {
		$this->self = $this;
	}

	public function myFn() {}

	public function __invoke() {}
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
$obj->ref_to_addr = $obj->address;
$obj->kids = array(
	'multiln_str'	=> "Lorem Ipsum is simply \ndummy text of the printing\nand typesetting industry",
	0				=> 'blah',
	'a'				=> null,
	'xxx'			=> new myObject,
);
$obj->date_str = '2011-12-13 15:25:03';
$obj->not_date = '123456';
$obj->otherSet = array();
$obj->moaarSet = array(
	new Account(array('name'=>'john','active'=>true,'deposit'=>531.34)),
	new Account(array('name'=>'mary','active'=>false,'deposit'=>95.15)),
	new Account(array('name'=>'michael','active'=>false,'deposit'=>12.21)),
	new Account(array('name'=>'charles','active'=>true,'deposit'=>1.01)),
);
$obj->anothSet = array(
	array('abc'=>'yay!','def'=>false,'ghi'=>152.15),
	array('abc'=>'yay!','def'=>true,'ghi'=>152.15),
	array('abc'=>'yay!','def'=>0.01,'ghi'=>152.15),
	array('abc'=>'yay!','def'=>true,'ghi'=>152.15),
);
//$obj->moaarRef = &$obj->moaarSet;
//$obj->moaarRef = $obj->moaarSet;

$obj->isFull = false;
$obj->food = null;
$obj->dom = new DOMDocument;
$obj->xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><root><moo attr="myAttribute">f</moo><moo2>g</moo2><sss>55.9</sss></root>');
$obj->afile = fopen(__FILE__, 'r');
$obj->afile_ref = $obj->afile;
//$obj->afile_ref = &$obj->afile;
//$obj->afile_ref = $obj->afile;


$obj->call_self = $obj;
$obj->call_closure = function($a) {};
$obj->call_closure_ref = $obj->call_closure;
// $obj->closure_refer = $obj->call_closure;
$obj->call_inst_meth = array($obj, 'myFn');
$obj->call_static_str = 'lib::myFn';
$obj->call_static_arr = array('lib' ,'myFn');
$obj->call_static_par = array('lib' ,'parent::myFn');
$obj->notfn = 'pi';		// global functions are excluded in favor of not mis-interpreting strings

$sub = new stdClass;
$sub->a = 'moo';
$sub->b = false;
$sub->c = array('hello', 'world', 2.98, null);
$sub->d = "75";

$obj->json_arr_str = json_encode(array(true,false,null,$sub));
$obj->json_obj_str = json_encode($sub);
$obj->xml_str = '<?xml version="1.0" encoding="utf-8"?><root><moo attr="myAttribute">f</moo><moo2>g</moo2><sss>55.9</sss></root>';
/*
$obj->xml_str2 = '<?xml version="1.0" encoding="ISO-8859-1"?><SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1564:hello xmlns:ns1564="http://tempuri.org"><name xsi:type="xsd:string" haha="moo">Scott</name></ns1564:hello></SOAP-ENV:Body></SOAP-ENV:Envelope>';
*/

$obj->sql_str = "SELECT firstname,lastname,mooo.blah,hehe.* FROM mytable moo INNER JOIN othertable hehe ON moo.id = hehe.some_id WHERE hehe.name IS NOT NULL AND moo.xxx = 'berries' GROUP BY foo.cookies ORDER BY foo.sum,moo.age DESC LIMIT 30,400";
$obj->str_trail = 'trail space ';
$obj->str_lead = ' lead space';
$obj->str_bothspc = ' both space ';

$obj->arr0 = ['a','b','c'];
$obj->arr1 = &$obj->arr0;

$obj->arr3 = ['a','b','c'];

$obj->arr3[] = &$obj->arr3;