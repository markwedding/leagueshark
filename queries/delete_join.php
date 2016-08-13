<?php
	$delete_join_sql = "DELETE FROM invites WHERE TeamID=:teamid AND Email=:playeremail;";
	$delete_join_statement = $pdo->prepare($delete_join_sql);
	$delete_join_statement->bindValue(':teamid', $teamid);
	$delete_join_statement->bindValue(':playeremail', $playeremail);
	$delete_join_statement->execute();
?>