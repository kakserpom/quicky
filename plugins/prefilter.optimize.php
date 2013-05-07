<?php
function quicky_prefilter_optimize($s, $quicky) {
	$s = preg_replace('~[\x20\t]+~', ' ', $s);
	$s = str_replace("\r", '', $s);
	$s = str_replace(' >', '>', $s);
	$s = preg_replace('~^\s+~m', '', $s);
	$s = preg_replace('~(\n\s*)+~', "\n", $s);
	return $s;
}
