<?php

include('includes/connect.inc.php');

// Collect information from the POST request
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$item_id = $request->item_id; // the M-tag for the item

// Construct query
$query = "DELETE FROM inventory WHERE item_id = ?";

// Prepare the query, bind the ID, and execute the command
if ($stmt = $mysqli->prepare($query)) {
	$stmt->bind_param("s", $item_id);
	$stmt->execute();
	$stmt->close();
	echo "{\"result\":\"success\",\"message\":\"Item with ID " . $item_id . " removed successfully.\"}";
} else {
	// if we weren't able to prepare the query, die and tell user.
	die("{\"result\":\"error\",\"message\":\"Unable to prepare your request. (3)\"}");
}

?>