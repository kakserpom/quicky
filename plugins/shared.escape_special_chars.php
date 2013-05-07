<?php
function quicky_function_escape_special_chars($string) {
	if (!is_array($string)) {
		$string = preg_replace('!&(#?\w+);!', '%%%QUICKY_START%%%\\1%%%QUICKY_END%%%', $string);
		$string = htmlspecialchars($string);
		$string = str_replace(array('%%%QUICKY_START%%%', '%%%QUICKY_END%%%'), array('&', ';'), $string);
	}
	return $string;
}
