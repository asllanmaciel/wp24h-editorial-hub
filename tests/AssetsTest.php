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

$hub->register();
assert_true( isset( $GLOBALS['wp24h_actions']['wp_enqueue_scripts'] ), 'The public asset hook is registered.' );
assert_true( isset( $GLOBALS['wp24h_actions']['add_meta_boxes'] ), 'The featured selector hook is registered.' );
assert_true( isset( $GLOBALS['wp24h_actions']['save_post'] ), 'The protected featured save hook is registered.' );
assert_true( isset( $GLOBALS['wp24h_filters']['the_content'] ), 'The hub content filter is registered.' );
assert_true( isset( $GLOBALS['wp24h_filters']['body_class'] ), 'The hub page body class filter is registered.' );

$GLOBALS['wp24h_is_page'] = false;
$hub->enqueueAssets();
assert_same( array(), $GLOBALS['wp24h_styles'], 'Assets are not loaded on unrelated pages.' );

$GLOBALS['wp24h_is_page'] = true;
$hub->enqueueAssets();
assert_same( 'wp24h-editorial-hub', $GLOBALS['wp24h_styles'][0]['handle'] );
assert_contains( 'assets/editorial-hub.css', $GLOBALS['wp24h_styles'][0]['src'] );
assert_same( '1.0.8', $GLOBALS['wp24h_styles'][0]['version'] );

$GLOBALS['wp24h_is_page'] = false;
assert_same( array( 'existing' ), $hub->addBodyClass( array( 'existing' ) ), 'Other pages keep their body classes.' );
$GLOBALS['wp24h_is_page'] = true;
assert_same( array( 'existing', 'wp24h-editorial-hub-page' ), $hub->addBodyClass( array( 'existing' ) ) );

$css = file_get_contents( __DIR__ . '/../assets/editorial-hub.css' );
assert_true( false !== $css, 'The public stylesheet exists.' );
assert_contains( '--wp24h-navy:', $css );
assert_contains( '--wp24h-cyan:', $css );
assert_contains( '.wp24h-hub-feed { display: grid; grid-template-columns: 1fr', $css, 'The desktop feed is a single editorial stream.' );
assert_contains( '.wp24h-hub-card { display: grid; grid-template-columns: minmax(190px, 28%) minmax(0, 1fr)', $css, 'Desktop cards use a compact horizontal image-and-copy layout.' );
assert_contains( '@media (max-width: 760px)', $css );
assert_contains( 'grid-template-columns: 1fr', $css, 'The mobile feed uses one column.' );
assert_contains( '.wp24h-hub-topics { display: grid;', $css, 'Topic navigation uses the current responsive grid.' );
assert_contains( ':focus-visible', $css, 'Keyboard focus remains visible.' );
assert_contains( '.wp24h-editorial-hub-page .elementor > .e-con:has(.elementor-page-title)', $css, 'The legacy Elementor title banner is hidden only on the configured hub page.' );
assert_contains( '.wp24h-editorial-hub-page { overflow-x: clip;', $css, 'The full-bleed layout does not create horizontal page scrolling.' );
assert_contains( 'min-height: 175px', $css, 'Feed cards stay compact when excerpts are short.' );
assert_contains( '.wp24h-hub-label--violet', $css, 'Category badges have distinct visual tones.' );
assert_contains( '.wp24h-hub-card-labels { display: flex;', $css, 'Category and series badges share a wrapping label row.' );
assert_contains( '.wp24h-hub-series-label { display: inline-flex;', $css, 'Series identity has a compact badge treatment.' );
assert_contains( '.wp24h-hub-card__body { min-height: 0;', $css, 'Mobile card bodies do not preserve the old empty vertical space.' );

echo "AssetsTest passed\n";
