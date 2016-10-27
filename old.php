<?php
# Webserver logs in /[state].log

class Vote {
	var $user;
	var $vote;
	var $timestamp;
	var $log;

	function Vote($user, $vote, $timestamp) {
		$this->user = $user;
		$this->vote = $vote;
		$this->timestamp = $timestamp;
		$this->rand = rand();
		$this->log = "$user/$timestamp";
	}

	function pretty_date() {
		list($y, $m, $d, $h) = explode("_", $this->timestamp);
		return "$m/$d/$y during hour $h.";
	}

	function __toString() {
		return $this->user . " voted for " . $this->vote . " on " . $this->pretty_date();
	}

	function __destruct() {
		if ($this->log) { #ermergerd, bug goes here
			print("Cleaning up vote from $this->user encrypted with " . base64_encode(file_get_contents("./keys/" . $this->log)));
		}
	}
}

$iv =  "thisisanivright?";
function encrypt($msg, $key) {
	global $iv;
	$bytes =  openssl_encrypt($msg, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
	return $bytes;
}

function decrypt($msg, $key) {
	global $iv;
	$bytes =  openssl_decrypt($msg, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
	return $bytes;
}

function safe_string($s) {
	# Spaces/alphanumeric and under 20 chars
	if (strlen($s) > 20) return False;
	return ctype_alnum(str_replace(" ","", $s));
}

function get_key($user, $date, $create=False) {
	$dir = "./keys/$user";
	$f = "$dir/$date";

	if (file_exists($f)) {
		$key = file_get_contents($f);
		return $key;
	}else if(!$create) {
		return False;
	}	

	if (file_exists($dir) == false) {
		print("Creating directory $dir" . PHP_EOL);
		mkdir($dir, 0700, true);
	}

	$key = openssl_random_pseudo_bytes(32);
	file_put_contents($f, $key) or die("Couldn't save key");
	chmod($f, 0400);

	return $key;
}

function log_verify($name) {
	# touch user/timestamp
	$dir = "./verify_logs/$name";
	if (file_exists($dir) == false) {
		print("Creating directory $dir" . PHP_EOL);
		mkdir($dir, 0700, true);
	}

	$time = (new Datetime())->getTimestamp();
	touch("$dir/$time");
}

function get_verify($name) {
	# Return timestamp of last verify check
	
	$dir = "./verify_logs/$name";
	if (file_exists($dir) == false) {
		print("Creating directory $dir" . PHP_EOL);
		mkdir($dir, 0700, true);
	}
	$files = scandir($dir);
	if (sizeof($files) > 2) {
		#print("Last verified on " . end($files) . PHP_EOL);
		return end($files);
	} else {
		return 0;
	}
}

function can_verify($name) {
	$now = (new Datetime())->getTimestamp();
	$last = get_verify($name);

	return $now-$last > 60*60*100; # 1 hr
}

function generate_hash($username, $vote) {
	safe_string($username) or die("Bad username");
	safe_string($vote) or die("Bad vote");

	$raw_date = new DateTime();
	$date = $raw_date->format('y_m_d_h');
	
	$vote = new Vote($username, $vote, $date);

	$key = get_key($username, $date, true) or die("Could not find key" . PHP_EOL);

	$encrypted = encrypt(serialize($vote), $key);
	return json_encode([$username, $date, base64_encode($encrypted)]);
}

function validate_hash($hash, $debug=false) {
	list($username, $date, $vote_enc) = json_decode($hash);

	$key = get_key($username, $date, false) or die ("Malformed hash" . PHP_EOL);

	$key_b64 = base64_encode($key);
	$vote_enc = base64_decode($vote_enc);
	$vote_dec = decrypt($vote_enc, $key);
	if (can_verify($username)) {
		log_verify($username);

		$vote = unserialize($vote_dec);
		print($vote . PHP_EOL);
		if ($debug) {
			print("DEBUG: Encrypted with $key_b64" . PHP_EOL);
		}
	}else{
		print("You can only validate a vote once per hour");
	}
}


$h = generate_hash("username", "password");
#print("hash: " . $h . PHP_EOL );

print(validate_hash($h, true));

?>
