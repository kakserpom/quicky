<?php
function quicky_block__foreach($params, $content, $compiler) {
	$params     = $compiler->_parse_params($params);
	$block_name = '_foreach';
	if (!isset($params['from'])) {
		return $compiler->_syntax_error('Missing parameter \'from\' in foreach tag.');
	}
	if (!isset($params['item']) && !isset($params['value'])) {
		return $compiler->_syntax_error('Missing parameter \'item\' in foreach tag.');
	}
	if (!isset($params['item'])) {
		$params['item'] = $params['value'];
	}
	$val = $compiler->_fetch_expr($params['from'], FALSE, FALSE, TRUE);
	if (!is_array($val) and !is_object($val)) {
		return $compiler->_syntax_error('Parameter \'from\' must be an array or object, ' . gettype($val) . ' given');
	}
	$return = '';
	if (isset($params['key'])) {
		foreach ($val as $compiler->_cpl_vars[$compiler->_dequote($params['key'])] => $compiler->_cpl_vars[$compiler->_dequote($params['item'])]) {
			$return .= $compiler->_tag_token($content, $block_name);
		}
	}
	else {
		foreach ($val as $compiler->_cpl_vars[$compiler->_dequote($params['item'])]) {
			$return .= $compiler->_tag_token($content, $block_name);
		}
	}
	return $return;
}
