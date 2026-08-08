<?php

// No unsafe-inline and no published nonce: inline script injection is dead.
$headerCSP = "Content-Security-Policy: script-src 'self'; object-src 'none'; base-uri 'none';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include'])) {
$page[ 'body' ] .= "
	" . htmlspecialchars ($_POST['include'], ENT_QUOTES, 'UTF-8') . "
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>Whatever you enter here is escaped before it is placed in the page.</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
';
