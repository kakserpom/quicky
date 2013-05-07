<?php
require_once '../Quicky.class.php';
define('MICROTIME_START', microtime(TRUE));

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$tpl = new Quicky;
//$tpl->force_compile = TRUE;
//$tpl->compile_check = FALSE;

$tpl->detect_form('test');
$tpl->display($fn = 'form/form_detection.tpl');

$form = $tpl->getFormByName('test');
echo 'Entered value: ' . htmlspecialchars($form->textbox1->getValue());

echo '<hr />' . (microtime(TRUE) - MICROTIME_START);
echo '<hr />';
highlight_file($tpl->_get_compile_path($fn, ''));
echo '<hr />';
highlight_file($tpl->template_dir . $fn);
echo '<hr />';
highlight_file(__FILE__);
