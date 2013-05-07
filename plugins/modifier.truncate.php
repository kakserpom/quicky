<?php
function quicky_modifier_truncate($string, $length = 80, $etc = '...',
	$break_words = FALSE, $middle = FALSE) {
	if ($length == 0) {
		return '';
	}
	if (mb_strlen($string) > $length) {
		$length -= mb_strlen($etc);
		if (!$break_words && !$middle) {
			$string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length + 1));
		}
		if (!$middle) {
			return mb_substr($string, 0, $length) . $etc;
		}
		else {
			return mb_substr($string, 0, $length / 2) . $etc . mb_substr($string, -$length / 2);
		}
	}
	else {
		return $string;
	}
}

/* vim: set expandtab: */

