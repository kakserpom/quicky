<?php
require_once '../Quicky.class.php';
define('MICROTIME_START', microtime(TRUE));

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$tpl = new Quicky;
//$tpl->force_compile = TRUE;
//$tpl->compile_check = FALSE;
require_once $tpl->fetch_plugin('addons/memory_cache.class');
$tpl->cache_dir = 'qmem://' . $tpl->cache_dir;
$fn             = 'caching/index.tpl';
if (isset($_REQUEST['clear'])) {
	$tpl->clear_cache($fn, '*');
}
$tpl->caching        = 1;
$tpl->cache_lifetime = 60; // ������
if (!$tpl->is_cached('index.tpl')) //��������� ���������� �� ���������� ���, ���� ��� �� �������� ������
{
	$tpl->assign('var', '�����-������ ��������, ��������, �� ��');
}
$tpl->display($fn);
