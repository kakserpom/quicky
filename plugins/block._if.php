<?php
function quicky_block__if($params, $content, $compiler) {
	$block_name = '_if';
	if (trim($params) === '') {
		return $compiler->_syntax_error('Empty condition.');
	}
	return $compiler->_fetch_expr($compiler->_expr_token($params, FALSE, TRUE, TRUE))
			? $compiler->_tag_token($content, $block_name)
			: '';
}
