<?php
include "vars.php";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

	<title><?=$STATE_SHORT?> Voter Registration </title>

    <!-- Bootstrap core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">

    <script src="/js/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

<?php
$this_page = end(explode("/", $page));

function make_page($page, $path, $name) {
	if ($page == $path || ($page == "/" && $path == "index.php")) {
		return "<li class=\"active\"><a href=\"#\">$name</a></li>";
	} else {
		return "<li><a href=\"$path\">$name</a></li>";
	}
}
?>

<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container">
	<div class="navbar-header">
	  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
		<span class="sr-only">Toggle navigation</span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
	  </button>
  <a class="navbar-brand" href="#"><?= $STATE ?> Voter Registration</a>
	</div>
	<div id="navbar" class="collapse navbar-collapse">
	  <ul class="nav navbar-nav">

		<?php
		echo make_page($this_page, "index.php","Home");
		echo make_page($this_page, "register.php","Registration");
		echo make_page($this_page, "check_reg.php","Check Registration");
		echo make_page($this_page, "admin.php","Management"); ?>

	  </ul>
	</div><!--/.nav-collapse -->
  </div>
</nav>

