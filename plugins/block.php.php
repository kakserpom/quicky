<?php
function quicky_block_php($params, $content, $compiler) {
	if (!isset($compiler->prefs['allow_php_native'])) {
		return $compiler->_syntax_error('Disallowed PHP-tag');
	}
	return '<?php ' . $content . ' ?>';
}
