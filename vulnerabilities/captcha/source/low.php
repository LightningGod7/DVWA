<?php

// Step 1: solve the CAPTCHA. The result of that check is recorded in the
// session only - the client is never trusted to say it passed.
if( isset( $_POST[ 'Change' ] ) && isset( $_POST[ 'step' ] ) && ( $_POST[ 'step' ] == '1' ) ) {
	$hide_form = true;

	$pass_new  = $_POST[ 'password_new' ];
	$pass_conf = $_POST[ 'password_conf' ];

	$resp = recaptcha_check_answer(
		$_DVWA[ 'recaptcha_private_key'],
		isset( $_POST['g-recaptcha-response'] ) ? $_POST['g-recaptcha-response'] : ''
	);

	if( !$resp ) {
		unset( $_SESSION[ 'captcha_passed' ] );
		$html     .= "<pre><br />The CAPTCHA was incorrect. Please try again.</pre>";
		$hide_form = false;
		return;
	}

	if( $pass_new == $pass_conf ) {
		$_SESSION[ 'captcha_passed' ] = true;
		$safe_new  = htmlspecialchars( $pass_new, ENT_QUOTES, 'UTF-8' );
		$safe_conf = htmlspecialchars( $pass_conf, ENT_QUOTES, 'UTF-8' );
		$html .= "
			<pre><br />You passed the CAPTCHA! Click the button to confirm your changes.<br /></pre>
			<form action=\"#\" method=\"POST\">
				<input type=\"hidden\" name=\"step\" value=\"2\" />
				<input type=\"hidden\" name=\"password_new\" value=\"{$safe_new}\" />
				<input type=\"hidden\" name=\"password_conf\" value=\"{$safe_conf}\" />
				<input type=\"submit\" name=\"Change\" value=\"Change\" />
			</form>";
	}
	else {
		unset( $_SESSION[ 'captcha_passed' ] );
		$html     .= "<pre>Both passwords must match.</pre>";
		$hide_form = false;
	}
}

// Step 2: only reachable if the server recorded a successful CAPTCHA.
if( isset( $_POST[ 'Change' ] ) && isset( $_POST[ 'step' ] ) && ( $_POST[ 'step' ] == '2' ) ) {
	$hide_form = true;

	if( empty( $_SESSION[ 'captcha_passed' ] ) ) {
		$html     .= "<pre><br />You have not passed the CAPTCHA.</pre>";
		$hide_form = false;
		return;
	}

	$pass_new  = $_POST[ 'password_new' ];
	$pass_conf = $_POST[ 'password_conf' ];

	if( $pass_new == $pass_conf ) {
		unset( $_SESSION[ 'captcha_passed' ] );

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
		$html     .= "<pre>Passwords did not match.</pre>";
		$hide_form = false;
	}
}

?>
