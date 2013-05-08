<?php
class Quicky_Stringtemplate {
	static $strings = array();
	var $body;
	var $position;

	function fetch_template($path) // You should rewrite this method
	{
		$name       = substr($path, strpos($path, '://') + 3);
		$this->body = self::$strings[$name];
		return TRUE;
	}

	function stream_open($path, $mode, $options, &$opened_path) {
		return $this->fetch_template($path);
	}

	function stream_read($count) {
		$ret = substr($this->body, $this->position, $count);
		$this->position += strlen($ret);
		return $ret;
	}

	function stream_write($data) {
		return;
	}

	function stream_tell() {
		return $this->position;
	}

	function stream_eof() {
		return $this->position >= strlen($this->body);
	}

	function stream_seek($offset, $whence) {
		return;
	}

	function stream_stat() {
	}

	function url_stat() {
		return array();
	}
}

stream_wrapper_register('string', 'Quicky_Stringtemplate') or die('Failed to register protocol string://');