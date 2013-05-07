<?php
function quicky_function_cycle($params, $quicky) {
	static $cycle_vars;
	$name    = (!isset($params['name'])) ? 'default' : $params['name'];
	$print   = (isset($params['print'])) ? (bool)$params['print'] : TRUE;
	$advance = (isset($params['advance'])) ? (bool)$params['advance'] : TRUE;
	$reset   = (isset($params['reset'])) ? (bool)$params['reset'] : FALSE;
	if (!in_array('values', array_keys($params))) {
		if (!isset($cycle_vars[$name]['values'])) {
			$quicky->trigger_error('cycle: missing \'values\' parameter');
			return;
		}
	}
	else {
		if (isset($cycle_vars[$name]['values']) && $cycle_vars[$name]['values'] != $params['values']) {
			$cycle_vars[$name]['index'] = 0;
		}
		$cycle_vars[$name]['values'] = $params['values'];
	}
	$cycle_vars[$name]['delimiter'] = (isset($params['delimiter'])) ? $params['delimiter'] : ',';
	if (is_array($cycle_vars[$name]['values'])) {
		$cycle_array = $cycle_vars[$name]['values'];
	}
	else {
		$cycle_array = explode($cycle_vars[$name]['delimiter'], $cycle_vars[$name]['values']);
	}
	if (!isset($cycle_vars[$name]['index']) || $reset) {
		$cycle_vars[$name]['index'] = 0;
	}
	if (isset($params['assign'])) {
		$print = false;
		$quicky->assign($params['assign'], $cycle_array[$cycle_vars[$name]['index']]);
	}
	if ($print) {
		$retval = $cycle_array[$cycle_vars[$name]['index']];
	}
	else {
		$retval = NULL;
	}
	if ($advance) {
		if ($cycle_vars[$name]['index'] >= count($cycle_array) - 1) {
			$cycle_vars[$name]['index'] = 0;
		}
		else {
			$cycle_vars[$name]['index']++;
		}
	}
	return $retval;
}
