<?php

namespace dump_r;

class Node {
	public $id;				// unique id of object, resource, array?
	public $ref;			// is reference?
	public $raw;			// raw value
	public $typ = [];		// type hierarchy
	public $sub = [];		// class-less subtypes eg: get_resource_type, get_class
	public $len;			// length
	public $emp;			// empty?
	public $num;			// numeric?
	public $rec; 			// recordset?
	public $lim = false;	// recursion-limited
	public $vis = [];		// child visibility (objects)
	public $hook = null;	// result of classifier hook
	public $inter;			// intermediate parsed/adapted value
	public $nodes = [];		// child nodes

	use Rend;

	public function __construct(&$raw, Type $hook, $lim = false) {
		$this->raw	= &$raw;
		$this->hook	= $hook;
		$this->inter = $hook->inter;

		$this->build($lim);
	}

	public function build($lim = false) {
		// reference?
		$this->ref = $this->chk_ref();
		// primary type by namespace/class path
		$this->typ = $this->get_typ();
		// classless sub-types
		$this->sub = $this->get_sub();
		// extend sub
//		print_r($this->hook);
		if ($this->hook->types !== null)
			$this->sub = array_merge($this->sub, $this->hook->types);
//		print_r($this->sub);
		// is reference? early exit
		if ($this->ref) {
			$this->sub[] = 'reference';
			return;
		}
		// empty?
		$this->emp = empty($this->raw);
		// numeric?
		$this->num = is_numeric($this->raw);
		// grab raw nodes regardless of depth, get_len() may depend on them
		$this->nodes = $this->hook->nodes !== null ? $this->hook->nodes : $this->get_nodes();
		// get length
		$this->len = $this->hook->length !== null ? $this->hook->length : $this->get_len();
		// is recordset?
		if ($this->rec = $this->chk_rec())
			$this->sub[] = 'recordset';
		// set limit if children exist, but will get trimmed
		$this->lim = $lim && $this->nodes;
		// unset sub-nodes if recursion depth reached
		if ($this->lim) {
			$this->nodes = [];
			$this->vis = [];
		}
	}

	// extracts types from namespace/class hierarchy
	// TODO: get_type() that allows each class to only return own type in chain
	public function get_typ() {
		$path = strtolower(get_class($this));

		$types = str_replace('\\_', '\\', $path);

		return array_slice(explode('\\', $types), 2);
	}

	public function get_sub() {
		return [];
	}

	public function get_nodes() {
		return [];
	}

	public function get_len() {
		return null;
	}

	// will by overridden for simple types
	public function chk_ref() {
		foreach (Type::$dic as $id => &$raw) {
			if ($raw === $this->raw) {
				$this->id = $id;
				return true;
			}
		}

		$this->id = Core::rand_str(16);
		Type::$dic[$this->id] = &$this->raw;

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
