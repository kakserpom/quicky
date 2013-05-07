<?php
function quicky_compiler_option($params, $compiler) {
	$tk = -1;
	for ($i = sizeof($compiler->_tag_stacks) - 1; $i >= 0; $i--) {
		if (isset($compiler->_tag_stacks[$i]['type']) && $compiler->_tag_stacks[$i]['type'] == 'select') {
			$tk = $i;
		}
	}
	if ($tk != -1) {
		$params = $compiler->_parse_params($params);
		$s      = '';
		foreach ($params as $k => $v) {
			if ($k == 'text') {
				continue;
			}
			if ($k == 'checked') {
				$s .= '<?php if (' . $v . ($params['type'] == '\'radio\'' ? ' == ' . $params['value'] . (is_int(array_search('\'default\'', $params)) ? ' or ' . $v . ' == \'\'' : '') : '') . ') {echo \' checked\';} ?>';
			}
			elseif (strpos($v, '$') !== FALSE) {
				$s .= ' ' . $k . '="<?php echo htmlspecialchars(' . $v . ',ENT_QUOTES); ?>"';
			}
			else {
				$s .= ' ' . $k . '="' . htmlspecialchars($compiler->_dequote($v, ENT_QUOTES)) . '"';
			}
		}
		$params['text'] = isset($params['text']) ? $params['text'] : '';
		if (!isset($params['value'])) {
			$params['value'] = $params['text'];
		}
		$params['text'] = strpos($params['text'], '$') !== FALSE ? '<?php echo htmlspecialchars(' . $params['text'] . ',ENT_QUOTES); ?>' : htmlspecialchars($compiler->_dequote($params['text']));
		if ($compiler->_tag_stacks[$tk]['value'] !== '' and isset($params['value'])) {
			$s .= '<?php if (' . $compiler->_tag_stacks[$tk]['value'] . ' == ' . $params['value'] . ') {echo \' selected\';} ?>';
		}
		return '<option' . $s . '>' . $params['text'] . '</option>';
	}
	else {
		return $compiler->_syntax_error('Unexcepted tag \'option\'');
	}
}
