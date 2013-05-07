<?php
function quicky_modifier_field_default($string, $default = '') {
	if (!isset($string) || ($string === FALSE) || ($string === NULL)) {
		return $default;
	}
	else {
		return $string;
	}
}
