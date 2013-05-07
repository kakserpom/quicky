<?php
function quicky_block_while($params, $content, $compiler) {
	$block_name = 'while';
	$expr       = $compiler->_expr_token($params);
	if (trim($expr) === '') {
		return $compiler->_syntax_error('Missing expression in while tag.');
	}
	return '<?php'
			. "\n" . 'while (' . $expr . '): ?>'
			. $compiler->_tag_token($content, $block_name)
			. '<?php endwhile; ?>';
}