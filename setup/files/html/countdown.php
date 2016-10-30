<html>
  <head>
    <script> setTimeout(function() { location.reload(); }, 10000); </script>
  </head>
<?php
require_once("util.php");
$now = time();
if ($has_ended) {
  print("<div style=\"text-align: center\">Registration is closed</div>");
}else{
  $delta = $end - $now;
  $days = intdiv($delta, (60*60*24));
  $hours = intdiv($delta, (60*60))%24;
  $min = intdiv($delta, (60))%60;
  print("<div style=\"text-align: center\">$days days, $hours hours, and $min minutes until registration closes</div>");
}
?>
</html>
