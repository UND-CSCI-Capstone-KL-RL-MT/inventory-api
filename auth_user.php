<?php

function session_is_active() {
    $setting = 'session.use_trans_sid';
    $current = ini_get($setting);
    if (FALSE === $current) {
        throw new UnexpectedValueException(sprintf('Setting %s does not exists.', $setting));
    }
    $result = @ini_set($setting, $current); 
    return $result !== $current;
}

include('includes/connect.inc.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$username = $request->username;
$password = $request->password;

$numRows = 0;

$query = "SELECT user_id, username, first_name, last_name, is_admin FROM users WHERE username = ? AND password = SHA1(?)";
// die("SELECT user_id, username, first_name, last_name, is_admin FROM users WHERE username = \"{$username}\" AND password = SHA1(\"{$password}\")");

if (!isset($_SESSION)) {
	if (!session_is_active()) {
		session_start();
	}
}

if(isset($_SESSION['undinv-sess'])) {
	die("{\"result\":\"failure\",\"message\":\"User already logged in.\"}");
}

if ($stmt = $mysqli->prepare($query)) {
	$stmt->bind_param("ss", $username, $password);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($user_id, $username, $first_name, $last_name, $is_admin);
	$numRows = $stmt->num_rows;
	$stmt->close();
}

if ($numRows <= 0 ) {
	// user not found or password does not match.
	die("{\"result\":\"failure\",\"message\":\"Invalid login credentials. Please try again.\"}");
} else {
	echo("{\"result\":\"success\",\"message\":{\"user_id\":{$user_id},\"username\":\"{$username}\",\"first_name\":\"{$first_name}\",\"last_name\":\"{$last_name}\",\"is_admin\":{$is_admin}}}");
}