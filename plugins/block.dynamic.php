<?php
function quicky_block_dynamic($params, $content, $compiler) {
	$block_name = 'dynamic';
	return '<?php /*' . UNIQUE_HASH_STATIC . '{dynamic}*/ ?>' . $compiler->_tag_token($content, $block_name) . '<?php /*{/dynamic}' . UNIQUE_HASH_STATIC . '*/ ?>';
}
