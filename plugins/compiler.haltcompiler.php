<?php
function quicky_compiler_haltcompiler($params, $compiler) {
	$compiler->_halt = TRUE;
	return '';
}
