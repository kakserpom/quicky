<?php
function quicky_function_config_load($params, $quicky) {
	$_file    = isset($params['file']) ? $params['file'] : '';
	$_section = isset($params['section']) ? $params['section'] : '';
	if (strlen($_file) == 0) {
		return $quicky->warning('missing \'file\' attribute in config_load tag');
	}
	$quicky->config_load($_file, $_section);
	return '';
}
