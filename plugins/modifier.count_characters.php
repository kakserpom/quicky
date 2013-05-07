<?php
function quicky_modifier_count_characters($string, $include_spaces = FALSE) {
	if ($include_spaces) {
		return strlen($string);
	}
	return preg_match_all('~\S~', $string, $match);
}


