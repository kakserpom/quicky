<?php
function quicky_compiler_foreachelse($params, $compiler) {
	$compiler->_tag_stacks[$compiler->_tag_stack_n][] = 'foreachelse';
	return '<?php endforeach; else: ?>';
}
