<?php

namespace dump_r;

class Type {
	static $dic		= [];		// global object registry for ref detection
	static $hooks	= [];

	public $class	= [];		// string of iterative node class to instantiate
	public $types	= [];		// array of subtypes to show (also added as css classes)
	public $length	= null;		// length to display, can by hijacked for other uses
	public $nodes	= [];		// subnodes to iterate
	public $classes	= [];		// additional css classes to add to node
	public $value	= null;		// value to display, can be re-formatted for easier display (eg: binary strings)
	public $depth	= null;		// depth that this node should be recursed to
	public $inter	= null;		// intermediate value from classifier hook

	public function __construct($class = [], Array $types = null, $length = null, Array $nodes = null, Array $classes = [], $value = null, $depth = null, $inter = null) {
		$this->class	= $class;
		$this->types	= $types;
		$this->length	= $length;
		$this->nodes	= $nodes;
		$this->classes	= $classes;
		$this->value	= $value;
		$this->depth	= $depth;
		$this->inter	= $inter;
	}

	// adds type classifier callbacks
	public static function hook($key, $fn) {
		if ($key === '*')
			$key = '';

		if (!array_key_exists($key, self::$hooks))
			self::$hooks[$key] = [];

		self::$hooks[$key][] = $fn;
	}

	// iterative classifier
	public static function pick($raw, Array $path = []) {
		$type = '';
		$subt = new Type();

		while (array_key_exists($type, self::$hooks)) {
			$last = $type;
			foreach (self::$hooks[$type] as $fn) {
				$resp = $fn($raw, $subt, $path);

				if ($resp === false)
					return false;

				if ($resp instanceof Type) {
					$subt = $resp;
					$type = implode('\\', $subt->class);
				}
			}

			if ($type == $last) break;
		}

		return [$type, $subt];
	}

	// factory method
	public static function fact(&$raw, Array $path = [], $depth = 1e3) {
		$picked = self::pick($raw, $path);

		if ($picked === false)
			return false;

		list($class, $hook) = $picked;

		$depth = $hook->depth !== null ? $hook->depth : $depth;

		$is_lim = $depth === 0;

		$class = __NAMESPACE__ . '\\Node\\' . $class;

		$node = new $class($raw, $hook, $is_lim);

		// build sub-nodes
		if (!empty($node->nodes)) {
			$raw_nodes = $node->nodes;
			$node->nodes = [];

			foreach ($raw_nodes as $key => &$raw) {
				$node2 = self::fact($raw, array_merge($path, [$key]), $depth - 1);

				if ($node2)
					$node->nodes[$key] = $node2;
			}
		}

		return $node;
	}
}