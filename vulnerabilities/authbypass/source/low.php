<?php
/*

Only the admin user is allowed to access this page. Hiding the menu entry is
not access control, so the check is enforced server side at every level.

*/

if (dvwaCurrentUser() != "admin") {
	print "Unauthorised";
	http_response_code(403);
	exit;
}
?>
