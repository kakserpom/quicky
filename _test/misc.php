<?php
define('MICROTIME_START', microtime(TRUE));
require_once '../Quicky.class.php';

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$tpl = new Quicky;
$tpl->assign('myFunc', function () { return 'myResult'; });
$tpl->force_compile = TRUE;
//$tpl->compile_check = FALSE;
$fn = 'syntax/misc.tpl';
$tpl->display($fn);
echo '<hr />' . (microtime(TRUE) - MICROTIME_START);
echo '<hr />';
highlight_file($p = $tpl->_get_compile_path($fn, ''));
echo '<hr />';
highlight_file($tpl->_get_template_path($fn));
