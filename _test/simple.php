<?php
require_once '../Quicky.class.php';
define('MICROTIME_START', microtime(TRUE));

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$tpl = new Quicky;
//$tpl->force_compile = TRUE;
//$tpl->compile_check = FALSE;

$tpl->assign('Name', 'Fred Irving Johnathan Bradley Peppergill');
$tpl->assign('FirstName', array('John', 'Mary', 'James', 'Henry'));
$tpl->assign('LastName', array('Doe', 'Smith', 'Johnson', 'Case'));
$tpl->assign('Class', array(array('A', 'B', 'C', 'D'), array('E', 'F', 'G', 'H'),
	array('I', 'J', 'K', 'L'), array('M', 'N', 'O', 'P')));

$tpl->assign('contacts', array(array('phone' => '1', 'fax' => '2', 'cell' => '3'),
	array('phone' => '555-4444', 'fax' => '555-3333', 'cell' => '760-1234')));

$tpl->assign('option_values', array('NY', 'NE', 'KS', 'IA', 'OK', 'TX'));
$tpl->assign('option_output', array('New York', 'Nebraska', 'Kansas', 'Iowa', 'Oklahoma', 'Texas'));
$tpl->assign('option_selected', 'NE');
$tpl->config_load('test.conf');

$fn = 'simple/index.tpl';
$tpl->display($fn);
echo '<br />' . (microtime(TRUE) - MICROTIME_START);
echo '<hr />';
highlight_file($tpl->_get_compile_path($fn, ''));
echo '<hr />';
highlight_file($tpl->template_dir . $fn);
