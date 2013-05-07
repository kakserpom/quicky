<?php
function quicky_compiler_assign($params, $compiler) {
	$params = $compiler->_parse_params($params);
	if (!isset($params['var'])) {
		$compiler->_syntax_error('assign: missing \'var\' parameter in assign function');
		return;
	}
	if (!isset($params['value'])) {
		$compiler->_syntax_error('assign: 	missing \'value\' parameter in assign function');
		return;
	}
	return '<?php $var[' . $params['var'] . '] = ' . $params['value'] . '; ?>';
}
