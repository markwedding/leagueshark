<?php
	$player_teams_sql = "SELECT t.TeamID,TeamName FROM teams AS t INNER JOIN jointeam AS j ON t.TeamID=j.TeamID WHERE PlayerID=:playerid ORDER BY JoinDateTime;";
	$player_teams_statement = $pdo->prepare($player_teams_sql);
	$player_teams_statement->bindValue(':playerid', $playerid);
	$player_teams_statement->execute();

	$first_team_sql = "SELECT t.TeamID,TeamName FROM teams AS t INNER JOIN jointeam AS j ON t.TeamID=j.TeamID WHERE PlayerID=:playerid ORDER BY JoinDateTime LIMIT 1;";
	$first_team_statement = $pdo->prepare($first_team_sql);
	$first_team_statement->bindValue(':playerid', $playerid);
	$first_team_statement->execute();
?>