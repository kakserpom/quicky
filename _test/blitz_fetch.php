<?php
require_once '../blitz.class.php';
define('MICROTIME_START', microtime(TRUE));

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$fn    = 'blitz/index.tpl';
$blitz = new Blitz($fn);
//$blitz->force_compile = TRUE;
//$blitz->compile_check = FALSE;
$blitz->assign('a', 'value of $a');
$blitz->iterate('test');
echo '<b>' . $blitz->_fetch('test') . '</b>';

echo '<hr />' . (microtime(TRUE) - MICROTIME_START);
echo '<hr />';
highlight_file($blitz->_get_compile_path($fn, ''));
echo '<hr />';
highlight_file($blitz->template_dir . $fn);
echo '<hr />';
highlight_file(__FILE__);
