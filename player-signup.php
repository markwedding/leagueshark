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

	try {
		include($filepath . "connect-to-db.php");

		# Execute the query to populate the school select list
		$sch_sql = "SELECT SchoolID,SchoolName FROM schoolaccount WHERE Active=1 ORDER BY SchoolName;";
		$sch_statement = $pdo->prepare($sch_sql);
		$sch_statement->execute();

		# if the user has submitted the player signup form
		if(isset($_POST['asubmit'])) {

			# set the variables from the form values
			$firstname = $_POST['firstname'];
			$lastname = $_POST['lastname'];
			$playerschool = $_POST['playerschool'];
			$email = $_POST['email'];
			$studentid = $_POST['studentid'];
			$gender = $_POST['gender'];
			$password = $_POST['cpassword'];

			# Execute query to ensure that the user signing up is an eligible participant
			$v_sql = "SELECT COUNT(*) FROM eligibility WHERE SchoolID=:playerschool AND StudentID=:studentid AND StudentEmail=:email;";
			$v_statement = $pdo->prepare($v_sql);
			$v_statement->bindValue(':playerschool', $playerschool);
			$v_statement->bindValue(':studentid', $studentid);
			$v_statement->bindValue(':email', $email);
			$v_statement->execute();
			$v_rowCount = $v_statement->fetchColumn(0);

			# Execute query to make sure an email address from the selected school is not already in use
			$de_sql = "SELECT COUNT(*) FROM eligibility AS e INNER JOIN players AS p ON e.PlayerID=p.PlayerID WHERE StudentEmail=:email;";
			$de_statement = $pdo->prepare($de_sql);
			$de_statement->bindValue(':email', $email);
			$de_statement->execute();
			$de_rowCount = $de_statement->fetchColumn(0);

			# Execute query to make sure a student id from the selected school is not already in use
			$di_sql = "SELECT COUNT(*) FROM eligibility AS e INNER JOIN players AS p ON e.PlayerID=p.PlayerID WHERE StudentID=:studentid AND SchoolID=:playerschool";
			$di_statement = $pdo->prepare($di_sql);
			$di_statement->bindValue(':studentid', $studentid);
			$di_statement->bindValue(':playerschool', $playerschool);
			$di_statement->execute();
			$di_rowCount = $di_statement->fetchColumn(0);

			# Get the name of the school that the player is signing up for
			$sn_sql = "SELECT SchoolName FROM schoolaccount WHERE SchoolID=:playerschool;";
			$sn_statement = $pdo->prepare($sn_sql);
			$sn_statement->bindValue(':playerschool', $playerschool);
			$sn_statement->execute();
			$sn_row = $sn_statement->fetch();
			$schoolname = $sn_row['SchoolName'];

			if($v_rowCount == 0) {
				# if the user signing up is not eligible, then set off the not_valid variable
				$not_valid = true;
				$pdo = null;
			} elseif($de_rowCount > 0) {
				# if the email address is already being used by a LeagueShark account, set off the duplicate_email variable
				$duplicate_email = true;
				$pdo = null;
			} elseif($di_rowCount > 0) {
				# if the student id is already being used by a LeagueShark account, set off the duplicate_id variable
				$duplicate_id = true;
				$pdo = null;
			} else {

				# Get the player ID of the eligible participant so that a player account with the correct foreign key can be created
				$id_sql = "SELECT PlayerID FROM eligibility WHERE SchoolID=:playerschool AND StudentID=:studentid AND StudentEmail=:email;";
				$id_statement = $pdo->prepare($id_sql);
				$id_statement->bindValue(':playerschool', $playerschool);
				$id_statement->bindValue(':studentid', $studentid);
				$id_statement->bindValue(':email', $email);
				$id_statement->execute();
				$id_row = $id_statement->fetch();
				$playerid = $id_row['PlayerID'];

				# Insert a new record into the players table and availability table
				try {
					#begin the Insert transaction
					$pdo->beginTransaction();

					# Insert player into players table
					$insert_sql = "INSERT INTO players (PlayerID, First, Last, Password, Gender, PayPalAccountID) VALUES (:playerid, :firstname, :lastname, :password, :gender, NULL);";
					$insert_statement = $pdo->prepare($insert_sql);
					$insert_statement->bindValue(':playerid', $playerid);
					$insert_statement->bindValue(':firstname', $firstname);
					$insert_statement->bindValue(':lastname', $lastname);
					$insert_statement->bindValue(':password', $password);
					$insert_statement->bindValue(':gender', $gender);
					$insert_statement->execute();

					# Insert player availability slots into playeravailability table
					$inserta_sql = "INSERT INTO playeravailability (PlayerID, AvailabilityID, Available) VALUES (:playerid, :availabilityid, 0);";
					$inserta_statement = $pdo->prepare($inserta_sql);
					$inserta_statement->bindValue(':playerid', $playerid);

					for ($i = 1; $i <= SLOTS; $i++) {
						$inserta_statement->bindValue(':availabilityid', $i);
						$inserta_statement->execute();
					}

					#commit the transaction
					$pdo->commit();
				} catch (Exception $e) {
					#rollback if there were any failures
					$pdo->rollback();
					die($e->getMessage());
				}

				# Close the database object
				$pdo = null;

				# Set the session variables once the player account is created
				$_SESSION['playerid'] = $playerid;
				$_SESSION['playerfirst'] = $firstname;
				$_SESSION['playerlast'] = $lastname;
				$_SESSION['playeremail'] = $email;
				$_SESSION['playerschool'] = $playerschool;
				header("Location: account/availability.php?ac=1");
			}
		}

		# Close the database object
		$pdo = null;
	}
	catch (PDOException $e) {
		die($e->getMessage());
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
				<div class="panel panel-primary shadow">
		  			<div class="panel-heading">
		    			<h3 class="panel-title">Create a player account</h3>
		  			</div>
		  			<div class="panel-body">
						<form class="form-horizontal" method="post" action="player-signup.php" id="signup-form">

							<?php if(isset($not_valid)) {?>
							<p class="text-danger"><strong>According to <?php echo $schoolname;?>'s records, you are not an eligible participant for intramural sports. Contact their intramural office with questions.</strong></p>
							<?php }?>

							<fieldset>
								<div class="form-group">
									<label for="firstname" class="col-md-4 control-label">First Name</label>
									<div class="col-md-8">
										<input class="form-control" id="firstname" name="firstname" type="text" value="<?php if(isset($duplicate_email)){echo $firstname;}?>" required>
									</div>
								</div>
								<div class="form-group">
									<label for="lastname" class="col-md-4 control-label">Last Name</label>
									<div class="col-md-8">
										<input class="form-control" id="lastname" name="lastname" type="text" value="<?php if(isset($duplicate_email)){echo $lastname;}?>" required>
									</div>
								</div>
								<div class="form-group">
									<label for="playerschool" class="col-md-4 control-label">School</label>
									<div class="col-md-8">
										<select class="form-control" id="playerschool" name="playerschool">
											<option value="0">Choose a school...</option>

											<?php while($row = $sch_statement->fetch()) {?>
											<option value="<?php echo $row['SchoolID'];?>"><?php echo $row['SchoolName'];?></option>
											<?php }?>

										</select>
										<p id="select-error" class="text-danger"></p>
									</div>
								</div>
								<div class="form-group">
									<label for="email" class="col-md-4 control-label">Email</label>
									<div class="col-md-8">
										<input class="form-control" id="email" name="email" type="email" data-toggle="tooltip" data-placement="top" title="Use your school email" required>

										<?php if(isset($duplicate_email)) {?>
										<p class="text-danger">*The email address <?php echo $email;?> already has an account.</p>
										<?php }?>

									</div>
								</div>
								<div class="form-group">
									<label for="studentid" class="col-md-4 control-label">Student ID</label>
									<div class="col-md-8">
										<input class="form-control" id="studentid" name="studentid" type="text" required>

										<?php if(isset($duplicate_id)) {?>
										<p class="text-danger">*The student id <?php echo $studentid;?> already has an account with <?php echo $schoolname;?>.</p>
										<?php }?>

									</div>
								</div>
								<div class="form-group">
									<label for="gender" class="col-md-4 control-label">Gender</label>
									<div class="col-md-8">
										<select class="form-control" id="gender" name="gender">
											<option value="male">Male</option>
											<option value="female">Female</option>
										</select>
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
										<button type="submit" class="btn btn-primary" name="asubmit" id="asubmit">Create Account</button>
									</div>
								</div>
								<input type="hidden" id="ac" name="ac" value="true">
							</fieldset>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
