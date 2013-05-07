<?php
function quicky_compiler_case($params, $compiler) {
	if (trim($params) == '') {
		return $compiler->_syntax_error('Empty condition.');
	}
	return '<?php case ' . $compiler->_expr_token($params, FALSE, TRUE) . ': ?>';
}
