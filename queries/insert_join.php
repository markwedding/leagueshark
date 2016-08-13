<?php
	$insert_join_sql = "INSERT INTO jointeam (PlayerID, TeamID, JoinDateTime) VALUES (:playerid, :teamid, :datetime);";
	$insert_join_statement = $pdo->prepare($insert_join_sql);
	$insert_join_statement->bindValue(':playerid', $playerid);
	$insert_join_statement->bindValue(':teamid', $teamid);
	$insert_join_statement->bindValue(':datetime', $datetime);
	$insert_join_statement->execute();
?>