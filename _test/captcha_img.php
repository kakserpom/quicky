<?php
session_start();
if (!isset($_REQUEST['id']) || !isset($_SESSION['captcha'][$id = intval($_REQUEST['id'])])) {
	exit(';-)');
}
//if ($_SESSION['captcha'][$id][1]) {exit(':-D');}
$_SESSION['captcha'][$id][1] = 1;
require_once '../plugins/Captcha/Captcha_draw.class.php';
$CAPTCHA       = new CAPTCHA_draw;
$CAPTCHA->text = $_SESSION['captcha'][$id][0];
$CAPTCHA->show();
