<?php
function quicky_compiler_export($params, $compiler) {
	$compiler->_scope_override = 'global';
	$left                      = $compiler->_expr_token(trim($m[2]));
	$compiler->_scope_override = 'local';
	$right                     = $compiler->_expr_token(trim($m[2]));
	$compiler->_scope_override = NULL;
	return '<?php ' . $left . ' = ' . $right . '; ?>';
}
