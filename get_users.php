<?php

include('includes/connect.inc.php');

$query = "SELECT user_id, username, first_name, last_name, is_admin FROM users WHERE 1"; // construct mysql query

if ($stmt = $mysqli->prepare($query)) { // if we were successfully able to prepare the query, continue

	$stmt->execute(); // execute the query
	$stmt->store_result();
	$stmt->bind_result($user_id, $user_name, $first_name, $last_name, $is_admin);
	$result_array = array();
	while($stmt->fetch()) {
		array_push($result_array, (object) array("user_id" => $user_id, "username" => $user_name, "first_name" => $first_name, "last_name" => $last_name, "is_admin" => $is_admin));
	}
	$stmt->free_result();
	$stmt->close();
	echo json_encode($result_array);

} else { // we weren't able to prepare the request for querying, something went wrong
	die("{\"result\":\"error\",\"message\":\"Unable to prepare your request. (0)\"}");
}

?>