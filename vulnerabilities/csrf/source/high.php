<?php

$change = false;
$request_type = "html";
$return_message = "Request Failed";

if ($_SERVER['REQUEST_METHOD'] == "POST" && array_key_exists ("CONTENT_TYPE", $_SERVER) && $_SERVER['CONTENT_TYPE'] == "application/json") {
	$data = json_decode(file_get_contents('php://input'), true);
	$request_type = "json";
	if (array_key_exists("HTTP_USER_TOKEN", $_SERVER) &&
		array_key_exists("password_new", $data) &&
		array_key_exists("password_conf", $data) &&
		array_key_exists("Change", $data)) {
		$token = $_SERVER['HTTP_USER_TOKEN'];
		$pass_new = $data["password_new"];
		$pass_conf = $data["password_conf"];
		$change = true;
	}
} else {
	if (array_key_exists("user_token", $_REQUEST) &&
		array_key_exists("password_new", $_REQUEST) &&
		array_key_exists("password_conf", $_REQUEST) &&
		array_key_exists("Change", $_REQUEST)) {
		$token = $_REQUEST["user_token"];
		$pass_new = $_REQUEST["password_new"];
		$pass_conf = $_REQUEST["password_conf"];
		$change = true;
	}
}

if ($change) {
	// Check Anti-CSRF token
	checkToken( $token, $_SESSION[ 'session_token' ], 'index.php' );

	// Reject state changing requests that did not originate from this site.
	$req_host = isset( $_SERVER['HTTP_HOST'] ) ? preg_replace( '/:\d+$/', '', $_SERVER['HTTP_HOST'] ) : $_SERVER['SERVER_NAME'];
	$req_src  = !empty( $_SERVER['HTTP_ORIGIN'] ) ? $_SERVER['HTTP_ORIGIN'] : ( !empty( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '' );
	if( $req_src !== '' ) {
		$req_parsed = parse_url( $req_src, PHP_URL_HOST );
		if( empty( $req_parsed ) || strcasecmp( $req_parsed, $req_host ) !== 0 ) {
			header( 'HTTP/1.1 403 Forbidden' );
			exit;
		}
	}

	// Do the passwords match?
	if( $pass_new == $pass_conf ) {
		// They do!
		$pass_new = md5( stripslashes( $pass_new ) );

		// Update the database with a parameterised statement
		$current_user = dvwaCurrentUser();
		$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE `users` SET password = ? WHERE user = ?" );
		if( $stmt ) {
			mysqli_stmt_bind_param( $stmt, "ss", $pass_new, $current_user );
			mysqli_stmt_execute( $stmt );
			mysqli_stmt_close( $stmt );
		}

		// Feedback for the user
		$return_message = "Password Changed.";
	}
	else {
		// Issue with passwords matching
		$return_message = "Passwords did not match.";
	}

	mysqli_close($GLOBALS["___mysqli_ston"]);

	if ($request_type == "json") {
		generateSessionToken();
		header ("Content-Type: application/json");
		print json_encode (array("Message" =>$return_message));
		exit;
	} else {
		$html .= "<pre>" . $return_message . "</pre>";
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
