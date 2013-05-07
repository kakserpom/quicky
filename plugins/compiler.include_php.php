<?php
function quicky_compiler_include_php($params, $compiler) {
	$params = $compiler->_parse_params($params);
	if (!isset($compiler->prefs['allow_php_native']) or !$compiler->prefs['allow_php_native']) {
		return $compiler->_syntax_error('include_php: allow_php_native is off. abort.');
	}
	if (!isset($params['file'])) {
		return $compiler->_syntax_error('include_php: missing \'file\' parameter in include function');
	}
	if (!isset($params['once'])) {
		$params['once'] = TRUE;
	}
	else {
		$params['once'] = !preg_match('~^0|FALSE$~i', $params['once']);
	}
	return '<?php include' . ($params['once'] ? '_once' : '') . ' ' . $params['file'] . '; ?>';
}
