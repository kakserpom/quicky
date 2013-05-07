<?php
function quicky_block_form($params, $content, $compiler) {
	$block_name = 'form';
	$params     = $compiler->_parse_params($params);
	if (!isset($params['name']) && !isset($params['no_quicky_form'])) {
		return $compiler->_syntax_error('Missing parameter \'name\' in form tag.');
	}
	$mode        = !isset($params['no_quicky_form']);
	$auto_object = isset($params['auto_object']);
	unset($params['no_quicky_form']);
	unset($params['auto_object']);
	$code = '';
	if ($mode) {
		if (!class_exists('Quicky_form')) {
			if ($auto_object) {
				require_once QUICKY_DIR . 'Quicky.form.class.php';
			}
			else {
				return $compiler->_syntax_error('Class Quicky_form isn\'t loaded');
			}
		}
		$name = $compiler->_dequote($params['name']);
		if (!isset(Quicky_form::$forms[$name])) {
			if ($auto_object) {
				$code = '<?php' . "\n" . 'if (!class_exists(\'Quicky_form\')) {require_once QUICKY_DIR.\'Quicky.form.class.php\';}' . "\n" . 'if (!isset(Quicky_form::$forms[\'' . $name . '\'])) {$form = new Quicky_form(\'' . $name . '\');' . "\n";
				preg_match_all('~' . preg_quote($compiler->left_delimiter, '~') . '(input|textarea)(\s+.*?)?' . preg_quote($compiler->right_delimiter, '~') . '~', $content, $m, PREG_SET_ORDER);
				foreach ($m as $v) {
					$params = $compiler->_parse_params($v[2], TRUE);
					if (!isset($params['name'])) {
						continue;
					}
					foreach ($params as $k => $v) {
						$params[$k] = $compiler->_dequote($v);
					}
					$code .= '$form->addElement(' . var_export($params['name'], TRUE) . ',' . var_export($params, TRUE) . ');' . "\n";
				}
				$code .= '} ?>';
				eval('?>' . $code);
			}
			else {
				return $compiler->_syntax_error('Form \'' . $name . '\' is not recognized');
			}
		}
		$objform = Quicky_form::$forms[$name];
		$props   = array('form');
		$a       = $compiler->block_props;
		$compiler->push_block_props($props, $block_name, $name);
		$content               = $compiler->_tag_token($content, $block_name);
		$compiler->block_props = $a;
	}
	else {
		$content = $compiler->_tag_token($content, $block_name);
	}
	$s = '';
	foreach ($params as $k => $v) {
		if (strpos($v, '$') !== FALSE) {
			$s .= ' ' . $k . '="<?php echo htmlspecialchars(' . $v . ',ENT_QUOTES); ?>"';
		}
		else {
			$s .= ' ' . $k . '="' . htmlspecialchars($compiler->_dequote($v, ENT_QUOTES)) . '"';
		}
	}
	$return = $code . '<form' . $s . '>' . $content . '</form>';
	return $return;
}
