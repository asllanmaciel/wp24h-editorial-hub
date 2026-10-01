<?php

declare( strict_types=1 );

require_once __DIR__ . '/bootstrap.php';

$package = __DIR__ . '/../dist/wp24h-editorial-hub-1.0.9.zip';
assert_true( is_file( $package ), 'The production ZIP exists.' );

$zip = new ZipArchive();
assert_same( true, $zip->open( $package ), 'The production ZIP opens.' );

$entries = array();
for ( $index = 0; $index < $zip->numFiles; $index++ ) {
	$entries[] = $zip->getNameIndex( $index );
}

$required = array(
	'wp24h-editorial-hub/wp24h-editorial-hub.php',
	'wp24h-editorial-hub/src/EditorialDataSource.php',
	'wp24h-editorial-hub/src/PostPresenter.php',
	'wp24h-editorial-hub/src/PostRepository.php',
	'wp24h-editorial-hub/src/View.php',
	'wp24h-editorial-hub/src/EditorialHub.php',
	'wp24h-editorial-hub/assets/editorial-hub.css',
	'wp24h-editorial-hub/readme.txt',
	'wp24h-editorial-hub/LICENSE',
);

foreach ( $required as $entry ) {
	assert_true( in_array( $entry, $entries, true ), 'Missing package entry: ' . $entry );
}

foreach ( $entries as $entry ) {
	assert_true( ! preg_match( '#/(?:tests|bin|\.git)(?:/|$)#', $entry ), 'Development file leaked into ZIP: ' . $entry );
	$contents = $zip->getFromName( $entry );
	if ( false !== $contents ) {
		assert_true( ! preg_match( '/github_pat_|gh[pousr]_[A-Za-z0-9]{20,}/', $contents ), 'Secret marker found in ZIP: ' . $entry );
	}
}

$zip->close();
echo "PackageTest passed\n";
