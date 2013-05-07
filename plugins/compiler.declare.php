<?php
function quicky_compiler_declare($params, $compiler) {
	if (!isset($params['name'])) {
		return $compiler->_syntax_error('declare: missing "name" parameter in include function');
	}
	$name  = $compiler->_fetch_expr($compiler->_expr_token($params['name']));
	$value = isset($params['value']) ? $compiler->_fetch_expr($compiler->_expr_token($params['value'])) : TRUE;
	if ($name == 'depart_scopes') {
		$compiler->parent->local_depart_scopes = (bool)$value;
	}
	return '';
}
