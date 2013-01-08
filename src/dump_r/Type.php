<?php

namespace dump_r;

class Type {
	static $dic = array();	// global object registry for ref detection

	public $id;				// unique id of object, resource, array?
	public $ref;			// is reference?
	public $raw;			// raw value
	public $typ = array();	// type hierarchy
	public $sub = array();	// class-less subtypes eg: get_resource_type, get_class
	public $val;			// intermediate parsed/adapted value ($intr)
	public $len;			// length
	public $emp;			// empty?
	public $num;			// numeric?
	public $rec; 			// recordset?
	public $lim = false;	// recursion-limited
	public $vis = array();	// child visibility (objects)
	public $nodes = array();// child nodes

/*--------------------------Factory----------------------------*/
	static $hooks = array();

	// adds type classifier callbacks
	public static function hook($key, $fn) {
		if ($key === '*')
			$key = '';

		if (!array_key_exists($key, self::$hooks))
			self::$hooks[$key] = array();

		self::$hooks[$key][] = $fn;
	}

	// iterative classifier
	public static function pick($raw) {
		$type = ''; $intr = null;
		while (array_key_exists($type, self::$hooks)) {
			$last = $type;

			foreach (self::$hooks[$type] as $fn) {
				if ($subt = $fn($raw, $intr)) {
					if (is_array($subt)) {
						$intr	= $subt[1];
						$subt	= $subt[0];
					}
					$type .= ($type ? '\\' : '') . $subt;
					break;
				}
			}

			if ($type == $last) break;
		}

		return array($type, $intr);
	}

	// factory method
	public static function fact($raw, $depth = 1000) {
		$pickd = self::pick($raw);

		$class = new \ReflectionClass(__NAMESPACE__ . '\\Type\\' . $pickd[0]);
		return $class->newInstance($raw, $depth, $pickd[1]);
	}
/*-------------------------------------------------------------*/

	// $intr: intermediate pre-proccesed $raw - a helpful side-effect of classifier
	public function __construct($raw, $depth = 1000, $intr = null) {
		$this->raw = $raw;
		$this->val = $intr;

		$this->build($depth);
	}

	public function build($depth = 1000) {
		// reference?
		$this->ref = $this->chk_ref();
		// primary type by namespace/class path
		$this->typ = $this->get_typ();
		// classless sub-types
		$this->sub = $this->get_sub();
		// is reference? early exit
		if ($this->ref) {
			$this->sub[] = 'reference';
			return;
		}
		// empty?
		$this->emp = empty($this->raw);
		// numeric?
		$this->num = is_numeric($this->raw);
		// grab nodes regardless of depth, get_len() may depend on them
		$this->nodes = $this->get_nodes();
		// get length
		$this->len = $this->get_len();
		// is recordset?
		if ($this->rec = $this->chk_rec())
			$this->sub[] = 'recordset';
		// set limit if children exist, but will get trimmed
		$this->lim = $this->nodes && $depth === 0;
		// unset sub-nodes if recursion depth reached
		if ($this->lim) {
			$this->nodes = array();
			$this->vis = array();
		}
		// build sub-nodes
		if ($this->nodes) {
			foreach ($this->nodes as $key => $raw)
				$this->nodes[$key] = self::fact($raw, $depth - 1, '');
		}
	}

	// extracts types from namespace/class hierarchy
	// TODO: get_type() that allows each class to only return own type in chain
	public function get_typ() {
		$path = strtolower(get_class($this));
		// replace language-constructs
		$types = strtr($path, array('array0'=>'array','function0'=>'function'));

		return array_slice(explode('\\', $types), 2);
	}

	public function get_sub() {
		return array();
	}

	public function get_nodes() {
		return array();
	}

	public function get_len() {
		return null;
	}

	public function chk_ref() {
		return false;
	}

	// recordset assertion
	public function chk_rec() {
		$rs = false;
		// only multi-element indexed-only arrays
		if ($this->len > 1 && array_key_exists(0, $this->nodes)) {
			$n0 = reset($this->nodes);
			$n1 = next($this->nodes);
			// 0,1 same-type elements
			if (is_object($n0) && is_object($n1) || is_array($n0) && is_array($n1)) {
				$n0 = (array)$n0;
				$n1 = (array)$n1;
				// 0,1 identical keys
				if (array_keys($n0) === array_keys($n1)) {
					// non-int keys, simple values only
					foreach ($n0 as $k => $v) {
						//$int_key = !is_int($k) ? (ctype_digit($k)) : true;
						if (/*$int_key || */is_object($v) || is_array($v) || is_resource($v)) {
							$rs = false;
							break;
						}
						$rs = true;
					}
				}
			}
		}

		return $rs;
	}
}