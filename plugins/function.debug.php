<?php
function quicky_function_debug($params, $quicky) {
	if (isset($params['charset'])) {
		$quicky->assign('_debug_charset', $params['charset']);
	}
	$quicky->display('|debug.tpl');
	return '';
}
