<?php
function quicky_compiler_textarea($params, $compiler) {
	$params = $compiler->_parse_params($params);
	$s      = '';
	if (isset($params['join'])) {
		$fieldname = $compiler->_dequote($params['join']);
		if (!isset($compiler->block_props['form'])) {
			return $compiler->_syntax_error('Parameter \'join\' in tag textarea must be into a form.');
		}
		$form = Quicky_form::$forms[$compiler->block_props['form'][0]];
		if (!isset($form->elements->$fieldname)) {
			return $compiler->_syntax_error('There are no field \'' . $fieldname . '\' in form \'' . $form->name . '\'');
		}
		unset($params['join']);
		foreach ($form->elements->$fieldname as $k => $v) {
			if (substr($k, 0, 1) != '_' and !isset($params[$k])) {
				$params[$k] = var_export($v, TRUE);
			}
		}
	}
	if (isset($params['pattern'])) {
		if (isset($params['onchange'])) {
			$params['onchange'] = $compiler->_dequote($params['onchange']) . '; ';
		}
		else {
			$params['onchange'] = '';
		}
		$params['onchange'] = var_export($params['onchange'] . 'if (!' . $compiler->_dequote($params['pattern']) . '.test(this.value)) {' . $compiler->_dequote($params['onunmatch']) . '}', TRUE);
	}
	foreach ($params as $k => $v) {
		if ($k == 'onunmatch' or $k == 'value' or $k == 'label') {
			continue;
		}
		if (strpos($v, '$') !== FALSE) {
			$s .= ' ' . $k . '="<?php echo htmlspecialchars(' . $v . ',ENT_QUOTES); ?>"';
		}
		else {
			$s .= ' ' . $k . '="' . htmlspecialchars($compiler->_dequote($v, ENT_QUOTES)) . '"';
		}
	}
	$r = '<textarea' . $s . '>';
	if ($params['value']) {
		if (strpos($params['value'], '$') !== FALSE) {
			$r .= '<?php echo htmlspecialchars(' . $params['value'] . ',ENT_QUOTES); ?>';
		}
		else {
			$r .= htmlspecialchars($compiler->_dequote($params['value'], ENT_QUOTES));
		}
	}
	$r .= '</textarea>';
	if (isset($params['label']) && isset($params['id'])) {
		$r = '<label for="' . htmlspecialchars($compiler->_dequote($params['id'], ENT_QUOTES)) . '">' . $r . htmlspecialchars($compiler->_dequote($params['label'], ENT_QUOTES)) . '</label>';
	}
	return $r;
}