<?php
chdir('..');
require_once '../Quicky.class.php';
define('MICROTIME_START', microtime(TRUE));

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$tpl = new Quicky;
$tpl->load_compiler('Quicky');
//$tpl->compiler_prefs['inline_includes'] = TRUE;
$tpl->force_compile = TRUE;
$tpl->debug_mode    = TRUE;
$tpl->plugins_dir[] = QUICKY_DIR . 'customplugins/';
//$tpl->depart_scopes = TRUE;
//$tpl->compile_check = FALSE;

$tpl->assign('testvariable', 'testvalue');

$fn = 'extreme/bugreport.tpl';
$tpl->display($fn);
echo '<br />' . (microtime(TRUE) - MICROTIME_START);
echo '<hr />';
highlight_file($tpl->_get_compile_path($fn, ''));
echo '<hr />';
highlight_file($tpl->template_dir . $fn);

