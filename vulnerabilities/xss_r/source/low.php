<?php

// Is there any input?
if( array_key_exists( "name", $_GET ) && $_GET[ 'name' ] != NULL ) {
	// Contextual output encoding: the value is rendered as text, never as markup.
	$name = htmlspecialchars( $_GET[ 'name' ], ENT_QUOTES | ENT_HTML5, 'UTF-8' );

	// Feedback for end user
	$html .= "<pre>Hello {$name}</pre>";
}

?>
