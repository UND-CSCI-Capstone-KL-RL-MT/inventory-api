<?php

include('includes/connect.inc.php');

// Collect information from POST request
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$item_id = $request->item_id; // the M-tag for the item
$item_desc = $request->item_description; // the description of the item
$item_loc = $request->item_location; // item location (room #)
$item_building = $request->item_building;

// Construct query
$query = "UPDATE inventory SET item_desc = ?, item_loc = ?, item_building = ? WHERE item_id = ?";

// Prepare the query, bind the description/location/ID, and execute.
if ($stmt = $mysqli->prepare($query)) {
	$stmt->bind_param("siss", $item_desc, $item_loc, $item_building, $item_id);
	$stmt->execute();
	$stmt->close();
	echo "{\"result\":\"success\",\"message\":\"Item information for " . $item_id . " updated successfully.\"}";
} else {
	// if we weren't able to prepare the query, die and tell the user.
	die("{\"result\":\"error\",\"message\":\"Unable to prepare your request. (2)\"}");
}

?>