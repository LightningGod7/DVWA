<?php

if( isset( $_REQUEST[ 'Login' ] ) ) {

	// Get credentials
	$user = stripslashes( $_REQUEST[ 'username' ] );
	$pass = md5( stripslashes( $_REQUEST[ 'password' ] ) );

	// Account lockout settings
	$total_failed_login = 3;
	$lockout_time       = 15;
	$account_locked     = false;

	// Look the account up with a parameterised statement
	$data = $db->prepare( 'SELECT failed_login, last_login FROM users WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	// Has this account been locked out by repeated failures?
	if( ( $data->rowCount() == 1 ) && ( $row[ 'failed_login' ] >= $total_failed_login ) ) {
		$last_login = strtotime( $row[ 'last_login' ] );
		$timeout    = $last_login + ( $lockout_time * 60 );
		if( time() < $timeout ) {
			$account_locked = true;
		}
	}

	// Verify the credentials
	$data = $db->prepare( 'SELECT * FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->bindParam( ':password', $pass, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	if( ( $data->rowCount() == 1 ) && ( $account_locked == false ) ) {
		$avatar = $row[ 'avatar' ];

		// Login successful
		$html .= "<p>Welcome to the password protected area " . htmlspecialchars( $user, ENT_QUOTES, 'UTF-8' ) . "</p>";
		$html .= "<img src=\"" . htmlspecialchars( $avatar, ENT_QUOTES, 'UTF-8' ) . "\" />";

		// Reset the bad login counter
		$data = $db->prepare( 'UPDATE users SET failed_login = "0" WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	}
	else {
		// Constant, non enumerable feedback plus a delay
		sleep( 2 );
		$html .= "<pre><br />Username and/or password incorrect.<br /><br />Alternatively the account has been locked because of too many failed logins.<br />If this is the case, <em>please try again in {$lockout_time} minutes</em>.</pre>";

		// Count the failure so lockout can engage
		$data = $db->prepare( 'UPDATE users SET failed_login = (failed_login + 1) WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	}

	// Record the attempt time
	$data = $db->prepare( 'UPDATE users SET last_login = now() WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->execute();
}

// Generate Anti-CSRF token
generateSessionToken();

?>
