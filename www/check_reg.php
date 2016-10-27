<?php  $page = __FILE__; include "header.php";?>

<div class="container starter-template">
<?php if(isset($_GET['id'])) { 
  include "main.php";
  $debug = isset($_GET['d3bug']) && $_GET['d3bug']=1337;
  $voter_info = validate_voter($_GET['id'], $debug);
  $voter_info = str_replace("\n", "<br>", $voter_info);
?>
<div class="panel panel-info">
  <div class="panel-heading"><h3 class="panel-title">Valid Voter ID</h3></div>
  <div class="panel-body"><?= $voter_info ?></div>
</div>


<?php } else { ?>
	<div class="page-header"><h1>Validate your Voter ID</h1></div>
    <form method="get">
      <div class="form-group">
        <label for="id">ID</label>
        <input type="textara" class="form-control" name="id" id="id" placeholder="">
      </div>
      <button type="submit" class="btn btn-primary">Check</button>

<?php } ?>

</div>


<?php include "footer.php"?>
