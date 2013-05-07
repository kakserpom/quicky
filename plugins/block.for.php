<?php
function quicky_block_for($params, $content, $compiler) {
	$block_name = 'for';
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
	return '<?php'
			. "\n" . 'for (' . $params['value'] . ' = ' . $params['start'] . '; ' . $params['value'] . ' < ' . $params['loop'] . '; ' . $params['value'] . ' += ' . $params['step'] . '): ?>'
			. $compiler->_tag_token($content, $block_name)
			. '<?php endfor; ?>';
}
