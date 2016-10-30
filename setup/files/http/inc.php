<?php
  if (!isset($_GET["p"])) die("error");
  if (strpos($_GET["p"], "base64") !== false) die("Hacking attempt detected!");
  include($_GET["p"] . ".php");
?>
