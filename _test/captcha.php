<?php
require_once '../Quicky.class.php';
define('MICROTIME_START',microtime(TRUE));

error_reporting(E_ALL);
ini_set('display_errors','On');

$tpl = new Quicky;
//$tpl->force_compile = TRUE;
//$tpl->compile_check = FALSE;

require_once '../Quicky.form.class.php';
require_once QUICKY_DIR.'plugins/addons/QCAPTCHA.class.session.php';
$form = new Quicky_form('form1');
$form->addElement('captcha1', new QCAPTCHA(array(
'name' => 'captcha1_text'
)));
$form->addElement('btn1', new QButton(array(
 'name' => 'submit',
 'type' => 'submit',
 'value' => 'Check!'
)));
$form->done = FALSE;
if ($form->btn1->clicked())
{
 if (!$form->captcha1->validate()) {$form->captcha1->_errormsg = 'invalid text';}
 else {$form->done = TRUE;}
}

$fn = 'form/captcha.tpl';
$tpl->display($fn);
echo '<hr />'.(microtime(TRUE)-MICROTIME_START);
echo '<hr />';
highlight_file($tpl->_get_compile_path($fn,''));
echo '<hr />';
highlight_file($tpl->template_dir.$fn);
echo '<hr />';
highlight_file(__FILE__);
