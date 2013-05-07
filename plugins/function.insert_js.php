<?php
function quicky_function_insert_js($params, $quicky) {
	$_files = isset($params['files']) ? $params['files'] : array();
	if (sizeof($_files) == 0) {
		return $quicky->warning('missing files.');
	}
	$_baseurl = isset($params['baseurl']) ? $params['baseurl'] : '';
	$_out     = isset($params['out']) ? $params['out'] : './';
	$_id      = isset($params['id']) ? $params['id'] : dechex(crc32(implode('//', $_files)));
	$fn       = $_id . '.js';
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
			$s = trim($s);
			//$s = preg_replace('~^[\X20\t]*/\*.*?\*/[\X20\t]*$~sm','',$s);
			$data .= '/* Compiled from ' . $v . " */\n";
			$data .= $s . "\n";
		}
		file_put_contents($tf = $f . '.tmp', $data, LOCK_EX);
		rename($tf, $f);
	}
	return '<script type="text/javascript" src="' . $_baseurl . $fn . '"></script>';
}
