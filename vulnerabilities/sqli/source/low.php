<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
	// Get input
	$id = $_REQUEST[ 'id' ];

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// Parameterised query - the user input is never concatenated into SQL.
			$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "SELECT first_name, last_name FROM users WHERE user_id = ?" );
			if( $stmt ) {
				mysqli_stmt_bind_param( $stmt, "s", $id );
				mysqli_stmt_execute( $stmt );
				$result = mysqli_stmt_get_result( $stmt );

				while( $row = mysqli_fetch_assoc( $result ) ) {
					$first = $row["first_name"];
					$last  = $row["last_name"];

					$html .= "<pre>ID: " . htmlspecialchars( $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: " . htmlspecialchars( $first, ENT_QUOTES, 'UTF-8' ) . "<br />Surname: " . htmlspecialchars( $last, ENT_QUOTES, 'UTF-8' ) . "</pre>";
				}
				mysqli_stmt_close( $stmt );
			}

			mysqli_close($GLOBALS["___mysqli_ston"]);
			break;
		case SQLITE:
			global $sqlite_db_connection;

			$stmt = $sqlite_db_connection->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = :id;' );
			$stmt->bindValue( ':id', $id, SQLITE3_TEXT );
			$results = $stmt->execute();

			if ($results) {
				while ($row = $results->fetchArray()) {
					$first = $row["first_name"];
					$last  = $row["last_name"];

					$html .= "<pre>ID: " . htmlspecialchars( $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: " . htmlspecialchars( $first, ENT_QUOTES, 'UTF-8' ) . "<br />Surname: " . htmlspecialchars( $last, ENT_QUOTES, 'UTF-8' ) . "</pre>";
				}
			}
			break;
	}
}

?>
