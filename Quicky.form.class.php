<?php
/**************************************************************************/
/* Quicky: smart and fast templates
/* ver. 0.5.0.0
/* http://code.google.com/p/quicky/
/* ===========================
/*
/* Copyright (c)oded 2007-2008 by WP
/* http://quicky-tpl.net
/*
/* Quicky.form.class.php: Quicky forms
/**************************************************************************/
class Quicky_form {
	static $forms = array();
	static $_uploads_temp = FALSE;
	public $name;
	public $elements;
	public $properties;
	public $_num_of_errors = 0;

	public function __construct($name) {
		$this->name                = $name;
		$this->elements            = (object)array();
		$this->properties          = (object)array();
		Quicky_form::$forms[$name] = $this;
	}

	public function getErrors() {
		$errors = array();
		foreach ($this->elements as $el) {
			if (isset($el->_errormsg)) {
				$errors[] = $el->_errormsg;
			}
		}
		return $errors;
	}

	public function complete() {
		if ($this->_num_of_errors) {
			return FALSE;
		}
		if (isset($this->elements->submit) && !$this->elements->submit->clicked()) {
			return FALSE;
		}
		return TRUE;
	}

	public function addElement($name, $properties = array()) {
		if (is_object($properties)) {
			if (!isset($properties->name)) {
				$properties->name = $name;
			}
		}
		else {
			if (!isset($properties['name'])) {
				$properties['name'] = $name;
			}
		}
		$this->elements->$name        = is_object($properties) ? $properties : new Quicky_form_element($properties);
		$this->elements->$name->_form = $this;
	}

	public function removeElement($name) {
		unset($this->elements->$name);
	}

	public function remove() {
		unset(Quicky_form::$forms[$this->name]);
	}

	public function __get($name) {
		return isset($this->$name) ? $this->$name : (isset($this->elements->$name) ? $this->elements->$name : NULL);
	}

	public function __clone() {
		trigger_error('Cloning Quicky_form is not allowed', E_USER_ERROR);
	}
}

class Quicky_form_filter {
	static function email($string) {
		return (bool)preg_match('~^[a-z0-9\._\-]+@[a-z0-9\._\-]+\.+[a-z]{2,}$~i', $string);
	}

	static function url($string) {
		return (bool)preg_match('~^\w+://[a-z0-9\._\-]+/?~i', $string);
	}

	static function format($string, $regexp) {
		return (bool)preg_match($regexp, $string);
	}

	static function length($string, $min = -1, $max = -1) {
		return ($min === -1 or strlen($string) >= $min) && ($max === -1 or strlen($string) <= $max);
	}

	static function digit($string) {
		return ctype_digit($string);
	}

	static function double($string, $abs = TRUE) {
		return (bool)preg_match('~^' . (!$abs ? '-?' : '') . '\d+(\.\d+)?$~', $string);
	}

	static function notempty($string) {
		return $string !== '';
	}
}

class Quicky_form_element {
	public $_errormsg;
	public $_form;
	public $type;
	public $defaultValue;

	public function __construct($properties = array()) {
		foreach ($properties as $k => $v) {
			$this->$k = $v;
		}
	}

	public function error($errmsg = '') {
		if (isset($this->_form)) {
			++$this->_form->_num_of_errors;
		}
		if ($errmsg !== '') {
			$this->_errormsg = $errmsg;
		}
	}

	public function addFilter($filter, $errmsg = '') {
		if (is_array($filter)) {
			$name      = $filter[0];
			$filter[0] = $this->getValue();
		}
		else {
			$name   = $filter;
			$filter = array($this->getValue());
		}
		if (!call_user_func_array(array('Quicky_form_filter', $name), $filter)) {
			$this->error($errmsg);
			return FALSE;
		}
		else {
			return TRUE;
		}
	}

	public function setValue($value) {
		$this->value = $value;
	}

	public function setDefaultValue($value) {
		$this->defaultValue = $value;
	}

	public function getValue() {
		if (!isset($this->name)) {
			return FALSE;
		}
		if (isset($this->value)) {
			return $this->value;
		}
		return isset($_REQUEST[$this->name]) ? $_REQUEST[$this->name] : $this->defaultValue;
	}

	public function getString() {
		if (!isset($this->name)) {
			return '';
		}
		return gpcvar_str($_REQUEST[$this->name]);
	}

	public function getInt() {
		if (!isset($this->name)) {
			return 0;
		}
		return gpcvar_int($_REQUEST[$this->name]);
	}

	public function getFloat() {
		if (!isset($this->name)) {
			return (float)0;
		}
		return gpcvar_float($_REQUEST[$this->name]);
	}

	public function getArray() {
		if (!isset($this->name)) {
			return array();
		}
		return gpcvar_array($_REQUEST[$this->name]);
	}

	public function __toString() {
		return strval($this->getValue());
	}
}

class QButton extends Quicky_form_element {
	public function __construct($properties = array()) {
		if (!isset($properties['type'])) {
			$properties['type'] = 'button';
		}
		foreach ($properties as $k => $v) {
			$this->$k = $v;
		}
	}

	public function clicked() {
		if (!isset($this->name)) {
			return FALSE;
		}
		return isset($_REQUEST[$this->name]);
	}

	public function notClicked() {
		if (!isset($this->name)) {
			return TRUE;
		}
		return !isset($_REQUEST[$this->name]);
	}
}

class QSubmit extends Quicky_form_element {
	public function __construct($properties = array()) {
		if (!isset($properties['type'])) {
			$properties['type'] = 'submit';
		}
		foreach ($properties as $k => $v) {
			$this->$k = $v;
		}
	}

	public function clicked() {
		if (!isset($this->name)) {
			return FALSE;
		}
		return isset($_REQUEST[$this->name]);
	}
}

class QCheckBox extends Quicky_form_element {
	public function __construct($properties = array()) {
		$properties['type'] = 'checkbox';
		foreach ($properties as $k => $v) {
			$this->$k = $v;
		}
	}

	public function checked() {
		if (!isset($this->name)) {
			return FALSE;
		}
		return isset($_REQUEST[$this->name]);
	}

	public function clicked() {
		return $this->checked();
	}
}

class QDropdown extends Quicky_form_element {
	public $elements = array();

	public function __construct($properties = array(), $elements = array()) {
		$properties['type'] = 'select';
		unset($properties['elements']);
		foreach ($properties as $k => $v) {
			$this->$k = $v;
		}
		$this->elements = $elements;
	}

	public function addElement($element, $name = '') {
		if ($name === '') {
			$this->elements[] = $element;
		}
		else {
			$this->elements[$name] = $element;
		}
	}

	public function removeElement($name) {
		unset($this->elements[$name]);
	}
}

class QTextBox extends Quicky_form_element {
	public function __construct($properties = array()) {
		if (!isset($properties['type'])) {
			$properties['type'] = 'text';
		}
		foreach ($properties as $k => $v) {
			$this->$k = $v;
		}
	}

	public function getText() {
		return $this->getValue();
	}
}

class QFile extends Quicky_form_element {
	public $_uploads_temp;
	public $_upload_id;

	public function __construct($properties = array()) {
		if (!isset($properties['type'])) {
			$properties['type'] = 'file';
		}
		foreach ($properties as $k => $v) {
			$this->$k = $v;
		}
		$this->_uploads_temp = Quicky_form::$_uploads_temp;
	}

	public function isUploaded() {
		return $this->getPath();
	}

	public function moveFileTo($dest) {
		$p = $this->getPath();
		if (!$p) {
			return FALSE;
		}
		copy($p, $dest);
		chmod($dest, 0644);
		return $dest;
	}

	public function moveTo($dest) {
		return $this->moveFileTo($dest);
	}

	public function getContents($empty = FALSE) {
		$p = $this->getPath();
		if (!$p) {
			return $empty ? '' : FALSE;
		}
		return file_get_contents($p);
	}

	public function getPath() {
		if ($this->_upload_id !== NULL) {
			$p = $this->_uploads_temp . basename($this->_upload_id);
			return file_exists($p) ? $p : FALSE;
		}
		if (!isset($this->name)) {
			return FALSE;
		}
		if (isset($_FILES[$this->name])) {
			return is_uploaded_file($_FILES[$this->name]['tmp_name']) ? $_FILES[$this->name]['tmp_name'] : FALSE;
		}
		if (isset($_REQUEST[$this->name]) && ($_REQUEST[$this->name] !== '')) {
			$p = $this->_uploads_temp . basename(strval($_REQUEST[$this->name]));
			return file_exists($p) ? $p : FALSE;
		}
		return FALSE;
	}

	public function getName() {
		return isset($_FILES[$this->name]['name']) ? $_FILES[$this->name]['name'] : '';
	}
}

class QBBarea extends Quicky_form_element {
	public function __construct($properties = array()) {
		if (!isset($properties['type'])) {
			$properties['type'] = 'textarea';
		}
		foreach ($properties as $k => $v) {
			$this->$k = $v;
		}
		require_once QUICKY_DIR . 'Quicky_BBcode.class.php';
		$this->_bbcode = new Quicky_BBcode;
	}

	public function getText() {
		return $this->getValue();
	}

	public function getHTML() {
		$this->_bbcode->load($this->getValue());
		return $this->_bbcode->getHTML();
	}
}

abstract class QCAPTCHA_Abstract extends Quicky_form_element {
	public $_imgid;

	abstract public function validate();
}