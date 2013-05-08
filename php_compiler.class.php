<?php
/**************************************************************************/
/* Quicky: smart and fast templates
/* ver. 0.5.0.0
/* ===========================
/*
/* Copyright (c)oded 2007-2008 by WP
/* http://quicky-tpl.net
/*
/* php_compiler.class.php: PHP Template compiler
/**************************************************************************/
class php_compiler {
	public $precompiled_vars = array();
	public $prefilters = array();
	public $postfilters = array();
	public $compiler_name = 'php';
	public $compiler_version = '0.1';
	public $load_plugins = array();
	public $prefs = array();
	public $template_defined_functions = array();
	public $syntax_error = NULL;
	public $template_from;
	public $blocks = array();
	public $_write_out_to = '';
	public $_cpl_vars = array();
	public $_cpl_config = array();

	public function Quicky_compiler() {
	}

	public function _compile_source_string($template, $from) {
		$old_load_plugins    = $this->load_plugins;
		$this->load_plugins  = array();
		$this->template_from = $from;
		//$template = str_replace("\r",'',$template);
		$template = preg_replace('~^/.*?/\r?\n~', '', $template);
		$source   = $template;
		$header   = '<?php /* PHP compiler version ' . $this->compiler_version . ', created  on ' . date('r') . '
			 compiled from ' . $from . ' */' . "\n";
		if (isset($this->prefs['export_vars']) && $this->prefs['export_vars']) {
			$header .= 'extract($var,EXTR_SKIP|EXTR_REFS);' . "\n";
		}
		$header .= '?>';
		$a = array_values($this->postfilters);
		for ($i = 0, $s = sizeof($a); $i < $s; $i++) {
			$source = call_user_func($a[$i], $source, $this);
		}
		$this->load_plugins = $old_load_plugins;
		return $header . $source;
	}

	public function _compile_source($path) {
		return $this->_compile_source_string(file_get_contents($path), substr($path, strlen($this->parent->template_dir)));
	}
}