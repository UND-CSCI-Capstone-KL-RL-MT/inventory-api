<?php

include('includes/connect.inc.php');

function generatePassword($length = 12) {
	
    $characters = '!@#$%^&*0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randPass = '';
	
    for ($i = 0; $i < $length; $i++) {
        $randPass .= $characters[rand(0, $charactersLength - 1)];
    }
	
    return $randPass;
	
}

// Collect information from the POST request

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$first_name = $request->first_name;
$last_name = $request->last_name;
$username = $request->email;
$password = generatePassword(12);

$numRows = 0; // number of rows

// Construct query to check to see if user exists, add user iff username is not taken
$query = "SELECT * FROM users WHERE username = ?";

// Bind the username and execute the query, fetching the number of rows returned in the result
if ($stmt = $mysqli->prepare($query)) {
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$stmt->store_result();
	$numRows = $stmt->num_rows;
	$stmt->close();
}

// If the number of rows returned is 0, we can continue (user does not exist)
if ($numRows <= 0) {
	// Construct query (call SHA1 to hash password)
	$query = "INSERT INTO users (first_name, last_name, username, password) VALUES (?, ?, ?, SHA1(?))";

	// Prepare the query, bind the values, and execute the command
	if ($stmt = $mysqli->prepare($query)) {
		echo $password;
		die("INSERT INTO users (first_name, last_name, username, password) VALUES ({$first_name}, {$last_name}, {$username}, SHA1({$password}))");
		$stmt->bind_param("ssss", $first_name, $last_name, $username, $password);
		$stmt->execute;
		$stmt->close();

		// Construct the success email and send it.
		$msg = "{$first_name},\r\n\r\nYou have been given access to the UND Inventory Management system. Please log in using your email address and the following password: {$password}.\r\n\r\nUpon logging in, you will need to choose a new password for all future log ins.\r\n\r\nTo log in, visit the following URL: {$system_url}\r\n\r\nThank you.";
		$msg = wordwrap($msg, 70, "\r\n");
		mail($username, 'UND Inventory Management Information', $msg);


		echo "{\"result\":\"success\",\"message\":\"User " . $username . " is now authorized to access the system.\"}";
	} else {
		// if we weren't able to prepare the query, die and tell user.
		die("{\"result\":\"error\",\"message\":\"Unable to prepare your request. (4)\"}");
	}

// If the number of rows returned is greater than 0, the user exists and they should pick a different username
} else {
	die("{\"result\":\"error\",\"message\":\"User already exists, please choose a different username. (4)\"}");
}

?>