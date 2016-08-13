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

		# Determine the league to display teams from
		if(isset($_POST['league'])) {
			$leagueid = $_POST['league'];
		} else {
			$first_league_row = $first_league_statement->fetch();
			$leagueid = $first_league_row['LeagueID'];
		}

		# Gets the teams to display
		$lg_sql = "SELECT TeamName,SignUpDateTime,Type,CONCAT(First,' ',Last) AS name FROM teams AS t INNER JOIN players AS p ON t.ManagerID=p.PlayerID WHERE LeagueID=:leagueid ORDER BY SignUpDateTime;";
		$lg_statement = $pdo->prepare($lg_sql);
		$lg_statement->bindValue(':leagueid', $leagueid);
		$lg_statement->execute();

		# Get the count of players for each team
		$pcount_sql = "SELECT t.TeamID,COUNT(PlayerID) AS pcount FROM jointeam AS j INNER JOIN teams AS t ON j.TeamID=t.TeamID WHERE LeagueID=:leagueid GROUP BY t.TeamID ORDER BY SignUpDateTime;";
		$pcount_statement = $pdo->prepare($pcount_sql);
		$pcount_statement->bindValue(':leagueid', $leagueid);
		$pcount_statement->execute();

		$pdo = null;
	}
	catch (PDOException $e) {
		die($e->getMessage());
	}
?>

<!DOCTYPE html>
<html>

<?php
	head("Teams",$filepath);
?>

<body>
	<div class="container-fluid">
		<?php navbar('school',$filepath,$filepath . 'index.php',$schoolname);?>
	</div>
	<div class="container page-content">
		<div class="row">
			<div class="col-md-6">
				<h2><strong>Teams</strong></h2>
			</div>
			<div class="col-md-6">
				<form class="form-horizontal pull-right" id="teams-select-league" method="post" action="teams.php">
					<fieldset>
						<div class="form-group menu-form-group">
							<label class="control-label col-md-4" for="league">League</label>
							<div class="col-md-8">
								<select class="form-control" id="teams-league" name="league">
									
									<?php while($row = $leagues_statement->fetch()) {?>
									<option value="<?php echo $row['LeagueID'];?>" <?php if($row['LeagueID']==$leagueid){echo 'selected="selected"';}?>><?php echo $row['LeagueName'];?></option>
									<?php }?>

								</select>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<table class="table table-striped table-hover table-responsive">
					<thead>
						<tr>
							<th>Name</th>
							<th>Sign-up Date</th>
							<th>Type</th>
							<th>Manager</th>
						</tr>
					</thead>
					<tbody>
						
						<?php while($lg_row = $lg_statement->fetch()) {?>
						<tr>
							<td><?php echo $lg_row['TeamName'];?></td>
							<td><?php echo date_format(date_create($lg_row['SignUpDateTime']),'F j, Y');?></td>
							<td><?php echo $lg_row['Type'];?></td>
							<td><?php echo $lg_row['name'];?></td>
						</tr>
						<?php }?>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</body>