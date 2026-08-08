<?php

if( isset( $_COOKIE[ 'id' ] ) ) {
	// Get input
	$id = $_COOKIE[ 'id' ];
	$exists = false;

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// Parameterised query - user input can never alter the statement structure.
			$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1" );
			if( $stmt ) {
				mysqli_stmt_bind_param( $stmt, "s", $id );
				mysqli_stmt_execute( $stmt );
				$result = mysqli_stmt_get_result( $stmt );
				if( $result !== false ) {
					$exists = ( mysqli_num_rows( $result ) > 0 );
				}
				mysqli_stmt_close( $stmt );
			}
			break;
		case SQLITE:
			global $sqlite_db_connection;

			$stmt = $sqlite_db_connection->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;' );
			$stmt->bindValue( ':id', $id, SQLITE3_TEXT );
			$results = $stmt->execute();
			if( $results !== false ) {
				$row = $results->fetchArray();
				$exists = $row !== false;
			}
			break;
	}

	if ($exists) {
		// Feedback for end user
		$html .= '<pre>User ID exists in the database.</pre>';
	} else {
		// Feedback for end user
		$html .= '<pre>User ID is MISSING from the database.</pre>';
	}
}

?>
