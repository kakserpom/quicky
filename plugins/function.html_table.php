<?php
function quicky_function_html_table($params, $quicky) {
	$table_attr = 'border="1"';
	$tr_attr    = '';
	$th_attr    = '';
	$td_attr    = '';
	$cols       = $cols_count = 3;
	$rows       = 3;
	$trailpad   = '&nbsp;';
	$vdir       = 'down';
	$hdir       = 'right';
	$inner      = 'cols';
	$caption    = '';

	if (!isset($params['loop'])) {
		$quicky->warning("html_table: missing 'loop' parameter");
		return;
	}

	foreach ($params as $_key => $_value) {
		switch ($_key) {
			case 'loop':
				$$_key = (array)$_value;
				break;

			case 'cols':
				if (is_array($_value) && !empty($_value)) {
					$cols       = $_value;
					$cols_count = count($_value);
				}
				elseif (!is_numeric($_value) && is_string($_value) && !empty($_value)) {
					$cols       = explode(',', $_value);
					$cols_count = count($cols);
				}
				elseif (!empty($_value)) {
					$cols_count = (int)$_value;
				}
				else {
					$cols_count = $cols;
				}
				break;

			case 'rows':
				$$_key = (int)$_value;
				break;

			case 'table_attr':
			case 'trailpad':
			case 'hdir':
			case 'vdir':
			case 'inner':
			case 'caption':
				$$_key = (string)$_value;
				break;

			case 'tr_attr':
			case 'td_attr':
			case 'th_attr':
				$$_key = $_value;
				break;
		}
	}

	$loop_count = count($loop);
	if (empty($params['rows'])) {
		/* no rows specified */
		$rows = ceil($loop_count / $cols_count);
	}
	elseif (empty($params['cols'])) {
		if (!empty($params['rows'])) {
			/* no cols specified, but rows */
			$cols_count = ceil($loop_count / $rows);
		}
	}

	$output = "<table $table_attr>\n";

	if (!empty($caption)) {
		$output .= '<caption>' . $caption . "</caption>\n";
	}

	if (is_array($cols)) {
		$cols = ($hdir == 'right') ? $cols : array_reverse($cols);
		$output .= "<thead><tr>\n";

		for ($r = 0; $r < $cols_count; $r++) {
			$output .= '<th' . quicky_function_html_table_cycle('th', $th_attr, $r) . '>';
			$output .= $cols[$r];
			$output .= "</th>\n";
		}
		$output .= "</tr></thead>\n";
	}

	$output .= "<tbody>\n";
	for ($r = 0; $r < $rows; $r++) {
		$output .= "<tr" . quicky_function_html_table_cycle('tr', $tr_attr, $r) . ">\n";
		$rx = ($vdir == 'down') ? $r * $cols_count : ($rows - 1 - $r) * $cols_count;

		for ($c = 0; $c < $cols_count; $c++) {
			$x = ($hdir == 'right') ? $rx + $c : $rx + $cols_count - 1 - $c;
			if ($inner != 'cols') {
				/* shuffle x to loop over rows*/
				$x = floor($x / $cols_count) + ($x % $cols_count) * $rows;
			}

			if ($x < $loop_count) {
				$output .= "<td" . quicky_function_html_table_cycle('td', $td_attr, $c) . ">" . $loop[$x] . "</td>\n";
			}
			else {
				$output .= "<td" . quicky_function_html_table_cycle('td', $td_attr, $c) . ">$trailpad</td>\n";
			}
		}
		$output .= "</tr>\n";
	}
	$output .= "</tbody>\n";
	$output .= "</table>\n";

	return $output;
}

function quicky_function_html_table_cycle($name, $var, $no) {
	if (!is_array($var)) {
		$ret = $var;
	}
	else {
		$ret = $var[$no % count($var)];
	}

	return ($ret) ? ' ' . $ret : '';
}