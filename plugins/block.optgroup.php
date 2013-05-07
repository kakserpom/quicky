<?php
function quicky_block_optgroup($params, $content, $compiler) {
	$block_name     = 'optgroup';
	$params         = $compiler->_parse_params($params);
	$params['text'] = isset($params['text']) ? $params['text'] : '';
	$params['text'] = strpos($params['text'], '$') !== FALSE ? '<?php echo htmlspecialchars(' . $params['text'] . ',ENT_QUOTES); ?>' : htmlspecialchars($compiler->_dequote($params['text']));
	return '<optgroup label="' . $params['text'] . '">'
			. $compiler->_tag_token($content, $block_name)
			. '</optgroup>';
}