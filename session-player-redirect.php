<?php
	if(!$playeraccount) {
		header("Location: " . $filepath . "index.php?error=session_player");
	}
?>