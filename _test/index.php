<?php
$h = opendir('.');
if (!$h) {
	exit('Couldn\'t open current directory');
}
while (($f = readdir($h)) !== FALSE) {
	if ($f == '.' or $f == '..') {
		continue;
	}
	echo '<a href="' . $f . '">' . $f . '</a><br />';
}
