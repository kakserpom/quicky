<?php
/**************************************************************************/
/* Quicky: smart and fast templates
/* ver. 0.5.0.0
/* http://code.google.com/p/quicky/
/* ===========================
/*
/* Quicky_compiler.class.php: Template compiler
/**************************************************************************/
class Quicky_compiler
{
 public $pcre_trace = FALSE;
 public $precompiled_vars = array();
 public $prefilters = array();
 public $postfilters = array();
 public $compiler_name = 'Quicky';
 public $compiler_version = '0.5.0.0';
 public $load_plugins = array();
 public $seq = array();
 public $seq_id = 0;
 public $_alt_tag = FALSE;
 public $prefs = array();
 public $template_defined_functions = array();
 public $allowed_php_tokens = array('array','date','strtotime','isset','empty','is_empty','count', 'sizeof', 'shuffle',
							'is_array','is_int','is_float','is_long','is_numeric','is_object',
							'is_scalar','is_string','gettype','is_real',
							'abs','acos','acosh','asin','asinh','atan2','atan','atanh','base_','bindec',
							'ceil','cos','cosh','decbin','dechex','decoct','deg2rad','exp','expm1','floor',
							'fmod','getrandmax','hexdec','hypot','is_finite','is_infinite','is_nan','lcg_','log10','log1p',
							'log','max','min','mt_getrandmax','mt_rand','mt_srand','octdec','pi','pow','rad2deg','rand',
							'round','sin','sinh','sqrt','srand','tan','tanh',
							'constant','strlen','time','var_dump','var_export',
							'gmp_*','ctype_*','array_*','addcslashes','addslashes','bin2hex','chop','chr',
							'chunk_split','convert_cyr_string','convert_uudecode','convert_uuencode','count_chars',
							'crc32','crypt','echo','explode','fprintf','get_html_translation_table','hebrev','hebrevc',
							'html_entity_decode','htmlentities','htmlspecialchars_decode','htmlspecialchars','implode',
							'join','levenshtein','localeconv','ltrim','md5_file','md5','metaphone','money_format',
							'nl_langinfo','nl2br','number_format','ord','parse_str','print','printf',
							'quoted_printable_decode','quotemeta','rtrim','sha1_file','sha1','similar_text',
							'soundex','sprintf','sscanf','str_ireplace','str_pad','str_repeat','str_replace',
							'str_rot13','str_shuffle','str_split','str_word_count','strcasecmp','strchr',
							'strcmp','strcoll','strcspn','strip_tags','stripcslashes','stripos','stripslashes',
							'stristr','strlen','strnatcasecmp','strnatcmp','strncasecmp','strncmp','strpbrk',
							'strpos','strrchr','strrev','strripos','strrpos','strspn','strstr','strtok',
							'strtolower','strtoupper','strtr','substr_compare','substr_count','substr_replace',
							'substr','trim','ucfirst','ucwords','vfprintf','vprintf','vsprintf','wordwrap','and','or','xor',
							'json_encode','lang_om_number','intval','floatval','strval','setcookie','in_array',
							'long2ip','ip2long','defined','file_exists','basename','mb_substr','mb_strlen','getdate');
 public $_def_mode = NULL;
 public $_scope_override = NULL;
 public $allowed_php_constants = array();
 public $syntax_errors = array();
 public $template_from;
 public $blocks = array();
 public $left_delimiter = '{';
 public $right_delimiter = '}';
 public $magic_constants = array('tplpath','tplpathdir','ldelim','rdelim');
 public $block_props = array();
 public $_write_out_to = '';
 public $_halt = FALSE;
 public $_line = array();
 public $_line_count = array();
 public $_current_tag = array();
 public $_tag_stacks = array();
 public $_tag_stack_n = 0;
 public $_no_magic = FALSE;
 public $no_optimize = FALSE;
 public $_tmp = array();
 public $_cpl_vars = array();
 public $_cpl_config = array();
 public $_cplmode = FALSE;
 public $_no_auto_escape = FALSE;
 public $_shortcuts = array();
 public $_shortcutslockmode = FALSE;
 public $_shortcutslocked = array();
 public $_var_map = array();
 public function Quicky_compiler() {}
 static function escape_string($s)
 {
  static $escape = array(
	'\\' => '\\\\',
	'\'' => '\\\''
  );
  return strtr($s,$escape);
 }
 public function push_block_props($props,$blocktype,$name)
 {
  for ($i = 0; $i < sizeof($props); $i++) {$this->block_props[$props[$i]] = array($name,$blocktype);}
  return TRUE;
 }
 public function _resolve_var($name)
 {
  if (isset($this->_scope_override)) {$type = $this->_scope_override;}
  elseif (isset($this->_var_map[$name])) {$type = $this->_var_map[$name];}
  else {$type = ($this->parent->depart_scopes || $this->parent->local_depart_scopes)?'local':'global';}
  if ($type == 'local') {return 'local';}
  elseif ($type == 'global') {return 'var';}
  else {$this->_syntax_error('Unknown scope "'.$type.'" for variable "'.$name.'"');}
 }
 public function _syntax_error($msg)
 {
  $error = 'Quicky syntax error '.$msg.' in template '.$this->template_from.' on line '.$this->_line[$this->template_from];
  if ($this->_line_count[$this->template_from] > 0) {$error .= ' (starts at line '.($this->_line[$this->template_from]-$this->_line_count[$this->template_from]).')';}
  $error .= '<br />Tag: '.$this->_current_tag[$this->template_from];
  $this->syntax_errors[] = $error;
 }
 public function register_block($block)
 {
  if (!in_array($block,$this->blocks)) {$this->blocks[] = $block;}
  return TRUE;
 }
 public function unregister_block($block)
 {
  if ($k = array_search($block,$this->blocks)) {unset($this->blocks[$k]); return TRUE;}
  else {return FALSE;}
 }
 public function _block_lang_callback($m)
 {
  $name = $m[2];
  $tag = $m[3];
  preg_match_all('~\{(\w+)\}(.*?)(?=\{\w+\}|\z)~s',$tag,$matches,PREG_SET_ORDER);
  $variants = array();
  foreach ($matches as $m) {$variants[strtolower($m[1])] = trim($m[2]);}
  $reqlang = $this->parent->lang;
  if (isset($variants[$reqlang])) {return $variants[$reqlang];}
  return isset($variants['default'])?$variants['default']:'Warning! Can\'t find phrase '.($name !== ''?'('.htmlspecialchars($name).')':'').' for this language.';
 }
 public function _form_detect_field($m)
 {
  $tag = strtolower($m[1] !== ''?$m[1]:$m[3]);
  $params = $this->_parse_params($m[1] !== ''?$m[2]:$m[4],TRUE);
  if ($tag == 'option')
  {
   $params['text'] = $m[5];
   if (!isset($params['value'])) {$params['value'] = $params['text'];}
  }
  elseif ($tag == 'textarea')
  {
   $params['value'] = $m[5];
  }
  $p = '';
  if (isset($params['name']) and !isset($params['join'])) {$params['join'] = $params['name'];}
  foreach ($params as $k => $v) {$p .= $k.'=\''.$this->escape_string($this->_dequote($v)).'\' ';}
  $p = substr($p,0,-1);

  if ($tag == 'input' or $tag == 'textarea')
  {
   $return = $this->left_delimiter.$tag.' '.$p.$this->right_delimiter;
  }
  elseif ($tag == 'option')
  {
   $return = $this->left_delimiter.'option '.$p.$this->right_delimiter;
  }
  elseif ($tag == 'select')
  {
   $body = preg_replace_callback('~<(option)(\s+.*?)?>(.*?)</\3>~si',array($this,'_form_detect_field'),$m[2]);
   $return = $this->left_delimiter.$tag.' '.$p.$this->right_delimiter.$m[5].$this->left_delimiter.'/'.$tag.$this->right_delimiter;

  }
  return $return;
 }
 public function _form_detect($m)
 {
  $params = $this->_parse_params($m[1],TRUE);
  $form_name = '';
  $p = '';
  $params['auto_object'] = 1;
  foreach ($params as $k => $v)
  {
   if (strtolower($this->_dequote($k)) == 'name') {$form_name = $this->_dequote($v);}
   $p .= $k.'=\''.$this->escape_string($this->_dequote($v)).'\' ';
  }
  if (!$this->parent->_auto_detect_forms and !in_array($form_name,$this->parent->_detect_forms)) {return $m[0];}
  $p = substr($p,0,-1);
  $body = preg_replace_callback('~<(input)(\s+.*?)?\s*/?\s*>|<(textarea|select)(\s+.*?)?>(.*?)</\3>~si',array($this,'_form_detect_field'),$m[2]);
  $return = '{form '.$p.'}'.$body.'{/form}';
  return $return;
 }
 public function _write_comment($m)
 {
  if (!isset($m[2])) {return $m[0];}
  return $this->_write_seq(array($m[0],$m[2]));
 }
 public function _read_comment($m)
 {
  $this->_read_seq($m);
  return '';
 }
 public function _write_seq($m)
 {
  if (!isset($this->seq[$this->seq_id])) {$this->seq[$this->seq_id] = array();}
  $this->seq[$this->seq_id][] = $m[1]; 
  //$this->_line[$this->template_from] += substr_count($m[0],"\n");
  return '~'.$this->seq_hash.'_'.$this->seq_id.str_repeat("\n",substr_count($m[1],"\n")).'~';
 }
 public function _read_seq($reset = FALSE)
 {
  static $i = array();
  if ($reset === TRUE or !isset($i[$this->seq_id])) {$i[$this->seq_id] = 0; return;}
  $r = $this->seq[$this->seq_id][$i[$this->seq_id]++];
  return $r;
 }
 public function _read_sequences($source)
 {
  $this->seq_id = 'comment';
  $this->_read_seq(TRUE);
  $source = preg_replace_callback('~\~'.$this->seq_hash.'_'.$this->seq_id.'\s*\~~si',array($this,'_read_comment'),$source);
  return $source;
 }
 public function _literal_callback($m)
 {
  if (isset($m[2]) && ($m[2] !== ''))
  {
   return $this->left_delimiter.'rdelim'.$this->right_delimiter;
  }
  return $this->left_delimiter.'ldelim'.$this->right_delimiter;
 }
 public function _literal($m)
 {
  $ldelim = preg_quote($this->left_delimiter,'~');
  $rdelim = preg_quote($this->right_delimiter,'~');
  return preg_replace_callback('~('.$ldelim.')|('.$rdelim.')~',array($this,'_literal_callback'),$m[1]);
 }
 public function _compile_source_string($template,$from)
 {
  $this->parent->local_depart_scopes = FALSE;
  $old_load_plugins = $this->load_plugins;
  $this->load_plugins = array();
  $old_template_from = $this->template_from;
  $this->template_from = $from;
  $this->_line[$this->template_from] = 1;
  $this->_line_count[$this->template_from] = 0;
  //$template = str_replace("\r",'',$template);
  $template = preg_replace('~^/.*?/\r?\n~','',$template);

  $ldelim = preg_quote($this->left_delimiter,'~');
  $rdelim = preg_quote($this->right_delimiter,'~');
 
  $this->seq_hash = md5(microtime());
  $this->seq = array();
  
  $this->seq_id = 'comment';
  $template = preg_replace_callback('~([\'"]).*?\1|('.$ldelim.'\*.*?\*'.$rdelim.')~s',array($this,'_write_comment'),$template);

  $a = array_values($this->prefilters);
  for ($i = 0,$s = sizeof($a); $i < $s; $i++) {$template = call_user_func($a[$i],$template,$this);}
  $source = $template;

  if ($this->parent->lang !== '')
  {
   $source = preg_replace_callback('~'.$ldelim.'_\s+(.*?)'.$rdelim.'~',$this->parent->lang_callback,$source);
   $source = preg_replace_callback('~'.$ldelim.'e_\s+(.*?)'.$rdelim.'~i',$this->parent->lang_callback_e,$source);
   $source = preg_replace_callback('~'.$ldelim.'LANG(?:=([\'|"])?(.*?)\1)?'.$rdelim.'(.*?)'.$ldelim.'/LANG'.$rdelim.'~si',array($this,'_block_lang_callback'),$source);
  }
  if ($this->parent->_auto_detect_forms or sizeof($this->parent->_detect_forms) > 0)
  {
   $source = preg_replace_callback('~<form(\s+.*?)?>(.*?)</form>~si',array($this,'_form_detect'),$source);
  }
  if (!isset($this->prefs['allow_php_native']) or !$this->prefs['allow_php_native'])
  {
   $source = preg_replace('~<\?(?:php)?|\?>~i','<?php echo \'$0\'; ?>',$source);
  }
  $source = preg_replace_callback('~'.$ldelim.'literal'.$rdelim.'(.*?)'.$ldelim.'/literal'.$rdelim.'~si',array($this,'_literal'),$source);
  
  $cur_seq = $this->seq;
  $cur_hash = $this->seq_hash;
  $source = $this->_tag_token($source);
  $this->seq = $cur_seq;
  $this->seq_hash = $cur_hash;
  
  $source = $this->_read_sequences($source);
  
  if (!$this->no_optimize and FALSE)
  {
   $source = preg_replace_callback('~\?>(.{0,20}?)<\?php~s',create_function('$m','if ($m[1] === \'\') {return \'\';} return \' echo \\\'\'.Quicky_compiler::escape_string($m[1]).\'\\\';'."\n".'\';'),$source);
   $source = preg_replace_callback('~^(.{1,20}?)(<\?php)~s',create_function('$m','return $m[2].\' echo \\\'\'.Quicky_compiler::escape_string($m[1]).\'\\\';'."\n".'\';'),$source);
   $source = preg_replace_callback('~(\?>)(.{1,20})$~s',create_function('$m','return \' echo \\\'\'.Quicky_compiler::escape_string($m[2]).\'\\\';'."\n".'\'.$m[1];'),$source);
  }
  $header = '<?php /* Quicky compiler version '.$this->compiler_version.', created on '.date('r').'
			 compiled from '.$from.' */'."\n";
  for ($i = 0,$s = sizeof($this->load_plugins); $i < $s; $i++) {$header .= 'require_once '.var_export($this->load_plugins[$i],TRUE).';'."\n";}
  $header .= '$local = &$this->_local_vars['.var_export($from,TRUE).'];'."\n";
  $header .= '$var_buff = &$this->_tpl_vars_buff['.var_export($from,TRUE).'];'."\n";
  $header .= '$var_buff = array();'."\n";
  $header .= 'if ($local === NULL) {$local = array();}'."\n";
  $header .= 'else
{
 foreach ($local as $k => $v)
 {
  $var_buff[$k] = isset($var[$k])?$var[$k]:NULL;
  $var[$k] = &$local[$k];
 }
}
';
  $header .= '?>';
  $footer = '<?php foreach ($this->_tpl_vars_buff['.var_export($from,TRUE).'] as $k => $v) {unset($var[$k]); $var[$k] = $v;} '."\n"
  .' $this->_local_vars['.var_export($from,TRUE).'] = array(); ?>'; 
  if (sizeof($this->syntax_errors))
  {
   return implode("<br />\n",$this->syntax_errors);
  }
  $this->_halt = FALSE;

  $a = array_values($this->postfilters);
  for ($i = 0,$s = sizeof($a); $i < $s; $i++) {$source = call_user_func($a[$i],$source,$this);}

  $this->load_plugins = $old_load_plugins;
  $this->template_from = $old_template_from;
  $source = preg_replace('~^(<\?php.*?)\?><\?php~si','$1',$header.$source.$footer);
  return $source;
 }
 public function _compile_source($path,$from) {return $this->_compile_source_string(file_get_contents($path),$from);}
 public function string_or_expr($s)
 {
  if (ctype_digit(substr($s,0,1) == '-'?substr($s,1):$s)) {return $s;}
  if (preg_match('~^\w+$~',$s))
  {
   if (defined($s) or in_array(strtolower($s),$this->magic_constants) or isset($this->_block_props[strtolower($s)]))
   {
    return $s;
   }
   return '\''.$s.'\'';
  }
  return $s;
 }
 public function _parse_params($p,$plain = FALSE)
 {
  $params = array();
  preg_match_all('~(?:\w+\s*=|(([\'"]).*?(?<!\\\\)\2|\w*\s*\(((?:(?R)|.)*?)\)'
    .'|_?[\$#]\w+#?(?:\\[(?:(?R)|((?:[^\\]\'"]*(?:([\'"]).*?(?<!\\\\)\5)?)*))*?\\]|\.[\$#]?\w+#?|->\s*[\$#]?\w+(?:\(((?:(?R)|.)*?)\))?)*'
    .'|-?\d+|(?<=^|[\s\)\:\.=+\-<>])(?:\w+)(?=$|[\s\|\.\:\(=+\-<>])))'
    .'(?:\|@?\s*\w+(?:\:\s*(?:[^\:\|\'"\s]*(?:([\'"]).*?(?<!\\\\)\1[^\:\|\'"]*)*))*)*'
    .'|.+?~s',$p,$m,PREG_SET_ORDER);
  $lastkey = '';
  foreach ($m as $v)
  {
   $s = sizeof($v);
   if (($s == 1) || ($s == 2) || ($s == 3))
   {
    if (preg_match('~^\w+\s*=$~',$v[0])) {$lastkey = ltrim(rtrim($v[0]," =\t"));}
    elseif ($lastkey !== '') {if (!isset($params[$lastkey])) {$params[$lastkey] = '';} $params[$lastkey] .= $v[0];}
    else {}
    continue;
   }
   if (trim($v[0]) === '') {continue;}
   if ($lastkey === '') {$params[] = $v[0];}
   else {$params[$lastkey] = $v[0];}
   $lastkey = '';
  }
  if (!$plain)
  {
   foreach ($params as $k => $v)
   {
    $v = trim($v);
    $params[$k] = $this->_expr_token($this->string_or_expr($v));
   }
  }
  return $params;
 }
 public function _dequote($string)
 {
  $a = substr($string,0,1);
  $string = preg_replace('~^\s*([\'"])(.*)\1\s*$~s','$2',$string);
  return preg_replace('~(?<!\\\\)\\\\'.preg_quote($a,'~').'~',$a,$string);
 }
 public function _get_expr_blockprop($name,$blocktype,$prop)
 {
  $blocktype = strtolower($blocktype);
  $prop = strtolower($prop);
  $a = '$'.$blocktype.'['.var_export($name,TRUE).']';
  if ($blocktype == 'foreach')
  {
	   if ($prop == 'iteration' or $prop == 'i') {$prop = 'i';}
   elseif ($prop == 'total') {$prop = 's';}
   elseif ($prop == 'first') {return '('.$a.'[\'i\'] == 1)';}
   elseif ($prop == 'last') {return '('.$a.'[\'i\'] == '.$a.'[\'s\'])';}
  }
  elseif ($blocktype == 'section')
  {
	   if ($prop == 'iteration' or $prop == 'rownum') {return '('.$a.'[\'i\']+1)';}
   elseif ($prop == 'index' or $prop == $name) {return 'Quicky::ind('.$a.')';}
   elseif ($prop == 'index_prev' or $prop == $name) {return 'Quicky::ind('.$a.',-1)';}
   elseif ($prop == 'index_next' or $prop == $name) {return 'Quicky::ind('.$a.',1)';}
   elseif ($prop == 'total') {$prop = 's';}
   elseif ($prop == 'first') {return '('.$a.'[\'i\'] == 0)';}
   elseif ($prop == 'last') {return '('.$a.'[\'i\']+1 == '.$a.'[\'s\'])';}
  }
  elseif ($blocktype == 'form')
  {
   if ($prop == 'form') {return 'Quicky_form::$forms['.var_export($name,TRUE).']';}
  }
  return $a.'['.var_export($prop,TRUE).']';
 }
 public function _optimize_callback($m)
 {
  $prefix = ' '.$this->_write_out_to !== ''?$this->_write_out_to.' .= ':'echo ';
  if (isset($m[1]) and $m[1] !== '') {$return = $prefix.var_export($m[1],TRUE).';';}
  elseif (isset($m[2]) and $m[2] !== '') {$return = $prefix.var_export($m[2],TRUE).';';}
  elseif (isset($m[3]) and $m[3] !== '') {$return = $prefix.var_export($m[3],TRUE).';';}
  else {$return = '';}
  return $return;
 }
 public function _optimize($block)
 {
  $block = preg_replace_callback((!preg_match('~<\?php|\?>~i',$block))?'~(.*)~s':'~\?>(.*?)<\?php|^(.*?)<\?php|\?>(.*?)$~si',array($this,'_optimize_callback'),$block);
  return $block;
 }
 public function _fetch_expr($expr)
 {
  $var = &$this->_cpl_vars;
  $config = &$this->_cpl_config;
  return eval('return '.$expr.';');
 }
 public function _varname($s)
 {
  if (preg_match('~^\w+$~',$s)) {$s = $this->_var_token('$'.$s);}
  elseif ((isset($this->prefs['interpret_varname_params']) && $this->prefs['interpret_varname_params']) || (substr($s,0,1) == '\'') || (substr($s,-1) == '\''))
  {
   $type = $this->_resolve_var($this->_dequote($s));
   $s = '$'.$type.'['.$s.']';
  }
  return $s;
 }
 public function _tag_token($mixed,$block_parent = '')
 {
  if (!isset($this->_tag_stacks[$this->_tag_stack_n])) {$this->_tag_stacks[$this->_tag_stack_n] = array();}
  if ($this->_halt && !(is_array($mixed) && isset($mixed[4]) && strtolower($mixed[4]) == 'resumecompiler'))
  {
   return is_array($mixed)?$mixed[0]:$mixed;
  }
  if (is_array($mixed))
  {
   if ((sizeof($mixed) == 1) && (($mixed[0] === "\r\n") || $mixed[0] === "\n"))
   {
    ++$this->_line[$this->template_from];
    return $mixed[0];
   }
   else
   {
    if (isset($mixed[6]) && ($mixed[6] !== '')) {$heap = $mixed[7];}
    else {$heap = $mixed[0];}
    $this->_line_count[$this->template_from] = substr_count($heap,"\n");
    if ($this->_tag_stack_n == 0) {$this->_line[$this->template_from] += $this->_line_count[$this->template_from];}
    $this->_current_tag[$this->template_from] = $mixed[0];
   }
   if ((isset($mixed[6]) && $mixed[6] !== '') || (isset($mixed[10]) && $mixed[10] !== ''))
   {
    $a = array(
     0 => $mixed[0],
     1 => $mixed[6]
    );
    if (isset($mixed[7])) {$a[2] = $mixed[7];}
    if (isset($mixed[9])) {$a[3] = $mixed[9];}
    if (isset($mixed[10])) {$a[4] = $mixed[10];}
    $mixed = $a;
    $this->_alt_tag = FALSE;
   }
   else {$this->_alt_tag = TRUE;}
   if (isset($mixed[4]) && $mixed[4] !== '')
   {
    $this->_no_auto_escape = FALSE;
    preg_match('~^\s*(\S+)(.*)$~s',$mixed[4],$m);
    if (!isset($m[0])) {$m[0] = '';}
    if (!isset($m[1])) {$m[1] = '';}
    $tag = strtolower($m[1]);
    if (preg_match('~^#(\w+)$~',$tag,$sm))
    {
     $name = $sm[1];
     $params = $m[2];
     return isset($this->_shortcuts[$name])?$this->_shortcuts[$name]:'';
    }
    elseif (preg_match('~^(/?)(\w+)$~',$tag,$tm) && ($p = $this->parent->fetch_plugin('compiler.'.($tag = $tm[2]))))
    {
     require_once $p;
     $params = $m[2];
     $close = $tm[1] !== '';
     $a = 'quicky_compiler_'.$tag;
     if ($close) {$return = $a($params,$this,TRUE);}
     else {$return = $a($params,$this);}
     return $return;
    }
    elseif (preg_match('~^\w+$~',$tag) && (isset($this->parent->reg_func[strtolower($tag)])))
    {
     $params = $this->_parse_params($m[2]);
     $key_params = array();
     foreach ($params as $k => $v) {$key_params[] = var_export($k,TRUE).' => '.$v;}
     $t = $this->parent->reg_func[strtolower($tag)];
     $pstr = 'array('.implode(',',$key_params).'),'.($this->_cplmode?'$this->parent':'$this').',TRUE';
     if (is_array($t) || is_object($t)) {$t = 'call_user_func('.($this->_cplmode?'$this->parent->reg_func['.var_export($tag,TRUE).']':'$this->reg_func['.var_export($tag,TRUE).']').','.$pstr.')';}
     else {$t = $t.'('.$pstr.')';}
     return '<?php '.($this->_write_out_to !== ''?$this->_write_out_to.' .=':'echo').' '.$t.';'."\n".'?>';
    }
    elseif (preg_match('~^\w+$~',$tag) && (($c = in_array($tag,$this->template_defined_functions)) || ($p = $this->parent->fetch_plugin('function.'.$tag))))
    {
     $params = $this->_parse_params($m[2]);
     $key_params = array();
     foreach ($params as $k => $v) {$key_params[] = var_export($k,TRUE).' => '.$v;}
     if (!in_array($p,$this->load_plugins) and !$c) {$this->load_plugins[] = $p;}
     return '<?php '.(($this->_cplmode and !$c)?'require_once '.var_export($p,TRUE).'; ':'').($this->_write_out_to !== ''?$this->_write_out_to.' .=':'echo').' quicky_function_'.$tag.'(array('.implode(',',$key_params).'),'.($this->_cplmode?'$this->parent':'$this').',TRUE);'."\n".'?>';
    }
    elseif (preg_match('~^'.preg_quote($this->left_delimiter,'~').'\\*.*\\*'.preg_quote($this->right_delimiter,'~').'$~s',$mixed[0])) {return '';}
    else
    {
     if ($this->_alt_tag and preg_match('~^\w+$~',trim($m[0])))
     {
      $m[0] = '$'.trim($m[0]);
     }
     $outoff = FALSE;
     $plain = FALSE;
     if (substr($m[0],0,1) == '_')
     {
      $m[0] = substr($m[0],1);
      if (substr($m[0],0,1) == '_') {$m[0] = substr($m[0],1);}
      if (substr($m[0],0,1) == '?') {$m[0] = substr($m[0],1); $outoff = TRUE;}
      $e = $this->_fetch_expr($this->_expr_token($m[0],FALSE,TRUE,TRUE));
      $plain = TRUE;
     }
     else
     {
      if (substr($m[0],0,1) == '?')
      {
       $e = $this->_expr_token(substr($m[0],1),FALSE,TRUE);
       $outoff = TRUE;
      }
      else {$e = $this->_expr_token($m[0],FALSE,TRUE);}
     }
     if ($plain) {return $e;}
     if ($e === '' or $e === '\'\'') {return '';}
     $auto_escape = isset($this->prefs['auto_escape']) && $this->prefs['auto_escape'] && (!$this->_no_auto_escape) && (!$outoff);
     $line = $this->parent->debug_mode?'$line = '.$this->_line[$this->template_from].'; ':'';
     return '<?php '.$line.($outoff?'':($this->_write_out_to !== ''?$this->_write_out_to.' .=':'echo').' ').($auto_escape?'htmlspecialchars(':'').$e.($auto_escape?')':'').'; ?>';
    }
    return;
   }
   ++$this->_tag_stack_n;
   $block_name = strtolower($mixed[1]);
   $block_content = $mixed[3];
   $p = FALSE;
   if (preg_match('~^[\w+\-_]+$~',$block_name) && ((function_exists('quicky_block_'.$block_name) || ($p = $this->parent->fetch_plugin('block.'.$block_name)))))
   {
    $block_params = $mixed[2];
    if ($p) {require_once $p;}
    $return = call_user_func('quicky_block_'.$block_name,$block_params,$block_content,$this);
   }
   else {$return = $this->_syntax_error('Unrecognized block-type \''.$block_name.'\'');}
   unset($this->_tag_stacks[$this->_tag_stack_n]);
   --$this->_tag_stack_n;
   return $return;
  }
  $blocks = array_values($this->blocks + $this->parent->_blocks);
  for ($i = 0,$s = sizeof($blocks); $i < $s; $i++) {$blocks[$i] = preg_quote($blocks[$i],'~');}
  $blocks[] = 'if';
  $blocks[] = 'foreach';
  $blocks[] = 'section';
  $blocks[] = 'for';
  $blocks[] = 'while';
  $blocks[] = 'switch';
  $blocks[] = 'literal';
  $blocks[] = 'capture';
  $blocks[] = 'php';
  $blocks[] = 'strip';
  $blocks[] = 'textformat';
  $blocks[] = 'dynamic';
  $blocks[] = 'select';
  $blocks[] = 'joincalculator';
  $blocks[] = 'function|helper';
  $blocks[] = 'form';
  $blocks[] = '_if|_foreach|_for';
  $blocks[] = 'shortcut|block';
  $blocks[] = 'optgroup';
  $blocks[] = 'blockelement';
  $blocks[] = 'extends';
  $ldelim = preg_quote($this->left_delimiter,'~');
  $rdelim = preg_quote($this->right_delimiter,'~');
  $return = $this->preg_replace_callback('~'
								.'\{\{?\s*(begin)(?:\s+(.*?))?\}\}?((?:(?R)|.)*?)\{\{?\s*(?:end(?:\s+\2)?)?\s*\}\}?'
								.'|\{\{'.($this->left_delimiter === '{{'?'\{':'').'(\\??(?:[^'.$rdelim.'\'"]*([\'"]).*?(?<!\\\\)\5)*.*?)'.($this->left_delimiter === '}}'?'\}':'').'\}\}'
								.'|'.$ldelim.'\s*('.implode('|',$blocks).')(\s(?:[^'.$rdelim.'\'"]*([\'"]).*?(?<!\\\\)\8)*.*?)?'.$rdelim.'((?:(?R)|.)*?)'.$ldelim.'/\s*\6?\s*'.$rdelim
								.'|'.$ldelim.'(\\??(?:[^'.$rdelim.'\'"]*([\'"]).*?(?<!\\\\)\11)*.*?)'.$rdelim
								.'|\r?\n'
								.'~si',array($this,'_tag_token'),$mixed);
  return $return;
 }
 public function _pcre_trace($regexp,$subject)
 {
  if ($this->pcre_trace)
  {
   $fp = fopen(QUICKY_DIR.'pcre-trace.php.txt','w');
   fwrite($fp,'<?php preg_replace('.var_export($regexp,TRUE).',\'\','.var_export($subject,TRUE)."); ?>");
   fclose($fp);
  }
 }
 public function preg_replace_callback($e,$c,$s)
 {
  $this->_pcre_trace($e,$s);
  return preg_replace_callback($e,$c,$s);
 }
 public function _var_token($token)
 {
  preg_match_all($a = '~([\'"]).*?(?<!\\\\)\1|\(((?:(?R)|.)*?)\)|->((?:_?[\$#]?\w*(?:\(((?:(?R)|.)*?)\)|(\\[((?:(?R)|(?:[^\\]\'"]*([\'"]).*?(?<!\\\\)\4)*.*?))*?\\]|\.[\$#]?\w+#?|(?!a)a->\w*(?:\(((?:(?R)|.)*?)\))?)?)?)+)~',$token,$properties,PREG_SET_ORDER);
  $token = preg_replace_callback($a,create_function('$m','if (!isset($m[3])) {return $m[0];} return \'\';'),$token);
  $obj_appendix = '';
  $type_c = FALSE;
  for ($i = 0,$s = sizeof($properties); $i < $s; $i++)
  {
   if (isset($properties[$i][3]))
   {
    $plain = FALSE;
    preg_match('~^((?:_?[\$#])?\w+#?)(.*)$~',$properties[$i][3],$q);
    if (preg_match('~^_?[\$#]~',$q[1]))
    {
     if (substr($q[1],0,1) == '_') {$plain = TRUE; $q[1] = substr($q[1],1);}
     $q[1] = $this->_var_token($q[1]);
     if ($plain) {$q[1] = $this->_fetch_expr($q[1],FALSE,FALSE,TRUE);}
    }
    $obj_appendix .= '->'.$q[1];
    if (substr($q[2],0,1) == '(') {$w = array();}
    else {preg_match_all('~(\\[((?:(?R)|(?:[^\\]\'"]*([\'"]).*?(?<!\\\\)\3)*.*?))*?\\]|\.[\$#]?\w+#?)~',$q[2],$w,PREG_SET_ORDER);}
    for ($j = 0,$n = sizeof($w); $j < $n; $j++)
    {
     if (substr($w[$j][1],0,1) == '.')
     {
      $expr = substr($w[$j][1],1);
      if (!isset($this->block_props[$expr]))
      {
       $expr = '"'.$expr.'"';
       $instring = TRUE;
      }
      else {$instring = FALSE;}
     }
     else {$expr = substr($w[$j][1],1,-1); $instring = FALSE;}
     $r = $this->_expr_token($expr,$instring);
     $obj_appendix .= '['.(preg_match('~^\w+$~',$r)?'\''.$r.'\'':$r).']';
    }
    if (isset($properties[$i][4]) && ($properties[$i][4] !== '' || !isset($properties[$i][5])))
    {
     $params = $this->_expr_token_parse_params($properties[$i][4]);
     $obj_appendix .= '('.implode(',',$params).')';
    }
   }
  }
  if (is_numeric($token)) {return $token;}
  if (substr($token,0,1) == '#' or substr($token,0,1) == '$' or (isset($this->block_props[$token]) and !$this->_no_magic))
  {
   $this->_no_magic = preg_match('~^\$(?:quicky|smarty)[\.\[]~i',$token);
   preg_match_all('~([\$#]?\w*#?)(\\[((?:(?R)|(?:[^\\]\'"]*([\'"]).*?(?<!\\\\)\4)*.*?))*?\\]|\.[\$#]?\w+#?|->\w*(?:\(((?:(?R)|.)*?)\))?)~',$token,$w,PREG_SET_ORDER);
   $appendix_set = array();
   for ($i = 0,$s = sizeof($w); $i < $s; $i++)
   {
    if ($w[$i][1] !== '') {$token = $w[$i][1];}
    if (substr($w[$i][2],0,1) == '.')
    {
     $expr = substr($w[$i][2],1);
     if (!isset($this->block_props[$expr]))
     {
      $expr = '"'.$expr.'"';
      $instring = TRUE;
     }
     else {$instring = FALSE;}
    }
    else {$expr = substr($w[$i][2],1,-1); $instring = FALSE;}
    $r = $this->_expr_token($expr,$instring);
    $appendix_set[] = preg_match('~^\w+$~',$r)?'\''.$r.'\'':$r;
   }
   $this->_no_magic = FALSE;
  }
  static $operators = array('or','xor','and','true','false','null');
  $mode = 0;
  $mode_special_var = FALSE;
  if (substr($token,0,1) == '\'' or substr($token,0,1) == '"')
  {
   if (substr($token,-1) != $token[0]) {return $this->_syntax_error('Bad string definition.');}
   if ($token[0] == '"') {return $this->_expr_token($token,TRUE);}
   return var_export($this->_dequote($token),TRUE);
  }
  elseif ($token == '$tplpath') {return '$path';}
  elseif ($token == '$tplpathdir') {return '$dir';}
  elseif ($token == '$rdelim') {return var_export($this->right_delimiter,TRUE);}
  elseif ($token == '$ldelim') {return var_export($this->left_delimiter,TRUE);}
  elseif ($token == '$SCRIPT_NAME') {return '$_SERVER[\'SCRIPT_NAME\']';}
  elseif ($token[0] == '$')
  {
   $token = substr($token,1);
   if (array_key_exists($token,$this->precompiled_vars)) {return var_export($this->precompiled_vars[$token],TRUE);}
   if (isset($this->_def_mode)) {$this->_var_map[$token] = $this->_def_mode;}
   $type = $this->_resolve_var($token);
   if (strtolower($token) == 'quicky' || strtolower($token) == 'smarty')
   {
    $t = isset($appendix_set[0])?strtolower($this->_dequote($appendix_set[0])):'';
    $appendix_set = array_slice($appendix_set,1);
    $type = '';
    if ($t == 'rdelim') {return var_export($this->right_delimiter,TRUE);}
    elseif ($t == 'ldelim') {return var_export($this->left_delimiter,TRUE);}

    elseif ($t == 'request') {$type = '_REQUEST'; $mode = 1;}
    elseif ($t == 'tplscope') {$type = 'var'; $mode = 1;}
    elseif ($t == 'cfgscope') {$type = 'config'; $mode = 1;}
    elseif ($t == 'get') {$type = '_GET'; $mode = 1;}
    elseif ($t == 'post') {$type = '_POST'; $mode = 1;}
    elseif ($t == 'cookie' or $t == 'cookies') {$type = '_COOKIE'; $mode = 1;}

    elseif ($t == 'requeststring') {$type = '_REQUEST'; $mode = 2;}
    elseif ($t == 'getstring') {$type = '_GET'; $mode = 2;}
    elseif ($t == 'poststring') {$type = '_POST'; $mode = 2;}
    elseif ($t == 'cookiestring' or $t == 'cookiesstring') {$type = '_COOKIE'; $mode = 2;}

    elseif ($t == 'session') {$type = '_SESSION';}
    elseif ($t == 'session_name') {return 'session_name()';}
    elseif ($t == 'session_id') {return 'session_id()';}
    elseif ($t == 'server') {$type = '_SERVER';}
    elseif ($t == 'env') {$type = '_ENV';}
    elseif ($t == 'capture') {$type = 'capture';}
    elseif ($t == 'now') {return 'time()';}
    elseif ($t == 'const') {return 'constant('.(isset($appendix_set[0])?$appendix_set[0]:'').')';}
    elseif ($t == 'compiler_prefs') {return $this->parent->_fetch_expr('$this->compiler_prefs['.(isset($appendix_set[0])?$appendix_set[0]:'\'\'').']');}
    elseif ($t == 'form') {$type = 'Quicky_form::$forms'; $mode = 1; $type_c = TRUE;}
    elseif ($t == 'template') {return '$path';}
    elseif ($t == 'version') {return '$this->version';}
    elseif ($t == 'foreach' or $t == 'section')
    {
     $name = isset($appendix_set[0])?strtolower($this->_dequote($appendix_set[0])):'';
     $prop = isset($appendix_set[1])?strtolower($this->_dequote($appendix_set[1])):'';
     return $this->_get_expr_blockprop($name,$t,$prop);
    }
    else {return $this->_syntax_error('Unknown property \''.$t.'\' of $quicky');}
    $token = '';
   }
  }
  elseif (substr($token,0,1) == '#')
  {
   if (substr($token,-1) != '#') {return var_export($token,TRUE);}
   $type = 'config'; $token = substr($token,1,-1);
  }
  elseif ($token == 'tplpath') {return var_export($this->template_from,TRUE);}
  elseif ($token == 'tplpathdir') {$a = dirname($this->template_from); return var_export($a !== ''?$a:'.',TRUE);}
  elseif ($token == 'rdelim') {return var_export($this->right_delimiter,TRUE);}
  elseif ($token == 'ldelim') {return var_export($this->left_delimiter,TRUE);}
  elseif (isset($this->block_props[$token]) and !$this->_no_magic)
  {
   $return = $this->_get_expr_blockprop($this->block_props[$token][0],$this->block_props[$token][1],$token);
   $mode_special_var = TRUE;
  }
  elseif (isset($this->block_props[$token])) {return $token;}
  elseif (in_array($token,$this->allowed_php_constants) || in_array(strtolower($token),$operators) || (defined($token) && preg_match('~^M_\w+$~',$token))) {return $token;}
  elseif (preg_match('~^\w+$~',$token))
  {
   if (isset($this->prefs['cast_undefined_token_to_strings']) && $this->prefs['cast_undefined_token_to_strings']) {return var_export($token,TRUE);}
   return $this->_syntax_error('Unexpected constant "'.$token.'"');
  }
  else {return $this->_syntax_error('Unrecognized token \''.$token.'\'');}
  $appendix = '';
  for ($i = 0,$s = sizeof($appendix_set); $i < $s; $i++) {$appendix .= '['.$appendix_set[$i].']';}
  if ($mode_special_var) {return $return.$appendix.$obj_appendix;}
  $return = ((!$type_c)?'$':'').$type.($token !== ''?'['.var_export($token,TRUE).']':'').$appendix.$obj_appendix;
  if ($mode == 2) {$return = 'gpcvar_strnull('.$return.')';}
  return $return;
 }
 public function _expr_token_parse_params($expr)
 { // This function without regular expressions just for fun
  $params = array();
  $cpos = 0;
  $instring = FALSE;
  $instring_delim = '';
  $bnl = 0;
  $size = strlen($expr);
  $param = '';
  while ($cpos <= $size)
  {
   if ($cpos == $size) {$params[] = $this->_expr_token($param); break;}
   $char = $expr[$cpos];
   if (!$instring)
   {
    if ($char == '"' or $char == '\'') {$instring = TRUE; $instring_delim = $char;}
    elseif ($char == '(') {$bnl++;}
    elseif ($char == ')') {$bnl--;}
   }
   else
   {
    if ($char == $instring_delim and $expr[$cpos-1] != '\\') {$instring = FALSE;}
   }
   if (!$instring and $bnl == 0 and $char == ',') {$params[] = $this->_expr_token($param); $param = '';}
   else {$param .= $char;}
   $cpos++;
  }
  return $params;
 }
 public function _expr_token_callback($m)
 {
  if (isset($m[13]) and $m[13] !== '')
  {
   preg_match('~^(\s*)(.*)(\s*)$~',$m[13],$q);
   $lspace = $q[1];
   $operator = $q[2];
   $rspace = $q[3];
   $operator = trim(preg_replace('~\s+~',' ',strtolower($operator)));
   if ($operator == 'eq' or $operator == 'is') {$code = '==';}
   elseif ($operator == 'ne' || $operator == 'neq') {$code = '!=';}
   elseif ($operator == 'gt') {$code = '>';}
   elseif ($operator == 'lt') {$code = '<';}
   elseif ($operator == 'ge' || $operator == 'gte') {$code = '>=';}
   elseif ($operator == 'le' || $operator == 'lte') {$code = '<=';}
   elseif ($operator == 'not') {$code = '!'; $rspace = '';}
   elseif ($operator == 'mod') {$code = '%';}
   elseif ($operator == 'not eq' or $operator == 'is not') {$code = '!=';}
   else {return $this->_syntax_error('Unknown operator '.var_export($operator,TRUE));}
   return $code;
  }
  elseif (isset($m[3]) and $m[3] !== '' || preg_match('~^(\w+)\s*\(~',$m[1]))
  {
   if (preg_match('~^(\w+)\s*\(~',$m[1],$q)) {$func = $q[1];}
   else {$func = '';}
   $expr = $m[3];
   if (trim($func.$expr) == '') {return;}
   if ($func != '')
   {
    $tag = $a = strtolower($func);    
    if (preg_match('~^\w+$~',$tag) && (isset($this->parent->reg_func[$tag])))
    {
     $params = $this->_expr_token_parse_params($expr);
     $t = $this->parent->reg_func[$tag];
     $pstr = implode(',',$params);
     if (is_array($t) || is_object($t)) {$t = 'call_user_func('.($this->_cplmode?'$this->parent->reg_func['.var_export($tag,TRUE).']':'$this->reg_func['.var_export($tag,TRUE).']').($pstr !== ''?',':'').$pstr.')';}
     else {$t = $t.'('.$pstr.')';}
     return $t;
    }
    $b = $p = $c = FALSE;
    foreach ($this->allowed_php_tokens as $i)
    {
     if (preg_match($e = '~^'.str_replace('\*','.*',preg_quote($i,'~')).'$~i',$a)) {$b = TRUE; break;}
    }
    if (!$b) {$c = in_array($a,$this->template_defined_functions) || function_exists('quicky_function_'.$a);}
    if (!$b and !$c)
    {
     $y = $this->_alt_tag && !in_array($a,get_class_methods('Quicky'));
    }
    if (preg_match('~^\w+$~',$a) && ($b || $c || $y || ($p = $this->parent->fetch_plugin('function.'.$a))))
    {
     $params = $this->_expr_token_parse_params($expr);
     if ($p || $c)
     {
      if ($p !== FALSE and !in_array($p,$this->load_plugins)) {$this->load_plugins[] = $p;}
      $return = '';
      if ($p && (!$c)) {$return .= '((require_once('.var_export($p,TRUE).'))?';}
      $return .= 'quicky_function_'.$a.'(array('.implode(',',$params).'),'.($this->_cplmode?'$this->parent':'$this').',TRUE)';
      if ($p && (!$c)) {$return .= ':NULL)';}
     }
     elseif ($b)
     {
      if ($a == 'count') {$a = 'sizeof';}
      $return = $a.'('.implode(',',$params).')';
     }
     elseif ($y)
     {
      $tk = FALSE;
      $ta = array('begin','function');
      for ($i = sizeof($this->_tag_stacks)-1; $i >= 0; $i--)
      {
       if (isset($this->_tag_stacks[$i]['type']) && in_array($this->_tag_stacks[$i]['type'],$ta)) {$tk = TRUE; break;}
      }
      if ($tk) {$prefix = 'Quicky::$obj->';}
      else {$prefix = '$this->';}
      return $prefix.$a.'('.implode(',',$params).')';
     }
     else {return $this->_syntax_error('Function \''.$func.'\' not available');}
    }
    else {return $this->_syntax_error('Function \''.$func.'\' not available');}
   }
   else {$return = '('.$this->_expr_token($expr).')';}
  }
  elseif (isset($m[1]) and $m[1] !== '')
  {
   $return = $this->_var_token($m[1]);
  }
  else {$return = '';}
  if (isset($m[7]) and $m[7] !== '')
  {
   preg_match('~^(\s*)(.*)(\s*)$~',$m[7],$q);
   $lspace = $q[1];
   $operator = $q[2];
   $rspace = $q[3];
   $operator = trim(preg_replace('~\s+~',' ',strtolower($operator)));
   if ($operator == 'is not odd') {$return = '(('.$return.') % 2 != 0)';}
   if ($operator == 'is not even') {$return = '(('.$return.') % 2 == 0)';}
   elseif ($operator == 'is odd') {$return = '(('.$return.') % 2 == 0)';}
   elseif ($operator == 'is even') {$return = '(('.$return.') % 2 != 0)';}
   elseif (preg_match('~^instanceof (.*)$~',$operator,$e))
   {
    $cn = preg_match('~^\w+$~',$e[1])?$e[1]:$this->_expr_token($e[1]);
    $return = '('.$return.' instanceof '.$cn.')';
   }
   elseif (preg_match('~^is( not)? odd by (.*)$~',$operator,$e))
   {
    $return = '(('.$return.' / '.$this->_expr_token($e[2]).') % 2 '.($e[1] != ''?'!':'=').'= 0)';
   }
   elseif (preg_match('~^is( not)? even by (.*)$~',$operator,$e))
   {
    $return = '(('.$return.' / '.$this->_expr_token($e[2]).') % 2 '.($e[1] == ''?'!':'=').'= 0)';
   }
   elseif (preg_match('~^is( not)? div by (.*)$~',$operator,$e))
   {
    $return = '(('.$return.' % '.$this->_expr_token($e[2]).') '.($e[1] != ''?'!':'=').'= 0)';
   }
   else {return $this->_syntax_error('Unexpected operator \''.$operator.'\'');}
  }
  if (isset($m[8]) and $m[8] !== '')
  {
   $mods_token = $m[8];
   preg_match_all('~\|@?\s*\w+(?:\:(?:[^\:\|\'"]*(?:([\'"]).*?(?<!\\\\)\1[^\:\|\'"]*)*))*~',$mods_token,$mods_m,PREG_SET_ORDER);
   $mods = array();
   for ($i = 0,$s = sizeof($mods_m); $i < $s; $i++)
   {
    preg_match('~\|(@?\w+)(.*)~',$mods_m[$i][0],$q);
    $mod_name = $q[1];
    $params_token = $q[2];
    preg_match_all('~\:([^\:\|\'"]*(?:([\'"]).*?(?<!\\\\)\2[^\:\|\'"]*)*)~',$params_token,$p,PREG_SET_ORDER);
    $params = array();
    $mod = array($mod_name,array());
    for ($j = 0,$ps = sizeof($p); $j < $ps; $j++) {$mod[1][] = $this->_expr_token($p[$j][1]);}
    $mods[] = $mod;
   }
   $internal_mods = array('html');
   for ($i = 0,$s = sizeof($mods); $i < $s; $i++)
   {
    if (substr($mods[$i][0],0,1) == '@') {$no_errors = TRUE; $mods[$i][0] = substr($mods[$i][0],1);}
    else {$no_errors = FALSE;}
    $mod_name = strtolower($mods[$i][0]);
    $mod_params = $mods[$i][1];
    if ($mod_name == 'upper' or $mod_name == 'lower') {$mod_name = 'strto'.$mod_name;}
    if (($mod_name == 'default') && (substr($return,0,1) == '$')) {$mod_name = 'default_var';}
    $short = FALSE;
    foreach ($this->allowed_php_tokens as $av)
    {
     if (preg_match($e = '~^'.str_replace('\*','.*',preg_quote($av,'~')).'$~i',$mod_name)) {$short = TRUE; break;}
    }
    if ($short || in_array($mod_name,$internal_mods)) {}
    elseif (!preg_match('~^\w+$~',$mod_name) || !($p = $this->parent->fetch_plugin('modifier.'.$mod_name)))
    {
     return $this->_syntax_error('Undefined modifier \''.$mod_name.'\'');
    }
     if ($mod_name == 'escape' or $mod_name == 'html') {$this->_no_auto_escape = TRUE;}
		if ($mod_name == 'escape' && sizeof($mod_params) == 0) {$return  = 'htmlspecialchars('.$return.')'; continue;}
		elseif ($mod_name == 'html') {continue;}
		elseif ($mod_name == 'escape' && sizeof($mod_params) > 0 && $mod_params[0] == '\'urlencode\'') {$return  = 'urlencode('.$return.')'; continue;}
		elseif ($mod_name == 'escape' && sizeof($mod_params) > 0 && $mod_params[0] == '\'urldecode\'') {$return  = 'urldecode('.$return.')'; continue;}
    elseif ($mod_name == 'count' || $mod_name == 'sizeof') {$return  = 'sizeof('.$return.')'; continue;}
    elseif ($mod_name == 'urlencode') {$return  = 'urlencode('.$return.')'; continue;}
    elseif ($mod_name == 'cat' && isset($mod_params[0])) {$return  = $return.'.'.$mod_params[0]; continue;}
    if (!$short and !in_array($p,$this->load_plugins)) {$this->load_plugins[] = $p;}
    $return = ($no_errors?'@':'').(!$short?'quicky_modifier_':'').$mod_name.'('.$return.(sizeof($mod_params)?','.implode(',',$mod_params):'').')';
   }
  }
  return $return;
 }
 function _var_string_callback($m)
 {
  if (isset($m[6])) {return stripslashes($m[6]);}
  if ($m[0] == '\\"') {return '"';}
  $prefix = '';
  if (strlen($m[1]) != 0)
  {
   if (strlen($m[1]) % 2 != 0) {return stripslashes($m[1]).$m[2];}
   else {$prefix = var_export(stripslashes($m[1]),TRUE).'.';}
  }
  $expr = $m[2];
  if ((substr($expr,0,1) == $this->left_delimiter and substr($expr,-1) == $this->right_delimiter) ||
	 (substr($expr,0,1) == '`' and substr($expr,-1) == '`'))
  {
   $expr = substr($expr,1,-1);
   $return = $this->_expr_token(stripslashes($expr));
  }
  elseif (substr($expr,0,1) == '_') {$return = $this->_expr_token(stripslashes($expr));}
  else {$return = $prefix.$this->_var_token($m[2]);}
  return '\'.'.$return.'.\'';
 }
 function _expr_token($token,$instring = FALSE,$emptynull = FALSE,$cplmode = FALSE)
 {
  while ((substr($token,0,1) == '`') && (substr($token,-1) == '`')) {$token = substr($token,1,-1);}
  if ($cplmode) {$_cplmode_old = $this->_cplmode; $this->_cplmode = TRUE;}
  if ($token === '') {return '';}
  if (substr($token,0,1) == '_')
  {
   if (substr($token,1,1) == '_') {$token = substr($token,1);}
   $token = substr($token,1);
   $return = var_export($this->_fetch_expr($this->_expr_token($token,FALSE,TRUE,TRUE)),TRUE);
   return $return;
  }
  $in = $token;
  $token = ltrim($token);
  if ($instring)
  {
   $a = $token;
   if ($a[0] == '"')
   {
    $a = '\''.strtr(substr($a,1,-1),array('\'' => '\\\'', '\\' => '\\\\')).'\'';
    $ldelim = preg_quote($this->left_delimiter,'~');
    $rdelim = preg_quote($this->right_delimiter,'~');
    $o = isset($this->prefs['cast_undefined_token_to_strings'])?$this->prefs['cast_undefined_token_to_strings']:FALSE;
    $this->prefs['cast_undefined_token_to_strings'] = TRUE;
    $a = preg_replace_callback('~(\\\*)('.$ldelim.'.*?'.$rdelim.'|`.*?`|_?[\$#]\w+#?(?:\[[\$#]?\w+#?\])*)|((?<!\\\\)\\\\")~',array($this,'_var_string_callback'),$a);
    $this->prefs['cast_undefined_token_to_strings'] = $o;
    $a = preg_replace('~\.\'(?<!\\\\)\'|(?<!\\\\)\'\'\.|^\'\.(?=[\$\(])|(?<=[\)\'])\.\'$|\'\.\'~','',$a);
   }
   return $a;
  }
  $return = preg_replace_callback(
    '~(([\'"]).*?(?<!\\\\)\2|\w*\s*\(((?:(?R)|.)*?)\)'
    .'|(?!(?:is\s+not|is|not\s+eq|eq|neq?|gt|lt|gt?e|ge|lt?e|mod)\W)_?[\$#]?\w+#?(?:\\[(?:(?R)|\w+|((?:[^\\]\'"]*(?:([\'"]).*?(?<!\\\\)\5)?)*))*?\\]|\.[\$#]?\w+#?|->\s*_?[\$#]?\w+#?(?:\(((?:(?R)|.)*?)\))?)*'
    .'|-?\d+|(?<=^|[\s\)\:\.=+\-<>])(?!(?:is\s+not|is|not\s+eq|eq|neq?|gt|lt|gt?e|ge|lt?e|mod)\W)(?:\w+)(?=$|[\s\|\.\:\(=+\-<>]))(\s+(?:instanceof (?:\w+|(?R))|is(?:\s+not)?\s+(?:odd|div|even)\s+by\s+(?:-?\d+|(?R))|is(?:\s+not)?\s+(?:odd|even)))?((?:\|@?\w+(?:\\:(?:'.'\w*\(((?:(?R)|.)*?)\)|[\$#]\w+#?(?:\\[(?:(?R)|((?:[^\\]\'"]*(?:([\'"]).*?(?<!\\\\)\11)?)*))*?\\]|\.[\$#]?\w+#?)*|[^\'"\:]*(?:[^\'"\:]*([\'"]).*?(?<!\\\\)\12[^\'"\:]*)*'.'))*)*)'
    .'|((?<=\s|\))(?:is\s+not|is|not\s+eq|eq|neq?|gt|lt|gt?e|ge|lt?e|mod)(?=\s|\()|(?:not\s+))'
    .'~si',array($this,'_expr_token_callback'),$token);
  if ($emptynull and trim($return) === '') {return 'NULL';}
  if ($cplmode) {$this->_cplmode = $_cplmode_old;}
  return $return;
 }
}
if (!function_exists('ctype_digit')) {function ctype_digit($s) {return (bool) preg_match('~^\d+$~',$s);}}