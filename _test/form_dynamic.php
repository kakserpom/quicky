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
	'title' => 'Textbox 1',
	'name'  => 'paramtext1'
));
$form->addElement('text2', array(
	'title' => 'Textbox 2',
	'name'  => 'paramtext2'
));
$form->addElement('text3', array(
	'title' => 'Textbox 3',
	'name'  => 'paramtext3'
));
$form->addElement('text4', array(
	'title' => 'Textbox 4',
	'name'  => 'paramtext4'
));

$form->addElement('dropdown1', new QDropdown(array(
												 'name' => 'mydropdown'
											 ), array('One', 'Two', 'Three')
));
$form->dropdown1->addElement(array(
								 'type' => 'optgroup',
								 'text' => 'mygroup'
							 ));
$form->dropdown1->addElement(array(
								 'text' => 'mygroup'
							 ));
$form->addElement('box1', new QCheckBox(array(
											'name'  => 'box1',
											'type'  => 'checkbox',
											'value' => '1'
										)));
$form->addElement('btn1', new QButton(array(
										  'name'  => 'submit',
										  'type'  => 'submit',
										  'value' => 'Test!'
									  )));
if ($form->btn1->clicked()) {
	$form->_done = TRUE;
}

$fn = 'form/dynamic.tpl';
$tpl->display($fn);
echo '<hr />' . (microtime(TRUE) - MICROTIME_START);
echo '<hr />';
highlight_file($tpl->_get_compile_path($fn, ''));
echo '<hr />';
highlight_file($tpl->template_dir . $fn);
echo '<hr />';
highlight_file(__FILE__);
