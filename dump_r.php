<?php
/**
* Copyright (c) 2011, Leon Sorokin
* All rights reserved.
*
* dump_r.php
* better than print_r()
* better than var_dump()
* for browsers
*/
function dump_r($input, $exp_lvls = 1000)
{
	// get the input arg passed to the function
	$src = debug_backtrace();
	$src = (object)$src[0];
	$file = file($src->file);
	$line = $file[$src->line - 1];
	preg_match('/dump_r\((.+?)(?:,|\);)/', $line, $m);

	echo dump_r::go($input, $m[1], $exp_lvls);
}

class dump_r
{
	// indicator for injecting css/js on first dump
	public static $initial = true;
	public static $css;
	public static $js;

	public static function go($inp, $key = 'root', $exp_lvls = 1000, $st = TRUE)
	{
		$inject = '';
		if (self::$initial) {
			$inject = self::$css . self::$js;
			self::$initial = false;
		}

		$buf = '';
		$buf .= $st ? "{$inject}<pre class=\"dump_r\"><ul>" : '';
		$t = self::checkType($inp);
		$disp = htmlspecialchars($t->disp);
		$len = !is_null($t->length) ? "<div class=\"len\">{$t->length}</div>" : '';
		$sub = !is_null($t->subtype) && !is_bool($inp) ? "<div class=\"sub\">{$t->subtype}</div>" : '';
		$excol = is_array($t->children) && !empty($t->children) ? '<div class="excol"></div>' : '';
		$exp_state = $excol ? ($exp_lvls > 0 ? ' expanded' : ' collapsed') : '';
		$empty = empty($inp) ? ' empty' : '';
		$numeric = is_numeric($inp) ? ' numeric' : '';
		$t->subtype = $t->subtype ? ' ' . $t->subtype : $t->subtype;
		$buf .= "<li class=\"{$t->type}{$t->subtype}{$numeric}{$empty}{$exp_state}\">{$excol}<div class=\"lbl\"><div class=\"key\">{$key}</div><div class=\"val\">{$disp}</div><div class=\"typ\">({$t->type})</div>{$sub}{$len}</div>";
		if ($t->children) {
			$buf .= '<ul>';
			foreach ($t->children as $k => $v)
				$buf .= self::go($v, $k, $exp_lvls - 1, FALSE);
			$buf .= '</ul>';
		}
		$buf .= '</li>';
		$buf .= $st ? '</ul></pre>' : '';

		return $buf;
	}

	// TODO?: get_class_methods()?
	// TODO?: is_numeric()
	public static function checkType($input)
	{
		$type = (object)array(
			'type'			=> null,
			'disp'			=> $input,
			'subtype'		=> null,
			'length'		=> null,
			'children'		=> null
		);
		// avoid detecting strings with names of global functions as callbacks
		if (is_callable($input) && !(is_string($input) && function_exists($input))) {
			$type->type		= 'function';
			$type->disp		= 'fn()';
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
		}
		else if (is_object($input)) {
			$type->type		= 'object';
			$type->disp		= '{ }';
			$type->subtype	= $input instanceof stdClass ? '' : get_class($input);
			$type->children	= array();

			$childs	= (array)$input;		// hacks access to protected and private props
			foreach ($childs as $k => $v) {
				// clean up odd chars left in private/protected names
				$k = preg_replace("/[^\w]?(?:{$type->subtype})?[^\w]?/", '', $k);
				$type->children[$k] = $v;
			}
		//	for SimpleXML dont show length, or find way to detect uniform subnodes and treat as XML [] vs XML {}
		//	$type->length	= count($type->children);
		}
		else if (is_int($input))
			$type->type		= 'integer';
		else if (is_float($input))
			$type->type		= 'float';
		else if (is_string($input)) {
			$type->type		= 'string';
			$type->length	= strlen($input);

			if (substr($input, 0, 5) == '<?xml' && ($xml = simplexml_load_string($input))) {
				$type->subtype	= 'XML';
				$type->children = (array)$xml;
				// dont show length, or find way to detect uniform subnodes and treat as XML [] vs XML {}
				$type->length = null;
			}
			else if ($type->length > 0 && ($input{0} == '{' || $input{0} == '[') && ($json = json_decode($input))) {
				// maybe set subtype as JSON [] or JSON {}, will screw up classname
				$type->subtype	= 'JSON';
				$type->children = (array)$json;
				// dont show length of objects, only arrays
				$type->length = $input{0} == '[' ? count($type->children) : null;
			}
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

		return $type;
	}
}

// css
ob_start();
?>
<style>
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
		font-size: 8pt;
		position: absolute;
		margin: 1px 0 0 -15px;
		cursor: pointer;
	}

	.dump_r .expanded > .excol			{font-size: 10pt;}		/* for FF */
	.dump_r .expanded > .excol:after	{content: "\25BC";}
	.dump_r .collapsed > .excol:after	{content: "\25B6";}
	.dump_r .collapsed > ul				{display: none;}

	.dump_r .lbl						{position: relative; padding-left: 3px; padding-right: 5px;}
	.dump_r .lbl > *					{display: inline-block;}


	.dump_r li > .lbl					{background-color: #F1F1F1;}
	.dump_r li:nth-child(odd) > .lbl	{background-color: #E9E9E9;}

	.dump_r .key						{font-weight: bold; width: 130px;}
	.dump_r .val						{margin-right: 5px; min-width: 5px;}
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
		function toggle(e) {
			if (e.target.className.indexOf("excol") !== -1) {
				e.target.parentNode.className = e.target.parentNode.className.replace(/\bexpanded\b|\bcollapsed\b/, function(m) {
					return m == "collapsed" ? "expanded" : "collapsed";
				});
			}
		}
		document.addEventListener("click", toggle, false);
	})();
</script>
<?php
dump_r::$js = ob_get_contents();
ob_end_clean();
