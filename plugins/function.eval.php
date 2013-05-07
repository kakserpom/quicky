<?php
function quicky_function_eval($params, $quicky) {
	if (!array_key_exists('var', $params)) {
		return $quicky->warning('eval: missing \'var\' parameter');
	}
	if ($params['var'] == '') {
		return;
	}
	$quicky->load_compiler('Quicky');
	$source = $quicky->compilers['Quicky']->_compile_source_string($params['var'], 'evaluated template');
	ob_start();
	$quicky->_eval('?>' . $source);
	$result = ob_get_contents();
	ob_end_clean();
	if (isset($params['assign'])) {
		$quicky->assign($params['assign'], $result);
	}
	else {
		return $result;
	}
}
