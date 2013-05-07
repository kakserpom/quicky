<?php
require_once $this->parent->fetch_plugin('block.shortcut');
function quicky_block_block($params, $content, $compiler) {
	return quicky_block_shortcut($params, $content, $compiler);
}
