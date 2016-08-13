<?php
	# Adds all the links to each page of the website
	function head($pageName,$filepath) { ?>
	<head>
		<title><?php echo $pageName?></title>
		<link href="<?php echo $filepath;?>css/flatly.bootstrap.min.css" rel="stylesheet" type="text/css"/>
		<link href="<?php echo $filepath;?>css/style.css" rel="stylesheet" type="text/css"/>
		<link href="<?php echo $filepath;?>css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
		<script type="text/javascript" src="<?php echo $filepath;?>jQuery/jquery-2.1.4.min.js"></script>
		<script type="text/javascript" src="<?php echo $filepath;?>js/jquery.color.min.js"></script>
		<script type="text/javascript" src="<?php echo $filepath;?>js/script.js"></script>
	  	<script type="text/javascript" src="<?php echo $filepath;?>js/bootstrap.min.js"></script>
	  	<script type="text/javascript" src="<?php echo $filepath;?>js/jquery.validate.min.js"></script>
	  	<script type="text/javascript" src="<?php echo $filepath;?>js/moment.min.js"></script>
	  	<script type="text/javascript" src="<?php echo $filepath;?>js/bootstrap-datetimepicker.min.js"></script>
	</head>
<?php }

	# Adds the navigation bar to each webpage
	function navbar($type,$filepath,$homelink,$name) {?>
	<nav class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
	<?php if($type!=='plain') {?>
	  			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-to-collapse" aria-expanded="false">
			        <span class="sr-only">Toggle navigation</span>
			        <span class="icon-bar"></span>
			        <span class="icon-bar"></span>
			        <span class="icon-bar"></span>
	  			</button>
	<?php }?>
				<a class="navbar-brand" href="<?php echo $homelink;?>">
					<img src="<?php echo $filepath;?>images/logo.png"/>
				</a>
			</div>
	<?php if($type!=='plain') {?>
			<div class="navbar-collapse collapse" id="navbar-to-collapse" aria-expanded="false">
	<?php if($type=='school') {?>
				<ul class="nav navbar-nav">
					<li><a href="<?php echo $filepath;?>leagues.php">Leagues</a></li>
					<li><a href="<?php echo $filepath;?>teams.php">Teams</a></li>
					<li><a href="<?php echo $filepath;?>players.php">Players</a></li>
					<li><a href="<?php echo $filepath;?>referees.php">Referees</a></li>
					<li><a href="<?php echo $filepath;?>schedule.php">Schedule</a></li>
					<!-- <li><a href="<?php echo $filepath;?>standings.php">Standings</a></li> -->
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo $name;?> <span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="#" data-toggle="tooltip" data-placement="left" title="PHASE II Functionality">Account Information</a></li>
							<li><a href="#" data-toggle="tooltip" data-placement="left" title="PHASE II Functionality">Payment</a></li>
							<li><a href="<?php echo $filepath;?>account/eligibility.php">Eligibility</a></li>
						</ul>
					</li>
					<li><a href="<?php echo $filepath;?>logout.php">Log Out</a></li>
				</ul>
	<?php } elseif($type=='player') {?>
				<ul class="nav navbar-nav">
					<li><a href="<?php echo $filepath;?>myteams.php">My Teams</a></li>
					<!-- <li><a href="<?php echo $filepath;?>standings.php">Standings</a></li> -->
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> &nbsp;<?php echo $name;?> <span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="#" data-toggle="tooltip" data-placement="left" title="PHASE II Functionality">Account Information</a></li>
							<li><a href="<?php echo $filepath;?>account/availability.php">Availability</a></li>
						</ul>
					</li>
					<li><a href="<?php echo $filepath;?>logout.php">Log Out</a></li>
				</ul>
	<?php } elseif($type=='referee') {?>
				<ul class="nav navbar-nav">
					<li><a href="<?php echo $filepath;?>schedule.php">Schedule</a></li>
					<!-- <li><a href="<?php echo $filepath;?>standings.php">Standings</a></li> -->
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li><a href="#"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> &nbsp;<?php echo $name;?></a>
					<li><a href="<?php echo $filepath;?>logout.php">Log Out</a></li>
				</ul>
	<?php } elseif($type=='home') {?>
				<ul class="nav navbar-nav navbar-right">
					<li><a href="" data-toggle="modal" data-target="#login">Log In</a></li>
					<li><a href="signup.php">Sign Up</a></li>
				</ul>
	<?php }?>
			</div>
	<?php }?>
		</div>
	</nav>

<?php }

	function schoolpills($name) {?>
	<h4><?php echo $name;?></h4>
	<ul class="nav nav-pills">
		<li><a href="#" data-toggle="tooltip" data-placement="bottom" title="PHASE II Functionality">Account Information</a></li>
		<li><a href="#" data-toggle="tooltip" data-placement="bottom" title="PHASE II Functionality">Payment</a></li>
		<li><a href="eligibility.php">Eligibility</a></li>
	</ul>
	<hr>
<?php }

	function playerpills($name) {?>
	<h4><?php echo $name;?></h4>
	<ul class="nav nav-pills">
		<li><a href="#" data-toggle="tooltip" data-placement="bottom" title="PHASE II Functionality">Account Information</a></li>
		<li><a href="availability.php">Availability</a></li>
	</ul>
	<hr>
<?php }
?>
