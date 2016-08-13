<?php
	ob_start();
	session_start();
	$filepath = "";

	include($filepath . "autoload.php");
	autoload($filepath);

	include($filepath . "session-status.php");	
	include($filepath . "session-school-redirect.php");

	try {
		include($filepath . "connect-to-db.php");

		if(isset($_POST['refaccount'])) {
			$first = $_POST['firstname'];
			$last = $_POST['lastname'];
			$email = $_POST['email'];
			$password = $_POST['cpassword'];

			try {
				#begin the Insert transaction
				$pdo->beginTransaction();

				# Insert school account fields into database
				$insert_sql = "INSERT INTO referees (Email, Password, SchoolID, First, Last) VALUES (:email, :password, :schoolid, :first, :last)";
				$insert_statement = $pdo->prepare($insert_sql);
				$insert_statement->bindValue(':email', $email);
				$insert_statement->bindValue(':password', $password);
				$insert_statement->bindValue(':schoolid', $schoolid);
				$insert_statement->bindValue(':first', $first);
				$insert_statement->bindValue(':last', $last);
				$insert_statement->execute();

				#commit the transaction
				$pdo->commit();
			} catch (Exception $e) {
				#rollback if there were any failures
				$pdo->rollback();
			}
		}
		
		# Get the referees to display
		$ref_sql = "SELECT CONCAT(First,' ',Last) AS name,Email FROM referees WHERE SchoolID=:schoolid ORDER BY Last;";
		$ref_statement = $pdo->prepare($ref_sql);
		$ref_statement->bindValue(':schoolid', $schoolid);
		$ref_statement->execute();

		$pdo = null;
	}
	catch (PDOException $e) {
		die($e->getMessage());
	}	

	if(isset($_POST['refemail'])) {
		$refemail = true;
		$refaccount = false;
		$message = $_POST['message'];
	} elseif(isset($_POST['refaccount'])) {
		$refaccount = true;
		$refemail = false;
		$email = $_POST['email'];
		$password = $_POST['cpassword'];
	} else {
		$refemail = false;
		$refaccount = false;
	}
?>

<!DOCTYPE html>
<html>

<?php
	head("Referees",$filepath);
?>

<body>
	<div class="container-fluid">
		<?php navbar('school',$filepath,$filepath . 'index.php',$schoolname);?>
	</div>
	<div class="container page-content">
		<?php if($refemail){?>
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-dismissible alert-info pull-left form-alert">
		  			<button type="button" class="close" data-dismiss="alert">
						<span aria-hidden="true">&times;</span>
						<span class="sr-only">Close</span>
					</button>
		  			<p><em>Example notification</em></p>
		  			<p>An email has been sent to the referees.</p>
				</div>
			</div>
		</div>
		<?php } elseif($refaccount) {?>
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-dismissible alert-info pull-left form-alert">
		  			<button type="button" class="close" data-dismiss="alert">
						<span aria-hidden="true">&times;</span>
						<span class="sr-only">Close</span>
					</button>
		  			<p>A new referee account has been created.</p>
				</div>
			</div>
		</div>
		<?php }?>
		
		<div class="row">
			<div class="col-md-4">
				<h2><strong>Referees</strong></h2>
			</div>
			<div class="col-md-6">
				<button class="btn btn-default pull-right menu-button" data-toggle="modal" data-target="#createref">
					<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> &nbsp;Create Referee Account
				</button>
			</div>
			<div class="col-md-2">
				<button class="btn btn-default pull-right menu-button" data-toggle="modal" data-target="#sendemail">
					<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> &nbsp;Send an Email
				</button>
			</div>
		</div>
		<div id="createref" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">
							<span aria-hidden="true">&times;</span>
							<span class="sr-only">Close</span>
						</button>
						<h4 class="modal-title">Create Referee Account</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-10 col-xs-push-1 col-sm-10 col-sm-push-1 col-md-10 col-md-push-1">
								<form class="form-horizontal" method="post" action="referees.php" id="signup-form">
									<fieldset>
										<div class="form-group">
											<label for="firstname" class="col-md-4 control-label">First Name</label>
											<div class="col-md-8">
												<input class="form-control" id="firstname" name="firstname" type="text" required>
											</div>
										</div>
										<div class="form-group">
											<label for="lastname" class="col-md-4 control-label">Last Name</label>
											<div class="col-md-8">
												<input class="form-control" id="lastname" name="lastname" type="text" required>
											</div>
										</div>
										<div class="form-group">
											<label for="email" class="col-md-4 control-label">Email</label>
											<div class="col-md-8">
												<input class="form-control" id="email" name="email" type="email" required>
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
										<div class="col-md-8 col-md-push-4">
											<button type="submit" class="btn btn-success">Create Account</button>
										</div>
										<input type="hidden" id="refaccount" name="refaccount" value="true"/>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="sendemail" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">
							<span aria-hidden="true">&times;</span>
							<span class="sr-only">Close</span>
						</button>
						<h4 class="modal-title">Send Email</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-10 col-xs-push-1 col-sm-10 col-sm-push-1 col-md-10 col-md-push-1">
								<p>An email will be sent to all of the referees.</p>
								<form class="form-horizontal" method="post" action="referees.php">
									<fieldset>
										<div class="form-group">
											<div class="col-md-12">
												<textarea class="form-control" id="message" name="message" rows="18" style="resize: none;"></textarea>
											</div>
										</div>
										<div class="col-md-6 col-md-push-3">
											<button type="submit" class="btn btn-success btn-block" data-toggle="tooltip" data-placement="left" title="PHASE II Functionality">Send Email</button>
										</div>
										<input type="hidden" id="refemail" name="refemail" value="true"/>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<table class="table table-striped table-hover table-responsive">
					<thead>
						<tr>
							<th>Name</th>
							<th>Email</th>
						</tr>
					</thead>
					<tbody>
						
						<?php while($row = $ref_statement->fetch()) {?>
						<tr>
							<td><?php echo $row['name'];?></td>
							<td><?php echo $row['Email'];?></td>
						</tr>
						<?php }?>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</body>
