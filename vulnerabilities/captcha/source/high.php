<?php

if( isset( $_POST[ 'Change' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Hide the CAPTCHA form
	$hide_form = true;

	$pass_new  = $_POST[ 'password_new' ];
	$pass_conf = $_POST[ 'password_conf' ];

	// The CAPTCHA must be validated by the third party service. There is no
	// magic value and no User-Agent that can stand in for a real solve.
	$resp = recaptcha_check_answer(
		$_DVWA[ 'recaptcha_private_key' ],
		isset( $_POST['g-recaptcha-response'] ) ? $_POST['g-recaptcha-response'] : ''
	);

	if( !$resp ) {
		$html     .= "<pre><br />The CAPTCHA was incorrect. Please try again.</pre>";
		$hide_form = false;
		return;
	}

	if( $pass_new == $pass_conf ) {
		$pass_new     = md5( $pass_new );
		$current_user = dvwaCurrentUser();

		$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE `users` SET password = ? WHERE user = ?" );
		if( $stmt ) {
			mysqli_stmt_bind_param( $stmt, "ss", $pass_new, $current_user );
			mysqli_stmt_execute( $stmt );
			mysqli_stmt_close( $stmt );
		}

		$html .= "<pre>Password Changed.</pre>";
	}
	else {
		$html     .= "<pre>Both passwords must match.</pre>";
		$hide_form = false;
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
