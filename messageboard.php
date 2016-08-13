<?php
	$filepath = "";

	include($filepath . "autoload.php");
	autoload($filepath);

	$teamid = $_POST['teamid'];
	$playerid = $_POST['playerid'];
	$message = $_POST['msg'];
	$datetime = date('Y-m-d h:i:s');

	try {
		include($filepath . "connect-to-db.php");

			try {
				#begin the Insert transaction
				$pdo->beginTransaction();

				# Insert new record into messages table
				$insert_msg_sql = "INSERT INTO messages (TeamID, PlayerID, Message, MessageDateTime) VALUES (:teamid, :playerid, :message, :datetime)";
				$insert_msg_statement = $pdo->prepare($insert_msg_sql);
				$insert_msg_statement->bindValue(':teamid', $teamid);
				$insert_msg_statement->bindValue(':playerid', $playerid);
				$insert_msg_statement->bindValue(':message', $message);
				$insert_msg_statement->bindValue(':datetime', $datetime);
				$insert_msg_statement->execute();

				#commit the transaction
				$pdo->commit();
			} catch (Exception $e) {
				#rollback if there were any failures
				$pdo->rollback();
			}

			# Get messages
			include($filepath . "queries/get_messages.php");

		$pdo = null;
	}
	catch (PDOException $e) {
		die($e->getMessage());
	}

	while($row = $messages_statement->fetch()) {?>
	<strong><span class="text-info"><?php echo $row['Name'];?>:&nbsp;</span></strong><?php echo $row['Message'];?>
	<br>
	<?php }?>
