<?php
/**************************************************************************/
/* Quicky: smart and fast templates
/* ver. 0.5.0.0
/* ===========================
/*												
/* Quicky.class.php: API class
/**************************************************************************/
ini_set('zend.ze1_compatibility_mode', 'Off');
define('QUICKY_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
if (!defined('UNIQUE_HASH')) {
	define('UNIQUE_HASH', abs(crc32(microtime(true) . microtime(true))));
}
if (!defined('UNIQUE_HASH_STATIC')) {
	define('UNIQUE_HASH_STATIC', '80323d4d5b350a87f174a3de4502483d');
}
require QUICKY_DIR . 'functions.php';

/**
 * Class Quicky
 */
class Quicky {
	public $template_dir = './templates/';
	public $compile_dir = './templates_c/';
	public $config_dir = './configs/';
	public $cache_dir = './templates_cache/';
	public $plugins_dir = array();
	public $_local_vars = array();
	public $_tpl_vars_buff = array();
	public $_tpl_vars = array();
	public $_tpl_config = array();
	public $_block_props = array();
	public $auto_filename_prefix = '';
	public $compilers = array();
	public $prefilters = array();
	public $postfilters = array();
	public $outputfilters = array();
	public $compile_check = true;
	public $force_compile = false;
	public $max_recursion_depth = 128;
	public $_auto_detect_forms = false;
	public $_detect_forms = array();
	public $compiler_prefs = array(
		'inline_includes'                 => false,
		'allow_php_native'                => false,
		'interpret_varname_params'        => false,
		'cast_undefined_token_to_strings' => false,
		'export_vars'                     => true,
		'auto_escape'                     => false,
	);
	public $error_reporting;
	public $version = '0.5.0.0';
	public $caching = 0;
	public $cache_lifetime = 60;
	public $precompiled_vars = array();
	public $lang = '';
	public $use_sub_dirs = false;
	public $cache_id = '';
	public $compile_id = '';
	static $obj;
	public $context_path = '/';
	public $_contexts_data = array();
	public $_blocks = array();
	public $default_compiler = 'Quicky';
	public $debug_mode = false; // DON'T TURN ON IN PRODUCTION ! LOW PERFORMANCE.
	public $debug_trace = array(
		'assign' => array(),
		'fetch'  => array(),
	);
	public $depart_scopes = false;
	public $local_depart_scopes = false;

	public function __construct() {
		$this->init();
	}

	public function init() {
		$this->error_reporting         = E_ALL;
		$this->plugins_dir             = array(QUICKY_DIR . 'plugins');
		$this->_smarty_vars            = & $this->_block_props;
		$this->_block_props['capture'] = array();
		$this->_block_props['foreach'] = array();
		$this->_block_props['section'] = array();
		$this->_block_props['begin']   = array();
		$this->capture                 = & $this->_block_props['capture'];
		$this->foreach                 = & $this->_block_props['foreach'];
		$this->section                 = & $this->_block_props['section'];
		$this->begin                   = & $this->_block_props['begin'];
		Quicky::$obj                   = $this;
	}

    /**
     * Apeend an array variable
     * @param $tpl_var
     * @param null $value
     * @param bool $merge
     */
	public function append($tpl_var, $value = null, $merge = false) {
		if (is_array($tpl_var)) {
			foreach ($tpl_var as $_key => $_val) {
				if ($_key != '') {
					if (!is_array($this->_tpl_vars[$_key])) {
						settype($this->_tpl_vars[$_key], 'array');
					}
					if ($merge && is_array($_val)) {
						foreach ($_val as $_mkey => $_mval) {
							$this->_tpl_vars[$_key][$_mkey] = $_mval;
						}
					}
					else {
						$this->_tpl_vars[$_key][] = $_val;
					}
				}
			}
			return;
		}
		if ($tpl_var != '' && isset($value)) {
			if (!is_array($this->_tpl_vars[$tpl_var])) {
				settype($this->_tpl_vars[$tpl_var], 'array');
			}
			if ($merge && is_array($value)) {
				foreach ($value as $_mkey => $_mval) {
					$this->_tpl_vars[$tpl_var][$_mkey] = $_mval;
				}
			}
			else {
				$this->_tpl_vars[$tpl_var][] = $value;
			}
		}
	}


    /**
     * Append an array variable with value by reference
     * @param $tpl_var
     * @param $value
     * @param bool $merge
     */
	public function append_by_ref($tpl_var, &$value, $merge = false) {
		if ($tpl_var != '' && isset($value)) {
			if (!is_array($this->_tpl_vars[$tpl_var])) {
				settype($this->_tpl_vars[$tpl_var], 'array');
			}
			if ($merge && is_array($value)) {
				foreach ($value as $_key => $_val) {
					$this->_tpl_vars[$tpl_var][$_key] = & $value[$_key];
				}
			}
			else {
				$this->_tpl_vars[$tpl_var][] = & $value;
			}
		}
	}

    /**
     * Register a custom function to use in templates.
     * @param $name
     * @param $func
     */
	public function register_function($name, $func) {
		$this->reg_func[$name] = $func;
	}


    /**
     * Register a block
     * @param $block
     * @return bool
     */
	public function register_block($block) {
		if (!in_array($block, $this->_blocks)) {
			$this->_blocks[] = $block;
		}
		return true;
	}

    /**
     * Unregister a block
     * @param $block
     * @return bool
     */
	public function unregister_block($block) {
		if ($k = array_search($block, $this->_blocks)) {
			unset($this->_blocks[$k]);
			return true;
		}
		else {
			return false;
		}
	}

    /**
     * Detect a form
     * @param $name
     */
	public function detect_form($name) {
		$this->_detect_forms[] = $name;
	}

    /**
     * Get a Quicky form by its name
     * @param $name
     * @return bool
     */
	public function getFormByName($name) {
		if (!class_exists('Quicky_form')) {
			require_once QUICKY_DIR . 'Quicky.form.class.php';
		}
		return isset(Quicky_form::$forms[$name]) ? Quicky_form::$forms[$name] : false;
	}

    /**
     * Fetch a context
     * @param $name
     * @return mixed
     */
	public function context_fetch($name) {
		$path = $this->context_path($name, false);
		if (!function_exists($func = 'quicky_context_' . $name)) {
			return $this->warning('Context \'' . $path . '\' does not exists');
		}
		return $func();
	}

    /**
     * Set a context
     * @param array $value
     */
	public function context_set($value = array()) {
		$this->_contexts_data[$this->context_path] = $value;
	}

    /**
     * @param string $name
     */
	public function context_iterate($name = '') {
		if ($name === '') {
			$name = $this->context_path;
		}
		$this->_contexts_data[$this->context_path($name, false)] = [[]];
	}

    /**
     * Load a custom template from string
     * @param $name
     * @param $string
     */
	public function load_string($name, $string) {
		require_once QUICKY_DIR . 'plugins/addons/stringtemplate.class.php';
		Quicky_Stringtemplate::$strings[$name] = $string;
	}

    /**
     * @param $path
     * @param bool $onlyget
     * @return string
     */
	public function context_path($path, $onlyget = false) {
		if ($path === '') {
			return $this->context_path;
		}
		if (substr($path, 0, 1) != '/') {
			$path = $this->context_path . $path . '/';
		}
		if (strpos($path, '../') !== false) {
			$e = explode('/', $path);
			for ($i = 0, $s = count($e); $i < $s; ++$i) {
				if ($e[$i] == '..') {
					unset($e[$i - 1]);
					unset($e[$i]);
					$e = array_values($e);
					$i -= 2;
					$s -= 2;
				}
				elseif ($e[$i] == '.') {
					unset($e[$i]);
				}
			}
			$path = implode('/', $e);
		}
		if (!$onlyget) {
			return $this->context_path = $path;
		}
		else {
			return $path;
		}
	}

    /**
     * Unlinks a file if it's expired
     * @param $resource
     * @param int|null $exp_time
     * @return bool
     */
	public function _unlink($resource, $exp_time = null) {
		if (isset($exp_time)) {
			if (time() - @filemtime($resource) >= $exp_time) {
				return @unlink($resource);
			}
		}
		else {
			return @unlink($resource);
		}
	}

    /**
     * @param $name
     * @return bool|string
     */
	public function fetch_plugin($name) {
		$a = (array) $this->plugins_dir;
		for ($i = 0, $s = count($a); $i < $s; $i++) {
			$path = rtrim($a[$i], '/\\') . DIRECTORY_SEPARATOR . $name . '.php';
			if (is_file($path) && is_readable($path)) {
				return $path;
			}
		}
		return false;
	}

    /**
     * @param $name
     * @param $func
     */
	public function register_prefilter($name, $func) {
		$this->prefilters[$name] = $func;
	}

    /**
     * @param $name
     */
	public function unregister_prefilter($name) {
		unset($this->prefilters[$name]);
	}

    /**
     * @param $name
     * @param $func
     */
	public function register_postfilter($name, $func) {
		$this->postfilters[$name] = $func;
	}

    /**
     * @param $name
     */
    public function unregister_postfilter($name) {
		unset($this->postfilters[$name]);
	}

    /**
     * @param $name
     * @param $value
     */
    public function register_outputfilter($name, $value) {
		$this->outputfilters[$name] = $value;
	}

    /**
     * @param $name
     */
    public function unregister_outputfilter($name) {
		unset($this->outputfilters[$name]);
	}

    /**
     * @param $file
     * @return bool
     */
    public function template_exists($file) {
		return file_exists($this->_get_template_path($file));
	}

    /**
     * @param $file
     * @param string $section
     * @return bool|void
     */
    public function config_load($file, $section = '') {
		$path = $this->config_dir . $file;
		if (!is_file($path) || !is_readable($path)) {
			return $this->warning('Can\'t open config-file \'' . $file . '\' ');
		}
		$ini = parse_ini_file($path, true);
		if (!$ini) {
			return $this->warning('Errorneus ini-file \'' . $file . '\'');
		}
		$section = (string)$section;
		if ($section !== '') {
			$ini = (isset($ini[$section]) and is_array($ini[$section])) ? $ini[$section] : array();
		}
		foreach ($ini as $k => $v) {
			if (is_array($v)) {
				$this->_tpl_config = array_merge($this->_tpl_config, $v);
			}
			else {
				$this->_tpl_config[$k] = $v;
			}
		}
		return;
	}

    /**
     * @param $type
     * @param $name
     * @return bool
     */
	public function load_filter($type, $name) {
		if (!in_array($type, ['output', 'pre', 'post'])) {
			return $this->warning('Unknown filter-type \'' . $type . '\'');
		}
		if (!$p = $this->fetch_plugin($type . 'filter.' . $name)) {
			return $this->warning('Can\'t load ' . $type . '-filter \'' . $name . '\'');
		}
		$a = $type . 'filters';
		if ($type == 'output') {
			$this->outputfilters[$name] = 'quicky_' . $type . 'filter_' . $name;
		}
		elseif ($type == 'pre') {
			$this->prefilters[$name] = 'quicky_' . $type . 'filter_' . $name;
		}
		elseif ($type == 'post') {
			$this->postfilters[$name] = 'quicky_' . $type . 'filter_' . $name;
		}
		include_once $p;
	}

	public function load_compiler($a) {
		if (!isset($this->compilers[$a])) {
			$path = QUICKY_DIR . $a . '_compiler.class.php';
			if (!is_file($path) || !is_readable($path)) {
				$this->warning('Can\'t load compiler \'' . $a . '\'.');
				return false;
			}
			require_once $path;
			$class_name                            = $a . '_compiler';
			$this->compilers[$a]                   = new $class_name;
			$this->compilers[$a]->parent           = $this;
			$this->compilers[$a]->prefilters       = & $this->prefilters;
			$this->compilers[$a]->postfilters      = & $this->postfilters;
			$this->compilers[$a]->prefs            = & $this->compiler_prefs;
			$this->compilers[$a]->precompiled_vars = & $this->precompiled_vars;
		}
		return true;
	}

    /**
     * @param $arg
     * @return mixed
     */
	public function _eval($arg) {
		$var     = & $this->_tpl_vars;
		$config  = & $this->_tpl_config;
		$capture = & $this->_block_props['capture'];
		$foreach = & $this->_block_props['foreach'];
		$section = & $this->_block_props['section'];
		$tpl     = $this;
		if (is_string($arg)) {
			return eval($arg);
		}
		return call_user_func($arg, $tpl);
	}

    /**
     * @param $name
     * @param null $object
     * @return bool
     */
	public function register_object($name, $object = null) {
		return $this->assign($name, $object);
	}

    /**
     * @param $name
     */
	public function unregister_object($name) {
		return $this->clear_assign($name);
	}

    /**
     * @param $name
     * @return mixed|null
     */
	public function get_register_object($name) {
		return isset($this->_tpl_vars[$name]) ? $this->_tpl_vars[$name] : null;
	}

    /**
     * @param null $name
     * @return array|mixed
     */
    public function get_template_vars($name = null) {
		return $name === null ? $this->_tpl_vars : $this->_tpl_vars[$name];
	}

    /**
     * @param null $name
     * @return array|mixed
     */
    public function get_templates_vars($name = null) {
		return $name === null ? $this->_tpl_vars : $this->_tpl_vars[$name];
	}

    /**
     * @param $name
     * @param null $value
     * @param null $scope
     * @return bool
     */
    public function assign($name, $value = null, $scope = null) {
		if ($this->debug_mode === true) {
			$dbg                           = debug_backtrace();
			$this->debug_trace['assign'][] = array(
				'name'  => $a,
				'value' => $b,
				'file'  => $dbg[0]['file'],
				'line'  => $dbg[0]['line']
			);
		}
		if ($scope !== null) {
			if (is_array($name)) {
				$this->_local_vars[$scope] = array_merge($this->_local_vars[$scope], $name);
			}
			else {
				$this->_local_vars[$scope][$name] = $value;
			}
		}
		else {
			if (is_array($name)) {
				$this->_tpl_vars = array_merge($this->_tpl_vars, $name);
			}
			else {
				$this->_tpl_vars[$name] = $value;
			}
		}
		return true;
	}

    /**
     * @param $name
     * @param null $value
     * @return bool
     */
	public function define($name, $value = null) {
		if (is_array($name)) {
			$this->_tpl_consts = array_merge($this->_tpl_consts, $name);
		}
		else {
			$this->_tpl_consts[$name] = $value;
		}
		return true;
	}

    public function assign_by_ref($name, &$b) {
		$this->_tpl_vars[$a] = & $b;
		return true;
	}

	function clear_assign($name) {
		if (is_array($name)) {
			$name = array_values($name);
			for ($i = 0, $s = count($name); $i < $s; $i++) {
				unset($this->_tpl_vars[$name[$i]]);
			}
		}
		else {
			unset($this->_tpl_vars[$name]);
		}
	}

    /**
     *
     */
	public function reset() {
		$this->_tpl_vars = [];
	}

    /**
     *
     */
	public function clear_all_assign() {
		$this->reset();
	}

    /**
     * @param $path
     * @param null $cache_id
     * @param null $compile_id
     * @param int $exp
     * @return bool
     */
    public function clear_cache($path, $cache_id = null, $compile_id = null, $exp = -1) {
		if ($compile_id === null) {
			$compile_id = $this->compile_id;
		}
		if ($cache_id === null) {
			$cache_id = $this->cache_id;
		}
		$p = $this->_get_cache_path($path, $cache_id, $compile_id);
		if ($cache_id == '*') {
			$h = opendir($this->cache_dir);
			if (!$h) {
				return $this->warning('Can\'t open cache-dir \'' . $this->cache_dir . '\'');
			}
			$e = explode('.', basename($p));
			$s = count($e);
			$e = $e[$s - 2] . '.' . $e[$s - 1];
			$s = strlen($e) * -1;
			while (($f = readdir($h)) !== false) {
				if (is_file($this->cache_dir . $f) && (substr($f, $s) == $e)) {
					unlink($this->cache_dir . $f);
				}
			}
			return true;
		}
		if (is_file($p) && ($exp == -1 || (filemtime($p) < time() - $exp))) {
			return unlink($p);
		}
		return false;
	}

    /**
     * @param int $exp
     * @return bool
     */
    public function clear_all_cache($exp = -1) {
		$h = opendir($this->cache_dir);
		if (!$h) {
			return $this->warning('Can\'t open cache-dir \'' . $this->cache_dir . '\'');
		}
		while (($f = readdir($h)) !== false) {
			if (is_file($this->cache_dir . $f) && ($exp == -1 || (filemtime($this->cache_dir . $f) < time() - $exp))) {
				unlink($this->cache_dir . $f);
			}
		}
	}

    /**
     * @param $path
     * @param null $compile_id
     * @param int $exp
     * @return bool
     */
	public function clear_compiled_tpl($path, $compile_id = null, $exp = -1) {
		if ($compile_id === null) {
			$compile_id = $this->compile_id;
		}
		if ($cache_id === null) {
			$cache_id = $this->cache_id;
		}
		$p = $this->_get_compile_path($path, $compile_id);
		if ($compile_id == '*') {
			$h = opendir($this->compile_dir);
			if (!$h) {
				return $this->warning('Can\'t open compile-dir \'' . $this->compile_dir . '\'');
			}
			$e = explode('.', $p);
			while (($f = readdir($h)) !== false) {
				if (is_file($this->compile_dir . $f) && strpos($f, '.' . $e[6] . '.') !== false) {
					unlink($this->compile_dir . $f);
				}
			}
			return true;
		}
		if (is_file($p) && ($exp == -1 || (filemtime($p) < time() - $exp))) {
			return unlink($p);
		}
		return false;
	}

    /**
     * @param int $exp
     * @return bool
     */
	function clear_all_compiled_tpl($exp = -1) {
		$h = opendir($this->compile_dir);
		if (!$h) {
			return $this->warning('Can\'t open compile-dir \'' . $this->cache_dir . '\'');
		}
		while (($f = readdir($h)) !== false) {
			if (is_file($this->compile_dir . $f) && ($exp == -1 || (filemtime($this->compile_dir . $f) < time() - $exp))) {
				unlink($this->compile_dir . $f);
			}
		}
	}

    /**
     * @param $err
     * @return bool
     */
	public function warning($err) {
		trigger_error($err, E_USER_WARNING);
		return false;
	}

    /**
     * @param $path
     * @return mixed|string
     */
    public function _get_template_path($path) {
		if ($path == '|debug.tpl') {
			return QUICKY_DIR . 'debug.tpl';
		}
		if (strpos($path, '://') !== false) {
			return $path;
		}
		static $cache = array();
		if (is_array($this->template_dir) && count($this->template_dir)) {
			if (isset($cache[$path])) {
				return $cache[$path];
			}
			foreach ($this->template_dir as &$v) {
				if (file_exists($v . $path)) {
					return $cache[$path] = $v . $path;
				}
			}
			return $cache[$path] = $this->template_dir[0] . $path;
		}
		return $this->template_dir . $path;
	}

    /**
     * @param $path
     * @param null $cache_id
     * @param null $compile_id
     * @return string
     */
    public function _get_auto_filename($path, $cache_id = null, $compile_id = null) {
		if ($compile_id === null) {
			$compile_id = $this->compile_id;
		}
		if ($cache_id === null) {
			$cache_id = $this->cache_id;
		}
		$path = $this->_get_template_path($path);
		$name = basename($path) . ($this->auto_filename_prefix !== '' ? '.' . $this->auto_filename_prefix : '') . ($this->lang !== '' ? '.' . $this->lang : '') . ($compile_id !== '' ? '.' . $compile_id : '') . ($cache_id !== '' ? '.' . $cache_id : '') . '.' . substr(abs(crc32($path)), 0, 6) . '.php';
		return $name;
	}

    /**
     * @param $path
     * @param null $cache_id
     * @param null $compile_id
     * @param string $compiler
     * @return bool|mixed|string
     */
    public function display($path, $cache_id = null, $compile_id = null, $compiler = 'Quicky') {
		return $this->fetch($path, $cache_id, $compile_id, true, $compiler);
	}

    /**
     * @param $path
     * @param null $cache_id
     * @param null $compile_id
     * @return bool|string
     */
    public function is_cached($path, $cache_id = null, $compile_id = null) {
		if ($compile_id === null) {
			$compile_id = $this->compile_id;
		}
		if ($cache_id === null) {
			$cache_id = $this->cache_id;
		}
		if (!$this->caching) {
			return false;
		}
		$p = $this->_get_cache_path($path, $cache_id, $compile_id);
		return (is_file($p) && (($this->cache_lifetime == -1) || (filemtime($p) > time() - $this->cache_lifetime))) ? $p : false;
	}

    /**
     * @param $path
     * @param $compile_id
     * @return mixed|string
     */
    public function _get_compile_path($path, $compile_id) {
		if ($compile_id === null) {
			$compile_id = $this->compile_id;
		}
		static $cache = array();
		if (isset($cache[$path])) {
			return $cache[$path];
		}
		return $cache[$path] = $this->compile_dir . $this->_get_auto_filename($path, '', $compile_id);
	}

    /**
     * @param $path
     * @param null $cache_id
     * @param null $compile_id
     * @return string
     */
    public function _get_cache_path($path, $cache_id = null, $compile_id = null) {
		if ($compile_id === null) {
			$compile_id = $this->compile_id;
		}
		if ($cache_id === null) {
			$cache_id = $this->cache_id;
		}
		return $this->cache_dir . $this->_get_auto_filename($path, $cache_id, $compile_id);
	}

    /**
     * @param $m
     * @return string
     */
    public function dynamic_callback($m) {
		if ($m[4] !== '?>') {
			$m[5] = '<?php ' . $m[5];
		}
		return ((isset($m[1]) && $m[1] !== '') ? $m[1] : '') . 'echo \'!' . UNIQUE_HASH . '!non_cache=' . base64_encode($m[5]) . '! \'; ' . ((isset($m[6]) && $m[6] !== '') ? $m[6] : '');
	}

    /**
     * @param $path
     * @param null $cache_id
     * @param null $compile_id
     * @param bool $display
     * @param string $compiler
     * @return bool|mixed|string
     */
    public function fetch($path, $cache_id = null, $compile_id = null, $display = false, $compiler = 'Quicky') {
		if ($path === '' or ($path === null)) {
			return $this->warning('Empty path given');
		}
		if ($compile_id === null) {
			$compile_id = $this->compile_id;
		}
		if ($cache_id === null) {
			$cache_id = $this->cache_id;
		}

		if ($this->debug_mode === true) {
			$dbg                          = debug_backtrace();
			$this->debug_trace['fetch'][] = array(
				'path'       => $path,
				'cache_id'   => $cache_id,
				'compile_id' => $cache_id,
				'display'    => $display,
				'compiler'   => $compiler,
				'file'       => $dbg[0]['file'],
				'line'       => $dbg[1]['line']
			);
		}

		static $nesting_path = array();
		static $_old_block_props = array();
		$return  = true;
		$var     = & $this->_tpl_vars;
		$const   = & $this->_tpl_consts;
		$config  = & $this->_tpl_config;
		$capture = & $this->_block_props['capture'];
		$foreach = & $this->_block_props['foreach'];
		$section = & $this->_block_props['section'];
		$tpl     = $this;
		$cache   = $compile = false;
		if (($cache = $this->caching ? $this->is_cached($path, $cache_id, $compile_id) : false) or ($compile = $this->_compile($path, $compile_id, $compiler))) {
			$p = $cache !== false ? $cache : $compile;
			if (error_reporting() != $this->error_reporting) {
				$old_err_rep = error_reporting();
				error_reporting($this->error_reporting);
			}
			else {
				$old_err_rep = -1;
			}
			if (!isset($nesting_path[$path])) {
				$nesting_path[$path] = 1;
			}
			else {
				++$nesting_path[$path];
			}
			if ($nesting_path[$path] > $this->max_recursion_depth) {
				$this->warning('Max recursion depth exceed.');
				return;
			}
			$old_nesting_path = $nesting_path;
			$dir              = dirname($path);
			if ($dir === '') {
				$dir = '.';
			}
			if ($this->caching && !$cache) {
				$c  = file_get_contents($p);
				/*AlexBaks 
				  Fix not the function announcement in cache files
				*/
				preg_match_all('{require_once(.*?);}', $c, $arr);
				$header = null;
				foreach ($arr[0] as $val) {
					$header .= $val."\n";
				}
				$header = '<? '.$header.' ?>';
				/*AlexBaks*/
				$a  = preg_replace_callback($e = '~(<\?php )?/\*(' . preg_quote(UNIQUE_HASH_STATIC, '~') . ')\{(dynamic)\}\*/ (\?>)?(.*?)(?:<\?php )?/\*\{/\3\}\2\*/( \?>)?~si', array($this, 'dynamic_callback'), $c);
				$a =  $header . $a; /*AlexBaks*/
				$fn = tempnam($this->cache_dir, 'tmp');
				$fp = fopen($fn, 'w');
				fwrite($fp, $a);
				fclose($fp);
				ob_start();
				$old = ob_get_contents();
				ob_clean();
				if ($this->caching == 1) {
					$this->caching = 0;
				}
				include $fn;
				$a = ob_get_contents();
				ob_end_clean();
				echo $old;
				unlink($fn);
				$a     = preg_replace($e = '~!' . preg_quote(UNIQUE_HASH, '~') . '!non_cache=(.*?)!~sie', 'base64_decode("$1")', $a);
				$a =  $header . $a; /*AlexBaks*/
				$p = $this->_get_cache_path($path, $cache_id, $compile_id);
				$fp    = fopen($p, 'w');
				fwrite($fp, $a);
				fclose($fp);
			}
			if (!$display or count($this->outputfilters) > 0) {
				ob_start();
				$old = ob_get_contents();
				ob_clean();
				if ($this->caching == 1) {
					$this->caching = 0;
				}
				include $p;
				$a = ob_get_contents();
				ob_end_clean();
				echo $old;
				if (count($this->outputfilters) > 0) {
					$filters = array_values($this->outputfilters);
					for ($i = 0, $s = count($filters); $i < $s; ++$i) {
						$a = call_user_func($filters[$i], $a, $this);
					}
				}
				if ($display) {
					echo $a;
				}
				else {
					$return = $a;
				}
			}
			else {
				if ($this->caching == 1) {
					$this->caching = 0;
				}
				include $p;
			}
			$nesting_path = $old_nesting_path;
			if ($old_err_rep !== -1) {
				error_reporting($old_err_rep);
			}
			--$nesting_path[$path];
			return $return;
		}
		else {
			return false;
		}
	}

    /**
     * @param $path
     * @param null $compile_id
     * @return bool|mixed|string
     */
    public function _is_compiled($path, $compile_id = null) {
		if ($compile_id === null) {
			$compile_id = $this->compile_id;
		}
		if ($this->force_compile) {
			return false;
		}
		$p = $this->_get_compile_path($path, $compile_id);
		if (!is_file($p)) {
			return false;
		}
		if ($this->compile_check) {
			if (filemtime($this->_get_template_path($path)) <= filemtime($p)) {
				return $p;
			}
			else {
				return false;
			}
		}
		else {
			return $p;
		}
	}

	function _compile($path, $compile_id = null, $compiler, $force = false) {
		if ($compile_id === null) {
			$compile_id = $this->compile_id;
		}
		if (!$force) {
			if ($p = $this->_is_compiled($path, $compile_id)) {
				return $p;
			}
		}
		$compiler_ver = array();
		$fp           = fopen($tp = $this->_get_template_path($path), 'r');
		if (!$fp) {
			$this->warning('Can\'t read template file: ' . $path);
			return false;
		}
		if ($l = fgets($fp)) {
			preg_match_all('~/(\w+)\=(.*?)(?=/|$)~', $l, $p, PREG_SET_ORDER);
			for ($i = 0, $s = count($p); $i < $s; $i++) {
				$name  = strtolower($p[$i][1]);
				$value = $p[$i][2];
				if ($name == 'compiler') {
					preg_match('~^(\w+)\s*(?:(>=|==|<=|<|>)?\s*(\S*))?~', $value, $q);
					$compiler = $q[1];
					if (isset($q[2]) && ($q[2] !== '')) {
						$compiler_ver = array($q[2], $q[3]);
					}
				}
			}
		}
		fclose($fp);
		if (!$this->load_compiler($compiler)) {
			return false;
		}
		if (count($compiler_ver) > 0) {
			if (!version_compare($this->compilers[$compiler]->compiler_version, $compiler_ver[1], $compiler_ver[0])) {
				$this->warning('Incompatible version of compiler ' . $compiler . ' (' . $this->compilers[$compiler]->compiler_version . ') for template ' . $path . ' needed ' . $compiler_ver[1]);
				return false;
			}
		}
		$source = $this->compilers[$compiler]->_compile_source($tp, $path);
		$fp     = fopen($c = $this->_get_compile_path($path, $compile_id), 'w');
		if (!$fp) {
			return false;
		}
		fwrite($fp, $source);
		fclose($fp);
		return $c;
	}

    /**
     * @param $string
     * @param $from
     * @param null $compiler
     * @return mixed
     */
    public function _compile_string($string, $from, $compiler = null) {
		if ($compiler === null) {
			$compiler = $this->default_compiler;
		}
		$this->load_compiler($compiler);
		return $this->compilers[$compiler]->_compile_source_string($string, $from);
	}

    /**
     * @param $a
     * @param int $b
     * @return float|int
     */
	public static function ind($a, $b = 0) {
		$s = $a['st'] + abs($a['step']) * ($a['i'] + $b);
		if ($s < 0) {
			return -1;
		}
		if ($a['step'] < 0) {
			$s = $a['s'] - $s;
		}
		return $s;
	}

    /**
     * @param $template
     * @return string
     */
	public function PHPtemplateFetch($template) {
		$template = eval('return function($tpl) {
			$var = &$tpl->_tpl_vars;
			$config = &$tpl->_tpl_config;
			$capture = &$tpl->_block_props[\'capture\'];
			$foreach = &$tpl->_block_props[\'foreach\'];
			$section = &$tpl->_block_props[\'section\'];
			?>' . $template . '<?php };');
		ob_start();
		$this->_eval($template);
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
