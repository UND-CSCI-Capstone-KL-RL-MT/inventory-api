<?php
	global $mysqli;
	$mysqli = new mysqli("mysqldev.aero.und.edu", "balman", "ks945tp", "balman");
	if ($mysqli->connect_error) {
		die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
	}
?>
