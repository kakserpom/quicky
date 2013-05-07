<?php
function quicky_block_shortcut($params, $content, $compiler) {
	$block_name = 'shortcut';
	$e          = preg_split('~\s+~', trim($params));
	$name       = trim($e[0]);
	$keyword    = isset($e[1]) ? strtolower(trim($e[1])) : '';
	$hidden     = $keyword === 'hidden';
	$content    = $compiler->_tag_token($content, $block_name);
	if (!in_array($name, $compiler->_shortcutslocked)) {
		$compiler->_shortcuts[$name] = $content;
	}
	if ($compiler->_shortcutslockmode) {
		$compiler->_shortcutslocked[] = $name;
	}
	return $hidden ? '' : $compiler->_shortcuts[$name];
}
