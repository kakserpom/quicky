<?php
class Quicky_joincalculator {
	public function _joincalc_callback($m) {
		if (isset($this->_tmp[$m[0]])) {
			return 'document.getElementById(\'' . $this->_tmp[$m[0]] . '\').value';
		}
		else {
			return $m[0];
		}
	}
}

function quicky_block_joincalculator($params, $content, $compiler) {
	static $obj = NULL;
	if ($obj === NULL) {
		$obj = new Quicky_joincalculator;
	}
	$params = $compiler->_parse_params($params);
	if (!isset($params['name'])) {
		return $compiler->_syntax_error('Missing parameter \'name\' in joincalculator tag.');
	}
	if (!isset($params['fields'])) {
		return $compiler->_syntax_error('Missing parameter \'fields\' in joincalculator tag.');
	}
	$params['name']   = strpos($params['name'], '$') !== FALSE ? '<?php echo ' . $params['name'] . '; ?>' : $compiler->_dequote($params['name']);
	$params['fields'] = explode(',', $compiler->_dequote($params['fields']));
	$fields           = array();
	$return           = '<script type="text/javascript">
function calculator_' . $params['name'] . '(field)
{
';
	foreach ($params['fields'] as $k => $v) {
		if (preg_match('~(.*?)\s+as\s+(.*)~i', $v, $q)) {
			if (isset($fields[$q[2]])) {
				return $compiler->_syntax_error('Field name \'' . $q[2] . '\' alredy in use ');
			}
			$fields[$q[2]] = $q[1];
			$fields[$q[1]] = $q[1];
		}
		else {
			$fields[$v] = $v;
		}
	}
	$f = '';
	foreach ($fields as $k => $v) {
		$f .= '|' . preg_quote($k, '~');
	}
	if ($f === '') {
		return $compiler->_syntax_error('No fields');
	}
	$obj->_tmp = $fields;
	preg_match_all('~\s*(.*?)\s*=\s*(.*?)(?:[\r\n]+|$)~m', $content, $m, PREG_SET_ORDER);
	foreach ($m as $v) {
		$name  = $v[1];
		$expr  = $v[2];
		$left  = preg_replace_callback($a = '~([\'"]).*?\1' . $f . '~', array($obj, '_joincalc_callback'), $name);
		$right = preg_replace_callback('~([\'"]).*?\1' . $f . '~', array($obj, '_joincalc_callback'), $expr);
		$return .= 'if (field != \'' . $name . '\') ' . $left . ' = ' . $right . ';
';
	}
	$obj->_tmp = array();
	$return .= '}';
	foreach ($fields as $k => $v) {
		if ($k != $v) {
			continue;
		}
		$return .= '
document.getElementById(\'' . $v . '\').onchange = function() {setTimeout(function() {calculator_' . $params['name'] . '("' . $v . '");},50);}';
		if (isset($params['onkeydown'])) {
			$return .= '
document.getElementById(\'' . $v . '\').onkeydown = function() {setTimeout(function() {calculator_' . $params['name'] . '("' . $v . '");},50);}';
		}
	}
	$return .= '
</script>';
	return $return;
}
