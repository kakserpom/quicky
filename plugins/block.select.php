<?php
function quicky_block_select($params, $content, $compiler) {
	$block_name                                              = 'select';
	$params                                                  = $compiler->_parse_params($params);
	$compiler->_tag_stacks[$compiler->_tag_stack_n]['type']  = $block_name;
	$compiler->_tag_stacks[$compiler->_tag_stack_n]['value'] = isset($params['value']) ? $params['value'] : '';
	unset($params['value']);
	if (isset($params['join'])) {
		$fieldname = $compiler->_dequote($params['join']);
		if (!isset($compiler->block_props['form'])) {
			return $compiler->_syntax_error('Parameter \'join\' in tag select must be into a form.');
		}
		$form = Quicky_form::$forms[$compiler->block_props['form'][0]];
		if (!isset($form->elements->$fieldname)) {
			return $compiler->_syntax_error('There are no field \'' . $fieldname . '\' in form \'' . $form->name . '\'');
		}
		unset($params['join']);
		foreach ($form->elements->$fieldname as $k => $v) {
			if ($k == 'elements') {
				continue;
			}
			if (substr($k, 0, 1) != '_' and !isset($params[$k])) {
				$params[$k] = var_export($v, TRUE);
			}
		}
		foreach ($form->elements->$fieldname->elements as $k => $v) {
			$t = gettype($v);
			if ($t == 'string') {
				$content .= '{option text=\'' . $compiler->escape_string($v) . '\'}' . "\n";
			}
			elseif ($t == 'array' or $t == 'object') {
				if ($t == 'array') {
					$v = (object)$v;
				}
				$type = isset($v->type) ? $v->type : 'option';
				unset($v->type);
				if ($type != 'option' and $type != 'optgroup') {
					return $compiler->_syntax_error('Unexcepted type of dropdown\'s child element: \'' . $type . '\'');
				}
				$s = '';
				foreach ($v as $h => $b) {
					$s .= $h . '=\'' . $compiler->escape_string($b) . '\' ';
				}
				$content .= '{' . $type . ' ' . rtrim($s, ' ') . '}' . "\n";
			}
		}
	}
	$s = '';
	foreach ($params as $k => $v) {
		if ($k == 'type') {
			continue;
		}
		if (strpos($v, '$') !== FALSE) {
			$s .= ' ' . $k . '="<?php echo htmlspecialchars(' . $v . ',ENT_QUOTES); ?>"';
		}
		else {
			$s .= ' ' . $k . '="' . htmlspecialchars($compiler->_dequote($v, ENT_QUOTES)) . '"';
		}
	}
	$return = '<select' . $s . '>' . $compiler->_tag_token($content, $block_name) . '</select>';
	return $return;
}

