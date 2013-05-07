<?php
function quicky_modifier_count_paragraphs($string) {
	// count \r or \n characters
	return sizeof(preg_split('~[\r\n]+~', $string));
}

