<?php
function quicky_modifier_signedint($int) {
	$int = intval($int);
	return ($int > 0 ? '+' : '') . $int;
}

