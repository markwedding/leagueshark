<?php
	$invites_count_sql = "SELECT COUNT(*) FROM invites AS i INNER JOIN teams AS t ON i.TeamID=t.TeamID INNER JOIN leagues AS l ON t.LeagueID=l.LeagueID WHERE i.Email=:playeremail;";
	$invites_count_statement = $pdo->prepare($invites_count_sql);
	$invites_count_statement->bindValue(':playeremail', $playeremail);
	$invites_count_statement->execute();
	$invites_count_rowCount = $invites_count_statement->fetchColumn(0);
?>