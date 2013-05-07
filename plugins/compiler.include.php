<?php
function quicky_compiler_include($params, $compiler) {
	if (!is_array($params)) {
		$params = $compiler->_parse_params($params);
	}
	static $nesting = array();
	static $tmp = 0;
	if (!isset($params['file'])) {
		return $compiler->_syntax_error('include: missing \'file\' parameter in include function');
	}
	if (isset($params['assign'])) {
		$params['assign'] = $compiler->_varname($params['assign']);
	}
	$assign   = '';
	$noinline = isset($params['noinline']);
	unset($params['noinline']);
	$inline = isset($params['inline']);
	unset($params['inline']);
	$import = isset($params['import']);
	unset($params['import']);
	if ($import) {
		$inline = TRUE;
	}
	foreach ($params as $k => $v) {
		if ($k != 'file' and $k != 'assign') {
			$assign .= '$tpl->_local_vars[' . $params['file'] . '][' . var_export($k, TRUE) . '] = ' . $v . ";\n";
		}
	}
	if ($assign !== '') {
		$assign = 'if (!isset($tpl->_local_vars[' . $params['file'] . '])) {$tpl->_local_vars[' . $params['file'] . '] = array();}' . "\n" . $assign;
	}
	$path = $params['file'];
	$dir  = dirname($compiler->template_from);
	if ($dir === '') {
		$dir = '.';
	}
	$path = trim(preg_replace('~(?:^|\'?\.)\$dir(?:$|\.\'?)?~', $dir, $path), '\'');
	if (strpos($path, '$') === FALSE && ($inline || (isset($compiler->prefs['inline_includes']) && $compiler->prefs['inline_includes'] && !$noinline))) {
		if ($path === '' or is_null($path)) {
			return $compiler->_syntax_error('Empty include-path given');
		}
		if (is_dir($compiler->parent->_get_template_path($path))) {
			return $compiler->_syntax_error('Path is directory');
		}
		$nesting[] = $path;
		$repeats   = sizeof(array_intersect($nesting, array($path)));
		if ($repeats > $compiler->parent->max_recursion_depth) {
			return $compiler->_syntax_error('Max recursion depth of inline-includes exceed (' . $path . ')');
		}
		$return = '';
		$from   = $compiler->template_from;
		if (isset($params['assign'])) {
			if (is_int(array_search('ob', $params))) {
				$return .= '<?php $old_inc_ob' . $tmp++ . ' = ob_get_clean(); ob_start(); ?>';
			}
			else {
				$return .= '<?php $old_inc_write_out_to' . $tmp++ . ' = $tpl->_write_out_to; $tpl->_write_out_to = ' . $params['assign'] . '; ?>';
			}
		}
		$nesting_old       = $nesting;
		$old_depart_scopes = $compiler->parent->local_depart_scopes;
		$compiler->parent->_compile($path, '', $compiler->compiler_name, $import);
		$compiler->parent->local_depart_scopes = $old_depart_scopes;
		$nesting                               = $nesting_old;
		if ($assign !== '') {
			$assign = "<?php\n" . $assign . '?>';
		}
		$return .= $assign . file_get_contents($compiler->parent->_get_compile_path($path, ''));
		if (isset($params['assign'])) {
			if (is_int(array_search('ob', $params))) {
				$return .= '<?php ' . $params['assign'] . ' = ob_get_clean(); echo $old_ob' . $tmp . '; ?>';
			}
			else {
				$return .= '<?php  $tpl->_write_out_to = $old_inc_write_out_to' . $tmp++ . '; ?>';
			}
		}
		$return .= '<?php $local = &$tpl->_local_vars[' . var_export($from, TRUE) . ']; ?>' . "\n";
		return $return;
	}
	if (isset($params['assign'])) {
		return '<?php ' . $params['assign'] . ' = $tpl->fetch(' . $params['file'] . '); ?>';
	}
	return '<?php ' . $assign . ' $tpl->display(' . $params['file'] . '); ?>';
}
