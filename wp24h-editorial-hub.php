<?php
/**
 * Plugin Name: WP24H Editorial Hub
 * Plugin URI: https://github.com/asllanmaciel/wp24h-editorial-hub
 * Description: Replaces the public blog listing with a first-party editorial hub for open content and courses.
 * Version: 1.0.4
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Asllan Maciel
 * Author URI: https://asllanmaciel.com.br/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp24h-editorial-hub
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

define( 'WP24H_EDITORIAL_HUB_VERSION', '1.0.4' );

require_once __DIR__ . '/src/EditorialDataSource.php';
require_once __DIR__ . '/src/PostPresenter.php';
require_once __DIR__ . '/src/PostRepository.php';
require_once __DIR__ . '/src/View.php';
require_once __DIR__ . '/src/EditorialHub.php';

$wp24h_editorial_hub_page_id = defined( 'WP24H_EDITORIAL_HUB_PAGE_ID' )
	? max( 1, (int) WP24H_EDITORIAL_HUB_PAGE_ID )
	: 6;

$GLOBALS['wp24h_editorial_hub'] = new \WP24H\EditorialHub\EditorialHub(
	new \WP24H\EditorialHub\PostRepository(),
	$wp24h_editorial_hub_page_id,
	__FILE__
);
$GLOBALS['wp24h_editorial_hub']->register();
