<?php
function quicky_function_get_debug_info($params, $quicky) {
	$vars = $quicky->_tpl_vars;
	ksort($vars);
	$var_info = array();
	foreach ($vars as $k => &$v) {
		$trace = array();
		foreach ($quicky->debug_trace['assign'] as &$vv) {
			if ($vv['name'] == $k) {
				$trace[] = $vv;
			}
		}
		$var_info[$k] = array(
			'value' => $v,
			'trace' => $trace
		);
	}
	return array(
		'var' => $var_info,
	);
}

?>
