<?php
	$leagues_sql = "SELECT LeagueID,LeagueName FROM leagues WHERE SchoolID=:playerschool ORDER BY SignUpDeadline;";
	$leagues_statement = $pdo->prepare($leagues_sql);
	$leagues_statement->bindValue(':playerschool', $playerschool);
	$leagues_statement->execute();
?>