<?php

declare( strict_types=1 );

function esc_url( string $value ): string {
	return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
}

function esc_attr( string $value ): string {
	return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
}

function esc_html( string $value ): string {
	return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
}

function add_query_arg( string $key, string $value, string $url ): string {
	return $url . '?' . rawurlencode( $key ) . '=' . rawurlencode( $value );
}

function assert_true( bool $condition, string $message ): void {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

require_once dirname( __DIR__ ) . '/src/View.php';

$topics = array(
	array( 'slug' => '', 'name' => 'Todos', 'url' => '/blog/' ),
	array( 'slug' => 'programacao', 'name' => 'Programação', 'url' => '/blog/?hub_topic=programacao' ),
	array( 'slug' => 'desenvolvimento-pessoal', 'name' => 'Desenvolvimento Pessoal', 'url' => '/blog/?hub_topic=desenvolvimento-pessoal' ),
	array( 'slug' => 'empreendedorismo', 'name' => 'Empreendedorismo', 'url' => '/blog/?hub_topic=empreendedorismo' ),
	array( 'slug' => 'cursos', 'name' => 'Cursos', 'url' => '/blog/?hub_topic=cursos' ),
	array( 'slug' => 'guia-para-iniciantes', 'name' => 'Guia para Iniciantes', 'url' => '/blog/?hub_topic=guia-para-iniciantes' ),
	array( 'slug' => 'dinheiro', 'name' => 'Dinheiro', 'url' => '/blog/?hub_topic=dinheiro' ),
	array( 'slug' => 'marketing-digital', 'name' => 'Marketing Digital', 'url' => '/blog/?hub_topic=marketing-digital' ),
	array( 'slug' => 'inteligencia-artificial', 'name' => 'Inteligência Artificial', 'url' => '/blog/?hub_topic=inteligencia-artificial' ),
	array( 'slug' => 'desenvolvimento', 'name' => 'Desenvolvimento', 'url' => '/blog/?hub_topic=desenvolvimento' ),
);

$html = \WP24H\EditorialHub\View::render(
	array(
		'categories'      => $topics,
		'active_category' => '',
		'courses_url'     => '/cursos/',
	)
);

assert_true( 10 === substr_count( $html, 'class="wp24h-hub-topic__icon"' ), 'Every topic renders one icon container.' );
assert_true( 10 === substr_count( $html, 'class="wp24h-hub-topic__label"' ), 'Every topic renders a wrapping label.' );
assert_true( str_contains( $html, '<svg' ), 'Icons are inline SVG and need no external library.' );
assert_true( str_contains( $html, 'aria-hidden="true"' ), 'Decorative topic icons are hidden from assistive technology.' );

$hero_end      = strpos( $html, '</header>' );
$search_start  = strpos( $html, 'class="wp24h-hub-search"' );
$topics_start  = strpos( $html, 'class="wp24h-hub-topics"' );
$sharing_start = strpos( $html, 'class="wp24h-hub-sharing"' );

assert_true( false !== $hero_end, 'The editorial hero is present.' );
assert_true( false !== $search_start && $search_start < $hero_end, 'Search is rendered inside the hero.' );
assert_true( false !== $topics_start && $topics_start > $search_start && $topics_start < $hero_end, 'Topic filters are rendered below search inside the hero.' );
assert_true( false !== $sharing_start && $sharing_start > $topics_start && $sharing_start < $hero_end, 'Icon sharing is rendered below the topic filters inside the hero.' );
assert_true( 4 === substr_count( $html, 'class="wp24h-hub-share"' ), 'The hero exposes four share actions.' );
assert_true( 4 === substr_count( $html, 'class="wp24h-hub-share"' ) && 4 === preg_match_all( '/class="wp24h-hub-share"[^>]*aria-label="Compartilhar no [^"]+"[^>]*>\s*<svg/s', $html ), 'Share actions are accessible icon-only links.' );
assert_true( 1 === substr_count( $html, '<h1>' ), 'The hub renders a single primary heading.' );

$css = file_get_contents( dirname( __DIR__ ) . '/assets/editorial-hub.css' );
assert_true( false !== $css, 'Topic stylesheet is readable.' );
assert_true( str_contains( $css, '.wp24h-hub-topics { display: grid;' ), 'Topics use a grid instead of a scroller.' );
assert_true( str_contains( $css, 'grid-template-columns: repeat(5, minmax(0, 1fr))' ), 'Desktop topics form two rows of five.' );
assert_true( str_contains( $css, 'grid-template-columns: repeat(3, minmax(0, 1fr))' ), 'Tablet topics use three columns.' );
assert_true( str_contains( $css, 'grid-template-columns: repeat(2, minmax(0, 1fr))' ), 'Mobile topics use two columns.' );
assert_true( ! str_contains( $css, '.wp24h-hub-topics { display: flex;' ), 'The horizontal topic scroller is removed.' );

echo "PASS: topic boxes render as an accessible responsive grid.\n";
