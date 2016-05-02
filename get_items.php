<?php

include('includes/connect.inc.php');

if (isset($_GET["filter"])) {
	
	$filter = strtolower($_GET["filter"]);
	$search = $mysqli->real_escape_string(filter_var($_GET["query"], FILTER_SANITIZE_STRING));
	$building = $mysqli->real_escape_string(filter_var($_GET["building"], FILTER_SANITIZE_STRING));
	$query = "SELECT item_id, item_desc, item_building, item_loc FROM inventory WHERE";
	
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
	
	if ($building != "" && isset($building)) {
		$query .= " AND item_building = '{$building}'";
	}
	
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