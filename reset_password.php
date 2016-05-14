<?php

include('includes/connect.inc.php');
$system_url = "http://people.cs.und.edu/~balman/inventory-web-app/";

function generatePassword($length = 12) {
	
    $characters = '!@#$%^&*0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randPass = '';
	
    for ($i = 0; $i < $length; $i++) {
        $randPass .= $characters[rand(0, $charactersLength - 1)];
    }
	
    return $randPass;
	
}
// collect POST data
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$username = $mysqli->real_escape_string(filter_var($request->email, FILTER_SANITIZE_STRING));
$numRows = 0;

// check to see if the user really exists
$query = "SELECT * FROM users WHERE username = ?";
if ($stmt = $mysqli->prepare($query)) {
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$stmt->store_result();
	$numRows = $stmt->num_rows;
	$stmt->close();
}

if ($numRows == 1) {
	$gen_pass = generatePassword(12);
	// create the reset query
	$query = "UPDATE users SET password = SHA1(?) WHERE username = ?";

	// Prepare the query, bind the values, and execute the command
	if ($stmt = $mysqli->prepare($query)) {
		$stmt->bind_param("ss", $gen_pass, $username);
		$stmt->execute();
		$stmt->close();
		
		// Construct the success email and send it.
		$msg = "{$first_name},\r\n\r\nYou or someone else has requested that your password be reset. If this was not you, please simply log in and change your password with the password below.\r\n\r\nPlease log in using your email address and the following password: {$gen_pass}\r\n\r\nBe sure to change your password to something you will remember after logging in.\r\n\r\nTo log in, visit the following URL: {$system_url}\r\n\r\nThank you.";
		$msg = wordwrap($msg, 70, "\r\n");
		$headers = "From: {$first_name} {$last_name} <{$username}>" . "\r\n";
		
		mail($username, 'UND Inventory Management Information', $msg, $headers);

		echo "{\"result\":\"success\",\"message\":\"" . $username . "'s password has been changed.\"}";
	} else {
		// if we weren't able to prepare the query, die and tell user.
		die("{\"result\":\"error\",\"message\":\"Unable to prepare your request. (4)\"}");
	}
} else {
	die("{\"result\":\"error\",\"message\":\"That email address was not found in the system.\"}");
}