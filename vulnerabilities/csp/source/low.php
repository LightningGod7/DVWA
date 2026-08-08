<?php

// Only same origin scripts are permitted. Loading code from user supplied
// third party origins is what made this level exploitable.
$headerCSP = "Content-Security-Policy: script-src 'self'; object-src 'none'; base-uri 'none';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include']) && $_POST['include'] !== "") {
	$page[ 'body' ] .= "
	<p>Including scripts from external sources is not permitted.</p>
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>The Content Security Policy now only allows scripts served from this origin, so remote includes are rejected:</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
';
