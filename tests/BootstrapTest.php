<?php

declare( strict_types=1 );

require_once __DIR__ . '/bootstrap.php';

defined( 'ABSPATH' ) || define( 'ABSPATH', __DIR__ . '/' );
require_once __DIR__ . '/../wp24h-editorial-hub.php';

assert_same( '1.0.8', WP24H_EDITORIAL_HUB_VERSION );
assert_true( isset( $GLOBALS['wp24h_editorial_hub'] ), 'The plugin keeps its controller alive.' );
assert_true( isset( $GLOBALS['wp24h_filters']['the_content'] ), 'Bootstrap registers the public replacement.' );

echo "BootstrapTest passed\n";
