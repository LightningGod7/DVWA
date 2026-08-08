<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Session identifiers must be unpredictable: 160 bits from a CSPRNG.
	// The cookie is additionally scoped and flagged HttpOnly and Secure.
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", $_SERVER['HTTP_HOST'], true, true);
}
?>
