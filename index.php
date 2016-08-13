<?php
	ob_start();
	session_start();
	$filepath = "";

	include($filepath . "autoload.php");
	autoload($filepath);

	if(isset($_SESSION['schoolid'])) {
		header("Location: leagues.php");
	} elseif(isset($_SESSION['playerid'])) {
		header("Location: myteams.php");
	} elseif(isset($_SESSION['refemail'])) {
		header("Location: schedule.php");
	}

	if(isset($_POST['lsubmit'])) {
		$email = $_POST['email'];
		$password = $_POST['password'];

		try {
			include($filepath . "connect-to-db.php");

			# Check to see if the email and password are connected to a school account
			$sch_sql = "SELECT COUNT(*) FROM schoolaccount WHERE Email=:email AND Password=:password;";
			$sch_statement = $pdo->prepare($sch_sql);
			$sch_statement->bindValue(':email', $email);
			$sch_statement->bindValue(':password', $password);
			$sch_statement->execute();
			$sch_rowCount = $sch_statement->fetchColumn(0);

			# Check to see if the email and password are connected to a player account
			$pl_sql = "SELECT COUNT(*) FROM eligibility AS e INNER JOIN players AS p ON e.PlayerID=p.PlayerID WHERE StudentEmail=:email AND Password=:password;";
			$pl_statement = $pdo->prepare($pl_sql);
			$pl_statement->bindValue(':email', $email);
			$pl_statement->bindValue(':password', $password);
			$pl_statement->execute();
			$pl_rowCount = $pl_statement->fetchColumn(0);

			# Check to see if the email and password are connected to a referee account
			$ref_sql = "SELECT COUNT(*) FROM referees WHERE Email=:email AND Password=:password;";
			$ref_statement = $pdo->prepare($ref_sql);
			$ref_statement->bindValue(':email', $email);
			$ref_statement->bindValue(':password', $password);
			$ref_statement->execute();
			$ref_rowCount = $ref_statement->fetchColumn(0);

			if($sch_rowCount == 1) {
				$ss_sql = "SELECT * FROM schoolaccount WHERE Email=:email AND Password=:password;";
				$ss_statement = $pdo->prepare($ss_sql);
				$ss_statement->bindValue(':email', $email);
				$ss_statement->bindValue(':password', $password);
				$ss_statement->execute();
				$ss_row = $ss_statement->fetch(PDO::FETCH_ASSOC);

				$_SESSION['schoolid'] = $ss_row['SchoolID'];
				$_SESSION['schoolname'] = $ss_row['SchoolName'];
				$pdo = null;
				header('Location: leagues.php');
			} elseif ($pl_rowCount == 1) {
				$ps_sql = "SELECT * FROM eligibility AS e INNER JOIN players AS p ON e.PlayerID=p.PlayerID WHERE StudentEmail=:email AND Password=:password;";
				$ps_statement = $pdo->prepare($ps_sql);
				$ps_statement->bindValue(':email', $email);
				$ps_statement->bindValue(':password', $password);
				$ps_statement->execute();
				$ps_row = $ps_statement->fetch(PDO::FETCH_ASSOC);

				$_SESSION['playerid'] = $ps_row['PlayerID'];
				$_SESSION['playerfirst'] = $ps_row['First'];
				$_SESSION['playerlast'] = $ps_row['Last'];
				$_SESSION['playeremail'] = $ps_row['StudentEmail'];
				$_SESSION['playerschool'] = $ps_row['SchoolID'];
				$pdo = null;
				header('Location: myteams.php');
			} elseif ($ref_rowCount == 1) {
				$rs_sql = "SELECT * FROM referees WHERE Email=:email AND Password=:password;";
				$rs_statement = $pdo->prepare($rs_sql);
				$rs_statement->bindValue(':email', $email);
				$rs_statement->bindValue(':password', $password);
				$rs_statement->execute();
				$rs_row = $rs_statement->fetch(PDO::FETCH_ASSOC);

				$_SESSION['refemail'] = $rs_row['Email'];
				$_SESSION['reffirst'] = $rs_row['First'];
				$_SESSION['reflast'] = $rs_row['Last'];
				$_SESSION['refschool'] = $rs_row['SchoolID'];
				$pdo = null;
				header('Location: schedule.php');
			} else {
				header('Location: index.php?error=failed_login');
			}

			$pdo = null;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	# Set the error message if an unauthorized user tries to access a page
	if(isset($_GET['error'])) {
		$error = true;
		if($_GET['error']=='session') {
			$error_message = 'Please log in or create an account to use LeagueShark.';
		} elseif($_GET['error']=='session_player') {
			$error_message = 'You must be logged in on a player account to view this page.';
		} elseif($_GET['error']=='session_school') {
			$error_message = 'You must be logged in on a school account to view this page.';
		} elseif($_GET['error']=='session_rs') {
			$error_message = 'You must be logged in on a school or referee account to view this page.';
		} elseif($_GET['error']=='failed_login') {
			$error_message = 'Username or password incorrect';
		}
	}
?>

<!DOCTYPE html>
<html>

<?php
	head("LeagueShark","");
?>

<body>
	<div class="container-fluid">
		<?php navbar('home','','#','');?>
		<div id="login" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">
							<span aria-hidden="true">&times;</span>
							<span class="sr-only">Close</span>
						</button>
						<h4 class="modal-title">Log In</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-10 col-xs-push-1 col-sm-10 col-sm-push-1 col-md-10 col-md-push-1">
								<form method="post" action="index.php" id="loginform">
									<div class="form-group">
										<label class="control-label" for="email">Email Address</label>
										<input type="email" class="form-control" id="email" name="email" required data-toggle="tooltip" data-placement="left" title="Enter bsmith@tech.edu for school account and justinb31@tech.edu for player account"/>
										<span class="text-danger"><small></small></span>
									</div>
									<div class="form-group">
										<label class="control-label" for="password">Password</label>
										<input type="password" class="form-control" id="password" name="password" required data-toggle="tooltip" data-placement="left" title="Enter ocean123"/>
										<span class="text-danger"><small></small></span>
									</div>
									<button type="submit" class="btn btn-success btn-block" id="lsubmit" name="lsubmit">Log In</button>
								</form>
								<hr class="separator">
								<p class="text-center" data-html="true"><br>Don't have an account?<a href="signup.php">&nbsp;Sign up here</a></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="container">
		<?php if(isset($error)){?>
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-dismissible alert-danger form-alert" style="margin-top: 30px;">
		  			<button type="button" class="close" data-dismiss="alert">
						<span aria-hidden="true">&times;</span>
						<span class="sr-only">Close</span>
					</button>
		  			<p><?php echo $error_message;?></p>
				</div>
			</div>
		</div>
		<?php }?>
		<div class="jumbotron">
			<div class="jumbotron-content text-center">
				<h1><strong>No more hassle.</strong></h1>
				<br>
				<p>With LeagueShark, managing your university's intramural leagues just got a whole lot easier.</p>
				<br>
				<p><a href="signup.php" class="btn btn-primary btn-lg">Get Started &raquo;</a></p>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3">
				<img src="images/homepage/soccer.jpg" class="img-responsive img-rounded"/>
			</div>
			<div class="col-md-3">
				<img src="images/homepage/basketball.jpg" class="img-responsive img-rounded"/>
			</div>
			<div class="col-md-3">
				<img src="images/homepage/volleyball.jpg" class="img-responsive img-rounded"/>
			</div>
			<div class="col-md-3">
				<img src="images/homepage/softball.jpg" class="img-responsive img-rounded"/>
			</div>
		</div>
		<div class="container">
			<h4 class="text-danger text-center">
				<em>**Warning: LeagueShark is a showcase project, not a real service. Click Log In to explore the site. Anything that says PHASE II Functionality has not been implemented.</em>
			</h4>
		</div>
	</div>
</body>
</html>
