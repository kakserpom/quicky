<?php
function quicky_modifier_count_sentences($string) {
	// find periods with a word before but not after.
	return preg_match_all('~\S\.(?!\w)~', $string, $match);
}


