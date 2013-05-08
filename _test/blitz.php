<?php
require_once '../blitz.class.php';
define('MICROTIME_START', microtime(TRUE));

error_reporting(E_ALL);
ini_set('display_errors', 'On');

class View extends Blitz {
	function escape($string) {
		return htmlspecialchars($string);
	}
}

$fn    = 'blitz/index.tpl';
$blitz = new View($fn);
//$blitz->force_compile = TRUE;
//$blitz->compile_check = FALSE;
$blitz->assign('a', 'value of $a');
$blitz->iterate('test');
$blitz->parse();

echo '<hr />' . (microtime(TRUE) - MICROTIME_START);
echo '<hr />';
highlight_file($blitz->_get_compile_path($fn, ''));
echo '<hr />';
highlight_file($blitz->template_dir . $fn);
echo '<hr />';
highlight_file(__FILE__);
