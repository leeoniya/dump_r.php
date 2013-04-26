<?php

namespace dump_r;

class Rend {
	protected static $first		= true;
	protected static $key_width	= 0;
	protected static $renderers	= array();

	// cfg opts
	const CHAR_WIDTH = 8;
	public static $val_space	= 4;
	public static $xml_pretty	= false;
	public static $sql_pretty	= false;
	public static $json_pretty	= false;
	public static $detect_tbls	= true;

/*--------------------------Factory----------------------------*/

	public static $hooks = array();

	public static function hook($key, $fn) {
		if ($key === '*')
			$key = '';

		if (!array_key_exists($key, self::$hooks))
			self::$hooks[$key] = array();

		self::$hooks[$key][] = $fn;
	}

	// iterative classifier
	public static function pick($node) {
		$type = '';
		while (array_key_exists($type, self::$hooks)) {
			$last = $type;

			foreach (self::$hooks[$type] as $fn) {
				$subt = is_callable($fn) ? call_user_func($fn, $node) : $fn;

				if (!$subt) continue;

				$type .= ($type ? '\\' : '') . $subt;
				break;
			}

			if ($type == $last) break;
		}

		return $type;
	}

	public static function fact($node) {
		$class = self::pick($node);

		if (!array_key_exists($class, self::$renderers)) {
			$refl = new \ReflectionClass(__NAMESPACE__ . '\\Rend\\' . $class);
			$inst =  $refl->newInstance();
			self::$renderers[$class] = $inst;
		}

		return self::$renderers[$class];
	}

/*-------------------------------------------------------------*/

	public function get_val($node) {
		return (string)$node->raw;
	}

	public function get_len($node) {
		return $node->len ? $node->len : '';
	}

/*-------------------------------------------------------------*/
	protected static function vfy_sql_pretty_deps() {
		if (self::$sql_pretty && !class_exists('\SqlFormatter')) {
			self::$sql_pretty = false;
			trigger_error("Setting 'sql_pretty' was disabled due to missing lib: jdorn/sql-formatter", E_USER_WARNING);
		}
	}

	// public html renderer
	public static function html0($file, $line, $key, Type $node, $expand = 1000) {
		self::vfy_sql_pretty_deps();

		$br = '<div style="clear:both;"></div>';
		$buf = $br;

		// inject css and js on first dump to page
		if (self::$first) {
			$buf .= '<style id="dump_r">' . file_get_contents(__DIR__ . '../../assets/dump_r.css') . '</style>';
			$buf .= '<script>' . file_get_contents(__DIR__ . '../../assets/dump_r.js') . '</script>';
			self::$first = false;
		}

		$dump_id = 'dump-' . rand(100,999);

		$buf .= '<pre class="dump_r" id="' . $dump_id . '">';
		$buf .= "<div class=\"file-line\">{$file} (line {$line})</div>";

		// select renderer
		$rend = self::fact($node);
		$buf .= $rend->html($node, $key, 2, $expand);

		$buf .= '</pre>';
		$buf .= $br;
		$buf .= "<style>#{$dump_id} .key {min-width: " . (self::$key_width * self::CHAR_WIDTH) . 'px;}</style>';

		self::$key_width = 0;

		return $buf;
	}

	public function html($node, $key = 'root', $vis = 2, $expand = 1000, $init = true) {
		self::$key_width = max(self::$key_width, strlen($key));

		$class = $this->html_css_class($node, $vis, $expand);

		$buf = '';
		$buf .= '<li class="' . implode(' ', $class) . '">';
		$buf .= $node->nodes || $node->lim ? '<div class="excol"></div>' : '';
		$buf .= '<div class="lbl">';
		$buf .= '<div class="key">' . $key . '</div>';

		$buf .= $this->html_value($this, $node);

		$buf .= '</div>';

		if ($node->nodes) {
			if ($node->rec)
				$buf .= $this->html_nodes_recordset($node, $expand-1);
			else
				$buf .= $this->html_nodes($node, $expand-1);
		}

		$buf .= '</li>';

		return $init ? "<ul>{$buf}</ul>" : $buf;
	}

	public function html_value(Rend $rend, Type $node) {
		$buf = '';

		$val = htmlspecialchars($rend->get_val($node), ENT_NOQUOTES);

		if ($node->ref)
			$val = str_replace('*', "<a href=\"#{$node->id}\">*</a>", $val);
		else if ($node->id)
			$val = "<a name=\"{$node->id}\">{$val}</a>";

		$buf .= '<div class="val">' . $val . '</div>';

		if ($len = $rend->get_len($node))
			$buf .= '<div class="len">' . htmlspecialchars($len, ENT_NOQUOTES) . '</div>';

		$typs = array_merge($node->typ, $node->sub);
		$buf2 = '';
		foreach ($typs as $t) {
			switch ($t) {
				case 'reference': $class = ' class="ref"'; break;
				case 'stdClass':  $class = ' class="std"'; break;
				default: $class = '';
			}
			$buf2 .= "<i{$class}>{$t}</i>";
		}
		$buf .= '<div class="typ">' . $buf2 . '</div>';

		return $buf;
	}

	// child renderer
	public function html_nodes($node, $expand) {
		$buf = '';
		foreach ($node->nodes as $key => $node2) {
			$vis = array_key_exists($key, $node->vis) ? $node->vis[$key] : 2;
			$rend = self::fact($node2);
			$buf .= $rend->html($node2, $key, $vis, $expand, false);
		}
		return "<ul>{$buf}</ul>";
	}

	// child renderer for recordsets
	// TODO: split recordset nodes renderer
	public function html_nodes_recordset($node, $expand) {
		$buf  = '<ul>';
		$buf .= '<li>';
		$buf .= '<table>';
		$buf .= '<tr>';
		$buf .= '<th style="text-align: left;">#</th>';
		foreach ($node->nodes[0]->nodes as $k => $v)
			$buf .= "<th>{$k}</th>";
		$buf .= '<th></th>';
		$buf .= '</tr>';
		foreach ($node->nodes as $i => $row) {
			$rend = self::fact($row);
			$class = $rend->html_css_class($row, 2, 1);
			$buf .= '<tr class="' . implode(' ', $class) . '">';
			$buf .= "<th class=\"key\">{$i}</th>";
			foreach ($row->nodes as $k => $node2) {
				$rend2 = self::fact($node2);
				$class2 = $rend2->html_css_class($node2, 2, 1);
				$val = htmlspecialchars($rend2->get_val($node2), ENT_NOQUOTES);
				$buf .= "<td class=\"" . implode(' ', $class2) . "\"><div class=\"lbl\"><div class=\"val\">{$val}</div></div></td>";
			}
			$buf .= "<td class=\"lbl\">" . $rend->html_value($rend, $row) . "</td>";
			$buf .= '</tr>';
		}
		$buf .= '</table>';
		$buf .= '</li>';
		$buf .= '</ul>';
		return $buf;
	}

	public function html_css_class($node, $vis, $expand) {
		$class = array_merge($node->typ, $node->sub);

		if ($node->emp)
			$class[] = 'empty';
		if ($node->num)
			$class[] = 'numeric';

		if ($vis == 0)
			$class[] = 'private';
		else if ($vis == 1)
			$class[] = 'protected';

		if ($node->lim) {
			$class[] = 'limited';
			$class[] = 'collapsed';
		}
		else if ($node->nodes)
			$class[] = $expand > 0 ? 'expanded' : 'collapsed';

		return $class;
	}

/*-------------------------------------------------------------*/

	// public text renderer
	public static function text0($file, $line, $key, Type $node) {
		self::vfy_sql_pretty_deps();

		$buf = '';

		$loc = "{$file} (line {$line})";
		$loc .= "\n" . str_repeat('-', strlen($loc)) . "\n";

		$buf .= $loc;

		$rend = self::fact($node);
		$buf .= $rend->text($node, $key, 2);

		self::$key_width = 0;

		return $buf;
	}

	public function text($node, $key = 'root', $vis = 2, $depth = 0, $init = true) {
		self::$key_width = max(self::$key_width, strlen($key));

		$val = $this->get_val($node);

		if ($node->typ[0] == 'string') {
			// json-encode multi-line strings. quote others.
			$val = preg_match('/[\r\n]/m', $val) ? 'strJSON|' . json_encode($val) : "'{$val}'";		// string nodes only (multi-line)
		}

		$ext = $this->text_val_suf($node);

		$val .= $ext ? ' ' . implode(' ', $ext): '';

		// process sub-nodes
		$cbuf = $node->nodes ? $this->text_nodes($node, $depth) : '';

		if ($init) {
			$all = $this->text_ind_line($key . '=' . $val, $depth) . $cbuf;
			return $this->text_ind_vals($all);
		}

		return array($key, $val, $cbuf);
	}

	public function text_nodes($node, $depth) {
		$buf = '';
		foreach ($node->nodes as $k => $node2) {
			$vis = array_key_exists($k, $node->vis) ? $node->vis[$k] : 2;

			$rend = self::fact($node2);
			$v = $rend->text($node2, $k, $vis, $depth + 1, false);

			$buf .= $this->text_ind_line($v[0] . '=' . $v[1], $depth + 1) . $v[2];
		}
		return $buf;
	}

	// indent line
	public function text_ind_line($str, $num) {
		return str_repeat('  ', $num) . $str . "\n";
	}

	// indent values
	public function text_ind_vals($str) {
		$len = self::$key_width + self::$val_space;

		$buf = '';
		preg_match_all('/^(\s*)(.*?)=(.*?)$/m', $str, $lines, PREG_SET_ORDER);
		foreach ($lines as $ln) {
			// key + value's first line padding
			$buf .= $ln[1] . str_pad($ln[2], $len, ' ');
			// multiline
			if (preg_match('/^strJSON\|(".*")(\s+.*)/m', $ln[3], $mln)) {				// string nodes only (multi-line)
				// indent remaining lines
				$str = "'" . json_decode($mln[1]) . "'"; $i = 0;
				$buf .= preg_replace_callback('/^.*/m', function($m) use (&$i, $ln, $len) {
					if ($i++ == 0) return $m[0];
					return str_repeat(' ', $len + 1) . $ln[1] . $m[0];
				}, $str);
				$buf .= $mln[2];
			}
			else
				$buf .= $ln[3];

			$buf.= "\n";
		}

		return $buf;
	}

	public function text_val_suf($node) {
		$ext = array();

		if ($node->len)
			$ext[] = $this->get_len($node);
		if (count($node->typ) > 1)
			$ext[] = implode(' ', array_slice($node->typ, 1));
		if (!$node->ref && $node->sub && $node->sub[0] != 'stdClass')
			$ext[] = implode(' ', $node->sub);

		return $ext;
	}
}