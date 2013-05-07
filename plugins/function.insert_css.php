<?php
function quicky_function_insert_css($params, $quicky) {
	$_files = isset($params['files']) ? $params['files'] : array();
	if (sizeof($_files) == 0) {
		return $quicky->warning('missing files.');
	}
	$_baseurl = isset($params['baseurl']) ? $params['baseurl'] : '';
	$_out     = isset($params['out']) ? $params['out'] : './';
	$_id      = isset($params['id']) ? $params['id'] : dechex(crc32(implode('//', $_files)));
	$fn       = $_id . '.css';
	$f        = $_out . $fn;
	$c        = (!file_exists($f)) || (isset($params['force_compile']) && $params['force_compile']);
	if (!$c) {
		$m = filemtime($f);
		foreach ($_files as $v) {
			if ((!file_exists($v)) || (filemtime($v) > $m)) {
				$c = TRUE;
				break;
			}
		}
	}
	if ($c) {
		$data = '';
		foreach ($_files as $v) {
			$s = file_get_contents($v);
			$s = str_replace("\r", '', $s);
			$s = preg_replace('~/\*.*?\*/~s', '', $s);
			$s = preg_replace('~[\x20\t]+~', ' ', $s);
			$s = preg_replace('~^\s+$~m', '', $s);
			$s = preg_replace('~\n{2,}~', "\n", $s);
			$s = trim($s);
			$data .= '/* Compiled from ' . $v . " */\n";
			$data .= $s . "\n";
		}
		$data = rtrim($data);
		file_put_contents($tf = $f . '.tmp', $data, LOCK_EX);
		rename($tf, $f);
	}
	return '<link href="' . $_baseurl . $fn . '" rel="stylesheet" type="text/css" />';
}
