<?php

include('includes/connect.inc.php');

// Collect information from the POST request

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$user_id = $request->user_id;
$first_name = $request->first_name;
$last_name = $request->last_name;
$username = $request->email;
$is_admin = $request->is_admin;
$password = $request->password;

$numRows = 0; // number of rows

// Construct query to check to see if user exists, add user iff username is not taken
$query = "SELECT * FROM users WHERE user_id = ?";

// Bind the username and execute the query, fetching the number of rows returned in the result
if ($stmt = $mysqli->prepare($query)) {
	$stmt->bind_param("i", $user_id);
	$stmt->execute();
	$stmt->store_result();
	$numRows = $stmt->num_rows;
	$stmt->close();
}

// If the number of rows returned is 0, we can continue (user does not exist)
if ($numRows == 1) {
	// Construct query
	if ($password == "" || empty($password) || !isset($password)) {
		$query = "UPDATE users SET first_name = ?, last_name = ?, username = ?, is_admin = ? WHERE user_id = ?";

		// Prepare the query, bind the values, and execute the command
		if ($stmt = $mysqli->prepare($query)) {
			$stmt->bind_param("sssii", $first_name, $last_name, $username, $is_admin, $user_id);
			$stmt->execute();
			$stmt->close();

			echo "{\"result\":\"success\",\"message\":\"" . $first_name . "'s account has been updated.\"}";
		} else {
			// if we weren't able to prepare the query, die and tell user.
			die("{\"result\":\"error\",\"message\":\"Unable to prepare your request. (4)\"}");
		}
	} else {
		// password exists
	}

// If the number of rows returned is greater than 0, the user exists and they should pick a different username
} else {
	die("{\"result\":\"error\",\"message\":\"No user exists with that ID. (4)\"}");
}

?>