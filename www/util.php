<?php
date_default_timezone_set('America/New_York');

function safe_string($s) {
	# Spaces/alphanumeric and under 20 chars
	if (strlen($s) > 20) return False;
	return ctype_alnum(str_replace(" ","", $s));
}

function create_key($user) {
	$dir = "./data/$user/";
	$f = "$dir/key";

	if (file_exists($f)) {
		return False;
	}

	if (file_exists($dir) == false) {
		mkdir($dir, 0700, true);
	}

	$key = openssl_random_pseudo_bytes(64);
	file_put_contents($f, $key) or die("Couldn't save key");
	chmod($f, 0400);
	return $key;
}

function get_key($user) {
	$dir = "./data/$user/";
	$f = "$dir/key";
	if (file_exists($f)) {
		$key = file_get_contents($f);
		return $key;
	}
	return False;
}

function get_system_key() {
	$f = "./system_key";
	if (file_exists($f)) {
		$key = file_get_contents($f);
	} else {
		$key = openssl_random_pseudo_bytes(64);
		file_put_contents($f, $key) or die("Couldn't save system key");
	}
	return $key;
}

function is_test_voter($name) {
	$f = "./data/$name/debug";
	if (file_exists($f)) {
		$debug_timestamp = file_get_contents($f);
		$debug_after = DateTime::createFromFormat('m/d/y h:m', $debug_timestamp);

		$now = new DateTime();
		if ($now < $debug_after) {
			return True;
		}
	}
	return False;
}

function mark_test_voter($name, $raw_date=null) {
	$f = "./data/$name/debug";
	if (file_exists($f)) return;

	if (!isset($raw_date)) $raw_date = new DateTime();
	$date = $raw_date->format('m/d/y h:m');

	file_put_contents($f, $date) or die("Couldn't mark $name as test voter" . PHP_EOL);
	chmod($f, 0400);

	file_put_contents("./data/$name/log", "Marked $name as test voter effective $date" . PHP_EOL, FILE_APPEND);

}

?>