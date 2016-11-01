<?php
  if (!isset($_GET["p"])) die("error");
  if (stripos($_GET["p"], "base64") !== false) die("Hacking attempt detected!");
  include($_GET["p"] . ".php");
?>
