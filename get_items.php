<?php

include('includes/connect.inc.php');

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

?>