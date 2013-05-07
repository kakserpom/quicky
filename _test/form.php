<?php
require_once '../Quicky.class.php';
define('MICROTIME_START', microtime(TRUE));

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$tpl                = new Quicky;
$tpl->force_compile = TRUE;
//$tpl->compile_check = FALSE;

require_once '../Quicky.form.class.php';
$form = new Quicky_form('form1');
$form->addElement('text1', array(
	'name' => 'paramtext1'
));
$form->addElement('btn1', new QButton(array(
										  'name'  => 'submit',
										  'type'  => 'submit',
										  'value' => 'Test!'
									  )));
if ($form->btn1->clicked()) {
	if ($form->text1->getValue() != 'Quicky') {
		$form->text1->_errormsg = 'value is n\'t \'Quicky\'';
	}
	else {
		$form->_done = TRUE;
	}
}

$fn = 'form/index.tpl';
$tpl->display($fn);
echo '<hr />' . (microtime(TRUE) - MICROTIME_START);
echo '<hr />';
highlight_file($tpl->_get_compile_path($fn, ''));
echo '<hr />';
highlight_file($tpl->template_dir . $fn);
echo '<hr />';
highlight_file(__FILE__);
