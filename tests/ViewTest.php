<?php

declare( strict_types=1 );

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/PostPresenter.php';
require_once __DIR__ . '/../src/View.php';

use WP24H\EditorialHub\PostPresenter;
use WP24H\EditorialHub\View;

$words = implode( ' ', array_fill( 0, 401, 'palavra' ) );
assert_same( 3, PostPresenter::readingTime( $words ), 'Reading time rounds partial minutes up.' );
assert_same( 1, PostPresenter::readingTime( '' ), 'Even a short article has a one-minute reading time.' );

$GLOBALS['wp24h_series_terms'][7] = array(
	(object) array( 'term_id' => 1240, 'name' => 'Quem Pensa Enriquece na Prática', 'slug' => 'quem-pensa-enriquece' ),
);
$presented = PostPresenter::fromPost(
	new WP_Post( 7, 'IA aplicada', 'Resumo direto.', $words, 'https://example.test/cover.webp' )
);
assert_same( 'IA aplicada', $presented['title'] );
assert_same( 'Inteligência Artificial', $presented['category']['name'] );
assert_same( 3, $presented['reading_time'] );
assert_same( false, $presented['image_lazy'], 'Presented posts default to lazy images; the view promotes only the hero.' );
assert_same(
	array(
		'name' => 'Quem Pensa Enriquece na Prática',
		'slug' => 'quem-pensa-enriquece',
		'url'  => 'https://example.test/series/quem-pensa-enriquece/',
	),
	$presented['series'] ?? null,
	'Presented series posts expose the public series identity and archive URL.'
);
assert_same(
	null,
	PostPresenter::fromPost( new WP_Post( 8, 'Artigo avulso', '', 'Conteúdo.', '' ) )['series'] ?? null,
	'Posts outside a series stay unlabelled.'
);

$post = static function ( int $id, string $title, bool $series = true ): array {
	return array(
		'id'           => $id,
		'title'        => $title,
		'url'          => 'https://example.test/' . $id . '/',
		'excerpt'      => 'Uma explicação prática para transformar tecnologia em resultado.',
		'image'        => 'https://example.test/' . $id . '.webp',
		'image_alt'    => 'Capa do artigo ' . $id,
		'category'     => array( 'name' => 'Inteligência Artificial', 'slug' => 'inteligencia-artificial' ),
		'date'         => '2 set 2026',
		'reading_time' => 6,
		'series'       => $series ? array(
			'name' => 'Quem Pensa Enriquece na Prática',
			'slug' => 'quem-pensa-enriquece',
			'url'  => 'https://example.test/series/quem-pensa-enriquece/',
		) : null,
	);
};

$html = View::render(
	array(
		'hero'            => $post( 1, 'Artigo <principal>' ),
		'secondary'       => array( $post( 2, 'Segundo artigo' ), $post( 3, 'Terceiro artigo' ) ),
		'posts'           => array( $post( 4, 'Quarto artigo' ), $post( 5, 'Quinto artigo', false ) ),
		'categories'      => array(
			array( 'name' => 'Todos', 'slug' => '', 'url' => 'https://example.test/blog/' ),
			array( 'name' => 'Inteligência Artificial', 'slug' => 'inteligencia-artificial', 'url' => 'https://example.test/blog/?hub_topic=inteligencia-artificial' ),
			array( 'name' => 'Programação', 'slug' => 'programacao', 'url' => 'https://example.test/blog/?hub_topic=programacao' ),
		),
		'active_category' => '',
		'search'          => 'agentes <script>',
		'pagination'      => '<a href="?hub_page=2">2</a>',
		'courses_url'     => 'https://example.test/cursos/',
		'is_filtered'     => false,
	)
);

assert_contains( '<h1>Tecnologia, IA e Negócios Digitais</h1>', $html );
assert_contains( 'Conteúdos práticos para construir produtos', $html );
assert_contains( 'role="search"', $html );
assert_contains( 'value="agentes &lt;script&gt;"', $html, 'Search values are escaped.' );
assert_contains( 'wp24h-hub-featured', $html );
assert_same( 2, substr_count( $html, '<article class="wp24h-hub-secondary-card ' ), 'Exactly two secondary cards are rendered.' );
assert_contains( 'wp24h-hub-topic is-active', $html );
assert_contains( 'wp24h-hub-feed', $html );
assert_contains( 'Últimos conteúdos', $html );
assert_contains( '6 min de leitura', $html );
assert_contains( 'Ler artigo <span aria-hidden="true">→</span>', $html );
assert_contains( 'Conhecer os cursos', $html );
assert_contains( 'Artigo &lt;principal&gt;', $html, 'Titles are escaped.' );
assert_contains( '<nav class="wp24h-hub-pagination" aria-label="Paginação dos artigos">', $html );
assert_contains( '<a class="wp24h-hub-label wp24h-hub-label--cyan" href="https://example.test/blog/?hub_topic=inteligencia-artificial">Inteligência Artificial</a>', $html, 'Card category badges link to the matching hub filter.' );
assert_same( 5, substr_count( $html, 'class="wp24h-hub-label wp24h-hub-label--cyan"' ), 'Every editorial card exposes its category as a link.' );
assert_contains( '<a class="wp24h-hub-series-label" href="https://example.test/series/quem-pensa-enriquece/">Série · Quem Pensa Enriquece na Prática</a>', $html, 'Series badges link to the public archive and identify it by name.' );
assert_same( 4, substr_count( $html, 'class="wp24h-hub-series-label"' ), 'Every series card type gets one badge while standalone posts get none.' );

echo "ViewTest passed\n";
