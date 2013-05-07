<?php
function quicky_block_foreach($params, $content, $compiler) {
	$block_name = 'foreach';
	$params     = $compiler->_parse_params($params);
	if (isset($params['key'])) {
		$params['key'] = $compiler->_varname($params['key']);
	}
	if (isset($params['item'])) {
		$params['item'] = $compiler->_varname($params['item']);
	}
	if (isset($params['value'])) {
		$params['value'] = $compiler->_varname($params['value']);
	}
	if (!isset($params['from'])) {
		return $compiler->_syntax_error('Missing parameter \'from\' in foreach tag.');
	}
	if (!isset($params['item']) && !isset($params['value'])) {
		return $compiler->_syntax_error('Missing parameter \'item\' in foreach tag.');
	}
	$return = '<?php $_from = ' . $params['from'] . ';' . "\n";
	if (isset($params['name']) and ($name = trim($compiler->_dequote($params['name']))) !== '') {
		$props = array('first', 'last', 'index', 'iteration', 'i', 'total');
		$a     = $compiler->block_props;
		$compiler->push_block_props($props, $block_name, $name);
		$block                 = $compiler->_tag_token($content, $block_name);
		$compiler->block_props = $a;
		$name                  = var_export($name, TRUE);
		$return .= "\n" . '$foreach[' . $name . '] = array();'
				. "\n" . '$foreach[' . $name . '][\'i\'] = 0;'
				. "\n" . '$foreach[' . $name . '][\'s\'] = sizeof($_from);'
				. "\n";
	}
	else {
		$return .= ' ';
		$name  = '';
		$block = $compiler->_tag_token($content, $block_name);
	}
	$return .= 'if (' . ($name !== '' ? '$foreach[' . $name . '][\'show\'] = ' : '') . ($name !== '' ? '$foreach[' . $name . '][\'s\']' : 'sizeof($_from)') . ' > 0):'
			. ' foreach ($_from as' . (isset($params['key']) ? ' ' . $params['key'] . ' =>' : '') . ' ' . (isset($params['item']) ? $params['item'] : $params['value']) . '): '
			. ($name !== '' ? "\n" . '++$foreach[' . $name . '][\'i\'];' . '' : '')
			. '?>'
			. $block;
	if (($k = array_search('foreachelse', $compiler->_tag_stacks[$compiler->_tag_stack_n])) !== FALSE) {
		$return .= '<?php endif; ?>';
		unset($compiler->_tag_stacks[$compiler->_tag_stack_n][$k]);
	}
	else {
		$return .= '<?php endforeach; endif; ?>';
	}
	return $return;
}
