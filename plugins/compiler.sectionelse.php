<?php
function quicky_compiler_sectionelse($params, $compiler) {
	$compiler->_tag_stacks[$compiler->_tag_stack_n][] = 'sectionelse';
	return '<?php endfor; else: ?>';
}
