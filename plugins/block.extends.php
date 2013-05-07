<?php
function quicky_block_extends($params, $content, $compiler) {
	$block_name = 'extends';
	$params     = $compiler->_parse_params($params);
	if (!isset($params['template'])) {
		return $compiler->_syntax_error('Missing parameter \'template\' in extends block.');
	}
	$path                         = $compiler->_dequote($params['template']);
	$old_lockmode                 = $compiler->_shortcutslockmode;
	$old_shortcutslocked          = $compiler->_shortcutslocked;
	$compiler->_shortcutslockmode = TRUE;
	$compiler->_tag_token($content, $block_name);
	$compiler->_shortcutslockmode = $old_lockmode;
	$compiler->parent->_compile($path, '', $compiler->compiler_name, TRUE);
	$compiler->_shortcutslocked = $old_shortcutslocked;
	$return                     = file_get_contents($compiler->parent->_get_compile_path($path, ''));
	return $return;
}
