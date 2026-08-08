<?php
// The token must not be derivable by the client. It is now an unpredictable
// single use nonce minted server side from a CSPRNG and bound to the session.
$_SESSION['javascript_token'] = bin2hex( random_bytes( 32 ) );

$page[ 'body' ] .= "
<script>
	var dvwaServerToken = " . json_encode( $_SESSION['javascript_token'] ) . ";
	function generate_token() {
		var t = document.getElementById('token');
		if (t) { t.value = dvwaServerToken; }
	}
	document.addEventListener('DOMContentLoaded', generate_token);
</script>
";
?>
