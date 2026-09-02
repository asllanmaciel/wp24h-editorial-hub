<?php

declare( strict_types=1 );

$tests = array(
	'ViewTest.php',
	'EditorialHubTest.php',
	'FeaturedPostTest.php',
	'AssetsTest.php',
	'BootstrapTest.php',
	'PackageTest.php',
);

foreach ( $tests as $test ) {
	$command = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __DIR__ . '/../tests/' . $test );
	passthru( $command, $exit_code );
	if ( 0 !== $exit_code ) {
		exit( $exit_code );
	}
}

echo count( $tests ) . " test files passed\n";
