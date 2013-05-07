<?php
require_once '../Quicky.class.php';
define('MICROTIME_START', microtime(TRUE));

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$tpl                = new Quicky;
$tpl->force_compile = TRUE;
//$tpl->compile_check = FALSE;

function testfunc() {
	return 'Hello World!';
}

$tpl->register_function('testfunction', 'testfunc');
$fn = 'userdefined_func.tpl';
$tpl->display($fn);
echo '<hr />' . (microtime(TRUE) - MICROTIME_START);
echo '<hr />';
highlight_file($tpl->_get_compile_path($fn, ''));
echo '<hr />';
highlight_file($tpl->template_dir . $fn);

