<?php
if (!function_exists('ftok')) {
	function ftok($path, $proj) {
		$st = stat($path);
		if (!$st) {
			return -1;
		}
		return (($st['ino'] & 0xffff) | (($st['dev'] & 0xff) << 16) | (($proj & 0xff) << 24));
	}
}
class Quicky_MemoryCache {
	public $position = 0;
	public $body;
	public $path;
	public $id;

	function stream_open($path, $mode, $options, &$opened_path) {
		$this->path = substr($path, 7);
		return TRUE;
	}

	function url_stat($url) {
		$url = substr($url, 7);
		return file_exists($url) ? stat($url) : FALSE;
	}

	function stream_read($count) {
		if ($this->body === NULL) {
			$this->id = ftok($this->path, 'M');
			$shm      = @shmop_open($this->id, 'a', 0, 0);
			if ($shm) {
				$this->body = shmop_read($shm, 0, shmop_size($shm));
			}
			else {
				$this->body = file_get_contents($this->path);
			}
		}
		$ret = substr($this->body, $this->position, $count);
		$this->position += strlen($ret);
		return $ret;
	}

	function stream_write($data) {
		$p  = $this->path;
		$fp = fopen($p, 'w');
		fwrite($fp, $data);
		fclose($fp);

		$this->id = ftok($this->path, 'M');
		if ($t = @shmop_open($this->id, 'a', 0, 0)) {
			shmop_delete($t);
		}
		$t = shmop_open($this->id, 'c', 0755, strlen($data));
		shmop_write($t, $data, 0);
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

stream_wrapper_register('qmem', 'Quicky_MemoryCache') or die('Failed to register protocol qmem://');
