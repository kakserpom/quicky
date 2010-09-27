<?php
/**************************************************************************/
/* Quicky: smart and fast templates
/* ver. 0.5.0.0
/* http://code.google.com/p/quicky/
/* ===========================
/*											
/* blitz.class.php: blitz-compatible API
/**************************************************************************/
require_once dirname(__FILE__).'/Quicky.class.php';
class Blitz extends Quicky
{
 public $tplname;
 public $parsed = FALSE;
 public function __construct($tpl)
 {
  $this->init();
  $this->tplname = $tpl;
 }
 public function _fetch($name)
 {
  if (!$this->parsed) {$this->parse(TRUE);}
  return $this->context_fetch($name);
 }
 public function set($value = array())
 {
  return $this->context_set($value);
 }
 public function iterate($name = '')
 {
  return $this->context_iterate($name);
 }
 public function context($path)
 {
  return $this->context_path($path);
 }
 public function parse($fetch = FALSE,$vars = array())
 {
  $this->parsed = TRUE;
  if ($fetch) {return $this->fetch($this->tplname);}
  return $this->display($this->tplname);
 }
 public function load($string)
 {
  $this->load_string($tpl->tplname = dechex(abs(crc32($string))),$string);
 }
 function escape($string) {return htmlspecialchars($string,ENT_QUOTES);}
}
