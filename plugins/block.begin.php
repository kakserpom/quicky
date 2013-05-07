<?php
function quicky_block_begin($params, $content, $compiler) {
	$block_name                                             = 'begin';
	$name                                                   = trim($params);
	$compiler->_tag_stacks[$compiler->_tag_stack_n]['type'] = 'begin';
	$compiler->_tag_stacks[$compiler->_tag_stack_n]['name'] = $name;
	$fullpath                                               = '/';
	$tk                                                     = 0;
	for ($i = sizeof($compiler->_tag_stacks) - 1; $i >= 0; $i--) {
		if (isset($compiler->_tag_stacks[$i]['type']) && $compiler->_tag_stacks[$i]['type'] == 'begin') {
			$fullpath = '/' . $compiler->_tag_stacks[$i]['name'] . $fullpath;
			++$tk;
		}
	}
	$s_name                  = var_export($name, TRUE);
	$s_fullpath              = var_export($fullpath, TRUE);
	$sf_name                 = 'quicky_context_' . $name;
	$old_write_out_to        = $compiler->_write_out_to;
	$compiler->_write_out_to = '$return';
	$block                   = $compiler->_tag_token($content, $block_name);
	$block                   = $compiler->_optimize($block);
	$compiler->_write_out_to = $old_write_out_to;
	$return                  = '<?php'
			. "\n" . 'if (!function_exists(\'' . $sf_name . '\')) {function ' . $sf_name . ' () {$var = &Quicky::$obj->_tpl_vars; $return = \'\';'
			. "\n" . 'if (isset(Quicky::$obj->_contexts_data[' . $s_fullpath . ']) and sizeof(Quicky::$obj->_contexts_data[' . $s_fullpath . ']) > 0) {'
			. "\n" . '$old = $var;'
			. "\n" . 'foreach (Quicky::$obj->_contexts_data[' . $s_fullpath . '] as $k => $v):'
			. "\n" . '$var = array_merge($var,$v);'
			. $block
			. 'endforeach; $var = $old;} return $return; }} ' . ($tk > 1 ? '$return .=' : 'echo') . ' ' . $sf_name . '(); ?>';
	return $return;
}

