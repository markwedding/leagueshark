<?php
	# Define the PDO connection string
	$connectionString = "mysql:host=" . DBHOST . ";dbname=" . DBNAME;
	$dbuser = DBUSER;
	$dbpassword = DBPASS;

	$pdo = new PDO($connectionString,$dbuser,$dbpassword);

	# Set the exception mode for development (NOT PRODUCTION - SILENT mode)
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
?>