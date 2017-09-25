<?php
if (@$GLOBALS['debugmsgs'] AND DEBUG AND $character) {
	echo "<hr>\n";
	print_msgs($GLOBALS['debugmsgs'],'',"<br>\n");
}