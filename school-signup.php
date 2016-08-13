<?php
	ob_start();
	session_start();
	$filepath = "";

	include($filepath . "autoload.php");
	autoload($filepath);

	# redirect to respective "home" pages if users are already logged on to LeagueShark
	if(isset($_SESSION['schoolid'])) {
		header("Location: leagues.php");
	} elseif(isset($_SESSION['playerid'])) {
		header("Location: myteams.php");
	} elseif(isset($_SESSION['refid'])) {
		header("Location: schedule.php");
	}

	if(isset($_POST['submitschool'])) {
		$submitschool = true;
		$schoolname = $_POST['schoolname'];

		try {
			include($filepath . "connect-to-db.php");

			$sql = "SELECT COUNT(*) FROM schoolaccount WHERE SchoolName=:schoolname";
			$statement = $pdo->prepare($sql);
			$statement->bindValue(':schoolname', $schoolname);
			$statement->execute();
			$rowCount = $statement->fetchColumn(0);

			if($rowCount > 0) {
				$duplicateschool = true;
				$pdo = null;
			}

			$pdo = null;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	} elseif(isset($_POST['accountsubmit'])) {
		$schoolname = $_POST['schoolname'];
		$first = $_POST['firstname'];
		$last = $_POST['lastname'];
		$email = $_POST['email'];
		$phone = $_POST['phonenumber'];
		$password = $_POST['cpassword'];

		try {
			include($filepath . "connect-to-db.php");

			$sql = "SELECT COUNT(*) FROM schoolaccount WHERE Email=:email";
			$statement = $pdo->prepare($sql);
			$statement->bindValue(':email', $email);
			$statement->execute();
			$rowCount = $statement->fetchColumn(0);

			if($rowCount > 0) {
				$duplicateemail = true;
				$pdo = null;
			} else {
				try {
					#begin the Insert transaction
					$pdo->beginTransaction();

					# Insert school account fields into database
					$isql = "INSERT INTO schoolaccount (SchoolName, First, Last, Email, Phone, Password, Active) VALUES (:schoolname, :first, :last, :email, :phone, :password, 1)";
					$istatement = $pdo->prepare($isql);
					$istatement->bindValue(':schoolname', $schoolname);
					$istatement->bindValue(':first', $first);
					$istatement->bindValue(':last', $last);
					$istatement->bindValue(':email', $email);
					$istatement->bindValue(':phone', $phone);
					$istatement->bindValue(':password', $password);
					$istatement->execute();
					$lastID = $pdo->lastInsertId();

					#commit the transaction
					$pdo->commit();
				} catch (Exception $e) {
					#rollback if there were any failures
					$pdo->rollback();
				}

				$pdo = null;

				$_SESSION['schoolid'] = $lastID;
				$_SESSION['schoolname'] = $schoolname;
				header("Location: account/eligibility.php?ac=1");
			}
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	if(isset($duplicateemail)) {
		$de=true;
	} else {
		$de=false;
	}

?>

<!DOCTYPE html>
<html>

<?php
	head("LeagueShark: Sign Up",$filepath);
?>

<body>
	<div class="container-fluid">
		<?php navbar('plain',$filepath,'index.php','');?>
	</div>
	<div class="container page-content">
		<div class="row">
			<div class="col-md-6 col-md-push-3">

				<?php if((isset($submitschool) and !isset($duplicateschool)) or isset($_POST['accountsubmit'])) {?>

					<div class="panel panel-primary shadow">
			  			<div class="panel-heading">
			    			<h3 class="panel-title">Create a school account</h3>
			  			</div>
			  			<div class="panel-body">
							<form class="form-horizontal" method="post" action="school-signup.php" id="signup-form">
								<fieldset>
									<div class="form-group">
										<label for="firstname" class="col-md-4 control-label">First Name</label>
										<div class="col-md-8">
											<input class="form-control" id="firstname" name="firstname" type="text" value="<?php if($de){echo $first;}?>" required>
										</div>
									</div>
									<div class="form-group">
										<label for="lastname" class="col-md-4 control-label">Last Name</label>
										<div class="col-md-8">
											<input class="form-control" id="lastname" name="lastname" type="text" value="<?php if($de){echo $last;}?>" required>
										</div>
									</div>
									<div class="form-group">
										<label for="email" class="col-md-4 control-label">Email</label>
										<div class="col-md-8">
											<input class="form-control" id="email" name="email" type="email" required>

											<?php if($de) {?>
											<p class="text-danger">*The email address <?php echo $email;?> is already taken. Please enter a different one.</p>
											<?php }?>

										</div>
									</div>
									<div class="form-group">
										<label for="phonenumber" class="col-md-4 control-label">Phone Number</label>
										<div class="col-md-8">
											<input class="form-control" id="phonenumber" name="phonenumber" type="text" value="<?php if($de){echo $phone;}?>" required>
										</div>
									</div>
									<hr>
									<div class="form-group">
										<label for="password" class="col-md-4 control-label">Password</label>
										<div class="col-md-8">
											<input class="form-control" id="password" name="password" type="password" required>
										</div>
									</div>
									<div class="form-group">
										<label for="cpassword" class="col-md-4 control-label">Confirm Password</label>
										<div class="col-md-8">
											<input class="form-control" id="cpassword" name="cpassword" type="password" required>
											<p id="password-error" class="text-danger"></p>
										</div>
									</div>
									<div class="form-group">
										<div class="col-md-8 col-md-push-4">
											<button type="submit" class="btn btn-primary" id="accountsubmit" name="accountsubmit">Create Account</button>
										</div>
									</div>
									<input type="hidden" id="schoolname" name="schoolname" value="<?php echo $schoolname;?>">
									<input type="hidden" id="ac" name="ac" value="true">
								</fieldset>
							</form>
						</div>
					</div>

				<?php } else {?>

					<div class="panel panel-primary shadow">
			  			<div class="panel-heading">
			    			<h3 class="panel-title">Create a school account</h3>
			  			</div>
			  			<div class="panel-body">
							<form id="schoolform" method="post" action="school-signup.php">
								<fieldset>
									<div class="form-group">
										<div class="row">
											<label for="schoolname" class="col-md-8 col-md-push-2 control-label">Enter the name of the school you are creating an account for:</label>
										</div>
										<div class="row">
											<div class="col-md-8 col-md-push-2">
												<input class="form-control" id="schoolname" name="schoolname" type="text" required>

												<?php if(isset($duplicateschool)) {?>
												<p class="text-danger">*The school name <?php echo $schoolname;?> already exists. Please enter a different one.</p>
												<?php }?>

											</div>
										</div>
									</div>
									<div class="form-group">
										<div class="row">
											<div class="col-md-8 col-md-push-2">
												<button type="submit" class="btn btn-primary btn-block" name="submitschool">Continue &raquo;</button>
											</div>
										</div>
									</div>
								</fieldset>
							</form>
						</div>
					</div>

				<?php }?>

			</div>
		</div>
	</div>
</body>
