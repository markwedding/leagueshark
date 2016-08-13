<?php
	if(isset($_SESSION['schoolid'])) {
		$schoolaccount = true;
		$refaccount = false;
		$playeraccount = false;
		$schoolid = $_SESSION['schoolid'];
		$schoolname	= $_SESSION['schoolname'];
	} elseif(isset($_SESSION['refemail'])) {
		$schoolaccount = false;
		$refaccount = true;
		$playeraccount = false;
		$refemail = $_SESSION['refemail'];
		$reffirst = $_SESSION['reffirst'];
		$reflast = $_SESSION['reflast'];
		$schoolid = $_SESSION['refschool'];
	} elseif(isset($_SESSION['playerid'])) {
		$schoolaccount = false;
		$refaccount = false;
		$playeraccount = true;
		$playerid = $_SESSION['playerid'];
		$playerfirst = $_SESSION['playerfirst'];
		$playerlast	= $_SESSION['playerlast'];
		$playeremail = $_SESSION['playeremail'];
		$playerschool = $_SESSION['playerschool'];
	} else {
		$schoolaccount = false;
		$refaccount = false;
		$playeraccount = false;
		header("Location: " . $filepath . "index.php?error=session");
	}
?>