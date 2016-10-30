<?php

class Voter {
  var $name;
  var $addr;
  var $affil;
  var $zip;
  var $log;
  var $show_log = false;

  function Voter($name, $addr, $affil, $zip) {
    $this->name = $name;
    $this->addr = $addr;
    $this->affil = $affil;
    $this->zip = $zip;
    $this->log = "./data/$name/log";
  }

  function read_log() {
    if ($this->log && file_exists($this->log)) {
      return end(file($this->log));
    } else {
      return "File " . $this->log . " does not exist";
    }
  }

  function __toString() {
    $out = "Voter registration for {$this->name}:\n\tAddress: {$this->addr}" .
      "\n\tAffiliation: {$this->affil}\n\tZip: {$this->zip}";

    if ($this->show_log)
      $out .= "\n\nLast update to voter:\n" . $this->read_log();

    return $out;
  }
}

?>
