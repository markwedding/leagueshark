<?php
	$public_teams_sql = "SELECT TeamID,TeamName FROM teams WHERE LeagueID=:league AND Type='Public' ORDER BY SignUpDateTime;";
	$public_teams_statement = $pdo->prepare($public_teams_sql);
	$public_teams_statement->bindValue(':league', $league);
	$public_teams_statement->execute();
?>