<?php

declare( strict_types=1 );

namespace WP24H\EditorialHub;

final class View {
	/**
	 * @param array<string,mixed> $data
	 */
	public static function render( array $data ): string {
		$hero        = is_array( $data['hero'] ?? null ) ? $data['hero'] : null;
		$secondary   = is_array( $data['secondary'] ?? null ) ? $data['secondary'] : array();
		$posts       = is_array( $data['posts'] ?? null ) ? $data['posts'] : array();
		$categories  = is_array( $data['categories'] ?? null ) ? $data['categories'] : array();
		$active      = (string) ( $data['active_category'] ?? '' );
		$search      = (string) ( $data['search'] ?? '' );
		$is_filtered = (bool) ( $data['is_filtered'] ?? false );
		$home_url    = (string) ( $categories[0]['url'] ?? '' );
		$category_urls = array_column( $categories, 'url', 'slug' );

		ob_start();
		?>
		<main class="wp24h-editorial-hub" id="conteudo-principal">
			<header class="wp24h-hub-hero">
				<div class="wp24h-hub-shell wp24h-hub-hero__inner">
					<div class="wp24h-hub-hero__copy">
						<span class="wp24h-hub-kicker">Conteúdo aberto · Aplicação real</span>
						<h1>Tecnologia, IA e Negócios Digitais</h1>
						<p>Conteúdos práticos para construir produtos, automatizar operações e transformar tecnologia em negócios reais.</p>
					</div>
					<form class="wp24h-hub-search" role="search" action="<?php echo esc_url( $home_url ); ?>" method="get">
						<label class="screen-reader-text" for="wp24h-hub-search">Pesquisar artigos</label>
						<input id="wp24h-hub-search" name="hub_search" type="search" value="<?php echo esc_attr( $search ); ?>" placeholder="Pesquisar artigos, ferramentas e tutoriais…">
						<?php if ( '' !== $active ) : ?>
							<input name="hub_topic" type="hidden" value="<?php echo esc_attr( $active ); ?>">
						<?php endif; ?>
						<button type="submit" aria-label="Pesquisar artigos"><span>Pesquisar</span><span aria-hidden="true">⌕</span></button>
					</form>
				</div>
			</header>

			<?php if ( ! $is_filtered && $hero ) : ?>
				<section class="wp24h-hub-shell wp24h-hub-featured" aria-labelledby="wp24h-featured-title">
					<h2 class="screen-reader-text" id="wp24h-featured-title">Conteúdos em destaque</h2>
					<?php self::renderHeroCard( $hero, $category_urls, $home_url ); ?>
					<div class="wp24h-hub-side">
						<?php foreach ( array_slice( $secondary, 0, 2 ) as $post ) : ?>
							<?php self::renderSecondaryCard( $post, $category_urls, $home_url ); ?>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<nav class="wp24h-hub-shell wp24h-hub-topics" aria-label="Assuntos do blog">
				<?php foreach ( $categories as $category ) : ?>
					<?php $selected = $active === (string) $category['slug']; ?>
					<a class="wp24h-hub-topic<?php echo $selected ? ' is-active' : ''; ?> wp24h-hub-label--<?php echo esc_attr( self::categoryTone( (string) $category['slug'] ) ); ?>" href="<?php echo esc_url( $category['url'] ); ?>"<?php echo $selected ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $category['name'] ); ?></a>
				<?php endforeach; ?>
			</nav>

			<section class="wp24h-hub-shell wp24h-hub-latest" aria-labelledby="wp24h-latest-title">
				<div class="wp24h-hub-section-heading">
					<div><span class="wp24h-hub-kicker"><?php echo $is_filtered ? 'Resultados' : 'Atualizações'; ?></span><h2 id="wp24h-latest-title"><?php echo $is_filtered ? 'Conteúdos encontrados' : 'Últimos conteúdos'; ?></h2></div>
					<?php if ( $is_filtered ) : ?><a href="<?php echo esc_url( $home_url ); ?>">Limpar filtros</a><?php endif; ?>
				</div>
				<?php if ( $posts ) : ?>
					<div class="wp24h-hub-feed">
						<?php foreach ( $posts as $post ) : ?><?php self::renderFeedCard( $post, $category_urls, $home_url ); ?><?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="wp24h-hub-empty"><h2>Nenhum conteúdo encontrado</h2><p>Tente outro termo ou escolha um assunto diferente.</p></div>
				<?php endif; ?>
				<?php if ( ! empty( $data['pagination'] ) ) : ?>
					<nav class="wp24h-hub-pagination" aria-label="Paginação dos artigos"><?php echo $data['pagination']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></nav>
				<?php endif; ?>
			</section>

			<section class="wp24h-hub-shell wp24h-hub-courses" aria-labelledby="wp24h-courses-title">
				<div><span class="wp24h-hub-kicker">Da leitura à prática</span><h2 id="wp24h-courses-title">Transforme conhecimento em projeto entregue</h2><p>Continue aprendendo com cursos construídos a partir de problemas, decisões e implementações reais.</p></div>
				<a class="wp24h-hub-button" href="<?php echo esc_url( (string) ( $data['courses_url'] ?? '' ) ); ?>">Conhecer os cursos <span aria-hidden="true">→</span></a>
			</section>
		</main>
		<?php

		return (string) ob_get_clean();
	}

	/** @param array<string,mixed> $post */
	private static function renderHeroCard( array $post, array $category_urls, string $home_url ): void {
		?>
		<article class="wp24h-hub-featured-card">
			<a class="wp24h-hub-featured-card__media" href="<?php echo esc_url( $post['url'] ); ?>" tabindex="-1" aria-hidden="true"><?php self::renderImage( $post, true ); ?></a>
			<div class="wp24h-hub-featured-card__body"><?php self::renderCategoryBadge( $post, $category_urls, $home_url, 'Destaque · ' ); ?><h2><a href="<?php echo esc_url( $post['url'] ); ?>"><?php echo esc_html( $post['title'] ); ?></a></h2><p><?php echo esc_html( $post['excerpt'] ); ?></p><?php self::renderMeta( $post ); ?><a class="wp24h-hub-button" href="<?php echo esc_url( $post['url'] ); ?>">Ler artigo <span aria-hidden="true">→</span></a></div>
		</article>
		<?php
	}

	/** @param array<string,mixed> $post */
	private static function renderSecondaryCard( array $post, array $category_urls, string $home_url ): void {
		?><article class="wp24h-hub-secondary-card"><a class="wp24h-hub-secondary-card__media" href="<?php echo esc_url( $post['url'] ); ?>" tabindex="-1" aria-hidden="true"><?php self::renderImage( $post ); ?></a><div><?php self::renderCategoryBadge( $post, $category_urls, $home_url ); ?><h2><a href="<?php echo esc_url( $post['url'] ); ?>"><?php echo esc_html( $post['title'] ); ?></a></h2><?php self::renderMeta( $post ); ?></div></article><?php
	}

	/** @param array<string,mixed> $post */
	private static function renderFeedCard( array $post, array $category_urls, string $home_url ): void {
		?><article class="wp24h-hub-card"><a class="wp24h-hub-card__media" href="<?php echo esc_url( $post['url'] ); ?>" tabindex="-1" aria-hidden="true"><?php self::renderImage( $post ); ?></a><div class="wp24h-hub-card__body"><?php self::renderCategoryBadge( $post, $category_urls, $home_url ); ?><h3><a href="<?php echo esc_url( $post['url'] ); ?>"><?php echo esc_html( $post['title'] ); ?></a></h3><p><?php echo esc_html( $post['excerpt'] ); ?></p><?php self::renderMeta( $post ); ?><a class="wp24h-hub-read" href="<?php echo esc_url( $post['url'] ); ?>">Ler artigo <span aria-hidden="true">→</span></a></div></article><?php
	}

	/** @param array<string,mixed> $post @param array<string,string> $category_urls */
	private static function renderCategoryBadge( array $post, array $category_urls, string $home_url, string $prefix = '' ): void {
		$slug = (string) ( $post['category']['slug'] ?? '' );
		$url  = (string) ( $category_urls[ $slug ] ?? add_query_arg( 'hub_topic', $slug, $home_url ) );
		?><a class="wp24h-hub-label wp24h-hub-label--<?php echo esc_attr( self::categoryTone( $slug ) ); ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $prefix . (string) $post['category']['name'] ); ?></a><?php
	}

	private static function categoryTone( string $slug ): string {
		$tones = array(
			'inteligencia-artificial' => 'cyan',
			'programacao'             => 'blue',
			'empreendedorismo'        => 'green',
			'cursos'                  => 'lime',
			'marketing-digital'       => 'violet',
			'dinheiro'                => 'gold',
			'saude-bem-estar'         => 'teal',
			'guia-para-iniciantes'    => 'sky',
			'desenvolvimento-pessoal' => 'rose',
			'geral'                   => 'slate',
		);

		if ( isset( $tones[ $slug ] ) ) {
			return $tones[ $slug ];
		}

		$fallback = array( 'cyan', 'blue', 'green', 'violet', 'gold', 'teal' );
		return $fallback[ (int) sprintf( '%u', crc32( $slug ) ) % count( $fallback ) ];
	}

	/** @param array<string,mixed> $post */
	private static function renderImage( array $post, bool $priority = false ): void {
		if ( empty( $post['image'] ) ) {
			echo '<span class="wp24h-hub-image-placeholder" aria-hidden="true">WP24H</span>';
			return;
		}

		printf(
			'<img src="%1$s" alt="%2$s" width="960" height="540" loading="%3$s"%4$s>',
			esc_url( $post['image'] ),
			esc_attr( $post['image_alt'] ),
			$priority ? 'eager' : 'lazy',
			$priority ? ' fetchpriority="high"' : ''
		);
	}

	/** @param array<string,mixed> $post */
	private static function renderMeta( array $post ): void {
		?><div class="wp24h-hub-meta"><time><?php echo esc_html( $post['date'] ); ?></time><span aria-hidden="true">·</span><span><?php echo esc_html( $post['reading_time'] ); ?> min de leitura</span></div><?php
	}
}
