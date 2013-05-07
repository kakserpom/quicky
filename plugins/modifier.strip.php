<?php
function quicky_modifier_strip($text, $replace = ' ') {
	return preg_replace('~\s+~', $replace, $text);
}

