<?php
require_once '../Quicky.class.php';
define('MICROTIME_START',microtime(TRUE));

error_reporting(E_ALL);
ini_set('display_errors','On');

$tpl = new Quicky;
//$tpl->force_compile = TRUE;
//$tpl->compile_check = FALSE;

require_once '../Quicky.form.class.php';
$form = new Quicky_form('bbform');
$form->addElement('mybbarea',new QBBarea(array(
 'name' => 'mybbarea'
)));
$form->mybbarea->_bbcode->smiles_dir = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'smiles').DIRECTORY_SEPARATOR;
$form->mybbarea->_bbcode->smiles_url = 'http://'.$_SERVER['HTTP_HOST'].dirname(dirname($_SERVER['SCRIPT_NAME'])).'/smiles/';
$form->addElement('btn1', new QButton(array(
 'name' => 'submit',
 'type' => 'submit',
 'value' => 'Show!'
)));
if ($form->btn1->clicked())
{
 $form->_done = $form->_num_of_errors == 0;
}
$fn = 'form/bbcode.tpl';
$tpl->display($fn);
echo '<hr />'.(microtime(TRUE)-MICROTIME_START);
echo '<hr />';
highlight_file($tpl->_get_compile_path($fn,''));
echo '<hr />';
highlight_file($tpl->template_dir.$fn);
echo '<hr />';
highlight_file(__FILE__);
