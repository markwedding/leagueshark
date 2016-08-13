<?php
	ob_start();
	include("autoload.php");
	autoload('');
?>

<!DOCTYPE html>
<html>

<?php head("LeagueShark: Sign Up",""); ?>

<body>
	<div class="container-fluid">
		<?php navbar('plain','','index.php','');?>
	</div>
	<div class="container page-content">
		<div class="row">
			<div class="col-md-6 col-md-push-3">
				<div class="panel panel-primary shadow">
		  			<div class="panel-heading">
		    			<h3 class="panel-title">What type of account?</h3>
		  			</div>
		  			<div class="panel-body">
		    			<div class="row">
		    				<div class="col-md-6">
			    				<div class="panel panel-primary signup-choice pointer">
			    					<div class="panel-body text-center">
				    					<h4><strong>Administrator</strong></h4>
				    					<p>Sign up a new university for LeagueShark.</p>
			    					</div>
		    					</div>
	    					</div>
	    					<div class="col-md-6">
			    				<div class="panel panel-primary signup-choice pointer">
			    					<div class="panel-body text-center">
				    					<h4><strong>Player</strong></h4>
				    					<p>Sign up for your university's intramurals.</p>
			    					</div>
		    					</div>
	    					</div>
	    				</div>
		  			</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>