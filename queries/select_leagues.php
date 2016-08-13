<?php
	$leagues_sql = "SELECT LeagueID,LeagueName FROM leagues WHERE SchoolID=:schoolid ORDER BY LeagueName ASC;";
	$leagues_statement = $pdo->prepare($leagues_sql);
	$leagues_statement->bindValue(':schoolid', $schoolid);
	$leagues_statement->execute();

	$first_league_sql = "SELECT LeagueID,LeagueName FROM leagues WHERE SchoolID=:schoolid ORDER BY LeagueName LIMIT 1;";
	$first_league_statement = $pdo->prepare($first_league_sql);
	$first_league_statement->bindValue(':schoolid', $schoolid);
	$first_league_statement->execute();
?>