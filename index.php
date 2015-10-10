<?php
include("includes/connect.inc.php");

if (isset($_GET["users"])) {
	$query = "SELECT user_id, username FROM users WHERE 1;";

	if ($stmt = $mysqli->prepare($query)) {
	
		$stmt->execute();
		$stmt->store_result();
	    	$stmt->bind_result($user_id, $user_name);
		$result_array = array();
	    	while($stmt->fetch()) {
	    		array_push($result_array, (object) array("user_id" => $user_id, "username" => $user_name));
	    	}
		$stmt->free_result();
		$stmt->close();
		echo json_encode($result_array);
	}
} else if (isset($_GET["inventory"])) {
	$query = "SELECT item_id, item_desc, item_loc FROM inventory WHERE 1;";

	if ($stmt = $mysqli->prepare($query)) {
	
		$stmt->execute();
		$stmt->store_result();
	    	$stmt->bind_result($item_id, $item_desc, $item_loc);
		$result_array = array();
	    	while($stmt->fetch()) {
	    		array_push($result_array, (object) array("item_id" => $item_id, "item_description" => $item_desc, "item_location" => $item_loc));
	    	}
		$stmt->free_result();
		$stmt->close();
		echo json_encode($result_array);
	}
}
?>
