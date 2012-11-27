<?php
/**
* Copyright (c) 2012-2013, Leon Sorokin
* All rights reserved. (MIT Licensed)
*
* dump_r.php - a better print_r & var_dump in HTML
* requires PHP >= 5.3
*/

function dump_r($input, $exp_lvls = 1000, $classy = null)
{
	// get the input arg passed to the function
	$src = debug_backtrace();
	$src = (object)$src[0];
	$file = file($src->file);
	$line = $file[$src->line - 1];
	preg_match('/dump_r\((.+?)(?:,|\)(;|\?>))/', $line, $m);

	dump_r::$classy = $classy;

	echo dump_r::render(dump_r::struct($input), $m[1], $exp_lvls);
}

class dump_r
{
	// indicator for injecting css/js on first dump
	public static $initial	= true;
	public static $keyWidth	= 0;
	public static $css;
	public static $js;
	public static $hooks = array();
	public static $classy = null;
	public static $xml_pretty = true;
	public static $json_pretty = true;

	// creates an internal dump representation
	public static function struct($inp, &$dict = array())
	{
		// detect references to existing objects + recursion
		if (is_object($inp)) {
			$hash = spl_object_hash($inp);

			if (array_key_exists($hash, $dict)) {
				$o = self::tyobj();
				$o->disp	= '{r}';
				$o->type	= 'ref';
				$o->ref		= $dict[$hash];
			}
			else {
				$o = self::type($inp);
				$o->hash = $hash;
				$dict[$hash] = $o;
			}
		}

		if (!isset($o))
			$o = self::type($inp);

		if (empty($o->children))
			return $o;

		foreach ($o->children as $k => $v)
			$o->children[$k] = self::struct($v, $dict);

		return $o;
	}

	public static function render($struct, $key = 'root', $exp_lvls = 1000, $st = true, $ln = 1)
	{
		// track max key width (8px/char)
		self::$keyWidth = max(self::$keyWidth, strlen($key) * 8);

		$inject = '';
		if (self::$initial) {
			$inject = self::$css . self::$js;
			self::$initial = false;
		}

		$buf = '';
		$buf .= $st ? "{$inject}<pre class=\"dump_r\"><ul>" : '';
		$s = &$struct;
		$disp = htmlspecialchars($s->disp);

		// add jumps to referenced objects
		if (!empty($s->hash))
			$disp = "<a name=\"{$s->hash}\">{$disp}</a>";
		else if ($s->type == 'ref')
			$disp = "<a href=\"#{$s->ref->hash}\">{$disp}</a>";

		$len = !is_null($s->length) ? "<div class=\"len\">{$s->length}</div>" : '';
		$sub = !is_null($s->subtype) ? "<div class=\"sub\">{$s->subtype}</div>" : '';
		$excol = !empty($s->children) ? '<div class="excol"></div>' : '';
		$exp_state = $excol ? ($exp_lvls > 0 ? ' expanded' : ' collapsed') : '';
		$empty		= $s->empty		? ' empty'			: '';
		$numeric	= $s->numeric	? ' numeric'		: '';
		$subtype	= $s->subtype	? " $s->subtype"	: '';
		$classes	= $s->classes	? ' ' . implode(' ', $s->classes) : '';
		$buf .= "<li class=\"{$s->type}{$subtype}{$numeric}{$empty}{$classes}{$exp_state}\">{$excol}<div class=\"lbl\"><div class=\"key\">{$key}</div><div class=\"val\">{$disp}</div><div class=\"typ\">({$s->type})</div>{$sub}{$len}</div>";
		if ($s->children) {
			$buf .= '<ul>';
			foreach ($s->children as $k => $s2)
				$buf .= self::render($s2, $k, $exp_lvls - 1, false, $ln++);
			$buf .= '</ul>';
		}
		$buf .= '</li>';
		$buf .= $st ? '</ul><style>.dump_r .key {min-width: ' . self::$keyWidth . 'px;}</style></pre>' : '';

		return $buf;
	}

	public static function tyobj()
	{
		return (object)array(
			'type'			=> null,
			'disp'			=> null,
			'subtype'		=> null,
			'empty'			=> null,
			'numeric'		=> null,
			'length'		=> null,
			'children'		=> null,
			'classes'		=> null,
		);
	}

	public static function type($input)
	{
		$type = self::tyobj();
		$type->disp		= $input;
		$type->empty	= empty($input);
		$type->numeric	= is_numeric($input);

		// avoid detecting strings with names of global functions and __invoke-able objects as callbacks
		if (is_callable($input) && !(is_object($input) && !($input instanceof Closure)) && !(is_string($input) && function_exists($input))) {
			$type->type		= 'function';
			$type->disp		= '( )';

			if (is_string($input)) {
				$type->disp		= $input;
				$type->subtype	= 'static';
			}
			else if (is_array($input)) {
				if (is_string($input[0])) {
					$type->disp		= implode(',', $input);
					$type->subtype	= 'static';
				}
				else {
					$type->disp		= "<obj>,{$input[1]}";
					$type->subtype	= 'instance';
				}
			}
			else if ($input instanceof Closure) {
				$type->disp		= '<closure>';
				$type->subtype	= 'closure';
			}
		}
		else if (is_array($input)) {
			$type->type		= 'array';
			$type->disp		= '[ ]';
			$type->children	= $input;
			$type->length	= count($type->children);
		}
		else if (is_resource($input)) {
			$type->type		= 'resource';
			$type->subtype	= get_resource_type($input);

			preg_match('/#\d+/', (string)$input, $matches);
			$type->disp		= $matches[0];

			// feel free to implement additional resource types below from http://www.php.net/manual/en/resource.php
			switch ($type->subtype) {
				case 'stream':
					$meta = stream_get_meta_data($input);
					$type->disp = $meta['uri'];
					break;
			}
		}
		else if (is_object($input)) {
			$type->type		= 'object';
			$type->disp		= '{ }';
			$type->subtype	= get_class($input);
			$type->children	= array();

			$childs	= (array)$input;		// hacks access to protected and private props
			foreach ($childs as $k => $v) {
				// clean up odd chars left in private/protected names
				$k = preg_replace("/[^\w]?(?:{$type->subtype})?[^\w]?/", '', $k);
				$type->children[$k] = $v;
			}
		}
		else if (is_int($input))
			$type->type		= 'integer';
		else if (is_float($input))
			$type->type		= 'float';
		else if (is_string($input)) {
			$type->type		= 'string';
			$type->length	= strlen($input);
		}
		else if (is_bool($input)) {
			$type->type		= 'boolean';
			$type->disp		= $input ? 'true' : 'false';
		}
		else if (is_null($input)) {
			$type->type		= 'null';
			$type->disp		= 'null';
		}
		else
			$type->type		= gettype($input);

		if (array_key_exists($type->type, self::$hooks))
			self::proc_hooks($type->type, $input, $type);

		if (is_callable(self::$classy)) {
			$classes = call_user_func(self::$classy, $input);
			if (is_string($classes))
				$classes = explode(' ', $classes);
			if (is_array($classes))
				$type->classes = $classes;
		}

		return $type;
	}

	public static function proc_hooks($key, $input, $type)
	{
		foreach(self::$hooks[$key] as $fn) {
			if ($fn($input, $type))
				return true;
		}
		return false;
	}

	// hook_string, hook_resource
	public static function __callStatic($name, $args)
	{
		if (substr($name, 0, 5) == 'hook_') {
			$hookey = substr($name, 5);
			if (count($args) == 2)
				self::$hooks[$hookey][$args[1]] = $args[0];
			else
				self::$hooks[$hookey][] = $args[0];
		}
	}
}

// util functions for hooks
class dump_r_lib
{
	public static function rel_date($datetime) {
		$rel_date = '';
		$timestamp = is_string($datetime) ? strtotime($datetime) : $datetime;
		$diff = time()-$timestamp;
		$dir = '-';
		if ($diff < 0) {
			$diff *= -1;
			$dir = '+';
		}
		$yrs = floor($diff/31557600);
		$diff -= $yrs*31557600;
		$mhs = floor($diff/2592000);
		$diff -= $mhs*2419200;
		$wks = floor($diff/604800);
		$diff -= $wks*604800;
		$dys = floor($diff/86400);
		$diff -= $dys*86400;
		$hrs = floor($diff/3600);
		$diff -= $hrs*3600;
		$mins = floor($diff/60);
		$diff -= $mins*60;
		$secs = $diff;

		if		($yrs > 0)	$rel_date .= $yrs.'y' . ($mhs > 0 ? ' '.$mhs.'m' : '');
		elseif	($mhs > 0)	$rel_date .= $mhs.'m' . ($wks > 0 ? ' '.$wks.'w' : '');
		elseif	($wks > 0)	$rel_date .= $wks.'w' . ($dys > 0 ? ' '.$dys.'d' : '');
		elseif	($dys > 0)	$rel_date .= $dys.'d' . ($hrs > 0 ? ' '.$hrs.'h' : '');
		elseif	($hrs > 0)	$rel_date .= $hrs.'h' . ($mins > 0 ? ' '.$mins.'m' : '');
		elseif	($mins > 0)	$rel_date .= $mins.'m';
		else				$rel_date .= $secs.'s';

		return $dir . $rel_date;
	}
}

dump_r::hook_string(function($input, $type) {
	if (substr($input, 0, 5) == '<?xml') {
		// strip namespaces
		$input = preg_replace('/<(\/?)[\w-]+?:/', '<$1', preg_replace('/\s+xmlns:.*?=".*?"/', '', $input));

		if ($xml = simplexml_load_string($input)) {
			if (dump_r::$xml_pretty) {
				$dom = dom_import_simplexml($xml)->ownerDocument;
				$dom->formatOutput = true;
				$type->disp = $dom->saveXML();
			}
			$type->subtype	= 'XML';
			$type->children = (array)$xml;
			// dont show length, or find way to detect uniform subnodes and treat as XML [] vs XML {}
			$type->length = null;

			return true;
		}

		return false;
	}

	return false;
}, 'is_xml');

dump_r::hook_string(function($input, $type) {
	if ($type->length > 0 && ($input{0} == '{' || $input{0} == '[') && ($json = json_decode($input))) {
		if (dump_r::$json_pretty)
			$type->disp = json_encode($json, JSON_PRETTY_PRINT);

		// maybe set subtype as JSON [] or JSON {}, will screw up classname
		$type->subtype	= 'JSON';
		$type->children = (array)$json;
		// dont show length of objects, only arrays
		$type->length = $input{0} == '[' ? count($type->children) : null;

		return true;
	}

	return false;
}, 'is_json');

dump_r::hook_string(function($input, $type) {
	if (strlen($input) > 5 && preg_match('#[:/-]#', $input) && ($ts = strtotime($input)) !== false) {
		$type->subtype = 'datetime';
		$type->length = dump_r_lib::rel_date($ts);

		return true;
	}

	return false;
}, 'is_datetime');

/* example of adding extra info to streams and more still to files
dump_r::hook_resource(function($input, $type) {
	if ($type->subtype == 'stream') {
		$meta = stream_get_meta_data($input);
		$type->children = array(
			'meta_data' => (object)$meta
		);

		if ($meta['wrapper_type'] == 'plainfile')
			$type->children['stat'] = (object)stat($meta['uri']);

		return true;		// skip any remaining hook_resource checks
	}

	return false;			// process additional hook_resource checks
}, 'is_resource');
*/

// css
ob_start();
?>
<style id="dump_r">
	.dump_r {
		clear: both;
	}

	.dump_r ul {
		list-style: none;
		padding: 0 0 0 15px;
		margin: 0;
	}

	.dump_r ul ul {
		margin-top: 2px;
	}

	.dump_r li {
		position: relative;
		margin-bottom: 2px;
	}

	.dump_r .excol {
		position: absolute;
		margin: 1px 0 0 -15px;
		cursor: pointer;
	}

	.dump_r .expanded > .excol:after	{content: "\25BC";}
	.dump_r .collapsed > .excol:after	{content: "\25B6";}
	.dump_r .collapsed > ul				{display: none;}

	.dump_r .lbl						{position: relative; padding-left: 3px; padding-right: 5px;}
	.dump_r .lbl > *					{display: inline-block;}


	.dump_r li > .lbl					{background-color: #F1F1F1;}
	.dump_r li:nth-child(odd) > .lbl	{background-color: #E9E9E9;}

	.dump_r .key						{font-weight: bold;}
	.dump_r .val						{margin: 0 5px 0 30px; min-width: 5px; vertical-align: top;}
	.dump_r .typ,
	.dump_r .sub,
	.dump_r .len						{color: #666666; margin-right: 5px;}

	.dump_r .typ						{display: none;}

	.dump_r .array			> .lbl .val {background-color: #C0BCFF;}
	.dump_r .object			> .lbl .val {background-color: #98FB98;}
	.dump_r .function		> .lbl .val {background-color: #FAFF5C;}
	.dump_r .boolean		> .lbl .val {background-color: #08F200;}
	.dump_r .boolean.empty	> .lbl .val {background-color: #FF8C8C;}
	.dump_r .null			> .lbl .val {background-color: #FFD782;}
	.dump_r .integer		> .lbl .val {background-color: #EAB2EA;}
	.dump_r .float			> .lbl .val {background-color: #EB65EB;}
	.dump_r .string			> .lbl .val {background-color: #FFBFBF;}
	.dump_r .resource		> .lbl .val {background-color: #E2FF8C;}
	.dump_r .numeric		> .lbl .val {}
	.dump_r .ref			> .lbl .val {background-color: #CEFBF3;}
	.dump_r .datetime		> .lbl .val {}

	.dump_r .stdClass .sub,
	.dump_r .datetime .sub {
		display: none;
	}

	/* hide length of empty stuff except numeric eg '0' strings */
	.dump_r .empty:not(.numeric) > .lbl .len {
		display: none;
	}

	/* display empty strings as a gray middle dot */
	.dump_r .empty.string:not(.numeric) > .lbl .val:after {
		content: "\0387";
		color: #BBBBBB;
	}

	/* hide empty strings completely
	.dump_r .empty.string:not(.numeric) > .lbl .val {
		display: none;
	}
	*/
</style>
<?php
dump_r::$css = ob_get_contents();
ob_end_clean();

// js
ob_start();
?>
<script>
	(function(){
		/*--- all this for expand/collapse arrow size consistency ---*/
		function isUa(re) {return re.test(window.navigator.userAgent);}

		var ua = isUa(/Chrom[ei]/) ? "ch" : isUa(/Firefox\//) ? "ff" : isUa(/Safari/) ? "sf" : isUa(/Opera/) ? "op" : isUa(/; MSIE \d/) ? "ie" : "oth";

		var cfg = {
			ff: [10,8,null,null],
			ch: [10,10,null,null],
			sf: [10,8.5,null,null],
			op: [11,8.5,11,11],
			ie: [10,13.5,null,11]
		};

		var fn = "font-size: ",
			ln = "line-height: ",
			un = "pt",
			c = cfg[ua],
			fe = fn + c[0] + un,
			fc = fn + c[1] + un,
			le = c[2] ? ln + c[2] + un : "",
			lc = c[3] ? ln + c[3] + un : "",
			sheet = document.getElementById("dump_r").sheet;

		sheet.insertRule(".dump_r .expanded  > .excol {" + [fe,le].join(";") + "}", 5);
		sheet.insertRule(".dump_r .collapsed > .excol {" + [fc,lc].join(";") + "}", 5);
		/*-----------------------------------------------------------*/

		// expandable or collapsible tester
		var re = /\bexpanded\b|\bcollapsed\b/;

		function toggle(actn, node, lvls) {
			if (lvls === 0 || !re.test(node.className)) return;

			node.className = node.className.replace(actn ? /\bcollapsed\b/ : /\bexpanded\b/, actn ? "expanded" : "collapsed");

			for (var i in node.childNodes) {
				if (node.childNodes[i].nodeName !== "UL") continue;
				for (var j in node.childNodes[i].childNodes)
					toggle(actn, node.childNodes[i].childNodes[j], lvls - 1);
			}
		}

		function toggleHandler(e) {
			if (e.which != 1 || e.target.className.indexOf("excol") == -1) return;

			var node = e.target.parentNode,
				actn = node.className.indexOf("collapsed") !== -1 ? 1 : 0,
				lvls = e.shiftKey ? 1000 : 1;

			toggle(actn, node, lvls);

			// toggle all following siblings
			if (e.ctrlKey) {
				while (node.nextSibling) {
					node = node.nextSibling;
					toggle(actn, node, lvls);
				}
			}
		}

		document.addEventListener("click", toggleHandler, false);
	})();
</script>
<?php
dump_r::$js = ob_get_contents();
ob_end_clean();
