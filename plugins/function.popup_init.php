<?php
function quicky_function_popup_init($params, $quicky) {
	$zindex = 1000;
	if (!empty($params['zindex'])) {
		$zindex = $params['zindex'];
	}
	if (!empty($params['src'])) {
		return '<div id="overDiv" style="position:absolute; visibility:hidden; z-index:' . $zindex . ';"></div>' . "\n"
				. '<script type="text/javascript" language="JavaScript" src="' . $params['src'] . '"></script>' . "\n";
	}
	else {
		$quicky->trigger_error('popup_init: missing src parameter');
	}
}
