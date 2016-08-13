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
		
		if(isset($_POST['lc'])) {
			$lc = true;
			$leaguename = $_POST['leaguename'];
			$sport = $_POST['sport'];
			$gender = $_POST['gender'];
			$signup = $_POST['signup'];
			$availability = $_POST['availability'];
			$teamlimit = $_POST['teamlimit'];

			try {
				#begin the Insert transaction
				$pdo->beginTransaction();

				# Insert school account fields into database
				$insert_sql = "INSERT INTO leagues (SchoolID, Sport, LeagueName, SignUpDeadline, AvailabilityDeadline, Gender, TeamLimit) VALUES (:schoolid, :sport, :leaguename, :signup, :availability, :gender, :teamlimit)";
				$insert_statement = $pdo->prepare($insert_sql);
				$insert_statement->bindValue(':schoolid', $schoolid);
				$insert_statement->bindValue(':sport', $sport);
				$insert_statement->bindValue(':leaguename', $leaguename);
				$insert_statement->bindValue(':signup', $signup);
				$insert_statement->bindValue(':availability', $availability);
				$insert_statement->bindValue(':gender', $gender);
				$insert_statement->bindValue(':teamlimit', $teamlimit);
				$insert_statement->execute();

				#commit the transaction
				$pdo->commit();
			} catch (Exception $e) {
				#rollback if there were any failures
				$pdo->rollback();
			}
		} else {
			$lc = false;
		}
		$lg_sql = "SELECT * FROM leagues WHERE SchoolID=:schoolid ORDER BY SignUpDeadline ASC;";
		$lg_statement = $pdo->prepare($lg_sql);
		$lg_statement->bindValue(':schoolid', $schoolid);
		$lg_statement->execute();

		$pdo = null;
	}
	catch (PDOException $e) {
		die($e->getMessage());
	}
?>

<!DOCTYPE html>
<html>

<?php
	head("Leagues",$filepath);
?>

<body>
	<div class="container-fluid">
		<?php navbar('school',$filepath,$filepath . 'index.php',$schoolname);?>
	</div>
	<div class="container page-content">
		<?php if($lc){?>
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-dismissible alert-info pull-left form-alert">
		  			<button type="button" class="close" data-dismiss="alert">
						<span aria-hidden="true">&times;</span>
						<span class="sr-only">Close</span>
					</button>
		  			<p><strong><?php echo $leaguename;?></strong> has been added.</p>
				</div>
			</div>
		</div>
		<?php }?>
		<div class="row">
			<div class="col-md-6">
				<h2><strong>Leagues</strong></h2>
			</div>
			<div class="col-md-6">
				<button class="btn btn-default pull-right menu-button" data-toggle="modal" data-target="#addleague">
					<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add New League
				</button>
			</div>
		</div>
		<div id="addleague" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">
							<span aria-hidden="true">&times;</span>
							<span class="sr-only">Close</span>
						</button>
						<h4 class="modal-title">Add League</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-10 col-xs-push-1 col-sm-10 col-sm-push-1 col-md-10 col-md-push-1">
								<form class="form-horizontal" method="post" action="leagues.php">
									<fieldset>
										<div class="form-group">
											<label class="control-label col-md-4" for="leaguename">League Name</label>
											<div class="col-md-8">
												<input type="text" class="form-control" id="leaguename" name="leaguename" required data-toggle="tooltip" data-placement="bottom" title="Provide a descriptive name so that players will be able to understand exactly what the league is (e.g. Men's 5 on 5 Basketball)."/>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="sport">Sport</label>
											<div class="col-md-8">
												<input type="text" class="form-control" id="sport" name="sport" required/>
<!-- 												<datalist id="sports">
													<option value="Flag Football">
													<option value="Volleyball">
													<option value="Soccer">
													<option value="Basketball">
													<option value="Softball">
													<option value="Baseball">
													<option value="Dodgeball">
													<option value="Innertube Water Polo">
													<option value="Kickball">
													<option value="Ultimate Frisbee">
													<option value="Wallyball">
												</datalist>
 -->											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="gender">Gender</label>
											<div class="col-md-8">
												<select class="form-control" id="gender" name="gender">
													<option>Men's</option>
													<option>Women's</option>
													<option>CoRec</option>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="signup">Sign-up Deadline</label>
											<div class="col-md-8">
												<input type="text" class="form-control" id="datetimepicker1" name="signup" required/>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="availability">Availability Deadline</label>
											<div class="col-md-8">
												<input type="text" class="form-control" id="datetimepicker2" name="availability" required/>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="teamlimit">Team Limit</label>
											<div class="col-md-8">
												<input type="number" min="1" max="500" class="form-control" id="teamlimit" name="teamlimit"/>
											</div>
										</div>
										<div class="col-md-8 col-md-push-4">
											<button type="submit" class="btn btn-success">Add League</button>
										</div>
										<input type="hidden" id="lc" name="lc" value="true"/>
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
							<th>Sport</th>
							<th>Gender</th>
							<th>Sign-up Deadline</th>
							<th>Availability Deadline</th>
							<th>Team Limit</th>
						</tr>
					</thead>
					<tbody>
						
						<?php while($row = $lg_statement->fetch()) {?>
						<tr>
							<td><?php echo $row['LeagueName'];?></td>
							<td><?php echo $row['Sport'];?></td>
							<td><?php echo $row['Gender'];?></td>
							<td><?php echo date_format(date_create($row['SignUpDeadline']),'F j, Y');?></td>
							<td><?php echo date_format(date_create($row['AvailabilityDeadline']),'F j, Y');?></td>
							<td><?php echo $row['TeamLimit'];?></td>
						</tr>
						<?php }?>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</body>