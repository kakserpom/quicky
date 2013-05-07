<?php
function quicky_modifier_default_var(&$string, $default = '') {
	if ($string === FALSE || $string === NULL || $string === '') {
		return $default;
	}
	return $string;
}
