<?php
/**************************************************************************/
/* Quicky: smart and fast templates
/* ver. 0.5.0.0
/* http://code.google.com/p/quicky/
/* ===========================
/*				
/* Quicky_BBCode.class.php: BB-code class
/**************************************************************************/
class Quicky_BBcode {
	public $smiles = array(
		'acute',
		'angry',
		'big_boss',
		'glare',
		'good',
		'happy',
		'hi',
		'huh',
		'lol',
		'not_i',
		'ohmy',
		'read',
		'smile',
		'smile3',
		'taunt',
		'to_keep_order',
		'wink',
		'yahoo',
		'yes',
		'rose' /*,
  'banned',
  'tema',
  'offtopic',
  'funny_post'*/,
	);
	public $source;
	public $blocks = array();
	public $tags = array();
	public $left_delimiter = '[';
	public $right_delimiter = ']';
	public $errors = array();
	public $allow_html_tags = FALSE;
	public $smiles_dir;
	public $smiles_url;
	private $_builtin_blocks = '[gm]|email|link|url|code|php|list|plain|literal';
	public $cast_unrecognized_tags = FALSE;
	public $stat = array();
	public $use_stat = TRUE;
	public $block_stacks = array();
	public $block_stack_n = 0;
	public $autourl = TRUE;
	public $allow_smiles = TRUE;

	public function __construct() {
	}

	public function load($string) {
		$this->source = $string;
	}

	public function safe_uri($uri) {
		$uri = trim($uri);
		if (preg_match('~^(?:java|vb)script:~i', preg_replace('~\s+~', '', $uri))) {
			return FALSE;
		}
		return TRUE;
	}

	private function _error($msg) {
		$this->errors[] = $msg;
		return FALSE;
	}

	public function register_block($name, $callback) {
		$this->blocks[strtolower($name)] = $callback;
	}

	public function register_tag($name, $callback) {
		$this->tags[strtolower($name)] = $callback;
	}

	private function _parse_params($p) {
		$params = array();
		preg_match_all('~\w+\s*=|(([\'"]).*?(?<!\\\\)\2|\S+)~s', $p, $m, PREG_SET_ORDER);
		$lastkey = '';
		foreach ($m as $v) {
			if (trim($v[0]) === '') {
				continue;
			}
			if (sizeof($v) == 1) {
				$lastkey = ltrim(rtrim($v[0], " =\t"));
				continue;
			}
			if ($lastkey === '') {
				$params[] = $v[0];
			}
			else {
				$params[$lastkey] = $v[0];
			}
			$lastkey = '';
		}
		if ($this->use_stat) {
			$this->stat['numparams'] += sizeof($params);
		}
		return $params;
	}

	private function _dequote($string) {
		if ((substr($string, 0, 1) == '"' and substr($string, -1) == '"')
				or (substr($string, 0, 1) == '\'' and substr($string, -1) == '\'')
		) {
			return substr($string, 1, -1);
		}
		return $string;
	}

	private function _tag_token($mixed) {
		if (is_array($mixed)) {
			if (sizeof($mixed) == 1) {
				if ($mixed[0] == "\n") {
					return '<br />' . "\n";
				}
				elseif ($mixed[0] == "\r") {
					return '';
				}
				return $this->allow_html_tags ? $mixed[0] : htmlspecialchars($mixed[0]);
			}
			if (isset($mixed[7]) and $mixed[7] !== '') {
				if (!$this->allow_smiles) {
					return $mixed[0];
				}
				$smile = substr($mixed[7], 1, -1);
				if (file_exists($this->smiles_dir . $smile . '.gif')) {
					return '<img src="' . htmlspecialchars($this->smiles_url . $smile) . '.gif">';
				}
				else {
					return $mixed[0];
				}
			}
			if ($mixed[1] !== '') {
				if ($this->use_stat) {
					++$this->stat['numblocks'];
					if (substr($mixed[2], 0, 1) == '=') {
						++$this->stat['numparams'];
					}
				}
				$block_type    = strtolower($mixed[1]);
				$block_content = $mixed[4];
				if ($block_type == 'img') {
					if (!$this->safe_uri($block_content)) {
						return $this->_error('Unsafe uri "' . $block_content . '" in tag ' . $block_type);
					}
					return '<img src="' . htmlspecialchars($block_content, ENT_QUOTES) . '" />';
				}
				elseif ($block_type == 'link' or $block_type == 'url') {
					if (substr($mixed[2], 0, 1) == '=') {
						$url = substr($mixed[2], 1);
					}
					else {
						$block_params = $this->_parse_params($mixed[2]);
						$url          = isset($block_params['src']) ? $block_params['src'] : '';
					}
					$url = $this->_dequote($url);
					if ($url === '') {
						$url = $block_content;
					}
					if (!$this->safe_uri($url)) {
						return $this->_error('Unsafe uri "' . $url . '" in tag ' . $block_type);
					}
					return '<a href="' . htmlspecialchars($url) . '">' . $this->_tag_token($block_content) . '</a>';
				}
				elseif ($block_type == 'php') {
					$s = trim($block_content, "\r\n");
					if (!preg_match('~<\?php~i', $s)) {
						$s = str_replace("\r", '', '<?php' . "\n" . $s . ' ?>');
					}
					else {
						$s = str_replace("\r", '', '<?php' . "\n" . $s . ' ?>');
					}
					$s    = @highlight_string($s, TRUE);
					$s    = substr_replace($s, '', strpos($s, '&lt;?php'), 8);
					$s    = substr_replace($s, '', strrpos($s, '?&gt;'), 5);
					$from = 0;
					$x    = 0;
					while ($i = strpos($s, '<br />', $from)) {
						$s    = substr($s, 0, $x == 0 ? $i : $i + 6) . "\n" .
								'<font style="color:#000000;background-color:#eeeeee;">&nbsp;' . sprintf('%03d', $x + 1) .
								'&nbsp;</font>&nbsp;' . substr($s, $i + 6);
						$from = $i + 5;
						$x++;
					}
					return '<div style="background-color:#cccccc">' . $s . '</div>';
				}
				elseif ($block_type == 'code') {
					$s = trim($block_content);
					$r = '';
					$x = 0;
					$e = explode("\n", $s);
					for ($i = 0, $s = sizeof($e); $i < $s; ++$i) {
						$line = $e[$i];
						if ($x != 0 or strlen(trim(str_replace('<br />', '', $line))) > 0) {
							$r .= '<font style="color:#000000;background-color:#eeeeee;">&nbsp;' . sprintf('%03d', $x + 1) .
									'&nbsp;</font>&nbsp;' . $line . "\n";
							$x++;
						}
					}
					return '<div style="background-color:#cccccc">' . $r . '</div>';
				}
				elseif (in_array($block_type, array('b', 'i', 'u', 's', 'p'))) {
					return '<' . $block_type . '>' . $this->_tag_token($block_content) . '</' . $block_type . '>';
				}
				elseif ($block_type == 'email') {
					if (substr($mixed[2], 0, 1) == '=') {
						$email = substr($mixed[2], 1);
					}
					else {
						$block_params = $this->_parse_params($mixed[2]);
						$email        = isset($block_params['address']) ? $this->_dequote($block_params['address']) : '';
					}
					if ($email === '') {
						$email = $block_content;
					}
					return '<a href="mailto:' . htmlspecialchars($email) . '" />' . $this->_tag_token($block_content) . '</a>';
				}
				elseif ($block_type == 'm') {
					return '<a href="http://php.net/' . urlencode($block_content) . '">' . htmlspecialchars($block_content) . '</a>';
				}
				elseif ($block_type == 'g') {
					return '<a href="http://www.google.com/search?q=' . urlencode($block_content) . '">' . htmlspecialchars($block_content) . '</a>';
				}
				elseif ($block_type == 'list') {
					if (substr($mixed[2], 0, 1) == '=') {
						$flag = substr($mixed[2], 1);
					}
					else {
						$flag = '0';
					}
					return '<table border=0 width=100%><tr><td width=50></td><td width="95%">'
							. ($flag == '0' ? '<ol>' : '<ul>')
							. $this->_tag_token($block_content)
							. ($flag == '0' ? '</ol>' : '</ul>') . '</td></tr></table>';
				}
				elseif ($block_type == 'plain' or $block_type == 'literal') {
					return htmlspecialchars($block_content);
				}
				elseif (isset($this->blocks[$block_type])) {
					return call_user_func($this->blocks[$block_type], $mixed[2], $block_content, $this);
				}
				else {
					return $this->cast_unrecognized_tags ? $mixed[0] : $this->_error('Unrecognized block-type: \'' . $block_type . '\'');
				}
			}
			elseif (isset($mixed[5]) && $mixed[5] !== '') {
				static $c_offsets = array();
				if (!isset($c_offsets[$this->block_stack_n])) {
					$c_offsets[$this->block_stack_n] = 0;
				}
				preg_match('~^\s*(/?)([^\s=]*)(.*?)$~s', $mixed[5], $m);
				if (!isset($m[0])) {
					$m[0] = '';
				}
				if (!isset($m[1])) {
					$m[1] = '';
				}
				if (!isset($m[2])) {
					$m[2] = '';
				}
				if (!isset($m[3])) {
					$m[3] = '';
				}
				$close = $m[1];
				$tag   = strtolower($m[2]);
				$param = $m[3];
				if ($this->use_stat) {
					++$this->stat['numtags'];
				}
				$bs = & $this->block_stacks[$this->block_stack_n];
				if ($close and ($tag == '')) {
					$el  = array_slice($bs, -$c_offsets[$this->block_stack_n], 1);
					$tag = current($el);
					++$c_offsets[$this->block_stack_n];
					if ($tag === FALSE or $tag[1]) {
						return '';
					}
					$tag             = $tag[0];
					$bs[key($el)][1] = TRUE;
				}
				elseif ($close) {
					$found = FALSE;
					for ($i = sizeof($bs) - 1; $i >= 0; --$i) {
						if ((!$bs[$i][1]) and ($bs[$i][0] == $tag)) {
							$bs[$i][1] = TRUE;
							$found     = TRUE;
							break;
						}
					}
					if (!$found) {
						return '';
					}
				}
				$return = $this->_exec_tag($close, $tag, $param);
				if (!$close and !in_array($tag, array('hr')) and ($return !== FALSE)) {
					$bs[]                            = array($tag, FALSE);
					$c_offsets[$this->block_stack_n] = 0;
				}
				return $return;
			}
			return;
		}
		++$this->block_stack_n;
		if (!isset($this->block_stacks[$this->block_stack_n])) {
			$this->block_stacks[$this->block_stack_n] = array();
		}
		$bs = & $this->block_stacks[$this->block_stack_n];
		static $regexp;
		if ($regexp === NULL) {
			$ldelim = preg_quote($this->left_delimiter, '~');
			$rdelim = preg_quote($this->right_delimiter, '~');
			$blocks = array($this->_builtin_blocks);
			for ($i = 0, $s = sizeof($this->blocks), $v = array_keys($this->blocks); $i < $s; ++$i) {
				$blocks[] = preg_quote($v[$i], '~');
			}
			$regexp = '~'
					. $ldelim . '\s*(' . implode('|', $blocks) . ')([\s=](?:[^' . $rdelim . '\'"]*([\'"]).*?(?<!\\\\)\3)*.*?)?' . $rdelim . '((?:(?R)|.)*?)' . $ldelim . '/\s*\1?\s*' . $rdelim
					. '|' . $ldelim . '(\\??(?:[^' . $rdelim . '\'"]*([\'"]).*?(?<!\\\\)\5)*.*?)' . $rdelim
					. '|(:\w+:)|[<>&\n\'"]'
					. '~si';
		}
		if ((strpos($mixed, $this->left_delimiter) !== FALSE) or (strpos($mixed, ':') !== FALSE)) {
			$return = preg_replace_callback($regexp, array($this, '_tag_token'), $mixed);
			for ($i = sizeof($bs) - 1; $i >= 0; --$i) {
				if (!$bs[$i][1]) {
					$return .= $this->_exec_tag('/', $bs[$i][0]);
				}
			}
			return $return;
		}
		--$this->block_stack_n;
		return $mixed;
	}

	public function _exec_tag($close, $tag, $param = '') {
		$bs = & $this->block_stacks[$this->block_stack_n];
		if ($tag == 'li' or $tag == '*') {
			$return = $close ? '' : '<li>';
		}
		elseif (in_array($tag, array('b', 'i', 'u', 's', 'p'))) {
			$return = '<' . $close . $tag . '>';
		}
		elseif ($tag == 'quote') {
			if ($close) {
				return '</td></tr><tr><td><hr size=1 nowhade></td></tr></table>';
			}
			if (substr($param, 0, 1) == '=') {
				$author = substr($param, 1);
			}
			else {
				$author = '';
			}
			return '<table border=0 width=100%><tr><td width="10" rowspan=3>&nbsp;</td><td width=90%><hr size=1 noshade></td></tr><tr><td>'
					. ($author != '' ? '<i>Original by: ' . htmlspecialchars($author) . '</i><br />' : '');
		}
		elseif ($tag == 'size') {
			if ($close) {
				return '</font>';
			}
			if (substr($param, 0, 1) == '=') {
				$size = substr($param, 1);
			}
			else {
				$size = '10';
			}
			return '<font size="' . htmlspecialchars($this->_dequote($size)) . '">';
		}
		elseif ($tag == 'color') {
			if (substr($param, 0, 1) == '=') {
				$color = substr($param, 1);
			}
			else {
				$color = 'black';
			}
			if ($close) {
				return '</font>';
			}
			return '<font color="' . htmlspecialchars($this->_dequote($color)) . '">';
		}
		elseif ($tag == 'sub') {
			if ($close) {
				return '</' . $tag . '>';
			}
			return '<' . $tag . '>';
		}
		elseif ($tag == 'hr') {
			return '<' . $tag . ' />';
		}
		elseif ($tag == 'font') {
			if (substr($param, 0, 1) == '=') {
				$face = substr($param, 1);
			}
			else {
				$face = 'Verdana';
			}
			if ($close) {
				return '</font>';
			}
			return '<font face="' . htmlspecialchars($this->_dequote($face)) . '">';
		}
		elseif ($tag == 'table') {
			if ($close) {
				return '</table>';
			}
			return '<table border="1">';
		}
		elseif ($tag == 'row') {
			$found = FALSE;
			for ($i = sizeof($bs) - 1; $i >= 0; --$i) {
				if ((!$bs[$i][1]) and ($bs[$i][0] == 'table')) {
					$found = TRUE;
					break;
				}
			}
			if (!$found) {
				$this->_error('Unexpected tag-type: \'' . $tag . '\'');
				return FALSE;
			}
			if ($close) {
				return '</tr>';
			}
			return '<tr>';
		}
		elseif ($tag == 'col') {
			$found = FALSE;
			for ($i = sizeof($bs) - 1; $i >= 0; --$i) {
				if ((!$bs[$i][1]) and ($bs[$i][0] == 'table')) {
					$found = TRUE;
					break;
				}
			}
			if (!$found) {
				return '';
			}
			if ($close) {
				return '</td>';
			}
			$p = $this->_parse_params($param);
			$s = '';
			static $col_params = array('width', 'height');
			foreach ($p as $k => $v) {
				if (in_array(strtolower($k), $col_params)) {
					$s .= ' ' . $k . '="' . htmlspecialchars($this->_dequote($v)) . '"';
				}
			}
			return '<td' . $s . '>';
		}
		elseif (isset($this->tags[$tag])) {
			$return = call_user_func($this->tags[$tag], $param, $close);
		}
		else {
			return $this->cast_unrecognized_tags ? NULL : $this->_error('Unrecognized tag-type: \'' . $tag . '\' (' . $close . ')');
		}
		return $return;
	}

	public function build() {
		$this->source = & $this->text;
		return $this->result = $this->getHTML();
	}

	public function prepareblock_callback($m) {
		if (empty($m[1])) {
			return $m[0];
		}
		if (isset($m[7])) {
			return '[URL="' . $m[7] . '"]' . $m[7] . '[/URL]';
		}
		$blockname = $m[1];
		if ($blockname == 'url' or $blockname == 'php' or $blockname == 'code') {
			return $m[0];
		}
		else {
			return '[' . $m[1] . $m[2] . ']' . $this->prepareblock($m[4]) . $m[5];
		}
	}

	public function prepareblock($source) {
		static $regexp = NULL;
		if ($regexp === NULL) {
			$ldelim = preg_quote($this->left_delimiter, '~');
			$rdelim = preg_quote($this->right_delimiter, '~');
			$blocks = array($this->_builtin_blocks);
			for ($i = 0, $s = sizeof($this->blocks), $v = array_keys($this->blocks); $i < $s; ++$i) {
				$blocks[] = preg_quote($v[$i], '~');
			}
			$regexp = '~'
					. $ldelim . '\s*(' . implode('|', $blocks) . ')([\s=](?:[^' . $rdelim . '\'"]*([\'"]).*?(?<!\\\\)\3)*.*?)?' . $rdelim . '((?:(?R)|.)*?)(' . $ldelim . '/\s*\1?\s*' . $rdelim . ')'
					. '|' . $ldelim . '(\\??(?:[^' . $rdelim . '\'"]*([\'"]).*?(?<!\\\\)\6)*.*?)' . $rdelim . '\r?\n?'
					. '|([a-z\d]+://[^\s\]]+)'
					. '~si';
		}
		return preg_replace_callback($regexp, array($this, 'prepareblock_callback'), $source);
	}

	public function getHTML() {
		$this->block_stacks  = array();
		$this->errors        = array();
		$this->block_stack_n = 0;
		$source              = $this->source;
		$this->stat          = array();
		if ($this->use_stat) {
			$this->stat = array(
				'numblocks' => 0,
				'numtags'   => 0,
				'numparams' => 0
			);
		}
		if ($this->autourl) {
			$source = $this->source = $this->prepareblock($source);
		}
		$source = $this->_tag_token($source);
		return $source;
	}
}