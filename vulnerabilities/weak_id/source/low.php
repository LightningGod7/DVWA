<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Session identifiers must be unpredictable: 160 bits from a CSPRNG.
	// HttpOnly stops script access and SameSite blocks cross site replay.
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value, [
		"expires"  => time() + 3600,
		"path"     => "/vulnerabilities/weak_id/",
		"httponly" => true,
		"samesite" => "Strict",
	]);
}
?>
