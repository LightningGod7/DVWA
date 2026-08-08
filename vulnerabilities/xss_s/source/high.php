<?php

if( isset( $_POST[ 'btnSign' ] ) ) {
	// Get input
	$message = trim( $_POST[ 'mtxMessage' ] );
	$name    = trim( $_POST[ 'txtName' ] );

	// Encode both fields so they can only ever be rendered as text.
	$message = htmlspecialchars( stripslashes( $message ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$name    = htmlspecialchars( stripslashes( $name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );

	// Update database using a parameterised statement.
	$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "INSERT INTO guestbook ( comment, name ) VALUES ( ?, ? )" );
	if( $stmt ) {
		mysqli_stmt_bind_param( $stmt, "ss", $message, $name );
		mysqli_stmt_execute( $stmt );
		mysqli_stmt_close( $stmt );
	}
}

?>
