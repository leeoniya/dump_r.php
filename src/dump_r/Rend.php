<?php

namespace dump_r;

trait Rend {
	protected static $first		= true;
	protected static $key_width	= 0;

	// cfg opts
	public static $char_width	= 8;
	public static $val_space	= 4;
	public static $xml_pretty	= false;
	public static $sql_pretty	= false;
	public static $json_pretty	= false;
	public static $recset_tbls	= true;

	public function disp_val() {
		return (string)$this->raw;
	}

	public function disp_val2() {
		return null;
	}

	protected static function vfy_sql_pretty_deps() {
		if (self::$sql_pretty && !class_exists('\SqlFormatter')) {
			self::$sql_pretty = false;
			trigger_error("Setting 'sql_pretty' was disabled due to missing lib: jdorn/sql-formatter", E_USER_WARNING);
		}
	}

	// public html renderer
	public function html0($file, $line, $key, $expand = 1e3) {
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

		$buf .= $this->html($key, 2, $expand, true);

		$buf .= '</pre>';
		$buf .= $br;
		$buf .= "<style>#{$dump_id} .key {min-width: " . (self::$key_width * self::$char_width) . 'px;}</style>';

		self::$key_width = 0;

		return $buf;
	}

	public function html($key, $vis = 2, $expand = 1e3, $init = false) {
		self::$key_width = max(self::$key_width, strlen($key));

		$class = $this->html_css_class($vis, $expand);

		$buf = '';
		$buf .= '<li class="' . implode(' ', $class) . '">';
		$buf .= $this->nodes || $this->lim ? '<div class="excol"></div>' : '';
		$buf .= '<div class="lbl">';
		$buf .= '<div class="key">' . $key . '</div>';

		$buf .= $this->html_value();

		$buf .= '</div>';

		if ($this->nodes) {
			if ($this->rec && self::$recset_tbls)
				$buf .= $this->html_nodes_recordset($expand - 1);
			else
				$buf .= $this->html_nodes($expand - 1);
		}

		$buf .= '</li>';

		return $init ? "<ul>{$buf}</ul>" : $buf;
	}

	public function html_value() {
		$buf = '';

		$val = htmlspecialchars($this->disp_val(), ENT_NOQUOTES);

		$val2 = $this->disp_val2();
		$val2 = $val2 !== null ? ' data-val="' . str_replace("\n", '\\\\n', htmlspecialchars($val2, ENT_NOQUOTES)) . '"' : '';

		// trailing newlines dont render in <pre>, so repeat them
		$val = preg_replace('/(\r\n|\r|\n)$/', '$1$1', $val);

		if ($this->ref)
			$val = str_replace('*', "<a href=\"#{$this->id}\">*</a>", $val);
		else if ($this->id)
			$val = "<a name=\"{$this->id}\">{$val}</a>";

		$buf .= "<div class=\"val\"{$val2}>" . $val . '</div>';

		if ($this->len)
			$buf .= '<div class="len">' . htmlspecialchars($this->len, ENT_NOQUOTES) . '</div>';

		$typs = array_merge($this->typ, $this->sub);
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
	public function html_nodes($expand = 1e3) {
		$buf = '';
		foreach ($this->nodes as $key => $node) {
			$vis = array_key_exists($key, $this->vis) ? $this->vis[$key] : 2;
			$buf .= $node->html($key, $vis, $expand);
		}
		return "<ul>{$buf}</ul>";
	}

	// child renderer for recordsets
	// TODO: split recordset nodes renderer
	public function html_nodes_recordset($expand) {
		$buf  = '<ul>';
		$buf .= '<li>';
		$buf .= '<table>';
		$buf .= '<tr>';
		$buf .= '<th class="key">#</th>';
		foreach ($this->nodes[0]->nodes as $k => $v) {
			$vis = array_key_exists($k, $this->nodes[0]->vis) ? $this->nodes[0]->vis[$k] : 2;
			$class = '';
			if ($vis = $this->html_vis_class($vis))
				$class = " class=\"{$vis}\"";
			$buf .= "<th{$class}>{$k}</th>";
		}
		$buf .= '<th></th>';
		$buf .= '</tr>';
		foreach ($this->nodes as $i => $row) {
			$class = $row->html_css_class(2, 1);
			$buf .= '<tr class="' . implode(' ', $class) . '">';
			$buf .= "<th class=\"key\">{$i}</th>";
			foreach ($row->nodes as $k => $node) {
				$class2 = $node->html_css_class(2, 1);
				$val = htmlspecialchars($node->disp_val(), ENT_NOQUOTES);
				$buf .= "<td class=\"" . implode(' ', $class2) . "\"><div class=\"lbl\"><div class=\"val\">{$val}</div></div></td>";
			}
			$buf .= "<td class=\"lbl\">" . $row->html_value() . "</td>";
			$buf .= '</tr>';
		}
		$buf .= '</table>';
		$buf .= '</li>';
		$buf .= '</ul>';
		return $buf;
	}

	public function html_css_class($vis, $expand) {
		$class = array_merge($this->typ, $this->sub, $this->hook->classes);

		if ($this->emp)
			$class[] = 'empty';
		if ($this->num)
			$class[] = 'numeric';

		if ($vis = $this->html_vis_class($vis))
			$class[] = $vis;

		if ($this->lim) {
			$class[] = 'limited';
			$class[] = 'collapsed';
		}
		else if ($this->nodes)
			$class[] = $expand > 0 ? 'expanded' : 'collapsed';

		return $class;
	}

	public function html_vis_class($vis) {
		switch ($vis) {
			case 0: return 'private';
			case 1: return 'protected';
			case 2: return null;			// public
		}
	}

/*-------------------------------------------------------------*/

	// public text renderer
	// pass depth?
	public function text0($file, $line, $key) {
		self::vfy_sql_pretty_deps();

		$buf = '';

		$loc = "{$file} (line {$line})";
		$loc .= "\n" . str_repeat('-', strlen($loc)) . "\n";

		$buf .= $loc;

		$buf .= $this->text($key);

		self::$key_width = 0;

		return $buf;
	}

	public function text($key, $depth = 0, $init = true) {
		self::$key_width = max(self::$key_width, strlen($key));

		$val = $this->disp_val();

		if ($this->typ[0] == 'string') {
			// json-encode multi-line strings. quote others.
			$val = preg_match('/[\r\n]/m', $val) ? 'strJSON|' . json_encode($val) : "'{$val}'";		// string nodes only (multi-line)
		}

		$ext = $this->text_val_suf();

		$val .= $ext ? ' ' . implode(' ', $ext): '';

		// process sub-nodes
		$cbuf = $this->nodes ? $this->text_nodes($depth) : '';

		if ($init) {
			$all = $this->text_ind_line($key . '=' . $val, $depth) . $cbuf;
			return $this->text_ind_vals($all);
		}

		return [$key, $val, $cbuf];
	}

	public function text_nodes($depth) {
		$buf = '';
		foreach ($this->nodes as $key => $node) {
		//	$vis = array_key_exists($key, $this->vis) ? $this->vis[$key] : 2;

			$v = $node->text($key, $depth + 1, false);

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

	public function text_val_suf() {
		$ext = [];

		if ($this->len)
			$ext[] = $this->len;
		if (count($this->typ) > 1)
			$ext[] = implode(' ', array_slice($this->typ, 1));
		if (!$this->ref && $this->sub && $this->sub[0] != 'stdClass')
			$ext[] = implode(' ', $this->sub);

		return $ext;
	}
}