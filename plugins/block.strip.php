<?php
function quicky_block_strip($params, $content, $compiler) {
	$block_name = 'strip';
	$content    = preg_replace('~[\t ]*[\r\n]+[\t ]*~', '', $content);
	return $compiler->_tag_token($content, $block_name);
}
