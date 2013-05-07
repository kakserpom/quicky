<?php
function quicky_compiler_local($params, $compiler) {
	$compiler->_def_mode = 'local';
	$expr                = $compiler->_expr_token(ltrim($params));
	$compiler->_def_mode = NULL;
	return '<?php ' . $expr . '; ?>';
}
