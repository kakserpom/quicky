<?php
require_once '../Quicky.class.php';
define('MICROTIME_START', microtime(TRUE));

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$tpl = new Quicky;
//$tpl->force_compile = TRUE;
//$tpl->compile_check = FALSE;

require_once '../Quicky.form.class.php';
$form = new Quicky_form('form1');
$form->addElement('text1', array(
	'name' => 'text1'
));
$form->addElement('btn1', new QButton(array(
										  'name' => 'submit',
										  'type' => 'submit'
									  )));
if ($form->btn1->clicked()) {
	$form->text1->addFilter('email', 'isn\'t e-mail');
	$form->text1->addFilter(array('format', '~_~'), 'need _');
	$form->_done = $form->_num_of_errors == 0;
}

$fn = 'form/filter.tpl';
$tpl->display($fn);
echo '<hr />' . (microtime(TRUE) - MICROTIME_START);
echo '<hr />';
highlight_file($tpl->_get_compile_path($fn, ''));
echo '<hr />';
highlight_file($tpl->template_dir . $fn);
echo '<hr />';
highlight_file(__FILE__);
