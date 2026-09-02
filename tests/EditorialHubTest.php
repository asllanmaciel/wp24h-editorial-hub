<?php

declare( strict_types=1 );

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/PostPresenter.php';
require_once __DIR__ . '/../src/View.php';
require_once __DIR__ . '/../src/EditorialDataSource.php';
require_once __DIR__ . '/../src/PostRepository.php';
require_once __DIR__ . '/../src/EditorialHub.php';

use WP24H\EditorialHub\EditorialDataSource;
use WP24H\EditorialHub\EditorialHub;
use WP24H\EditorialHub\PostRepository;

$GLOBALS['wp24h_terms'] = array(
	(object) array( 'term_id' => 10, 'name' => 'Programação', 'slug' => 'programacao' ),
);

$make_post = static fn( int $id ): WP_Post => new WP_Post( $id, 'Post ' . $id, 'Resumo ' . $id, 'conteúdo do post', 'https://example.test/' . $id . '.webp' );

WP_Query::$calls = array();
WP_Query::$queue = array(
	array( 'posts' => array( $make_post( 1 ) ) ),
	array( 'posts' => array( $make_post( 2 ), $make_post( 3 ) ) ),
	array( 'posts' => array( $make_post( 4 ), $make_post( 5 ) ), 'max_num_pages' => 3 ),
);

$repository = new PostRepository();
$home       = $repository->home( '', '', 1 );
assert_same( 1, $home['hero']['id'], 'The explicitly featured query provides the hero.' );
assert_same( array( 2, 3 ), array_column( $home['secondary'], 'id' ) );
assert_same( array( 4, 5 ), array_column( $home['posts'], 'id' ) );
assert_same( array( 1, 2, 3 ), WP_Query::$calls[2]['post__not_in'], 'The feed does not repeat highlighted posts.' );
assert_contains( 'hub_topic=programacao', $home['categories'][1]['url'] );
assert_contains( 'hub_page=2', $home['pagination'] );

WP_Query::$calls = array();
WP_Query::$queue = array( array( 'posts' => array( $make_post( 6 ) ), 'max_num_pages' => 1 ) );
$filtered = $repository->home( 'agentes', 'programacao', 2 );
assert_true( $filtered['is_filtered'], 'Search, topic, or later pages use results mode.' );
assert_same( 1, count( WP_Query::$calls ), 'Filtered mode skips highlight queries.' );
assert_same( 'agentes', WP_Query::$calls[0]['s'] );
assert_same( 'programacao', WP_Query::$calls[0]['category_name'] );
assert_same( 2, WP_Query::$calls[0]['paged'] );

WP_Query::$calls = array();
WP_Query::$queue = array( array( 'posts' => array( $make_post( 7 ) ), 'max_num_pages' => 4 ) );
$repository->home( 'agentes', 'programacao', 1 );
assert_contains( 'hub_search=agentes', $GLOBALS['wp24h_paginate_args']['base'], 'Pagination preserves the active search.' );
assert_contains( 'hub_topic=programacao', $GLOBALS['wp24h_paginate_args']['base'], 'Pagination preserves the active topic.' );
assert_contains( 'hub_page=%#%', $GLOBALS['wp24h_paginate_args']['base'], 'Pagination exposes an unencoded WordPress page placeholder.' );
assert_true( ! str_contains( $GLOBALS['wp24h_paginate_args']['base'], '%25%23%25' ), 'The page placeholder is not URL-encoded.' );

$source = new class() implements EditorialDataSource {
	public array $received = array();

	public function home( string $search, string $topic, int $page ): array {
		$this->received = compact( 'search', 'topic', 'page' );
		return array(
			'hero' => null, 'secondary' => array(), 'posts' => array(),
			'categories' => array( array( 'name' => 'Todos', 'slug' => '', 'url' => 'https://example.test/blog/' ) ),
			'active_category' => $topic, 'search' => $search, 'pagination' => '',
			'courses_url' => 'https://example.test/cursos/', 'is_filtered' => true,
		);
	}
};

$hub = new EditorialHub( $source, 6, __DIR__ . '/../wp24h-editorial-hub.php' );
$_GET = array( 'hub_search' => ' <b>agentes</b> ', 'hub_topic' => 'PROGRAMAÇÃO<script>', 'hub_page' => '2' );
assert_contains( 'wp24h-editorial-hub', $hub->filterContent( 'ORIGINAL' ) );
assert_same( 'agentes', $source->received['search'] );
assert_same( 'programaoscript', $source->received['topic'] );
assert_same( 2, $source->received['page'] );

$GLOBALS['wp24h_is_page'] = false;
assert_same( 'ORIGINAL', $hub->filterContent( 'ORIGINAL' ), 'Other pages retain their original content.' );
$GLOBALS['wp24h_is_page'] = true;
$GLOBALS['wp24h_in_the_loop'] = false;
assert_same( 'ORIGINAL', $hub->filterContent( 'ORIGINAL' ), 'Secondary loops retain their original content.' );
$GLOBALS['wp24h_in_the_loop'] = true;

$failing_source = new class() implements EditorialDataSource {
	public function home( string $search, string $topic, int $page ): array {
		throw new RuntimeException( 'Repository unavailable.' );
	}
};
assert_same( 'ORIGINAL', ( new EditorialHub( $failing_source, 6, __FILE__ ) )->filterContent( 'ORIGINAL' ), 'Repository failures preserve Elementor content.' );

echo "EditorialHubTest passed\n";
