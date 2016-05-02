<?php

include('includes/connect.inc.php');

// NOTE: $_GET filter must be set in order to filter down the data
if (isset($_GET["filter"])) {
	
	// Get our variables
	$filter = strtolower($_GET["filter"]); // filter specifies which field to filter by: description, item ID, room number, or all
	$search = $mysqli->real_escape_string(filter_var($_GET["query"], FILTER_SANITIZE_STRING)); // the search query
	$building = $mysqli->real_escape_string(filter_var($_GET["building"], FILTER_SANITIZE_STRING)); // the building the items should be in (opt)
	$query = "SELECT item_id, item_desc, item_building, item_loc FROM inventory WHERE"; // select items
	
	// switch case to add the appropriate LIKE clause to the query
	switch($filter) {
		case 'description':
			$query .= " item_desc LIKE '%{$search}%'";
			break;
		case 'id':
			$query .= " item_id LIKE '%{$search}%'";
			break;
		case 'room':
			$query .= " item_loc LIKE '%{$search}%'";
			break;
		default:
			$query .= " item_desc LIKE '%{$search}%' OR item_id LIKE '%{$search}%' OR item_loc LIKE '%{$search}%'";
	}
	
	// if we have a building, add it to the DB query
	if ($building != "" && isset($building)) {
		$query .= " AND item_building = '{$building}'";
	}
	
	// typical prepare and execute, return JSON array
	if ($stmt = $mysqli->prepare($query)) {

		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($item_id, $item_desc, $item_building, $item_loc);
		$result_array = array();
		while($stmt->fetch()) {
			array_push($result_array, (object) array("item_id" => $item_id, "item_description" => $item_desc, "item_building" => $item_building, "item_location" => $item_loc));
		}
		$stmt->free_result();
		$stmt->close();
		echo json_encode($result_array);

	}
	
} else {
	
	// simple prepare and execute, select all items in database
	$query = "SELECT item_id, item_desc, item_building, item_loc FROM inventory WHERE 1";

	if ($stmt = $mysqli->prepare($query)) {

		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($item_id, $item_desc, $item_building, $item_loc);
		$result_array = array();
		while($stmt->fetch()) {
			array_push($result_array, (object) array("item_id" => $item_id, "item_description" => $item_desc, "item_building" => $item_building, "item_location" => $item_loc));
		}
		$stmt->free_result();
		$stmt->close();
		echo json_encode($result_array);

	}
	
}

?>