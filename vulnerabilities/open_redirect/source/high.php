<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$target = $_GET['redirect'];

	// Allow-list: only the local info page, with a numeric id, may ever be a target.
	// Absolute URLs, protocol relative URLs, traversal and CRLF are all rejected.
	if (preg_match ('#^info\.php(\?id=[0-9]+)?$#', $target)) {
		header ("location: " . $target);
		exit;
	}

	http_response_code (500);
	?>
	<p>You can only redirect to the info page.</p>
	<?php
	exit;
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
