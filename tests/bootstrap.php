<?php

declare( strict_types=1 );

function assert_true( bool $condition, string $message = 'Assertion failed.' ): void {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

function assert_same( mixed $expected, mixed $actual, string $message = '' ): void {
	if ( $expected !== $actual ) {
		throw new RuntimeException( $message ?: 'Values are not identical.' );
	}
}

function assert_contains( string $needle, string $haystack, string $message = '' ): void {
	assert_true( str_contains( $haystack, $needle ), $message ?: 'Missing expected text: ' . $needle );
}

function esc_html( mixed $value ): string {
	return htmlspecialchars( (string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
}

function esc_attr( mixed $value ): string {
	return esc_html( $value );
}

function esc_url( mixed $value ): string {
	return filter_var( (string) $value, FILTER_SANITIZE_URL ) ?: '';
}

function wp_strip_all_tags( string $value ): string {
	return strip_tags( $value );
}

function strip_shortcodes( string $value ): string {
	return preg_replace( '/\[[^\]]+\]/', '', $value ) ?? $value;
}

function get_permalink( int $post_id ): string {
	return 'https://example.test/post-' . $post_id . '/';
}

function get_the_post_thumbnail_url( WP_Post $post, string $size ): string {
	return $post->thumbnail_url;
}

function get_the_category( int $post_id ): array {
	return array( (object) array( 'name' => 'Inteligência Artificial', 'slug' => 'inteligencia-artificial' ) );
}

function get_the_date( string $format, WP_Post $post ): string {
	return '2 set 2026';
}

$GLOBALS['wp24h_is_page']       = true;
$GLOBALS['wp24h_in_the_loop']   = true;
$GLOBALS['wp24h_is_main_query'] = true;
$GLOBALS['wp24h_terms']         = array();
$GLOBALS['wp24h_registered_meta'] = array();
$GLOBALS['wp24h_meta_boxes']      = array();
$GLOBALS['wp24h_post_meta']       = array();
$GLOBALS['wp24h_deleted_meta']    = array();
$GLOBALS['wp24h_updated_meta']    = array();
$GLOBALS['wp24h_nonce_valid']     = true;
$GLOBALS['wp24h_can_edit']        = true;
$GLOBALS['wp24h_autosave']        = false;
$GLOBALS['wp24h_revision']        = false;
$GLOBALS['wp24h_other_featured']  = array();
$GLOBALS['wp24h_actions']         = array();
$GLOBALS['wp24h_filters']         = array();
$GLOBALS['wp24h_styles']          = array();

function is_page( int $page_id = 0 ): bool {
	return $GLOBALS['wp24h_is_page'] && ( 0 === $page_id || 6 === $page_id );
}

function in_the_loop(): bool {
	return $GLOBALS['wp24h_in_the_loop'];
}

function is_main_query(): bool {
	return $GLOBALS['wp24h_is_main_query'];
}

function sanitize_text_field( mixed $value ): string {
	return trim( strip_tags( (string) $value ) );
}

function sanitize_key( mixed $value ): string {
	return preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $value ) ) ?? '';
}

function wp_unslash( mixed $value ): mixed {
	return $value;
}

function absint( mixed $value ): int {
	return abs( (int) $value );
}

function home_url( string $path = '' ): string {
	return 'https://example.test' . $path;
}

function add_query_arg( array|string $key, mixed $value = null, ?string $url = null ): string {
	$args = is_array( $key ) ? $key : array( $key => $value );
	$url  = is_array( $key ) ? (string) $value : (string) $url;
	$separator = str_contains( $url, '?' ) ? '&' : '?';

	return $url . $separator . http_build_query( $args );
}

function get_terms( array $args ): array {
	return $GLOBALS['wp24h_terms'];
}

function get_category_link( int $term_id ): string {
	return 'https://example.test/category/' . $term_id . '/';
}

function is_wp_error( mixed $value ): bool {
	return $value instanceof WP_Error;
}

function paginate_links( array $args ): string {
	$GLOBALS['wp24h_paginate_args'] = $args;
	return '<a href="?hub_page=2">2</a>';
}

function wp_reset_postdata(): void {}

function register_post_meta( string $post_type, string $key, array $args ): void {
	$GLOBALS['wp24h_registered_meta'] = compact( 'post_type', 'key', 'args' );
}

function add_meta_box( string $id, string $title, callable $callback, string $screen, string $context, string $priority ): void {
	$GLOBALS['wp24h_meta_boxes'] = compact( 'id', 'title', 'callback', 'screen', 'context', 'priority' );
}

function wp_nonce_field( string $action, string $name ): void {
	echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="test-nonce">';
}

function wp_verify_nonce( string $nonce, string $action ): bool {
	return $GLOBALS['wp24h_nonce_valid'];
}

function current_user_can( string $capability, int $post_id = 0 ): bool {
	return $GLOBALS['wp24h_can_edit'];
}

function wp_is_post_autosave( int $post_id ): bool {
	return $GLOBALS['wp24h_autosave'];
}

function wp_is_post_revision( int $post_id ): bool {
	return $GLOBALS['wp24h_revision'];
}

function get_post_meta( int $post_id, string $key, bool $single = false ): mixed {
	return $GLOBALS['wp24h_post_meta'][ $post_id ][ $key ] ?? '';
}

function update_post_meta( int $post_id, string $key, mixed $value ): void {
	$GLOBALS['wp24h_updated_meta'][] = compact( 'post_id', 'key', 'value' );
}

function delete_post_meta( int $post_id, string $key ): void {
	$GLOBALS['wp24h_deleted_meta'][] = compact( 'post_id', 'key' );
}

function get_posts( array $args ): array {
	return $GLOBALS['wp24h_other_featured'];
}

function checked( mixed $checked, mixed $current = true, bool $display = true ): string {
	$value = (string) $checked === (string) $current ? ' checked="checked"' : '';
	if ( $display ) {
		echo $value;
	}
	return $value;
}

function add_action( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
	$GLOBALS['wp24h_actions'][ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function add_filter( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
	$GLOBALS['wp24h_filters'][ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function plugins_url( string $path, string $plugin_file ): string {
	return 'https://example.test/wp-content/plugins/wp24h-editorial-hub/' . ltrim( $path, '/' );
}

function wp_enqueue_style( string $handle, string $src, array $dependencies, string $version ): void {
	$GLOBALS['wp24h_styles'][] = compact( 'handle', 'src', 'dependencies', 'version' );
}

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {}
}

if ( ! class_exists( 'WP_Query' ) ) {
	class WP_Query {
		public static array $queue = array();
		public static array $calls = array();
		public array $posts = array();
		public int $max_num_pages = 1;

		public function __construct( array $args ) {
			self::$calls[] = $args;
			$result = array_shift( self::$queue ) ?: array();
			$this->posts = $result['posts'] ?? array();
			$this->max_num_pages = $result['max_num_pages'] ?? 1;
		}
	}
}

if ( ! class_exists( 'WP_Post' ) ) {
	class WP_Post {
		public int $ID;
		public string $post_title;
		public string $post_excerpt;
		public string $post_content;
		public string $thumbnail_url;

		public function __construct( int $id, string $title, string $excerpt, string $content, string $thumbnail_url ) {
			$this->ID            = $id;
			$this->post_title    = $title;
			$this->post_excerpt  = $excerpt;
			$this->post_content  = $content;
			$this->thumbnail_url = $thumbnail_url;
		}
	}
}
