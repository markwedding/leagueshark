<?php
	ob_start();
	session_start();
	$filepath = "";

	include($filepath . "autoload.php");
	autoload($filepath);

	include($filepath . "session-status.php");
	include($filepath . "session-player-redirect.php");

	if(isset($_POST['sleague'])) {
		$chooseteam = true;
		$createteam = false;
	} elseif(isset($_POST['createteam'])) {
		$chooseteam = false;
		$createteam = true;
	} else {
		$chooseteam = false;
		$createteam = false;
	}

	try {
		include($filepath . "connect-to-db.php");

		# Get leagues to populate the select list
		include($filepath . "queries/get_leagues.php");

		if($chooseteam or isset($_POST['chooseteam'])) {
			$league = $_POST['league'];

			# Get public teams in the league that the player selected
			include($filepath . "queries/get_public_teams.php");
		} elseif($createteam) {
			$league = $_POST['league'];

			# Get the name of the league that the player signed up for
			$leaguename_sql = "SELECT LeagueName FROM leagues WHERE LeagueID=:league";
			$leaguename_statement = $pdo->prepare($leaguename_sql);
			$leaguename_statement->bindValue(':league', $league);
			$leaguename_statement->execute();
			$leaguename_row = $leaguename_statement->fetch();
			$leaguename = $leaguename_row['LeagueName'];
		}

		if(isset($_POST['finishcreate'])) {
			$league = $_POST['league'];
			$leaguename = $_POST['leaguename'];
			$teamname = $_POST['teamname'];
			$type = $_POST['type'];
			$invitesString = $_POST['invites'];
			if(empty($invitesString)) {
				$no_invites = true;
			} else {
				$no_invites = false;
				$invites = array();
				$invites = explode(',', $invitesString);
			}
			$datetime = date('Y-m-d h:i:s');

			# Execute query to ensure that the team name is not already in use
			$dt_sql = "SELECT COUNT(*) FROM teams WHERE LeagueID=:league AND TeamName=:teamname;";
			$dt_statement = $pdo->prepare($dt_sql);
			$dt_statement->bindValue(':league', $league);
			$dt_statement->bindValue(':teamname', $teamname);
			$dt_statement->execute();
			$dt_rowCount = $dt_statement->fetchColumn(0);

			# Execute query to ensure that the player has not already created a team in this league
			$dm_sql = "SELECT COUNT(*) FROM teams WHERE LeagueID=:league AND ManagerID=:playerid;";
			$dm_statement = $pdo->prepare($dm_sql);
			$dm_statement->bindValue(':league', $league);
			$dm_statement->bindValue(':playerid', $playerid);
			$dm_statement->execute();
			$dm_rowCount = $dm_statement->fetchColumn(0);

			# Execute query to ensure that the player is not already a member of this league
			$dp_sql = "SELECT COUNT(*) FROM jointeam AS j INNER JOIN teams AS t ON j.TeamID=t.TeamID WHERE PlayerID=:playerid AND LeagueID=:league;";
			$dp_statement = $pdo->prepare($dp_sql);
			$dp_statement->bindValue(':playerid', $playerid);
			$dp_statement->bindValue(':league', $league);
			$dp_statement->execute();
			$dp_rowCount = $dp_statement->fetchColumn(0);

			if($dt_rowCount > 0) {
				# if there is already a team with the entered team name, then set off the duplicate_team variable
				$duplicate_team = true;
				$pdo = null;
			} elseif($dm_rowCount > 0) {
				# if the player has already created a team in this league, then set off the duplicate_manager variable
				$duplicate_manager = true;
				$pdo = null;
			} elseif($dp_rowCount > 0) {
				# if the player already is in this league, then set off the duplicate_player variable
				$duplicate_player = true;
			} else {
				# Insert new records into the teams, teamavailability, jointeam, and invites tables
				try {
					#begin the Insert transaction
					$pdo->beginTransaction();

					# Insert team into teams table
					$insert_team_sql = "INSERT INTO teams (LeagueID, ManagerID, TeamName, Type, SignUpDateTime) VALUES (:league, :playerid, :teamname, :type, :datetime);";
					$insert_team_statement = $pdo->prepare($insert_team_sql);
					$insert_team_statement->bindValue(':league', $league);
					$insert_team_statement->bindValue(':playerid', $playerid);
					$insert_team_statement->bindValue(':teamname', $teamname);
					$insert_team_statement->bindValue(':type', $type);
					$insert_team_statement->bindValue(':datetime', $datetime);
					$insert_team_statement->execute();
					$teamid = $pdo->lastInsertId();

					# Insert team availability slots into teamavailability table
					$insert_ta_sql = "INSERT INTO teamavailability (TeamID, AvailabilityID, Available) VALUES (:teamid, :availabilityid, 0);";
					$insert_ta_statement = $pdo->prepare($insert_ta_sql);
					$insert_ta_statement->bindValue(':teamid', $teamid);
					
					for ($i = 1; $i <= SLOTS; $i++) {
						$insert_ta_statement->bindValue(':availabilityid', $i);
						$insert_ta_statement->execute();
					}

					# Insert new join into jointeam table
					include($filepath . "queries/insert_join.php");

					# Insert invites into invites table
					if(!$no_invites) {
						include($filepath . "queries/insert_invite.php");
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

				header("Location: myteams.php");
			}

		}

		if(isset($_POST['chooseteam'])) {
			$teamid = $_POST['team'];
			$league = $_POST['league'];
			$datetime = date('Y-m-d h:i:s');

			# Execute query to ensure that the player is not already a member of this league
			$dp_sql = "SELECT COUNT(*) FROM jointeam AS j INNER JOIN teams AS t ON j.TeamID=t.TeamID WHERE PlayerID=:playerid AND LeagueID=:league;";
			$dp_statement = $pdo->prepare($dp_sql);
			$dp_statement->bindValue(':playerid', $playerid);
			$dp_statement->bindValue(':league', $league);
			$dp_statement->execute();
			$dp_rowCount = $dp_statement->fetchColumn(0);

			if($dp_rowCount > 0) {
				$duplicate_player = true;
			} else {
				# Insert new record into the jointeam table
				try {
					#begin the Insert transaction
					$pdo->beginTransaction();

					# Insert new join into jointeam table
					include($filepath . "queries/insert_join.php");

					#commit the transaction
					$pdo->commit();
				} catch (Exception $e) {
					#rollback if there were any failures
					$pdo->rollback();
					die($e->getMessage());
				}

				# Close the database object
				$pdo = null;

				header("Location: myteams.php");
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
	head("Join a Team","");
?>

<body>
	<div class="container-fluid">
		<?php navbar('plain','','index.php','');?>
	</div>
	<div class="container page-content">
		<div class="row">
			<div class="col-md-6 col-md-push-3">
				
				<?php if($chooseteam or isset($_POST['chooseteam'])) {?>

				<div class="panel panel-primary shadow">
		  			<div class="panel-heading">
		    			<h3 class="panel-title">Join a Team</h3>
		  			</div>
		  			<div class="panel-body">
						<form method="post" action="join.php">
							<fieldset>
								<div class="form-group">
									<div class="row">
										<div class="col-md-8 col-md-push-2">
											<label for="team" class="control-label">Choose a team or create your own:</label><br><p>(Private teams are not listed)</p>
										</div>
									</div>
									<div class="row">
										<div class="col-md-8 col-md-push-2">
											<select multiple="" class="form-control" id="team" name="team">
												
												<?php while($row = $public_teams_statement->fetch()) {?>
												<option value="<?php echo $row['TeamID'];?>"><?php echo $row['TeamName'];?></option>
												<?php }?>

											</select>
											<p id="select-error" class="text-danger"></p>

											<?php if(isset($duplicate_player)) {?>
											<p class="text-danger">*You may only join one team in each league.</p>
											<?php }?>

										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-md-8 col-md-push-2">
											<button type="submit" class="btn btn-info" name="createteam" id="createteam">
												<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> &nbsp;Create Team
											</button>
											<button type="submit" class="btn btn-primary pull-right" name="chooseteam" id="chooseteam">Choose</button>
										</div>
									</div>
								</div>
								<input type="hidden" id="league" name="league" value="<?php echo $_POST['league'];?>">
							</fieldset>
						</form>
					</div>
				</div>

				<?php } elseif($createteam or isset($_POST['finishcreate'])) {?>

				<div class="panel panel-primary shadow">
		  			<div class="panel-heading">
		    			<h3 class="panel-title">Create a Team</h3>
		  			</div>
		  			<div class="panel-body">
						<form class="form-horizontal" method="post" action="join.php">
							
							<?php if(isset($duplicate_manager)) {?>
							<p class="text-danger"><strong>You are only allowed to create one team within each league.</strong></p>
							<?php } elseif(isset($duplicate_player)) {?>
							<p class="text-danger"><strong>You are already a member of this league.</strong></p>
							<?php }?>

							<fieldset>
								<div class="col-md-4 text-right">
									<p><strong>League</strong></p>
								</div>
								<div class="col-md-8">
									<p><?php echo $leaguename;?></p>
								</div>
								<input type="hidden" id="league" name="league" value="<?php echo $league;?>">
								<input type="hidden" id="leaguename" name="leaguename" value="<?php echo $leaguename;?>">
								<div class="form-group">
									<label for="teamname" class="col-md-4 control-label">Team Name</label>
									<div class="col-md-8">
										<input class="form-control" id="teamname" name="teamname" type="text" required>

										<?php if(isset($duplicate_team)) {?>
										<p class="text-danger">*The team name <?php echo $teamname;?> is already in use for this league.</p>
										<?php }?>

									</div>
								</div>
								<div class="form-group">
									<label for="teamname" class="col-md-4 control-label">Type</label>
									<div class="col-md-8">
										<div class="radio">
											<label>
												<input type="radio" name="type" id="type" value="Public" checked>Public
											</label>
										</div>
										<div class="radio">
											<label>
												<input type="radio" name="type" id="type" value="Private">Private
											</label>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label for="invites" class="col-md-4 control-label">Invites</label>
									<div class="col-md-8">
										<input class="form-control" id="invites" name="invites" type="text" data-toggle="tooltip" data-placement="top" title="Invite other players to play on your team by typing their email addresses. Separate multiple email addresses with a comma.">
									</div>
								</div>
								<div class="form-group">
									<div class="col-md-8 col-md-push-4">
										<button type="submit" class="btn btn-primary" id="finishcreate" name="finishcreate">Create Team</button>
									</div>
								</div>
							</fieldset>
						</form>
					</div>
				</div>

				<?php } else {?>

				<div class="panel panel-primary shadow">
		  			<div class="panel-heading">
		    			<h3 class="panel-title">Join a Team</h3>
		  			</div>
		  			<div class="panel-body">
						<form method="post" action="join.php">
							<fieldset>
								<div class="form-group">
									<div class="row">
										<label for="league" class="col-md-8 col-md-push-2 control-label">Choose a league to play in:</label>
									</div>
									<div class="row">
										<div class="col-md-8 col-md-push-2">
											<select multiple="" class="form-control" id="league" name="league" required>
												
												<?php while($row = $leagues_statement->fetch()) {?>
												<option value="<?php echo $row['LeagueID'];?>"><?php echo $row['LeagueName'];?></option>
												<?php }?>

											</select>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-md-8 col-md-push-2">
											<button type="submit" class="btn btn-primary pull-right" name="sleague" id="sleague">Continue &raquo;</button>
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
