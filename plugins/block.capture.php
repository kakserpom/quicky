<?php
function quicky_block_capture($params, $content, $compiler) {
	$block_name = 'capture';
	$params     = $compiler->_parse_params($params);
	$name       = isset($params['name']) ? trim($compiler->_dequote($params['name'])) : 'default';
	$assign     = isset($params['assign']) ? trim($compiler->_dequote($params['assign'])) : '';
	if (isset($params['ob'])) {
		$return = '<?php ob_start(); ?>' . $compiler->_tag_token($content, $block_name) . '<?php $capture[\'' . $name . '\'] = ob_get_contents(); ob_end_clean();' . ($assign !== '' ? ' $var[\'' . $assign . '\'] = $capture[\'' . $name . '\'];' : '') . ' ?>';
	}
	else {
		$old_write_out_to        = $compiler->_write_out_to;
		$compiler->_write_out_to = '$capture[\'' . $name . '\']';
		$block                   = $compiler->_tag_token($content, $block_name);
		$block                   = $compiler->_optimize($block);
		$return                  = '<?php $capture[\'' . $name . '\'] = \'\';' . "\n" . $block
				. ($assign !== '' ? '$' . $compiler->_resolve_var($assign) . '[\'' . $assign . '\'] = $capture[\'' . $name . '\'];' : '') . ' ?>';
		$compiler->_write_out_to = $old_write_out_to;
	}
	return $return;
}
