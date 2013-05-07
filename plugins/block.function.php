<?php
function quicky_block_function($params, $content, $compiler) {
	$block_name = 'function';
	if (!preg_match('~^\s*(.*?)\s*\((.*)\)\s*$~', $params, $g)) {
		return $compiler->_syntax_error('Parse error in Function header.');
	}
	if (trim($g[2]) === '') {
		return $compiler->_syntax_error('Missing expression in Function tag.');
	}
	$compiler->_tag_stacks[$compiler->_tag_stack_n]['type'] = 'function';
	$compiler->_tag_stacks[$compiler->_tag_stack_n]['name'] = $g[1];
	$args                                                   = explode(',', $g[2]);
	$args_a                                                 = array();
	$args_default                                           = array();
	foreach ($args as $v) {
		if (!preg_match('~^\s*\$(\w+)\s*(?:=\s*(\-?\d+|NULL|([\'"]).*?(?<!\\\\)\3))?$~i', $v, $q)) {
			return $compiler->_syntax_error('Parse error in Function header.');
		}
		$args_names[]   = $q[1];
		$args_default[] = (isset($q[2]) && $q[2] !== '') ? $compiler->_expr_token($q[2]) : 'NULL';
	}
	$args_f  = '';
	$args_fs = '';
	$args_v  = '';
	$args_vs = '';
	foreach ($args_names as $v) {
		$args_f .= 'isset($args[\'' . $v . '\'])?$args[\'' . $v . '\']:NULL,';
		$args_fs .= '$args[\'' . $v . '\'],';
		$args_v .= 'isset($var[\'' . $v . '\'])?$var[\'' . $v . '\']:NULL,';
		$args_vs .= '$var[\'' . $v . '\'],';
	}
	$args_f  = rtrim($args_f, ',');
	$args_fs = rtrim($args_fs, ',');
	$args_v  = rtrim($args_v, ',');
	$args_vs = rtrim($args_vs, ',');
	$return  = '<?php'
			. "\n" . 'if (!function_exists("quicky_function_' . $g[1] . '")) {'
			. "\n" . 'function quicky_function_' . $g[1] . '($args,$quicky)'
			. "\n" . '{'
			. "\n" . '$var = &$quicky->_tpl_vars;'
			. "\n" . '$save_vars = array(' . $args_v . ');';
	foreach ($args as $k => $v) {
		$return .= "\n" . 'if (isset($args[\'' . $args_names[$k] . '\'])) {$var[\'' . $args_names[$k] . '\'] = $args[\'' . $args_names[$k] . '\'];}'
				. "\n" . 'elseif (isset($args[' . $k . '])) {$var[\'' . $args_names[$k] . '\'] = $args[' . $k . '];}'
				. "\n" . 'else {$var[\'' . $args_names[$k] . '\'] = ' . $args_default[$k] . ';}';
	}
	$return .=
			"\n" . '$config = &$quicky->_tpl_config;'
					. "\n" . '$capture = &$quicky->_block_props[\'capture\'];'
					. "\n" . '$foreach = &$quicky->_block_props[\'foreach\'];'
					. "\n" . '$section = &$quicky->_block_props[\'section\'];'
					. "\n?>";
	$compiler->template_defined_functions[] = $g[1];
	$return .= $compiler->_tag_token($content, $block_name)
			. "\n" . '<?php list(' . $args_vs . ') = $save_vars;'
			. "\n" . '}} ?>';
	return $return;
}
