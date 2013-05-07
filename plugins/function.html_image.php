<?php
function quicky_function_html_image($params, $quicky) {
	require_once $quicky->fetch_plugin('shared', 'escape_special_chars');

	$alt         = '';
	$file        = '';
	$height      = '';
	$width       = '';
	$extra       = '';
	$prefix      = '';
	$suffix      = '';
	$path_prefix = '';
	$server_vars = ($quicky->request_use_auto_globals) ? $_SERVER : $GLOBALS['HTTP_SERVER_VARS'];
	$basedir     = isset($server_vars['DOCUMENT_ROOT']) ? $server_vars['DOCUMENT_ROOT'] : '';
	foreach ($params as $_key => $_val) {
		switch ($_key) {
			case 'file':
			case 'height':
			case 'width':
			case 'dpi':
			case 'path_prefix':
			case 'basedir':
				$$_key = $_val;
				break;

			case 'alt':
				if (!is_array($_val)) {
					$$_key = quicky_function_escape_special_chars($_val);
				}
				else {
					$quicky->warning("html_image: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
				}
				break;

			case 'link':
			case 'href':
				$prefix = '<a href="' . $_val . '">';
				$suffix = '</a>';
				break;

			default:
				if (!is_array($_val)) {
					$extra .= ' ' . $_key . '="' . quicky_function_escape_special_chars($_val) . '"';
				}
				else {
					$quicky->warning("html_image: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
				}
				break;
		}
	}

	if (empty($file)) {
		$quicky->warning("html_image: missing 'file' parameter", E_USER_NOTICE);
		return;
	}

	if (substr($file, 0, 1) == '/') {
		$_image_path = $basedir . $file;
	}
	else {
		$_image_path = $file;
	}

	if (!isset($params['width']) || !isset($params['height'])) {
		if (!$_image_data = @getimagesize($_image_path)) {
			if (!file_exists($_image_path)) {
				$quicky->warning("html_image: unable to find '$_image_path'", E_USER_NOTICE);
				return;
			}
			else {
				if (!is_readable($_image_path)) {
					$quicky->warning("html_image: unable to read '$_image_path'", E_USER_NOTICE);
					return;
				}
				else {
					$quicky->warning("html_image: '$_image_path' is not a valid image file", E_USER_NOTICE);
					return;
				}
			}
		}
		if ($quicky->security &&
				($_params = array('resource_type' => 'file', 'resource_name' => $_image_path)) &&
				(require_once(quicky_CORE_DIR . 'core.is_secure.php')) &&
				(!quicky_core_is_secure($_params, $quicky))
		) {
			$quicky->warning("html_image: (secure) '$_image_path' not in secure directory", E_USER_NOTICE);
		}

		if (!isset($params['width'])) {
			$width = $_image_data[0];
		}
		if (!isset($params['height'])) {
			$height = $_image_data[1];
		}

	}

	if (isset($params['dpi'])) {
		if (strstr($server_vars['HTTP_USER_AGENT'], 'Mac')) {
			$dpi_default = 72;
		}
		else {
			$dpi_default = 96;
		}
		$_resize = $dpi_default / $params['dpi'];
		$width   = round($width * $_resize);
		$height  = round($height * $_resize);
	}

	return $prefix . '<img src="' . $path_prefix . $file . '" alt="' . $alt . '" width="' . $width . '" height="' . $height . '"' . $extra . ' />' . $suffix;
}
