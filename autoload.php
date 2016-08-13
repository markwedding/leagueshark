<?php
	function autoload($filepath) {
		include($filepath . "config.php");
		include($filepath . "components.php");
	}
?>