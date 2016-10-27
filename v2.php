<?php
date_default_timezone_set('America/New_York');

class Voter {
	var $name;
	var $addr;
	var $affil;
	var $zip;
	var $log;
	var $verbose = false;

	function Voter($name, $addr, $affil, $zip) {
		$this->name = $name;
		$this->addr = $addr;
		$this->affil = $affil;
		$this->zip = $zip;
		$this->log = "./data/$name/log";
	}

	function read_log() {
		if ($this->log && file_exists($this->log)) {
			return @file_get_contents($this->log);
		}else{
			return "File " . $this->log . " does not exist";
		}
	}

	function __toString() {
		$out = "Voter registration for {$this->name}:\n\tAddress: {$this->addr}" .
			"\n\taffiliation: {$this->affil}\n\tZip: {$this->zip}";

		if ($this->verbose)
			$out .= "\n\nVoter History:\n" . $this->read_log();

		return $out;
	}
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

function create_voter_id($voter) {
	# Serialize is unsafe without hmac 
	$vote_s = serialize($voter);
	$user_key = create_key($voter->name) or die("Couldn't create key for user - account already created" . PHP_EOL);

	$date = (new DateTime())->format('m/d/y h:m');
	file_put_contents($voter->log, "Registered {$voter->name} at $date" . PHP_EOL, FILE_APPEND) or die("Couldn't write to user log");

	$signed_vote_s = hash_hmac("sha512", $vote_s, $user_key);

	$system_key = get_system_key() or die("couldn't get system key");;
	$signed_name = hash_hmac("sha512", $voter->name, $system_key);

	return json_encode([$vote_s, $signed_vote_s, $voter->name, $signed_name]);
}

function safe_string($s) {
	# Spaces/alphanumeric and under 20 chars
	if (strlen($s) > 20) return False;
	return ctype_alnum(str_replace(" ","", $s));
}

function generate_voter($name, $addr, $affil, $zip) {
	safe_string($name)  or die("Bad name");
	safe_string($addr)  or die("Bad address");
	safe_string($affil) or die("Bad affiliation");
	safe_string($zip)   or die("Bad zip");

	$v = new Voter($name, $addr, $affil, $zip);

	return base64_encode(create_voter_id($v));
}

function validate_voter($blob, $debug=False) {
	$unb64 = base64_decode($blob) or die("Could not decode base64");
	list($vote_s, $signed_vote_s, $name, $signed_name) = json_decode($unb64) or die("Could not decode json");
	$system_key = get_system_key();
	$valid_name_sig = hash_hmac("sha512", $name, $system_key);
	hash_equals($signed_name, $valid_name_sig) or die("Bad signature for name");

	$user_key = get_key($name);
	if (is_test_voter($name)) {
		die("$name is a testing account - it can't be used to vote");
	}
	if ($debug) {
		mark_test_voter($name);
		print("DEBUG: User signed with key " . base64_encode($user_key) . PHP_EOL);
	}

	$valid_vote_s_sig = hash_hmac("sha512", $vote_s, $user_key);
	hash_equals($signed_name, $valid_name_sig) or die("Bad signature for Voter");

	# hmac validations passed- vote_s is unmodified so it's safe to unserialize
	$voter = unserialize($vote_s, ["Voter"]);
	return $voter;
}

$voter_id = (generate_voter("andrew", "addr", "dem", "12345"));
print($voter_id . PHP_EOL . PHP_EOL);
print(validate_voter($voter_id) . PHP_EOL);
print(validate_voter($voter_id) . PHP_EOL);

print(validate_voter($voter_id, true) . PHP_EOL);

print(PHP_EOL . "Normal" . PHP_EOL);
print(validate_voter($voter_id) . PHP_EOL);
?>
