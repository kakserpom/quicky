<?php
function quicky_compiler_math($params, $compiler) {
	$params = $compiler->_parse_params($params);
	$outoff = FALSE;
	// be sure equation parameter is present
	if (empty($params['equation'])) {
		$compiler->parent->warning("math: missing equation parameter");
		return;
	}

	$equation = $compiler->_dequote($params['equation']);
	if (strpos($equation, '$') !== FALSE) {
		$p = 'array(';
		$i = 0;
		$f = $compiler->parent->fetch_plugin('function.math');
		if (!in_array($f, $compiler->load_plugins)) {
			$compiler->load_plugins[] = $f;
		}
		foreach ($params as $k => $v) {
			$p .= ($i++ > 0 ? ',' : '') . '\'' . $k . '\' => ' . $v;
		}
		$p .= ')';
		$r = '<?php ' . ($outoff ? '' : ($compiler->_write_out_to !== '' ? $compiler->_write_out_to . ' .=' : 'echo') . ' ') .
				'quicky_function_math(' . $p . ',$this,TRUE); ?>';
		return $r;
	}

	// make sure parenthesis are balanced
	if (substr_count($equation, "(") != substr_count($equation, ")")) {
		$this->parent->warning("math: unbalanced parenthesis");
		return;
	}

	// match all vars in equation, make sure all are passed
	preg_match_all("!(?:0x[a-fA-F0-9]+)|([a-zA-Z][a-zA-Z0-9_]+)!", $equation, $match);
	$allowed_funcs = array('int', 'abs', 'ceil', 'cos', 'exp', 'floor', 'log', 'log10',
		'max', 'min', 'pi', 'pow', 'rand', 'round', 'sin', 'sqrt', 'srand', 'tan');

	foreach ($match[1] as $curr_var) {
		if ($curr_var && !in_array($curr_var, array_keys($params)) && !in_array($curr_var, $allowed_funcs)) {
			$this->parent->warning("math: function call $curr_var not allowed");
			return;
		}
	}

	foreach ($params as $key => $val) {
		if ($key != "equation" && $key != "format" && $key != "assign") {
			// make sure value is not empty
			if (strlen($val) == 0) {
				$this->parent->warning("math: parameter $key is empty");
				return;
			}
			$equation = preg_replace("/\b$key\b/", $params[$key], $equation);
		}
	}
	if (isset($params['assign']) && ($params['assign'] !== '')) {
		$part = $compiler->_varname($params['assign']) . ' =';
	}
	else {
		$part = ($compiler->_write_out_to !== '' ? $compiler->_write_out_to . ' .=' : 'echo');
	}
	return '<?php ' . ($outoff ? '' : $part . ' ') . $equation . '; ?>';

	if (empty($params['format'])) {
		if (empty($params['assign'])) {
			return $this->parent_math_result;
		}
		else {
			$this->parent->assign($params['assign'], $this->parent_math_result);
		}
	}
	else {
		if (empty($params['assign'])) {
			printf($params['format'], $this->parent_math_result);
		}
		else {
			$this->parent->assign($params['assign'], sprintf($params['format'], $this->parent_math_result));
		}
	}
}

/* vim: set expandtab: */

