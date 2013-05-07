<?php
function quicky_modifier_debug_print_var($var, $charset = 'utf-8', $depth = 1, $length = 40) {
	$countForCollapse = 5;
	if ($depth > 10) {
		return ' max depth !!! ';
	}
	$_replace = array(
		"\n" => '<i>\n</i>',
		"\r" => '<i>\r</i>',
		"\t" => '<i>\t</i>'
	);
	$hache    = 'i' . substr(md5(serialize($var) . rand(0, 1000) . $depth), 3, 12);
	switch (gettype($var)) {
		case 'array' :
			$count   = count($var);
			$results = '<span class="active" onclick="toggle(\'' . $hache . '\'); toggle(\'' . $hache . '_close\');"><b>Array</b> (' . $count . ') <span id="' . $hache . '_close" ' . ($count <= $countForCollapse ? 'style="display:none;"' : '') . ' class="collapse_title">[...]</span></span>';
			$results .= '<span id="' . $hache . '" class="collapseble" ' . ($count > $countForCollapse ? 'style="display:none;"' : '') . '>';
			foreach ($var as $curr_key => $curr_val) {
				$results .= '<br>' . str_repeat('&nbsp;', $depth * 2)
						. '<i>' . strtr($curr_key, $_replace) . '</i> =&gt; '
						. quicky_modifier_debug_print_var($curr_val, $charset, $depth + 1, 20);

			}
			$results .= '</span>';
			break;
		case 'object' :
			$reflectionObject = new ReflectionObject($var);
			$properties       = $reflectionObject->getProperties(ReflectionProperty::IS_PUBLIC);
			$desc             = iconv('utf-8', $charset, htmlspecialchars($reflectionObject->getDocComment()));
			$desc             = str_replace(array('"', ' ', "\n", "\r", "\t"), array('\"', '&nbsp;', '<br/>', '&nbsp;&nbsp;&nbsp;&nbsp;'), $desc);
			$methods          = $reflectionObject->getMethods(ReflectionMethod::IS_PUBLIC);
			$count            = count($properties) + count($methods);
			$results          = '<span title="' . $desc . '" class="nobr active" onclick="toggle(\'' . $hache . '\'); toggle(\'' . $hache . '_close\');"><b>' . get_class($var) . '</b> Object (vars: ' . count($properties) . ' / methods: ' . count($methods) . ') <span id="' . $hache . '_close" ' . ($count <= $countForCollapse ? 'style="display:none;"' : '') . ' class="collapse_title">{...}</span></span>';
			$results .= '<span id="' . $hache . '" class="collapseble" ' . ($count > $countForCollapse ? 'style="display:none;"' : '') . '>';
			foreach ($properties as $propertyReflection) {
				/* @var $propertyReflection ReflectionProperty */
				$desc = iconv('utf-8', $charset, htmlspecialchars($propertyReflection->getDocComment()));
				$desc = str_replace(array('"', ' ', "\n", "\r", "\t"), array('\"', '&nbsp;', '<br/>', '&nbsp;&nbsp;&nbsp;&nbsp;'), $desc);
				$results .= '<br>' . str_repeat('&nbsp;', $depth * 2)
						. '<span class="nobr" title="' . $desc . '"><i> -&gt;' . strtr($propertyReflection->getName(), $_replace) . '</i></span> = '
						. quicky_modifier_debug_print_var($propertyReflection->getValue($var), $charset, $depth + 1, 20);
			}
			foreach ($methods as $methodReflection) {
				/* @var $methodReflection ReflectionMethod */
				$args = array();
				foreach ($methodReflection->getParameters() as $param) {
					/* @var $param ReflectionParameter */
					$argument = $param->getName();
					if ($param->isArray()) {
						$argument = 'array ' . $argument;
					}
					elseif ($param->getClass()) {
						$argument = $param->getClass()->getName() . ' ' . $argument;
					}
					if ($param->isPassedByReference()) {
						$argument = '&' . $argument;
					}
					if ($param->isOptional()) {
						if ($param->isDefaultValueAvailable()) {
							$defData = str_replace(array(' ', "\n", "\r", "\t"), '', var_export($param->getDefaultValue(), 1));
							/** @see http://bugs.php.net/bug.php?id=33312 */
							if (!empty($defData)) {
								$argument = $argument . '=' . $defData;
							}
						}
						$argument = '[' . $argument . ']';
					}
					$args[] = $argument;
				}

				$desc = iconv('utf-8', $charset, $methodReflection->getDocComment());
				if (!$desc) {
					$desc = $methodReflection->getDocComment();
				}
				$desc = str_replace(array('"', ' ', "\n", "\r", "\t"), array('\"', '&nbsp;', '<br/>', '&nbsp;&nbsp;&nbsp;&nbsp;'), htmlspecialchars($desc));
				$results .= '<br>' . str_repeat('&nbsp;', $depth * 2)
						. '<span class="nobr" title="' . $desc . '"><b> -&gt; function</b> ' . $methodReflection->getName() . '(' . implode(', ', $args) . ')</span>';
			}
			$results .= '</span>';
			break;
		case 'boolean' :
		case 'NULL'    :
		case 'resource' :
			if (true === $var) {
				$results = 'true';
			}
			elseif (false === $var) {
				$results = 'false';
			}
			elseif (null === $var) {
				$results = 'null';
			}
			else {
				$results = htmlspecialchars((string)$var);
			}
			$results = '<i>' . $results . '</i>';
			break;
		case 'integer' :
		case 'float' :
			$results = htmlspecialchars((string)$var);
			break;
		case 'unknown type' :
		default :
			$results = (string)$var;
			if (strlen($results) > $length) {
				$results =
						'<span id="' . $hache . '_close" class="active nobr collapse_title" onclick="toggle(\'' . $hache . '_close\'); toggle(\'' . $hache . '\');">' .
								strtr(htmlspecialchars(substr($results, 0, $length - 3)), $_replace) .
								'... (' . strlen($results) . ')' .
								'</span>' .
								'<span id="' . $hache . '" style="display:none;" class="active collapseble" onclick="toggle(\'' . $hache . '_close\'); toggle(\'' . $hache . '\');">' .
								strtr(htmlspecialchars($results), $_replace) .
								'</span>' .
								'<script> function f' . $hache . '_new_window(){
						window.open("", "", "width=880, height=600, resizable, scrollbars=yes").document.write("<textarea rows=40 cols=80>' . str_replace(array("\n", "\r"), array('\n', '\r'), htmlspecialchars($results, ENT_QUOTES, $charset)) . '</textarea>");
						return false;
            			}
            		</script>
            		<span class="active" onclick="return f' . $hache . '_new_window();" title="open in new window">' .
								'&curren;' .
								'</span>';
			}
	}
	return $results;
}
