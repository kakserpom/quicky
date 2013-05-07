<?php
function quicky_compiler_input($params, $compiler) {
	$params = $compiler->_parse_params($params);
	$s      = '';
	if (isset($params['join'])) {
		$fieldname = $compiler->_dequote($params['join']);
		if (!isset($compiler->block_props['form'])) {
			return $compiler->_syntax_error('Parameter \'join\' in tag input must be into a form.');
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
	if (isset($params['type']) and $compiler->_dequote($params['type']) == 'textarea') {
		require_once QUICKY_DIR . 'plugins/compiler.textarea.php';
		unset($params['type']);
		return quicky_compiler_textarea($params, $compiler);
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
		if ($k == 'default' or $k == 'onunmatch' or $k == 'pattern' or $k == 'label') {
			continue;
		}
		if ($v == '\'checked\'' and is_int($k)) {
			$s .= ' checked';
		}
		elseif ($k == 'checked') {
			$s .= '<?php if (' . $v . (isset($params['default']) ? ' or ' . $params['default'] : '') . ($params['type'] == '\'radio\'' ? ' == ' . $params['value'] . (isset($params['default']) ? ' or ' . $v . ' == \'\'' : '') : '') . ') {echo \' checked\';} ?>';
		}
		elseif (strpos($v, '$') !== FALSE) {
			$s .= ' ' . $k . '="<?php echo htmlspecialchars(' . $v . ',ENT_QUOTES); ?>"';
		}
		else {
			$s .= ' ' . $k . '="' . htmlspecialchars($compiler->_dequote($v), ENT_QUOTES) . '"';
		}
	}
	$r = '<input' . $s . ' />';
	if (isset($params['label']) && isset($params['id'])) {
		$r = '<label for="' . htmlspecialchars($compiler->_dequote($params['id']), ENT_QUOTES) . '">' . $r . htmlspecialchars($compiler->_dequote($params['label']), ENT_QUOTES) . '</label>';
	}
	return $r;
}