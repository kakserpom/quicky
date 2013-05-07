<?php
// Multilang example

require_once '../Quicky.class.php';
define('MICROTIME_START', microtime(TRUE));

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$tpl = new Quicky;

$tpl->lang = gpcvar_str($_REQUEST['lang']);
if ($tpl->lang === '') {
	$tpl->lang = 'en';
}

function lang_getmessage($phrase) // your function returning phrases
{
	if (Quicky::$obj->lang == 'ru') {
		if ($phrase = 'HI') {
			return 'Привет';
		}
		if ($phrase = 'BYE') {
			return 'Пока';
		}
	}
	else {
		if ($phrase = 'HI') {
			return 'Hi';
		}
		if ($phrase = 'BYE') {
			return 'Bye';
		}
	}
}

function quicky_lang_callback($m) { return lang_getmessage($m[1]); }

function quicky_lang_callback_e($m) { return addslashes(lang_getmessage($m[1])); }

$tpl->lang_callback   = 'quicky_lang_callback';
$tpl->lang_callback_e = 'quicky_lang_callback_e';

$tpl->display($fn = 'multilang.tpl');
echo '<hr />' . (microtime(TRUE) - MICROTIME_START);
echo '<hr />';
highlight_file(__FILE__);
echo '<hr />';
highlight_file($tpl->_get_template_path($fn));
echo '<hr />';
highlight_file($tpl->_get_compile_path($fn, ''));
