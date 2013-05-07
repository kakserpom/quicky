<?php
function quicky_modifier_default($string, $default = '') {
	if ($string === FALSE || $string === NULL || $string === '') {
		return $default;
	}
	return $string;
}
