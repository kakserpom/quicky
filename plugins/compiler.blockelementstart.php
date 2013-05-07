<?php
function quicky_compiler_blockelementstart($params, $compiler) {
	$params = $compiler->_parse_params($params);
	require_once $compiler->parent->fetch_plugin('compiler.include');
	$type             = $compiler->_dequote($params['type']);
	$params['import'] = 1;
	$params['file']   = '\'blockelements/' . $compiler->_fetch_expr($params['type']) . '.start.tpl\'';
	$start            = quicky_compiler_include($params, $compiler);
	return $start;
}
