<?php
function quicky_compiler_return($params, $compiler) {
	$code = $compiler->_expr_token($params);
	return '<?php return' . ($code !== '' ? ' ' . $code : '') . '; ?>';
}
