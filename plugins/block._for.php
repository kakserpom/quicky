<?php
function quicky_block__for($params, $content, $compiler) {
	$block_name = '_for';
	$params     = $compiler->_parse_params($params);
	if (!isset($params['start'])) {
		$params['start'] = 0;
	}
	if (!isset($params['step'])) {
		$params['step'] = 1;
	}
	if (!isset($params['loop'])) {
		return $compiler->_syntax_error('Missing parameter \'loop\' in for tag.');
	}
	if (!isset($params['value'])) {
		return $compiler->_syntax_error('Missing parameter \'value\' in for tag.');
	}
	$params['value'] = $compiler->_varname($params['value']);
	$params['start'] = $compiler->_fetch_expr($params['start'], FALSE, FALSE, TRUE);
	$params['step']  = $compiler->_fetch_expr($params['step'], FALSE, FALSE, TRUE);
	$params['loop']  = $compiler->_fetch_expr($params['loop'], FALSE, FALSE, TRUE);
	$compiler->_fetch_expr($params['value'] . ' = ' . $params['start'], FALSE, FALSE, TRUE);
	$val_var = $params['value'];
	$return  = '';
	for ($params['value'] = $params['start']; $params['value'] < $params['loop']; $params['value'] += $params['step']) {
		$compiler->_fetch_expr($val_var . ' = ' . $params['value'], FALSE, FALSE, TRUE);
		$return .= $compiler->_tag_token($content, $block_name);
	}
	return $return;
}
