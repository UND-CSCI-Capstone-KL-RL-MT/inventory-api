<?php
	global $mysqli;
	include("connectvars.inc.php"); // our db vars are defined here
	$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name); // create mysqli object
	if ($mysqli->connect_error) {
		die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error); // if couldn't connect
	}
