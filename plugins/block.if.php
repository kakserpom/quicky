<?php
function quicky_block_if($params, $content, $compiler) {
	$block_name = 'if';
	if (trim($params) == '') {
		return $compiler->_syntax_error('Empty condition.');
	}
	return '<?php ' . $block_name . ' (' . $compiler->_expr_token($params, FALSE, TRUE) . '): ?>' . $compiler->_tag_token($content, $block_name) . '<?php endif; ?>';
}
