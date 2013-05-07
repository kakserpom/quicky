<?php
function quicky_block_section($params, $content, $compiler) {
	$block_name = 'section';
	$params     = $compiler->_parse_params($params);
	if (!isset($params['name'])) {
		return $compiler->_syntax_error('Missing parameter \'name\' in section tag.');
	}
	if (!isset($params['loop'])) {
		return $compiler->_syntax_error('Missing parameter \'loop\' in section tag.');
	}
	$params['loop'] = $compiler->_dequote($params['loop']);
	if (!isset($params['start'])) {
		$params['start'] = '0';
	}
	else {
		$params['start'] = $compiler->_dequote($params['start']);
	}
	if (!isset($params['max'])) {
		$params['max'] = '-1';
	}
	else {
		$params['max'] = $compiler->_dequote($params['max']);
	}
	if (!isset($params['step'])) {
		$params['step'] = '1';
	}
	else {
		$params['step'] = $compiler->_dequote($params['step']);
	}
	$name  = $compiler->_dequote($params['name']);
	$props = array('index', 'index_prev', 'index_next', 'iteration', 'first', 'last', 'rownum', 'loop', 'show', 'total');
	if (in_array($name, $props)) {
		return $compiler->_syntax_error('Disallowed value (\'' . $params['name'] . '\') of parameter \'name\' in section tag.');
	}
	$props[] = $name;
	$a       = $compiler->block_props;
	$compiler->push_block_props($props, $block_name, $name);
	$block                 = $compiler->_tag_token($content, $block_name);
	$compiler->block_props = $a;
	$name                  = var_export($name, TRUE);
	$return                = '<?php'
			. "\n" . '$section[' . $name . '] = array();'
			. "\n" . '$section[' . $name . '][\'s\'] = ' . (isInteger($params['loop']) ? $params['loop'] : 'isInteger(' . $params['loop'] . ')?' . $params['loop'] . ':sizeof(' . $params['loop'] . ')') . ';'
			. "\n" . '$section[' . $name . '][\'st\'] = ' . (isInteger($params['start']) ? $params['start'] : 'isInteger(' . $params['start'] . ')?' . $params['start'] . ':sizeof(' . $params['start'] . ')') . ';'
			. "\n" . '$section[' . $name . '][\'step\'] = ' . (isInteger($params['step']) ? $params['step'] : 'isInteger(' . $params['step'] . ')?' . $params['step'] . ':sizeof(' . $params['step'] . ')') . ';'
			. "\n" . 'if ($section[' . $name . '][\'s\'] > 0):'
			. ' for ($section[' . $name . '][\'i\'] = 0; $section[' . $name . '][\'i\'] < $section[' . $name . '][\'s\']-$section[' . $name . '][\'st\']' . ($params['max'] != '-1' ? ' and $section[' . $name . '][\'i\'] < ' . $params['max'] : '') . '; ++$section[' . $name . '][\'i\']): '
			. '?>' . $block;
	if (($k = array_search('sectionelse', $compiler->_tag_stacks[$compiler->_tag_stack_n])) !== FALSE) {
		$return .= '<?php endif; ?>';
		unset($compiler->_tag_stacks[$compiler->_tag_stack_n][$k]);
	}
	else {
		$return .= '<?php endfor; endif; ?>';
	}
	return $return;
}
