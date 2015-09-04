<?php

namespace dump_r\Node\_String;
use dump_r\Node\_String;

class _Datetime extends _String {
	public function get_len() {
		$ts = strtotime($this->raw);
		return $this->rel_date($ts);
	}

	public function rel_date($datetime) {
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