<?php
class QCAPTCHA extends QCAPTCHA_Abstract {
	public function __construct($properties = array()) {
		require_once QUICKY_DIR . 'plugins/Captcha/Captcha_draw.class.php';
		$CAPTCHA = new CAPTCHA_draw;
		$CAPTCHA->generate_text();
		if (!session_id()) {
			session_start();
		}
		if (!isset($_SESSION['captcha'])) {
			$_SESSION['captcha'] = array();
		}
		$this->_imgid = -1;
		while ($this->_imgid == -1 or isset($_SESSION['captcha'][$this->_imgid])) {
			$this->_imgid = rand(0, mt_getrandmax());
		}
		$_SESSION['captcha'][$this->_imgid] = array($CAPTCHA->text, 0);
		foreach ($properties as $k => $v) {
			$this->$k = $v;
		}
	}

	public function validate() {
		$id = isset($_REQUEST['captcha_id']) ? strval($_REQUEST['captcha_id']) : '';
		$r  = isset($_SESSION['captcha'][$id]) && (strtolower($this->getValue()) == strtolower($_SESSION['captcha'][$id][0]));
		unset($_SESSION['captcha'][$id]);
		return $r;
	}
}