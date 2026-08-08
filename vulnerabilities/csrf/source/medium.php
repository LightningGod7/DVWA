<?php

if( !function_exists( 'dvwaSameOriginRequest' ) ) {
	// Reject state changing requests whose Origin/Referer is not this site.
	function dvwaSameOriginRequest() {
		$host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		$source = '';
		if( !empty( $_SERVER['HTTP_ORIGIN'] ) ) {
			$source = $_SERVER['HTTP_ORIGIN'];
		} elseif( !empty( $_SERVER['HTTP_REFERER'] ) ) {
			$source = $_SERVER['HTTP_REFERER'];
		} else {
			return true;
		}
		$parsed = parse_url( $source, PHP_URL_HOST );
		if( empty( $parsed ) ) {
			return false;
		}
		$port = parse_url( $source, PHP_URL_PORT );
		$bare = preg_replace( '/:\d+$/', '', $host );
		return ( strcasecmp( $parsed, $bare ) === 0 ) || ( $port && strcasecmp( $parsed . ':' . $port, $host ) === 0 );
	}
}

if( isset( $_REQUEST[ 'Change' ] ) ) {
	// Check Anti-CSRF token - a forged cross site request cannot read this value.
	checkToken( isset( $_REQUEST[ 'user_token' ] ) ? $_REQUEST[ 'user_token' ] : '', $_SESSION[ 'session_token' ], 'index.php' );

	if( !dvwaSameOriginRequest() ) {
		$html .= "<pre>That request didn't look correct.</pre>";
	}
	else {
		// Get input
		$pass_new  = $_REQUEST[ 'password_new' ];
		$pass_conf = $_REQUEST[ 'password_conf' ];

		// Do the passwords match?
		if( $pass_new == $pass_conf ) {
			$pass_new = md5( stripslashes( $pass_new ) );

			// Update the database with a parameterised statement
			$current_user = dvwaCurrentUser();
			$data = $db->prepare( 'UPDATE users SET password = (:password) WHERE user = (:user);' );
			$data->bindParam( ':password', $pass_new, PDO::PARAM_STR );
			$data->bindParam( ':user', $current_user, PDO::PARAM_STR );
			$data->execute();

			// Feedback for the user
			$html .= "<pre>Password Changed.</pre>";
		}
		else {
			// Issue with passwords matching
			$html .= "<pre>Passwords did not match.</pre>";
		}
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
