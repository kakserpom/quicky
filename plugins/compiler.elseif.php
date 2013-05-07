<?php
function quicky_compiler_elseif($params, $compiler) {
	if (trim($params) == '') {
		return $compiler->_syntax_error('Empty condition.');
	}
	return '<?php elseif (' . $compiler->_expr_token($params, FALSE, TRUE) . '): ?>';
}
