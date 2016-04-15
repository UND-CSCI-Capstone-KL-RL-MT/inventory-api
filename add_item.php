<?php

include('includes/connect.inc.php');

/**
 * Pass in item with the following format:
 * {
 *	item_id: #,
 *	item_description: '',
 *	item_location: #,
 *	item_building: ''
 * }
 *
 * All fields required.
 */

// Collect information from POST request

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$item_id = $mysqli->real_escape_string(filter_var($request->item_id, FILTER_SANITIZE_STRING)); // the M-tag for the item
$item_desc = $mysqli->real_escape_string(filter_var($request->item_description, FILTER_SANITIZE_STRING)); // the description of the item
$item_loc = filter_var($request->item_location, FILTER_SANITIZE_NUMBER_INT); // item location (room #)
$item_building = $mysqli->real_escape_string(filter_var($request->item_building, FILTER_SANITIZE_STRING));

// Construct query
$query = "INSERT INTO inventory (item_id, item_desc, item_loc, item_building) VALUES (?, ?, ?, ?)";

// Prepare the query, bind the ID/description/location, and execute.
if ($stmt = $mysqli->prepare($query)) {
	$stmt->bind_param("ssis", $item_id, $item_desc, $item_loc, $item_building);
	$stmt->execute();
	$stmt->close();
	echo "{\"result\":\"success\",\"message\":\"Item successfully added to inventory.\"}";
} else {
	// if we weren't able to prepare the query, die and tell the user.
	die("{\"result\":\"error\",\"message\":\"Unable to prepare your request. (1)\"}");
}

?>