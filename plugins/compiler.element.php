<?php
function quicky_compiler_element($params, $compiler, $close = FALSE) {
	if ($close) {
		return '';
	}
	if (!is_array($params)) {
		$params = $compiler->_parse_params($params);
	}
	$pre = '';
	if (isset($params['join'])) {
		$fieldname = $compiler->_dequote($params['join']);
		if (!isset($compiler->block_props['form'])) {
			return $compiler->_syntax_error('Parameter \'join\' in tag element must be into a form.');
		}
		$form = Quicky_form::$forms[$compiler->block_props['form'][0]];
		if (!isset($form->elements->$fieldname)) {
			return $compiler->_syntax_error('There are no field \'' . $fieldname . '\' in form \'' . $form->name . '\'');
		}
		unset($params['join']);
		foreach ($form->elements->$fieldname as $k => $v) {
			if (!isset($params[$k])) {
				$params[$k] = 'Quicky_form::$forms[\'' . $compiler->block_props['form'][0] . '\']->' . $fieldname . '->' . $k;
			}
		}
		if (!isset($params['value'])) {
			$params['value'] = 'Quicky_form::$forms[\'' . $compiler->block_props['form'][0] . '\']->' . $fieldname . '->getValue()';
		}
		if (isset($params['defaultValue'])) {
			$pre = '<?php Quicky_form::$forms[\'' . $compiler->block_props['form'][0] . '\']->' . $fieldname . '->setDefaultValue(' . $params['defaultValue'] . ");\n?>";
			unset($params['defaultValue']);
		}
	}
	elseif (isset($params['errors'])) {
		$fieldname           = isset($params['errorkey']) ? $params['errorkey'] : $params['name'];
		$params['_errormsg'] = '(isset(' . $params['errors'] . '[' . $fieldname . '])?' . $params['errors'] . '[' . $fieldname . ']:\'\')';
	}
	else {
		$params['_errormsg'] = 'NULL';
	}
	require_once $compiler->parent->fetch_plugin('compiler.include');
	$params['file']   = '\'elements/' . $compiler->_fetch_expr($params['type']) . '.tpl\'';
	$params['import'] = 1;
	return $pre . quicky_compiler_include($params, $compiler);
}
