<?php
function quicky_modifier_capitalize($string, $uc_digits = false) {
	quicky_modifier_capitalize_ucfirst(NULL, $uc_digits);
	return preg_replace_callback('!\'?\b\w(\w|\')*\b!', 'quicky_modifier_capitalize_ucfirst', $string);
}

function quicky_modifier_capitalize_ucfirst($string, $uc_digits = null) {
	static $_uc_digits = FALSE;
	if (isset($uc_digits)) {
		$_uc_digits = $uc_digits;
		return;
	}
	if (substr($string[0], 0, 1) != '\'' && !preg_match('!\d!', $string[0]) || $_uc_digits) {
		return ucfirst($string[0]);
	}
	else {
		return $string[0];
	}
}
