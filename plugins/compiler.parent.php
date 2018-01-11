<?php
function quicky_compiler_parent($params, $compiler) {
    if (!$compiler->_current_shortcut) {
        $compiler->parent->warning("{parent} is called outside of {block}");
        return '';
    }
    return $compiler->_shortcuts[$compiler->_current_shortcut];
}
