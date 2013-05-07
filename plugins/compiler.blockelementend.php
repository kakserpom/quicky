<?php
function quicky_compiler_blockelementend($params, $compiler) {
	$params = $compiler->_parse_params($params);
	require_once $compiler->parent->fetch_plugin('compiler.include');
	$type             = $compiler->_dequote($params['type']);
	$params['import'] = 1;
	$params['file']   = '\'blockelements/' . $compiler->_fetch_expr($params['type']) . '.end.tpl\'';
	$end              = quicky_compiler_include($params, $compiler);
	return $end;
}
