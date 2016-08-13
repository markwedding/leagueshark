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
		
		# Get the leagues associated with the school account to populate the select input
		include($filepath . "queries/select_leagues.php");

		if(isset($_POST['league'])) {
			$leagueid = $_POST['league'];
			$teamid = $_POST['team'];

			if($leagueid!=='all') {
				$league_selected = true;
				# Get the teams in the league to populate the team select input
				$teams_sql = "SELECT TeamID,TeamName FROM teams AS t INNER JOIN leagues AS l ON t.LeagueID=l.LeagueID WHERE l.LeagueID=:leagueid ORDER BY TeamName;";
				$teams_statement = $pdo->prepare($teams_sql);
				$teams_statement->bindValue(':leagueid', $leagueid);
				$teams_statement->execute();

				if($teamid!=='all') {
					$teamid = $_POST['team'];
					$team_selected = true;
				}
			}
		}

		# Gets the players to display
		if(isset($league_selected) and !isset($team_selected)) {
			$pl_sql = "SELECT CONCAT(First,' ',Last) AS name,StudentEmail FROM eligibility AS e INNER JOIN players AS p ON e.PlayerID=p.PlayerID INNER JOIN jointeam AS j ON p.PlayerID=j.PlayerID INNER JOIN teams AS t ON j.TeamID=t.TeamID WHERE SchoolID=:schoolid AND LeagueID=:leagueid ORDER BY Last;";
			$pl_statement = $pdo->prepare($pl_sql);
			$pl_statement->bindValue(':leagueid', $leagueid);
		} elseif(isset($league_selected) and isset($team_selected)) {
			$pl_sql = "SELECT CONCAT(First,' ',Last) AS name,StudentEmail FROM eligibility AS e INNER JOIN players AS p ON e.PlayerID=p.PlayerID INNER JOIN jointeam AS j ON p.PlayerID=j.PlayerID INNER JOIN teams AS t ON j.TeamID=t.TeamID WHERE SchoolID=:schoolid AND LeagueID=:leagueid AND t.TeamID=:teamid ORDER BY Last;";
			$pl_statement = $pdo->prepare($pl_sql);
			$pl_statement->bindValue(':leagueid', $leagueid);
			$pl_statement->bindValue(':teamid', $teamid);
		} else {
			$pl_sql = "SELECT CONCAT(First,' ',Last) AS name,StudentEmail FROM eligibility AS e INNER JOIN players AS p ON e.PlayerID=p.PlayerID WHERE SchoolID=:schoolid ORDER BY Last;";
			$pl_statement = $pdo->prepare($pl_sql);
		}

		$pl_statement->bindValue(':schoolid', $schoolid);
		$pl_statement->execute();

		$pdo = null;
	}
	catch (PDOException $e) {
		die($e->getMessage());
	}	

	if(isset($_POST['playeremail'])) {
		$playeremail = true;
		$message = $_POST['message'];
		$league = $_POST['hleague'];
		$team = $_POST['hteam'];
	} else {
		$playeremail = false;
	}	
?>

<!DOCTYPE html>
<html>

<?php
	head("Players",$filepath);
?>

<body>
	<div class="container-fluid">
		<?php navbar('school',$filepath,$filepath . 'index.php',$schoolname);?>
	</div>
	<div class="container page-content">
		<?php if($playeremail){?>
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-dismissible alert-info pull-left form-alert">
		  			<button type="button" class="close" data-dismiss="alert">
						<span aria-hidden="true">&times;</span>
						<span class="sr-only">Close</span>
					</button>
		  			<p><em>Example notification</em></p>
		  			<p>An email has been sent to players in the following list:</p>
		  			<p>League: <strong><?php echo $league;?></strong></p>
		  			<p>Team: <strong><?php echo $team;?></strong></p>
				</div>
			</div>
		</div>
		<?php }?>
		
		<div class="row">
			<div class="col-md-2">
				<h2><strong>Players</strong></h2>
			</div>
			<form class="form-horizontal" id="players-select" method="post" action="players.php">
				<div class="col-md-4">
					<fieldset>
						<div class="form-group menu-form-group">
							<label class="menu-left-label control-label col-md-2" for="league">League</label>
							<div class="col-md-10">
								<select class="form-control" id="players-league" name="league">
									<option value="all">All</option>
									
									<?php while($l_row = $leagues_statement->fetch()) {?>
									<option value="<?php echo $l_row['LeagueID'];?>" <?php if(isset($leagueid) and $leagueid==$l_row['LeagueID']){echo 'selected="selected"';}?>><?php echo $l_row['LeagueName'];?></option>
									<?php }?>

								</select>
							</div>
						</div>
					</fieldset>
				</div>
				<div class="col-md-4">
					<fieldset>
						<div class="form-group menu-form-group">
							<label class="control-label col-md-2" for="team">Team</label>
							<div class="col-md-10">
								<select class="form-control" id="players-team" name="team">
									<option value="all">All</option>

									<?php if(isset($league_selected)) {
										while($t_row = $teams_statement->fetch()) {?>
										<option value="<?php echo $t_row['TeamID'];?>" <?php if(isset($teamid) and $teamid==$t_row['TeamID']){echo 'selected="selected"';}?>><?php echo $t_row['TeamName'];?></option>
									<?php }}?>

								</select>
							</div>
						</div>
					</fieldset>
				</div>
			</form>
			<div class="col-md-2">
				<button class="btn btn-default pull-right menu-button" data-toggle="modal" data-target="#sendemail" id="emailbutton">
					<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> &nbsp;Send an Email
				</button>
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
								<p>An email will be sent to the currently selected list of players.</p>
								<form class="form-horizontal" method="post" action="players.php">
									<fieldset>
										<div class="form-group">
											<div class="col-md-12">
												<textarea class="form-control" id="message" name="message" rows="18" style="resize: none;"></textarea>
											</div>
										</div>
										<div class="col-md-6 col-md-push-3">
											<button type="submit" class="btn btn-success btn-block" data-toggle="tooltip" data-placement="left" title="PHASE II Functionality">Send Email</button>
										</div>
										<input type="hidden" id="playeremail" name="playeremail" value="true"/>
										<input type="hidden" id="hleague" name="hleague" value=""/>
										<input type="hidden" id="hteam" name="hteam" value=""/>
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
						
						<?php while($pl_row = $pl_statement->fetch()) {?>
						<tr>
							<td><?php echo $pl_row['name'];?></td>
							<td><?php echo $pl_row['StudentEmail'];?></td>
						</tr>
						<?php }?>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</body>
