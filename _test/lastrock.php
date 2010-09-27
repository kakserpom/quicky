<?php
// ѕример разработки простого приложени€
require_once '../Quicky.class.php';
define('MICROTIME_START',microtime(TRUE));

error_reporting(E_ALL);
ini_set('display_errors','On');
session_start();
if (!isset($_SESSION['lastrock_action']) || isset($_REQUEST['stop'])) {$_SESSION['lastrock_action'] = 'intro';}

$tpl = new Quicky;
//$tpl->force_compile = TRUE;
//$tpl->compile_check = FALSE;

require_once '../Quicky.form.class.php';

$playform = new Quicky_form('play');
$playform->addElement('btn1',new QButton(array('name' => 'numbtn1')));
$playform->addElement('btn2',new QButton(array('name' => 'numbtn2')));
$playform->addElement('btn3',new QButton(array('name' => 'numbtn3')));

$form = new Quicky_form('startgame');
$form->addElement('num',array(
 'name' => 'num'
));
$form->addElement('startbtn', new QButton(array(
 'name' => 'submit'
)));

if ($_SESSION['lastrock_action'] == 'play')
{
 if ($playform->btn1->clicked()) {$humantake = 1;}
 elseif ($playform->btn2->clicked()) {$humantake = 2;}
 elseif ($playform->btn3->clicked()) {$humantake = 3;}
 else {$humantake = 0;}
 if ($humantake)
 {
  $_SESSION['lastrock_num'] -= $humantake;
  $_SESSION['lastrock_who'] = TRUE;
  $tpl->assign('humantake',$humantake);
  if ($_SESSION['lastrock_num'] == 0)
  {
   $_SESSION['lastrock_action'] = 'intro';
   $_SESSION['lastrock_gameover'] = TRUE;
   header('Location: '.$_SERVER['PHP_SELF']);
   exit;
  }
 }
 if ($_SESSION['lastrock_who'])
 {
  $comp = $_SESSION['lastrock_num']-(floor(($_SESSION['lastrock_num']-1)/4)*4+1);
  if ($comp == 0) {$comp = 1;}
  $_SESSION['lastrock_num'] -= $comp;
  $_SESSION['lastrock_who'] = FALSE;
  $tpl->assign('comptake',$comp);
 }
}
else
{ 
 if ($form->startbtn->clicked())
 {
  if ($form->num->addFilter('digit','isn\'t number') and $form->num->getValue() > 100)
  {
   $form->num->error('number may not be greater then 100');
  }
  if ($form->_num_of_errors == 0)
  {
   $_SESSION['lastrock_action'] = 'play';
   $_SESSION['lastrock_num'] = $form->num->getValue();
   $_SESSION['lastrock_who'] = !(($_SESSION['lastrock_num']-1)%4 == 0);
   header('Location: '.$_SERVER['PHP_SELF']);
  }
 }
}
$tpl->assign('action',$_SESSION['lastrock_action']);
$tpl->display($fn = 'lastrock.tpl');
echo '<hr />'.(microtime(TRUE)-MICROTIME_START);
echo '<hr />';
highlight_file(__FILE__);
echo '<hr />';
highlight_file($tpl->_get_template_path($fn));
