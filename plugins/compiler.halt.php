<?php
function quicky_compiler_halt($params, $compiler) {
	$this->_halt = TRUE;
	$code        = $compiler->_expr_token($params);
	return '<?php return' . ($code !== '' ? ' ' . $code : '') . '; ?>';
}
