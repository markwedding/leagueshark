<?php
	ob_start();
	session_start();
	$filepath = "";

	include($filepath . "autoload.php");
	autoload($filepath);

	include($filepath . "session-status.php");
	if($playeraccount) {
		header("Location: index.php?error=session_rs");
	}

	try {
		include($filepath . "connect-to-db.php");

		if(isset($_POST['submitgame'])) {
			$addgame = true;
			$addlocation = false;

			$hometeam = $_POST['hometeam'];
			$awayteam = $_POST['awayteam'];
			$time = date_format(date_create($_POST['time']),'Y-m-d H:i:s');
			$location = $_POST['location'];

			try {
				#begin the Insert transaction
				$pdo->beginTransaction();

				# Insert new record into games table
				$insert_game_sql = "INSERT INTO games (GameDateTime, LocationID) VALUES (:time, :location)";
				$insert_game_statement = $pdo->prepare($insert_game_sql);
				$insert_game_statement->bindValue(':time', $time);
				$insert_game_statement->bindValue(':location', $location);
				$insert_game_statement->execute();
				$gameid = $pdo->lastInsertId();

				# Insert new records into gameassignment table
				$insert_ga_sql = "INSERT INTO gameassignment (GameId, TeamID, HomeAway) VALUES (:gameid, :teamid, :homeaway)";
				$insert_ga_statement = $pdo->prepare($insert_ga_sql);
				$insert_ga_statement->bindValue(':gameid', $gameid);

				$insert_ga_statement->bindValue(':teamid', $hometeam);
				$insert_ga_statement->bindValue(':homeaway', 'home');
				$insert_ga_statement->execute();

				$insert_ga_statement->bindValue(':teamid', $awayteam);
				$insert_ga_statement->bindValue(':homeaway', 'away');
				$insert_ga_statement->execute();

				#commit the transaction
				$pdo->commit();
			} catch (Exception $e) {
				#rollback if there were any failures
				$pdo->rollback();
			}

		} elseif(isset($_POST['submitlocation'])) {
			$addlocation = true;
			$addgame = false;

			$lname = $_POST['lname'];
			$street1 = $_POST['street1'];
			$street2 = $_POST['street2'];
			$city = $_POST['city'];
			$state = $_POST['state'];
			$zip = $_POST['zip'];
			$description = $_POST['description'];

			try {
				#begin the Insert transaction
				$pdo->beginTransaction();

				# Insert new record into locations table
				$insert_location_sql = "INSERT INTO locations (SchoolID, LocationName, Street1, Street2, City, State, Zip, Description) 
					VALUES (:schoolid, :lname, :street1, :street2, :city, :state, :zip, :description)";
				$insert_location_statement = $pdo->prepare($insert_location_sql);
				$insert_location_statement->bindValue(':schoolid', $schoolid);
				$insert_location_statement->bindValue(':lname', $lname);
				$insert_location_statement->bindValue(':street1', $street1);
				$insert_location_statement->bindValue(':street2', $street2);
				$insert_location_statement->bindValue(':city', $city);
				$insert_location_statement->bindValue(':state', $state);
				$insert_location_statement->bindValue(':zip', $zip);
				$insert_location_statement->bindValue(':description', $description);
				$insert_location_statement->execute();

				#commit the transaction
				$pdo->commit();
			} catch (Exception $e) {
				#rollback if there were any failures
				$pdo->rollback();
			}

		} else {
			$addgame = false;
			$addlocation = false;
		}

		# Populate the league and team select inputs
		
		# Get the leagues associated with the school account to populate the select input
		include($filepath . "queries/select_leagues.php");

		# Create array for leagues result set
		$leagues = array();
		while($l_row = $leagues_statement->fetch()) {
			$leagues[] = $l_row;
		}

		if(isset($_POST['league'])) {
			$leagueid = $_POST['league'];
			$teamid = $_POST['team'];

			if($leagueid!=='all') {
				$league_selected = true;

				# Get the name of the league
				$leaguename_sql = "SELECT LeagueName FROM leagues WHERE LeagueID=:leagueid";
				$leaguename_statement = $pdo->prepare($leaguename_sql);
				$leaguename_statement->bindValue(':leagueid', $leagueid);
				$leaguename_statement->execute();
				$leaguename_row = $leaguename_statement->fetch();
				$leaguename = $leaguename_row['LeagueName'];

				# Get the teams in the league to populate the team select input
				$teams_sql = "SELECT TeamID,TeamName FROM teams AS t INNER JOIN leagues AS l ON t.LeagueID=l.LeagueID WHERE l.LeagueID=:leagueid ORDER BY TeamName;";
				$teams_statement = $pdo->prepare($teams_sql);
				$teams_statement->bindValue(':leagueid', $leagueid);
				$teams_statement->execute();

				# Create array for teams result set
				$teams = array();
				while($t_row = $teams_statement->fetch()) {
					$teams[] = $t_row;
				}

				if($teamid!=='all') {
					$teamid = $_POST['team'];
					$team_selected = true;
				}

				# Get the locations
				$locations_sql = "SELECT LocationID,LocationName FROM locations WHERE SchoolID=:schoolid ORDER BY LocationName;";
				$locations_statement = $pdo->prepare($locations_sql);
				$locations_statement->bindValue(':schoolid', $schoolid);
				$locations_statement->execute();
			}
		}

		# Get the games to display
		if(isset($league_selected)) {
			$games_sql = "SELECT th.TeamName AS Home,th.TeamID AS HomeID,ta.TeamName AS Away,ta.TeamID AS AwayID,l.LocationName,GameDateTime,th.LeagueID 
				FROM games AS g, locations AS l, gameassignment AS ah, teams AS th, gameassignment AS aa, teams AS ta 
				WHERE g.LocationID=l.LocationID AND g.GameID=ah.GameID AND g.GameID=aa.GameID AND ah.TeamID=th.TeamID AND aa.TeamID=ta.TeamID 
				AND ah.HomeAway='home' AND aa.HomeAway='away' AND th.LeagueID=:leagueid ORDER BY GameDateTime;";
			$games_statement = $pdo->prepare($games_sql);
			$games_statement->bindValue(':leagueid', $leagueid);
		} else {
			$games_sql = "SELECT l.SchoolID,th.TeamName AS Home,th.TeamID AS HomeID,ta.TeamName AS Away,ta.TeamID AS AwayID,l.LocationName,GameDateTime 
				FROM games AS g, locations AS l, gameassignment AS ah, teams AS th, gameassignment AS aa, teams AS ta 
				WHERE g.LocationID=l.LocationID AND g.GameID=ah.GameID AND g.GameID=aa.GameID AND ah.TeamID=th.TeamID AND aa.TeamID=ta.TeamID 
				AND ah.HomeAway='home' AND aa.HomeAway='away' AND SchoolID=:schoolid ORDER BY GameDateTime;";
			$games_statement = $pdo->prepare($games_sql);
			$games_statement->bindValue(':schoolid', $schoolid);
		}

		$games_statement->execute();

		$pdo = null;
	}
	catch (PDOException $e) {
		die($e->getMessage());
	}

	$states=array("AL","AK","AZ","AR","CA","CO","CT","DE","FL","GA","HI","ID","IL","IN","IA","KS","KY","LA","ME","MD","MA","MI","MN","MS","MO","MT","NE","NV","NH","NJ","NM","NY","NC","ND","OH","OK","OR","PA","RI","SC","SD","TN","TX","UT","VT","VA","WA","WV","WI","WY");
?>

<!DOCTYPE html>
<html>

<?php
	head("Schedule",$filepath);
?>
<body>
	<div class="container-fluid">
		<?php
			if($schoolaccount) {
				navbar('school',$filepath,$filepath . 'index.php',$schoolname);
			} elseif($refaccount) {
				navbar('referee',$filepath,$filepath . 'index.php',$reffirst);
			}
		?>
	</div>
	<div class="container page-content">
		<?php if($addgame){?>
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-dismissible alert-info pull-left form-alert">
		  			<button type="button" class="close" data-dismiss="alert">
						<span aria-hidden="true">&times;</span>
						<span class="sr-only">Close</span>
					</button>
		  			<p>A new game has been scheduled.</p>
				</div>
			</div>
		</div>
		<?php } elseif($addlocation) {?>
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-dismissible alert-info pull-left form-alert">
		  			<button type="button" class="close" data-dismiss="alert">
						<span aria-hidden="true">&times;</span>
						<span class="sr-only">Close</span>
					</button>
		  			<p><strong><?php echo $lname;?></strong> has been added as a location.</p>
				</div>
			</div>
		</div>
		<?php }?>
		<div class="row">
			<div class="col-md-2">
				<h2><strong>Schedule</strong></h2>
			</div>
			<form class="form-horizontal" method="post" action="schedule.php" id="schedule-select">
				<div class="col-md-4">
					<fieldset>
						<div class="form-group menu-form-group">
							<label class="menu-left-label control-label col-md-2" for="league">League</label>
							<div class="col-md-10">
								<select class="form-control" id="schedule-league" name="league" data-toggle="tooltip" data-placement="top" title="Select a league to be able to add a game.">
									<option value="all">All</option>
									
									<?php for($i=0; $i<sizeof($leagues); $i++) {?>
									<option value="<?php echo $leagues[$i]['LeagueID'];?>" <?php if(isset($leagueid) and $leagueid==$leagues[$i]['LeagueID']){echo 'selected="selected"';}?>><?php echo $leagues[$i]['LeagueName'];?></option>
									<?php }?>

								</select>
							</div>
						</div>
					</fieldset>
				</div>
				
				<?php if($schoolaccount) {?>
				<div class="col-md-4">
				<?php } else {?>
				<div class="col-md-4 col-md-push-2">
				<?php }?>
					<fieldset>
						<div class="form-group menu-form-group">
							<label class="control-label col-md-2" for="team">Team</label>
							<div class="col-md-10">
								<select class="form-control" id="schedule-team" name="team">
									<option value="all">All</option>

									<?php if(isset($league_selected)) {
										for($i=0; $i<sizeof($teams); $i++) {?>
										<option value="<?php echo $teams[$i]['TeamID'];?>" <?php if(isset($teamid) and $teamid==$teams[$i]['TeamID']){echo 'selected="selected"';}?>><?php echo $teams[$i]['TeamName'];?></option>
									<?php }}?>

								</select>
							</div>
						</div>
					</fieldset>
				</div>
			</form>

			<?php if($schoolaccount) {?>
			<div class="col-md-2">
				<button class="btn btn-default pull-right menu-button <?php if(!isset($team_selected)){echo 'disabled';}?>" id="abutton" data-toggle="tooltip" data-placement="top" title="PHASE II Functionality">View Availability</button>
			</div>
			<?php }?>

		</div>
		<div class="row menu-form-group">
			<div class="col-md-8 col-md-push-2">
				<form class="form-inline" method="post" action="schedule.php">
					<fieldset>
						<div class="form-group">
							<label class="control-label" for="datetimepicker1">Start Date</label>
							<input class="form-control" id="datetimepicker1" name="startdate" type="text">
						</div>
						<div class="form-group" style="margin-left: 10px;">
							<label class="control-label" for="datetimepicker2">End Date</label>
							<input class="form-control" id="datetimepicker2" name="enddate" type="text">
						</div>
						<button type="submit" class="btn btn-default" data-toggle="tooltip" data-placement="bottom" title="PHASE II Functionality.">
							<span class="glyphicon glyphicon-filter" aria-hidden="true"></span> &nbsp;Filter
						</button>
					</fieldset>
				</form>
			</div>
			
			<?php if($schoolaccount) {?>
			<div class="col-md-2 col-md-push-2">
				<div class="dropdown pull-right">
					<button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						<span class="glyphicon glyphicon-info" aria-hidden="true"></span> &nbsp;Add
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu" aria-labelledby="dropdownMenu">
						<li class="<?php if(!isset($league_selected)){echo 'disabled';}?>"><a href="#"<?php if(isset($league_selected)) {echo ' data-toggle="modal" data-target="#addgame"';}?>>Game</a></li>
						<li><a href="#" data-toggle="modal" data-target="#addlocation">Location</a></li>
					</ul>
				</div>				
			</div>
			<?php }?>

		</div>
		
		<?php if($schoolaccount) {
		if(isset($league_selected)) {?>
		<div id="addgame" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">
							<span aria-hidden="true">&times;</span>
							<span class="sr-only">Close</span>
						</button>
						<h4 class="modal-title">Add Game</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-10 col-xs-push-1 col-sm-10 col-sm-push-1 col-md-10 col-md-push-1">
								<form class="form-horizontal" method="post" action="schedule.php" id="addgame-form">
									<fieldset>
										<div class="form-group">
											<div class="col-md-4">
												<p class="text-right"><strong>League</strong></p>
											</div>
											<div class="col-md-8">
												<p><?php echo $leaguename;?></p>
											</div>
										</div>
										<input type="hidden" name="league" value="<?php echo $leagueid;?>">
										<input type="hidden" name="team" value="all">
										<div class="form-group">
											<label class="control-label col-md-4" for="hometeam">Home Team</label>
											<div class="col-md-8">
												<select class="form-control" id="hometeam" name="hometeam">
													
													<?php for($i=0; $i<sizeof($teams); $i++) {?>
													<option value="<?php echo $teams[$i]['TeamID'];?>"><?php echo $teams[$i]['TeamName'];?></option>
													<?php }?>

												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="awayteam">Away Team</label>
											<div class="col-md-8">
												<select class="form-control" id="awayteam" name="awayteam">
													
													<?php for($i=0; $i<sizeof($teams); $i++) {?>
													<option value="<?php echo $teams[$i]['TeamID'];?>"><?php echo $teams[$i]['TeamName'];?></option>
													<?php }?>

												</select>
												<p id="password-error" class="text-danger"></p>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="time">Time</label>
											<div class="col-md-8">
												<input type="text" class="form-control" id="datetimepicker3" name="time" required/>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="location">Location</label>
											<div class="col-md-8">
												<select class="form-control" id="location" name="location">
													
													<?php while($lc_row = $locations_statement->fetch()) {?>
													<option value="<?php echo $lc_row['LocationID'];?>"><?php echo $lc_row['LocationName'];?></option>
													<?php }?>

												</select>
											</div>
										</div>
										<div class="col-md-8 col-md-push-4">
											<button type="submit" class="btn btn-success" id="submitgame" name="submitgame">Add Game</button>
										</div>
										<input type="hidden" id="addgame" name="addgame" value="true"/>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php }?>
		<div id="addlocation" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">
							<span aria-hidden="true">&times;</span>
							<span class="sr-only">Close</span>
						</button>
						<h4 class="modal-title">Add Location</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-10 col-xs-push-1 col-sm-10 col-sm-push-1 col-md-10 col-md-push-1">
								<form class="form-horizontal" method="post" action="schedule.php">
									<fieldset>
										<div class="form-group">
											<label class="control-label col-md-4" for="lname">Name</label>
											<div class="col-md-8">
												<input type="text" class="form-control" id="lname" name="lname" required/>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="street1">Street (Line 1)</label>
											<div class="col-md-8">
												<input type="text" class="form-control" id="street1" name="street1" required/>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="street2">Street (Line 2)</label>
											<div class="col-md-8">
												<input type="text" class="form-control" id="street2" name="street2"/>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="city">City</label>
											<div class="col-md-8">
												<input type="text" class="form-control" id="city" name="city" required/>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="state">State</label>
											<div class="col-md-8">
												<select class="form-control" id="state" name="state" required>
													<?php for($i=0;$i<count($states);++$i){?>
														<option><?php echo $states[$i]?></option><?php echo "\n";
													}?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="zip">Zip</label>
											<div class="col-md-8">
												<input type="text" class="form-control" id="zip" name="zip" required/>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="description">Description</label>
											<div class="col-md-8">
												<textarea class="form-control" id="description" name="description" rows="4" style="resize: none;"></textarea>
											</div>
										</div>
										<div class="col-md-8 col-md-push-4">
											<button type="submit" class="btn btn-success" name="submitlocation" id="submitlocation">Add Location</button>
										</div>
										<input type="hidden" id="addlocation" name="addlocation" value="true"/>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php }?>

		<hr>
		<div class="row">
			<div class="col-md-12">
				<table class="table table-striped table-hover table-responsive">
					<thead>
						<tr>
							<th>Home</th>
							<th>Away</th>
							<th>Location</th>
							<th>Date</th>
							<th>Time</th>
							<!-- <th>Winner</th>
							<th>Score</th>
							<th></th> -->
						</tr>
					</thead>
					<tbody>
						
						<?php while($g_row = $games_statement->fetch()) {
							if(isset($team_selected)) {
								if($teamid==$g_row['HomeID'] or $teamid==$g_row['AwayID']) {
									$print_record = true;
								} else {
									$print_record = false;
								}
							} else {
								$print_record = true;
							}

						if($print_record) {?>
						<tr>
							<td><?php echo $g_row['Home'];?></td>
							<td><?php echo $g_row['Away'];?></td>
							<td><?php echo $g_row['LocationName'];?></td>
							<td><?php echo date_format(date_create($g_row['GameDateTime']),'F j, Y');?></td>
							<td><?php echo date_format(date_create($g_row['GameDateTime']),'g:ia');?></td>
							<!-- <td></td>
							<td></td>
							<td><?php if($schoolaccount){?><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span><?php }?></td> -->
						</tr>
						<?php }}?>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</body>