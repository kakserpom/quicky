<?php
require_once '../Quicky.class.php';
define('MICROTIME_START',microtime(TRUE));

error_reporting(E_ALL);
ini_set('display_errors','On');

$tpl = new Quicky;
//$tpl->force_compile = TRUE;
$tpl->compile_check = FALSE;


class Quicky_simple_wrapper
{
 public $position = 0;
 public $body;
 function fetch_template($path)
 { // Fetch body
  $this->body = 'Hello from wrapper! {pow(123,123)}';
  return TRUE;
 }
 function stream_open($path,$mode,$options,&$opened_path)
 {
  return $this->fetch_template($path);
 }
 function stream_read($count)
 {
  $ret = substr($this->body,$this->position,$count);
  $this->position += strlen($ret);
  return $ret;
 }
 function stream_write($data) {return;}
 function stream_tell() {return $this->position;}
 function stream_eof() {return $this->position >= strlen($this->body);}
 function stream_seek($offset,$whence) {return;}
 function stream_stat() {}
 function url_stat() {}
}
stream_wrapper_register('simple','Quicky_simple_wrapper') or die('Failed to register protocol');

$fn = 'simple://123';
$tpl->display($fn);
echo '<hr />'.(microtime(TRUE)-MICROTIME_START);
echo '<hr />';
highlight_file($p = $tpl->_get_compile_path($fn,''));
echo '<hr />';
highlight_file($tpl->_get_template_path($fn));

