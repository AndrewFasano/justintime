<?php
date_default_timezone_set('America/New_York');

function debug($epoch) {
	print($epoch . PHP_EOL);
	echo date("m/d/y h:m:s e I ", $epoch) . PHP_EOL;
}

$debug_pre = 1478411885; 		#11/06/16 01:11:05 America/New_York EDT
$debug_post = $debug_pre+3600;  #11/06/16 01:11:05 America/New_York EST


?>
