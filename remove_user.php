<?php

include('includes/connect.inc.php');

// Collect information from POST request
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$user_id = $request->user_id;

// Construct query
$query = "DELETE FROM users WHERE user_id = ?";

// Prepare the query, bind username and execute
if ($stmt = $mysqli->prepare($query)) {
	$stmt->bind_param("s", $user_id);
	$stmt->execute;
	$stmt->close();
	echo "{\"result\":\"success\",\"message\":\"User " . $user_id . " has been removed from the system.\"}";
} else {
	// if we weren't able to prepare the query, die and tell user.
	die("{\"result\":\"error\",\"message\":\"Unable to prepare your request. (5)\"}");
}

?>