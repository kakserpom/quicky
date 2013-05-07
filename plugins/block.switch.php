<?php
function quicky_block_switch($params, $content, $compiler) {
	$block_name = 'switch';
	$expr       = $compiler->_expr_token($params);
	if (trim($expr) === '') {
		return $compiler->_syntax_error('Missing expression in switch tag.');
	}
	$block = $compiler->_tag_token($content, $block_name);
	$block = ltrim($block);
	return '<?php'
			. "\n" . 'switch (' . $expr . '): ?>'
			. $block
			. '<?php endswitch; ?>';
}