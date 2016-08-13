<?php
	ob_start();
	session_start();
	$filepath = "";

	include($filepath . "autoload.php");
	autoload($filepath);

	include($filepath . "session-status.php");
	include($filepath . "session-player-redirect.php");

	try {
		include($filepath . "connect-to-db.php");

		#Process invite accept or decline
		if(isset($_POST['accept'])) {
			$teamid = $_POST['teamid'];
			$datetime = date('Y-m-d h:i:s');

			# Insert the join record into the jointeam table
			include($filepath . "queries/insert_join.php");

			# Delete the invite
			include($filepath . "queries/delete_join.php");

		} elseif(isset($_POST['decline'])) {
			$teamid = $_POST['teamid'];

			# Delete the invite
			include($filepath . "queries/delete_join.php");
		}

		# Get the teams the player is currently on
		include($filepath . "queries/get_player_teams.php");

		# Get the invites for the player
		include($filepath . "queries/get_player_invites.php");

		if($invites_count_rowCount > 0) {
			$has_invites = true;

			$player_invites_sql = "SELECT t.TeamID,TeamName,LeagueName FROM invites AS i INNER JOIN teams AS t ON i.TeamID=t.TeamID INNER JOIN leagues AS l ON t.LeagueID=l.LeagueID WHERE i.Email=:playeremail;";
			$player_invites_statement = $pdo->prepare($player_invites_sql);
			$player_invites_statement->bindValue(':playeremail', $playeremail);
			$player_invites_statement->execute();

		} else {
			$has_invites = false;
		}

		# Set the team that the page is displaying

		# If invites have been sent, create new records in the invites table and set the team id
		if(isset($_POST['invites-submit'])) {
			$teamid = $_POST['invites-teamid'];
			$invitesString = $_POST['invites'];
			$invites = array();
			$invites = explode(',', $invitesString);
			$datetime = date('Y-m-d h:i:s');

			include($filepath . "queries/insert_invite.php");

		} elseif(isset($_POST['team'])) {
			$teamid = $_POST['team'];

		} else {
			$first_team_row = $first_team_statement->fetch();
			$teamid = $first_team_row['TeamID'];
		}

		# Get the league name of the selected team
		$leaguename_sql = "SELECT LeagueName FROM leagues AS l INNER JOIN teams AS t ON l.LeagueID=t.LeagueID WHERE TeamID=:teamid;";
		$leaguename_statement = $pdo->prepare($leaguename_sql);
		$leaguename_statement->bindValue(':teamid', $teamid);
		$leaguename_statement->execute();
		$leaguename_row = $leaguename_statement->fetch();
		$leaguename = $leaguename_row['LeagueName'];

		# Determine if the player is the captain of the team
		$captain_sql = "SELECT COUNT(*) FROM teams WHERE TeamID=:teamid AND ManagerID=:playerid;";
		$captain_statement = $pdo->prepare($captain_sql);
		$captain_statement->bindValue(':teamid', $teamid);
		$captain_statement->bindValue(':playerid', $playerid);
		$captain_statement->execute();
		$captain_rowCount = $captain_statement->fetchColumn(0);

		if($captain_rowCount > 0) {
			$captain = true;
		} else {
			$captain = false;
		}

		# Get the team roster
		$roster_sql = "SELECT p.PlayerID,First,Last FROM jointeam as j INNER JOIN players AS p ON j.PlayerID=p.PlayerID WHERE TeamID=:teamid ORDER BY Last;";
		$roster_statement = $pdo->prepare($roster_sql);
		$roster_statement->bindValue(':teamid', $teamid);
		$roster_statement->execute();

		# Get the id of the captain of the team
		$cap_id_sql = "SELECT ManagerID FROM teams WHERE TeamID=:teamid;";
		$cap_id_statement = $pdo->prepare($cap_id_sql);
		$cap_id_statement->bindValue(':teamid', $teamid);
		$cap_id_statement->execute();
		$cap_id_row = $cap_id_statement->fetch();
		$captainid = $cap_id_row['ManagerID'];

		# Get the team schedule
		$schedule_sql = "SELECT t.TeamName,l.LocationName,g.GameDateTime
			FROM games AS g, gameassignment AS ga, gameassignment AS gao, locations AS l, teams AS t
			WHERE g.GameID=ga.GameID AND ga.GameID=gao.GameID AND g.LocationID=l.LocationID AND gao.TeamID=t.TeamID
			AND ga.TeamID=:teamid AND ga.TeamID<>gao.TeamID ORDER BY g.GameDateTime;";
		$schedule_statement = $pdo->prepare($schedule_sql);
		$schedule_statement->bindValue(':teamid', $teamid);
		$schedule_statement->execute();

		# Get team messages
		include($filepath . "queries/get_messages.php");

	}
	catch (PDOException $e) {
		die($e->getMessage());
	}
?>

<!DOCTYPE html>
<html>

<?php
	head("My Teams",$filepath);
?>

<body>
	<div class="container-fluid">
		<?php navbar('player',$filepath,'index.php',$playerfirst);?>
	</div>
	<div class="container page-content">
		<div class="row">
			<div class="col-md-2">
				<h2><strong>My Teams</strong></h2>
			</div>
			<div class="col-md-6">
				<form class="form-inline" method="post" action="myteams.php" id="myteams-select-team">
					<fieldset>
						<div class="form-group menu-form-group">
							<label class="control-label" for="team">Team&nbsp;&nbsp;</label>
							<select class="form-control" id="myteams-team" name="team">

								<?php while($row = $player_teams_statement->fetch()) {?>
								<option value="<?php echo $row['TeamID'];?>" <?php if($row['TeamID']==$teamid){echo 'selected="selected"';}?>><?php echo $row['TeamName'];?></option>
								<?php }?>

							</select>
						</div>
						<div class="form-group menu-form-group">
							<label class="control-label" style="margin-left: 10px;">League: <span class="text-info"><?php echo $leaguename;?></span></label>
						</div>
					</fieldset>
				</form>
			</div>

			<?php if($has_invites) {?>
			<div class="col-md-2">
				<a href="" class="btn btn-link menu-button pull-right" data-toggle="modal" data-target="#invitesmodal">Invites &nbsp;<span class="badge"><?php echo $invites_count_rowCount;?></span></a>
			</div>
			<div class="col-md-2">
			<?php } else {?>
			<div class="col-md-4">
			<?php }?>

				<a href="join.php" class="btn btn-default pull-right menu-button">
					<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Join New Team
				</a>
			</div>
		</div>

		<?php if($captain) {?>
		<div class="row" id="captain-menubar">
			<div class="col-md-4">
				<h4 class="menu-heading">You are the <strong><span class="text-info">captain</span></strong> of this team.</h4>
			</div>
			<div class="col-md-5">
				<form class="form-inline pull-right menu-form-group" method="post" action="myteams.php">
					<fieldset>
						<div class="form-group">
							<input class="form-control" placeholder="Invites.." id="invites" name="invites" type="text" required data-toggle="tooltip" data-placement="top" title="Invite other players to play on your team by typing their email addresses. Separate multiple email addresses with a comma.">
							<input type="hidden" name="invites-teamid" id="invites-teamid" value="<?php echo $teamid;?>">
						</div>
						<button type="submit" class="btn btn-default" name="invites-submit" id="invites-submit">
							<span class="glyphicon glyphicon-send" aria-hidden="true"></span> &nbsp;Send Invites
						</button>
					</fieldset>
				</form>
			</div>
			<div class="col-md-3">
				<a href="#" class="btn btn-default menu-button pull-right" data-toggle="tooltip" data-placement="bottom" title="PHASE II Functionality">
					<span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Update Team Availability
				</a>
			</div>
		</div>
		<?php }?>

		<?php if($has_invites) {?>
		<div id="invitesmodal" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">
							<span aria-hidden="true">&times;</span>
							<span class="sr-only">Close</span>
						</button>
						<h4 class="modal-title">Invites</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-10 col-xs-push-1 col-sm-10 col-sm-push-1 col-md-10 col-md-push-1">
								<table class="table table-striped table-hover table-responsive">
									<thead>
										<tr>
											<th>Team</th>
											<th>League</th>
											<th></th>
											<th></th>
										</tr>
									</thead>
									<tbody>

										<?php while($i_row = $player_invites_statement->fetch()) {?>
										<tr>
											<td><?php echo $i_row['TeamName'];?></td>
											<td><?php echo $i_row['LeagueName'];?></td>
											<td><form id="form<?php echo $i_row['TeamID'];?>" method="post" action="myteams.php"><input type="hidden" name="teamid" value="<?php echo $i_row['TeamID'];?>"></form><button form="form<?php echo $i_row['TeamID'];?>" type="submit" class="btn btn-success btn-xs" id="accept" name="accept">Accept</button></td>
											<td><button form="form<?php echo $i_row['TeamID'];?>" type="submit" class="btn btn-danger btn-xs" id="decline" name="decline">Decline</button></td>
										</tr>
										<?php }?>

									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php }?>

		<hr>
		<div class="row">
			<div class="col-md-2">
				<h4><strong>Roster</strong></h4>
				<div class="list-group">

					<?php while($r_row = $roster_statement->fetch()) {?>
					<a class="list-group-item">

						<?php if($r_row['PlayerID']==$captainid) {?>
						<span class="badge">Capt</span>
						<?php }?>

						<p class="list-group-item-text"><?php echo $r_row['First'] . ' ' . $r_row['Last'];?></p>
					</a>
					<?php }?>

				</div>
			</div>
			<div class="col-md-6">
				<h4><strong>Schedule</strong></h4>
				<table class="table table-striped table-hover table-responsive">
					<thead>
						<tr>
							<th>Opponent</th>
							<th>Location</th>
							<th>Date</th>
							<th>Time</th>
							<!-- <th>Result</th> -->
						</tr>
					</thead>
					<tbody>

						<?php while($s_row = $schedule_statement->fetch()) {?>
						<tr>
							<td><?php echo $s_row['TeamName'];?></td>
							<td><?php echo $s_row['LocationName'];?></td>
							<td><?php echo date_format(date_create($s_row['GameDateTime']),'F j, Y');?></td>
							<td><?php echo date_format(date_create($s_row['GameDateTime']),'g:ia');?></td>
						</tr>
						<?php }?>

					</tbody>
				</table>
			</div>
			<div class="col-md-4">

				<script>
					$(document).ready(function(){
						$('#reply').click(function(e){
							e.preventDefault();
							if(!$('#message').val()) {
								$('.form-group').has('#message').addClass('has-error');
							} else {
								var message = $('#message').val();
								$('#message').val('');
								$('#messageboard').load('messageboard.php', {msg: message, teamid: <?php echo $teamid;?>, playerid: <?php echo $playerid;?>});
								var mb = $('#messageboard');
								var height = mb[0].scrollHeight;
								mb.scrollTop(height);
								$('.form-group').has('#message').removeClass('has-error');
							}
						});
					});
				</script>
				<h4><strong>Message Board</strong></h4>
				<div class="panel panel-default">
					<div class="panel-body" id="messageboard" style="height: 300px; overflow-y: scroll;">

					<?php while($row = $messages_statement->fetch()) {?>
					<strong><span class="text-info"><?php echo $row['Name'];?>:&nbsp;</span></strong><?php echo $row['Message'];?>
					<br>
					<?php }?>

					</div>
					<div class="panel-footer">
						<form class="form-inline">
							<fieldset>
								<div class="form-group">
									<input class="form-control" id="message" name="message" type="text" placeholder="Type message...">
								</div>
								<button class="btn btn-default" id="reply" name="reply">Reply</button>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
