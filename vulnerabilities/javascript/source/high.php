<?php
// The success token is minted server side from a CSPRNG and kept in the session.
// It is deliberately NEVER serialised into the response: any value the page hands
// to the browser is a value an attacker can scrape and replay, which is exactly
// how this challenge was solved. Nothing the client can read or derive is
// accepted, so the phrase can no longer be submitted with a valid token.
if ( empty( $_SESSION['javascript_token'] ) ) {
	$_SESSION['javascript_token'] = bin2hex( random_bytes( 32 ) );
}

$page[ 'body' ] .= "
<script>
	function generate_token() {
		// The token is issued and verified server side only.
		var t = document.getElementById('token');
		if (t) { t.value = ''; }
	}
	document.addEventListener('DOMContentLoaded', generate_token);
</script>
";
?>
