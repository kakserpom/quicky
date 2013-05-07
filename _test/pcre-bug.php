<?php
ini_set('pcre.backtrack_limit', '19820');
preg_replace('~\\{\\{?\\s*(begin)(?:\\s+(.*?))?\\}\\}?((?:(?R)|.)*?)\\{\\{?\\s*(?:end(?:\\s+\\2)?)?\\s*\\}\\}?|\\{\\{(\\??(?:[^\\}\'"]*([\'"]).*?(?<!\\\\)\\5)*.*?)\\}\\}|\\{\\s*(if|foreach|section|for|while|switch|literal|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup|capture|php|strip|textformat|dynamic|select|joincalculator|function|helper|form|_if|_foreach|_for|shortcut|block|optgroup)(\\s(?:[^\\}\'"]*([\'"]).*?(?<!\\\\)\\8)*.*?)?\\}((?:(?R)|.)*?)\\{/\\s*\\6?\\s*\\}|\\{(\\??(?:[^\\}\'"]*([\'"]).*?(?<!\\\\)\\11)*.*?)\\}|\\r?\\n~si', '', '{?$_debug_info = get_debug_info()}
{capture assign="debug_output"}
{if empty($_debug_charset)}{assign var="_debug_charset" value="utf-8"}{/if}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
    else {ldelim}
       var title = \'Debug Console_\' + self.name;
    {rdelim}
    _quicky_console = window.open("", title.value, "width=880, height=600, resizable, scrollbars=yes");
    _quicky_console.document.write({$debug_output|native_json_encode});
    _quicky_console.document.close();
// ]]>
</script><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
    else {ldelim}
       var title = \'Debug Console_\' + self.name;
    {rdelim}
    _quicky_console = window.open("", title.value, "width=880, height=600, resizable, scrollbars=yes");
    _quicky_console.document.write({$debug_output|native_json_encode});
    _quicky_console.document.close();
// ]]>
</script>
{/if}'); ?>