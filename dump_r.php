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
	public function __construct($input, $key = 'root', $exp_lvls = 1000)
	{
		echo self::go($input, $key, $exp_lvls);
	}
	
	public static function go($inp, $key = 'root', $exp_lvls = 1000, $st = TRUE)
	{
		$buf = '';
		$buf .= $st ? '<pre id="dump_r"><ul>' : '';
		$t = self::checkType($inp);
		$v = is_array($inp) ? '[ ]' : (is_object($inp) ? '{ }' : $inp);
		if (is_resource($v)) {
			preg_match('/#\d+/', (string)$inp, $matches);
			$v = $matches[0];
		}
		$v = $v === TRUE ? 'true' : ($v === FALSE ? 'false' : ($v === NULL ? 'null' : $v));
		$x = isset($t[1]) ? "<div class=\"xtra\">$t[1]</div>" : '';
		$excol = is_array($t[2]) && count($t[2]) > 0 ? '<div class="excol"></div>' : null;
		$exp = $excol ? ($exp_lvls > 0 ? ' expanded' : ' collapsed') : '';
		$t[0] .= $v == 'false' ? ' false' : ($v == 'true' ? ' true' : '');
		$buf .= "<li class=\"$t[0]$exp\">$excol<div class=\"lbl\"><div class=\"key\">$key</div><div class=\"val\">$v</div>$x</div>";
		if ($t[2]) {
			$buf .= '<ul>';
			foreach ($t[2] as $k => $v)
				$buf .= self::go($v, $k, $exp_lvls - 1, FALSE);
			$buf .= '</ul>';
		}
		$buf .= '</li>';
		$buf .= $st ? '</ul></pre>' : '';
		
		return $buf;
	}
	
	// tweaked version of checkType() http://jacksleight.com/old/assets/blog/really-shiny/scripts/php-dump.txt
	// TODO?: get_class_methods()?
	// TODO?: is_numeric()
	public static function checkType($input)
	{
		$type = array(null, null, false);
		
		if(is_array($input)) {
			$type[0] = 'array';
			$type[1] = count($input);
			$type[2] = $input;
		}
		elseif(is_resource($input)) {
			$type[0] = 'resource';
			$type[1] = get_resource_type($input);
		}
		elseif(is_object($input)) {
			$type[0] = 'object';
			$type[1] = get_class($input);
			$type[2] = get_object_vars($input);
		}
		elseif(is_int($input))
			$type[0] = 'integer';
		elseif(is_float($input))
			$type[0] = 'float';
		elseif(is_string($input)) {
			$type[0] = 'string';
			$type[1] = strlen($input);
			if (substr($input, 0, 5) == '<?xml' && ($xml = simplexml_load_string($input)))
				$type[2] = (array)$xml;
			else if (($input{0} == '{' || $input{0} == '[') && ($json = json_decode($input)))
				$type[2] = (array)$json;
			
		}
		elseif(is_bool($input))
			$type[0] = 'boolean';
		elseif(is_null($input))
			$type[0] = 'null';
		else
			$type[0] = gettype($input);
		
		return $type;
	}
}