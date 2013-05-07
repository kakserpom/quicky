<?php
function quicky_function_math($params, $quicky) {
	// be sure equation parameter is present
	if (empty($params['equation'])) {
		$quicky->warning("math: missing equation parameter");
		return;
	}

	$equation = $params['equation'];

	// make sure parenthesis are balanced
	if (substr_count($equation, "(") != substr_count($equation, ")")) {
		$quicky->warning("math: unbalanced parenthesis");
		return;
	}

	// match all vars in equation, make sure all are passed
	preg_match_all("!(?:0x[a-fA-F0-9]+)|([a-zA-Z][a-zA-Z0-9_]+)!", $equation, $match);
	$allowed_funcs = array('int', 'abs', 'ceil', 'cos', 'exp', 'floor', 'log', 'log10',
		'max', 'min', 'pi', 'pow', 'rand', 'round', 'sin', 'sqrt', 'srand', 'tan');

	foreach ($match[1] as $curr_var) {
		if ($curr_var && !in_array($curr_var, array_keys($params)) && !in_array($curr_var, $allowed_funcs)) {
			$quicky->warning("math: function call $curr_var not allowed");
			return;
		}
	}

	foreach ($params as $key => $val) {
		if ($key != "equation" && $key != "format" && $key != "assign") {
			// make sure value is not empty
			if (strlen($val) == 0) {
				$quicky->warning("math: parameter $key is empty");
				return;
			}
			if (!is_numeric($val)) {
				$quicky->warning("math: parameter $key: is not numeric");
				return;
			}
			$equation = preg_replace("/\b$key\b/", " \$params['$key'] ", $equation);
		}
	}

	eval("\$quicky_math_result = " . $equation . ";");

	if (empty($params['format'])) {
		if (empty($params['assign'])) {
			return $quicky_math_result;
		}
		else {
			$quicky->assign($params['assign'], $quicky_math_result);
		}
	}
	else {
		if (empty($params['assign'])) {
			printf($params['format'], $quicky_math_result);
		}
		else {
			$quicky->assign($params['assign'], sprintf($params['format'], $quicky_math_result));
		}
	}
}

/* vim: set expandtab: */

