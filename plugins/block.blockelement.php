<?php
function quicky_block_blockelement($params, $content, $compiler) {
	$block_name = 'blockelement';
	if (!is_array($params)) {
		$params = $compiler->_parse_params($params);
	}
	require_once $compiler->parent->fetch_plugin('compiler.include');
	$type             = $compiler->_dequote($params['type']);
	$params['import'] = 1;
	$params['file']   = '\'blockelements/' . $compiler->_fetch_expr($params['type']) . '.start.tpl\'';
	$start            = quicky_compiler_include($params, $compiler);
	$params['file']   = '\'blockelements/' . $compiler->_fetch_expr($params['type']) . '.end.tpl\'';
	$end              = quicky_compiler_include($params, $compiler);
	return $start . $compiler->_tag_token($content, $block_name) . $end;
}
