<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = stripslashes( $_REQUEST[ 'ip' ] );

	// Strict allow-list validation: only a dotted-quad IPv4 address is accepted.
	// This removes every shell metacharacter path (;, &&, ||, |, `, $(), newline).
	if( filter_var( $target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) !== false ) {
		// Argument is additionally escaped so it can never be re-interpreted by the shell.
		$safe = escapeshellarg( $target );

		// Determine OS and execute the ping command.
		if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
			// Windows
			$cmd = shell_exec( 'ping  ' . $safe );
		}
		else {
			// *nix
			$cmd = shell_exec( 'ping  -c 4 ' . $safe );
		}

		// Feedback for the end user
		$html .= "<pre>" . htmlspecialchars( $cmd, ENT_QUOTES, 'UTF-8' ) . "</pre>";
	}
	else {
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	}
}

?>
