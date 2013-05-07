<?php
function quicky_outputfilter_trimwhitespace($source, $quicky) {
	// Pull out the script blocks
	preg_match_all("!<script[^>]+>.*?</script>!is", $source, $match);
	$_script_blocks = $match[0];
	$source         = preg_replace("!<script[^>]+>.*?</script>!is",
								   '@@@quicky:TRIM:SCRIPT@@@', $source);

	// Pull out the pre blocks
	preg_match_all("!<pre>.*?</pre>!is", $source, $match);
	$_pre_blocks = $match[0];
	$source      = preg_replace("!<pre>.*?</pre>!is",
								'@@@quicky:TRIM:PRE@@@', $source);

	// Pull out the textarea blocks
	preg_match_all("!<textarea[^>]+>.*?</textarea>!is", $source, $match);
	$_textarea_blocks = $match[0];
	$source           = preg_replace("!<textarea[^>]+>.*?</textarea>!is",
									 '@@@quicky:TRIM:TEXTAREA@@@', $source);

	// remove all leading spaces, tabs and carriage returns NOT
	// preceeded by a php close tag.
	$source = trim(preg_replace('/((?<!\?>)\n)[\s]+/m', '\1', $source));

	// replace textarea blocks
	quicky_outputfilter_trimwhitespace_replace("@@@quicky:TRIM:TEXTAREA@@@", $_textarea_blocks, $source);

	// replace pre blocks
	quicky_outputfilter_trimwhitespace_replace("@@@quicky:TRIM:PRE@@@", $_pre_blocks, $source);

	// replace script blocks
	quicky_outputfilter_trimwhitespace_replace("@@@quicky:TRIM:SCRIPT@@@", $_script_blocks, $source);
	return $source;
}

function quicky_outputfilter_trimwhitespace_replace($search_str, $replace, &$subject) {
	$_len = strlen($search_str);
	$_pos = 0;
	for ($_i = 0, $_count = count($replace); $_i < $_count; $_i++) {
		if (($_pos = strpos($subject, $search_str, $_pos)) !== false) {
			$subject = substr_replace($subject, $replace[$_i], $_pos, $_len);
		}
		else {
			break;
		}
	}

}