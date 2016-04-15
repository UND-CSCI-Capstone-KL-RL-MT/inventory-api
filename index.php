<?php

/**
 * INVENTORY SYSTEM API DESCRIPTION
 * --------------------------------------------------------------------------
 * index.php contains all of the necessary handlers for a basic, PHP-based
 * "API" that handles GET requests for user data and inventory data. It also
 * handles POST requests for adding new users to the system, removing users
 * from the system, adding a new inventory item, updating an inventory item's
 * associated information, and removing an item from the inventory. Any info
 * returned by the API is JSON-ified and returned in that format. Any POST
 * requests are expected to be of type application/json, NOT form-data or
 * www-form-urlencoded.
 * --------------------------------------------------------------------------
 * Last updated 11/30/2015 by Ben Alman
 * --------------------------------------------------------------------------
 * TODOS:
 * - Continue testing API
 * - Add new request handlers as necessary
 * - Add comments to connect file
 */

include("includes/connect.inc.php");

/* When a GET request is made for users */
if (isset($_GET["users"])) {
    
	$query = "SELECT user_id, username FROM users WHERE 1"; // construct mysql query

	if ($stmt = $mysqli->prepare($query)) { // if we were successfully able to prepare the query, continue
	
        $stmt->execute(); // execute the query
		$stmt->store_result();
        $stmt->bind_result($user_id, $user_name);
		$result_array = array();
        while($stmt->fetch()) {
            array_push($result_array, (object) array("user_id" => $user_id, "username" => $user_name));
        }
		$stmt->free_result();
		$stmt->close();
		echo json_encode($result_array);
        
	} else { // we weren't able to prepare the request for querying, something went wrong
        die("{\"result\":\"error\",\"message\":\"Unable to prepare your request. (0)\"}");
    }
    
/* When a GET request is made for inventory items */
} else if (isset($_GET["inventory"])) {
    
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
    
/* When a POST request is made */
} else if (isset($_POST)) {
    
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $request_type = $request->type;
    
    /* Handle POST request adding an item */
    if ($request_type == "add_item") {
        
        // Collect information from POST request
        $item_id = $mysqli->real_escape_string(filter_var($request->itemID, FILTER_SANITIZE_STRING)); // the M-tag for the item
        $item_desc = $mysqli->real_escape_string(filter_var($request->itemDescription, FILTER_SANITIZE_STRING)); // the description of the item
        $item_loc = filter_var($request->itemLocation, FILTER_SANITIZE_NUMBER_INT); // item location (room #)
				$item_building = $mysqli->real_escape_string(filter_var($request->itemBuilding, FILTER_SANITIZE_STRING));
        
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
        
    }
    
    /* Handle POST request updating an item */
    else if ($request_type == "update_item") {
        
        // Collect information from POST request
        $item_id = $request->itemID; // the M-tag for the item
        $item_desc = $request->itemDescription; // the description of the item
        $item_loc = $request->itemLocation; // item location (room #)
        
        // Construct query
        $query = "UPDATE inventory SET item_desc = ?, item_loc = ? WHERE item_id = ?";
        
        // Prepare the query, bind the description/location/ID, and execute.
        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param("sis", $item_desc, $item_loc, $item_id);
            $stmt->execute();
            $stmt->close();
            echo "{\"result\":\"success\",\"message\":\"Item information for " . $item_id . " updated successfully.\"}";
        } else {
            // if we weren't able to prepare the query, die and tell the user.
            die("{\"result\":\"error\",\"message\":\"Unable to prepare your request. (2)\"}");
        }
        
        
    }
    
    /* Handle POST request deleting an item */
    else if ($request_type == "remove_item") {
        
        // Collect information from the POST request
        $item_id = $request->itemID; // the M-tag for the item
        
        // Construct query
        $query = "DELETE FROM inventory WHERE item_id = ?";
        
        // Prepare the query, bind the ID, and execute the command
        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param("s", item_id);
            $stmt->execute;
            $stmt->close();
            echo "{\"result\":\"success\",\"message\":\"Item with ID " . $item_id . " removed successfully.\"}";
        } else {
            // if we weren't able to prepare the query, die and tell user.
            die("{\"result\":\"error\",\"message\":\"Unable to prepare your request. (3)\"}");
        }
        
    }
    
    /* Handle POST request adding a user */
    else if ($request_type == "auth_user") {
        
        // Collect information from the POST request
		$first_name = $request->firstName;
		$last_name = $request->lastName;
        $username = $request->userName;
		$password = generatePassword();
        
        $numRows = 0; // number of rows
        
        // Construct query to check to see if user exists, add user iff username is not taken
        $query = "SELECT * FROM users WHERE username = ?";
        
        // Bind the username and execute the query, fetching the number of rows returned in the result
        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            $numRows = $stmt->num_rows;
            $stmt->close();
        }
        
        // If the number of rows returned is 0, we can continue (user does not exist)
        if ($numRows <= 0) {
            // Construct query (call SHA1 to hash password)
            $query = "INSERT INTO users (first_name, last_name, username, password) VALUES (?, ?, ?, SHA1(?))";

            // Prepare the query, bind the values, and execute the command
            if ($stmt = $mysqli->prepare($query)) {
                $stmt->bind_param("ssss", $first_name, $last_name, $username, $password);
                $stmt->execute;
                $stmt->close();
				
				// Construct the success email and send it.
				$msg = "{$first_name},\r\n\r\nYou have been given access to the UND Inventory Management system. Please log in using your email address and the following password: {$password}.\r\n\r\nUpon logging in, you will need to choose a new password for all future log ins.\r\n\r\nTo log in, visit the following URL: {$system_url}\r\n\r\nThank you.";
				$msg = wordwrap($msg, 70, "\r\n");
				mail($username, 'UND Inventory Management Information', $msg);
				
				
                echo "{\"result\":\"success\",\"message\":\"User " . $username . " is now authorized to access the system.\"}";
            } else {
                // if we weren't able to prepare the query, die and tell user.
                die("{\"result\":\"error\",\"message\":\"Unable to prepare your request. (4)\"}");
            }
            
        // If the number of rows returned is greater than 0, the user exists and they should pick a different username
        } else {
            die("{\"result\":\"error\",\"message\":\"User already exists, please choose a different username. (4)\"}");
        }
        
        
    }
    
    /* Handle POST request deauthorizing user */
    else if ($request_type == "deauth_user") {
        
        // Collect information from POST request
        $username = $request->userName;
        
        // Construct query
        $query = "DELETE FROM users WHERE username = ?";
        
        // Prepare the query, bind username and execute
        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param("s", $username);
            $stmt->execute;
            $stmt->close();
            echo "{\"result\":\"success\",\"message\":\"User " . $username . " has been removed from the system.\"}";
        } else {
            // if we weren't able to prepare the query, die and tell user.
            die("{\"result\":\"error\",\"message\":\"Unable to prepare your request. (5)\"}");
        }
        
    }
    
}

function generatePassword($length = 12) {
	
    $characters = '!@#$%^&*0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randPass = '';
	
    for ($i = 0; $i < $length; $i++) {
        $randPass .= $characters[rand(0, $charactersLength - 1)];
    }
	
    return $randomPass;
	
}