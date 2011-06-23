<?php
/**
* Copyright (c) 2011, Leon Sorokin
* All rights reserved.
*
* dump_r.php
* better than print_r()
* better than var_dump()
* in HTML
*/
class dump_r {

	public function __construct($input, $exp_lvls = 1000)
	{
		// get the input arg passed to the function
		$src = debug_backtrace();
		$src = (object)$src[0];
		$file = file($src->file);
		$line = $file[$src->line - 1];
		preg_match('/dump_r\((.+?)(?:,|\);)/', $line, $m);
		
		echo self::go($input, $m[1], $exp_lvls);
	}
	
	public static function go($inp, $key = 'root', $exp_lvls = 1000, $st = TRUE)
	{
		$buf = '';
		$buf .= $st ? '<pre id="dump_r"><ul>' : '';
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
		
		if (is_array($input)) {
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
			$type->subtype	= get_class($input);
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
			else if (($input{0} == '{' || $input{0} == '[') && ($json = json_decode($input))) {
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