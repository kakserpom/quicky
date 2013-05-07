<?php
function quicky_compiler_import($params, $compiler) {
	$params                    = $compiler->_parse_params($params);
	$compiler->_scope_override = 'local';
	$compiler->_def_mode       = 'local';
	$left                      = $compiler->_expr_token(trim($params));
	$compiler->_def_mode       = NULL;
	$compiler->_scope_override = 'global';
	$right                     = $compiler->_expr_token(trim($params));
	$compiler->_scope_override = NULL;
	return '<?php ' . $left . ' = ' . $right . '; ?>';
}
