<?php

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

?>
