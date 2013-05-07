<?php
function quicky_compiler_sethash($params, $compiler) {
	$params = $compiler->_parse_params($params);
	$h      = $params[0];
	return '<?php if (($c = crc32(implode(\'|\',' . $h . '))) !== ' . eval('return crc32(implode(\'|\',' . $h . '));') . ') {return $this->display($path,$cache_id,$compile_id.\'_\'.$c);} ?>';
}
