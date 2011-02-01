<?php
function quicky_modifier_get_query($params,$mode = '')
{
 $q = '';
 $all = strpos($mode,'a') !== FALSE;
 $plain = strpos($mode,'p') !== FALSE;
 $d = $plain?'&':'&amp;';
 if ($all) {$params = array_unique(array_merge(array_keys($_GET),$params));}
 foreach ($params as $k => $v)
 {
  $q .= is_int($k)
			?(isset($_REQUEST[$v]) && (!array_key_exists($v,$params))?($q !== ''?$d:'?').$v.'='.urlencode(gpcvar_str($_REQUEST[$v])):'')
			:($v !== NULL?($q !== ''?$d:'?').$k.'='.urlencode($v):'')
  ;
 }
 return $q;
}
