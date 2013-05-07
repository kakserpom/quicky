<?php
function quicky_compiler_global($params, $compiler) {
	$compiler->_def_mode = 'global';
	$expr                = $compiler->_expr_token(ltrim($params));
	$compiler->_def_mode = NULL;
	return '<?php ' . $expr . '; ?>';
}
