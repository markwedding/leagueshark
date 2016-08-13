<?php
	$insert_invite_sql = "INSERT INTO invites (TeamID, Email, InviteDateTime) VALUES (:teamid, :email, :datetime);";
	$insert_invite_statement = $pdo->prepare($insert_invite_sql);
	$insert_invite_statement->bindValue(':teamid', $teamid);
	$insert_invite_statement->bindValue(':datetime', $datetime);

	foreach($invites as $email) {
		$insert_invite_statement->bindValue(':email', $email);
		$insert_invite_statement->execute();
	}
?>