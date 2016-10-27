<?php
include "util.php";
include "voter.php";

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
