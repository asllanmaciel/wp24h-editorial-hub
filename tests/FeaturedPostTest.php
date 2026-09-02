<?php

declare( strict_types=1 );

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/EditorialDataSource.php';
require_once __DIR__ . '/../src/EditorialHub.php';

use WP24H\EditorialHub\EditorialDataSource;
use WP24H\EditorialHub\EditorialHub;

$source = new class() implements EditorialDataSource {
	public function home( string $search, string $topic, int $page ): array { return array(); }
};
$hub = new EditorialHub( $source, 6, __DIR__ . '/../wp24h-editorial-hub.php' );

$hub->registerFeaturedMeta();
assert_same( 'post', $GLOBALS['wp24h_registered_meta']['post_type'] );
assert_same( '_wp24h_editorial_featured', $GLOBALS['wp24h_registered_meta']['key'] );
assert_same( 'boolean', $GLOBALS['wp24h_registered_meta']['args']['type'] );
assert_true( $GLOBALS['wp24h_registered_meta']['args']['show_in_rest'], 'Featured state is available to authenticated editors through REST.' );

$hub->addFeaturedMetaBox();
assert_same( 'wp24h-editorial-featured', $GLOBALS['wp24h_meta_boxes']['id'] );
assert_same( 'side', $GLOBALS['wp24h_meta_boxes']['context'] );

$post = new WP_Post( 8, 'Post em destaque', '', 'Conteúdo', '' );
$GLOBALS['wp24h_post_meta'][8]['_wp24h_editorial_featured'] = '1';
ob_start();
$hub->renderFeaturedMetaBox( $post );
$box = (string) ob_get_clean();
assert_contains( 'name="wp24h_editorial_featured"', $box );
assert_contains( 'checked="checked"', $box );
assert_contains( 'name="wp24h_editorial_featured_nonce"', $box );

$reset_writes = static function (): void {
	$GLOBALS['wp24h_deleted_meta'] = array();
	$GLOBALS['wp24h_updated_meta'] = array();
};

$_POST = array( 'wp24h_editorial_featured_nonce' => 'invalid', 'wp24h_editorial_featured' => '1' );
$GLOBALS['wp24h_nonce_valid'] = false;
$reset_writes();
$hub->saveFeaturedMeta( 8, $post );
assert_same( array(), $GLOBALS['wp24h_updated_meta'], 'An invalid nonce prevents writes.' );

$GLOBALS['wp24h_nonce_valid'] = true;
$GLOBALS['wp24h_can_edit'] = false;
$reset_writes();
$hub->saveFeaturedMeta( 8, $post );
assert_same( array(), $GLOBALS['wp24h_updated_meta'], 'Insufficient capability prevents writes.' );

$GLOBALS['wp24h_can_edit'] = true;
$GLOBALS['wp24h_autosave'] = true;
$reset_writes();
$hub->saveFeaturedMeta( 8, $post );
assert_same( array(), $GLOBALS['wp24h_updated_meta'], 'Autosaves do not change editorial selection.' );

$GLOBALS['wp24h_autosave'] = false;
$GLOBALS['wp24h_other_featured'] = array( 2, 3, 8 );
$reset_writes();
$hub->saveFeaturedMeta( 8, $post );
assert_same( array( 2, 3 ), array_column( $GLOBALS['wp24h_deleted_meta'], 'post_id' ), 'Selecting a feature clears only other posts.' );
assert_same( 8, $GLOBALS['wp24h_updated_meta'][0]['post_id'] );
assert_same( 1, $GLOBALS['wp24h_updated_meta'][0]['value'] );

$_POST = array( 'wp24h_editorial_featured_nonce' => 'valid' );
$reset_writes();
$hub->saveFeaturedMeta( 8, $post );
assert_same( 8, $GLOBALS['wp24h_deleted_meta'][0]['post_id'], 'Unchecking removes the current featured flag.' );
assert_same( array(), $GLOBALS['wp24h_updated_meta'] );

echo "FeaturedPostTest passed\n";
