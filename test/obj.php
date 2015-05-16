<?php

class lib0 {
	public static function myFn() {}
}

class lib extends lib0 {
	public static function myFn() {}
}

class Account {
	protected $name;
	private $deposit;

	public function __construct($props) {
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
$obj->kids = [
	'multiln_str'	=> "Lorem Ipsum is simply \ndummy text of the printing\nand typesetting industry",
	0				=> 'blah',
	'a'				=> null,
	'xxx'			=> new myObject,
];
$obj->date_str = '2011-12-13 15:25:03';
$obj->not_date = '123456';
$obj->otherSet = [];
$obj->moaarSet = [
	new Account(['name'=>'john','active'=>true,'deposit'=>531.34]),
	new Account(['name'=>'mary','active'=>false,'deposit'=>95.15]),
	new Account(['name'=>'michael','active'=>false,'deposit'=>12.21]),
	new Account(['name'=>'charles','active'=>true,'deposit'=>1.01]),
];
$obj->anothSet = [
	['abc'=>'yay!','def'=>false,'ghi'=>152.15],
	['abc'=>'yay!','def'=>true,'ghi'=>152.15],
	['abc'=>'yay!','def'=>0.01,'ghi'=>152.15],
	['abc'=>'yay!','def'=>true,'ghi'=>152.15],
];
//$obj->moaarRef = &$obj->moaarSet;
//$obj->moaarRef = $obj->moaarSet;

$obj->isFull = false;
$obj->food = null;
$obj->binary_str = "\x49\x4d\x47\x3a\x50\x6f\x77\x65\x72\x53\x68\x6f\x74\x20\x53\x34\x30\x20\x4a\x50\x45\x47\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x46\x69\x72\x6d\x77\x61\x72\x65\x20\x56\x65\x72\x73\x69\x6f\x6e\x20\x31\x2e\x31\x30\x00\x00\x00\x41\x6e\x64\x72\x65\x61\x73\x20\x48\x75\x67\x67\x65\x6c\x00\x00\x00\x00\x00\x00\x00\x00\x00\x0d\x0a\x09\x0c\x00\x00\x00\x00\x00\x00\x00\x00\x00\x2a\x00\x03\x00\x01\x80\x7a\x01\x01\x80";
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
$obj->call_inst_meth = [$obj, 'myFn'];
$obj->call_static_str = 'lib::myFn';
$obj->call_static_arr = ['lib' ,'myFn'];
$obj->call_static_par = ['lib' ,'parent::myFn'];
$obj->notfn = 'pi';		// global functions are excluded in favor of not mis-interpreting strings

$sub = new stdClass;
$sub->a = 'moo';
$sub->b = false;
$sub->c = ['hello', 'world', 2.98, null];
$sub->d = "75";

$obj->json_arr_str = json_encode([true,false,null,$sub]);
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

$obj->sparse = [
	7	=> 'sss',
	100	=> 'ddd',
];

$obj->uni_str = "ру́сский язы́к, russky yazyk, pronounced [ˈruskʲɪj jɪˈzɨk]\t\r\n\v\fabc 网络";
$obj->bin_str = "\x03\x00\x01\xF5";
$obj->uni_bin = $obj->uni_str . $obj->bin_str;
$obj->rus_str = "энцикло педии a b\tc";
$obj->rus_bin = $obj->rus_str . $obj->bin_str;