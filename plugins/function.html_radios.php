<?php
function quicky_function_html_radios($params, $quicky) {
	require_once $quicky->fetch_plugin('shared.escape_special_chars');

	$name      = 'radio';
	$values    = null;
	$options   = null;
	$selected  = null;
	$separator = '';
	$labels    = true;
	$label_ids = false;
	$output    = null;
	$extra     = '';

	foreach ($params as $_key => $_val) {
		switch ($_key) {
			case 'name':
			case 'separator':
				$$_key = (string)$_val;
				break;

			case 'checked':
			case 'selected':
				if (is_array($_val)) {
					$quicky->warning('html_radios: the "' . $_key . '" attribute cannot be an array', E_USER_WARNING);
				}
				else {
					$selected = (string)$_val;
				}
				break;

			case 'labels':
			case 'label_ids':
				$$_key = (bool)$_val;
				break;

			case 'options':
				$$_key = (array)$_val;
				break;

			case 'values':
			case 'output':
				$$_key = array_values((array)$_val);
				break;

			case 'radios':
				$quicky->warning('html_radios: the use of the "radios" attribute is deprecated, use "options" instead', E_USER_WARNING);
				$options = (array)$_val;
				break;

			case 'assign':
				break;

			default:
				if (!is_array($_val)) {
					$extra .= ' ' . $_key . '="' . quicky_function_escape_special_chars($_val) . '"';
				}
				else {
					$quicky->warning("html_radios: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
				}
				break;
		}
	}

	if (!isset($options) && !isset($values)) {
		return '';
	} /* raise error here? */

	$_html_result = array();

	if (isset($options)) {

		foreach ($options as $_key => $_val) {
			$_html_result[] = quicky_function_html_radios_output($name, $_key, $_val, $selected, $extra, $separator, $labels, $label_ids);
		}

	}
	else {

		foreach ($values as $_i => $_key) {
			$_val           = isset($output[$_i]) ? $output[$_i] : '';
			$_html_result[] = quicky_function_html_radios_output($name, $_key, $_val, $selected, $extra, $separator, $labels, $label_ids);
		}

	}

	if (!empty($params['assign'])) {
		$quicky->assign($params['assign'], $_html_result);
	}
	else {
		return implode("\n", $_html_result);
	}

}

function quicky_function_html_radios_output($name, $value, $output, $selected, $extra, $separator, $labels, $label_ids) {
	$_output = '';
	if ($labels) {
		if ($label_ids) {
			$_id = quicky_function_escape_special_chars(preg_replace('![^\w\-\.]!', '_', $name . '_' . $value));
			$_output .= '<label for="' . $_id . '">';
		}
		else {
			$_output .= '<label>';
		}
	}
	$_output .= '<input type="radio" name="'
			. quicky_function_escape_special_chars($name) . '" value="'
			. quicky_function_escape_special_chars($value) . '"';

	if ($labels && $label_ids) {
		$_output .= ' id="' . $_id . '"';
	}

	if ((string)$value == $selected) {
		$_output .= ' checked="checked"';
	}
	$_output .= $extra . ' />' . $output;
	if ($labels) {
		$_output .= '</label>';
	}
	$_output .= $separator;

	return $_output;
}

