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
	<div class="container">
		<p class="text-danger">
			<em>
				**Currently, the only university in the LeagueShark database is a fictional university called Oceanside Tech. You can create a new fictional university account, but you will not be able to sign up as a player at Oceanside Tech because any player attempting to sign up is validated against a list of pre-loaded eligible students at whatever university they choose. I recommend clicking Log In on the home page to explore the site.
			</em>
		</p>
	</div>
</body>
</html>
