<?php
class Quicky_DBtemplate {
	static $driver;
	public $position = 0;
	public $body;

	function fetch_template($path) // You should rewrite this method
	{
		$this->body = 'Body from DB';
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
}

Quicky_DBtemplate::$driver = (object)array();
stream_wrapper_register('db', 'Quicky_DBtemplate') or die('Failed to register protocol db://');