<?php
	$messages_sql = "SELECT CONCAT(First,' ',Last) AS Name,Message FROM players AS p INNER JOIN messages AS m ON p.PlayerID=m.PlayerID 
		WHERE TeamID=:teamid ORDER BY MessageDateTime;";
	$messages_statement = $pdo->prepare($messages_sql);
	$messages_statement->bindValue(':teamid', $teamid);
	$messages_statement->execute();
?>