<?php

// The page we wish to display
$file = isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : '';

// Strict allow-list. Nothing outside this set can ever be included, which blocks
// path traversal, remote file inclusion and PHP stream wrappers alike.
$configFileNames = [
	'include.php',
	'file1.php',
	'file2.php',
	'file3.php',
];

if( !in_array( $file, $configFileNames, true ) ) {
	// This isn't the page we want!
	echo "ERROR: File not found!";
	exit;
}

?>
