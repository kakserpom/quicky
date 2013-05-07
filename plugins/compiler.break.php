<?php
function quicky_compiler_break($params, $compiler) {
	$code = $compiler->_expr_token($params);
	return '<?php break' . ($code !== '' ? ' ' . $code : '') . '; ?>';
}
