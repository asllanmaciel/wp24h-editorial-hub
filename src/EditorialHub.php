<?php

declare( strict_types=1 );

namespace WP24H\EditorialHub;

use Throwable;

final class EditorialHub {
	private const VERSION = '1.0.7';

	private const FEATURED_META = '_wp24h_editorial_featured';

	public function __construct(
		private readonly EditorialDataSource $repository,
		private readonly int $pageId,
		private readonly string $pluginFile,
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'registerFeaturedMeta' ) );
		add_action( 'add_meta_boxes', array( $this, 'addFeaturedMetaBox' ) );
		add_action( 'save_post', array( $this, 'saveFeaturedMeta' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueAssets' ) );
		add_filter( 'the_content', array( $this, 'filterContent' ), 50 );
		add_filter( 'body_class', array( $this, 'addBodyClass' ) );
	}

	/**
	 * @param string[] $classes Existing body classes.
	 * @return string[]
	 */
	public function addBodyClass( array $classes ): array {
		if ( is_page( $this->pageId ) ) {
			$classes[] = 'wp24h-editorial-hub-page';
		}

		return $classes;
	}

	public function enqueueAssets(): void {
		if ( ! is_page( $this->pageId ) ) {
			return;
		}

		wp_enqueue_style(
			'wp24h-editorial-hub',
			plugins_url( 'assets/editorial-hub.css', $this->pluginFile ),
			array(),
			self::VERSION
		);
	}

	public function filterContent( string $content ): string {
		if ( ! is_page( $this->pageId ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$search = isset( $_GET['hub_search'] ) ? sanitize_text_field( wp_unslash( $_GET['hub_search'] ) ) : '';
		$topic  = isset( $_GET['hub_topic'] ) ? sanitize_key( wp_unslash( $_GET['hub_topic'] ) ) : '';
		$page   = isset( $_GET['hub_page'] ) ? max( 1, absint( $_GET['hub_page'] ) ) : 1;

		try {
			return View::render( $this->repository->home( $search, $topic, $page ) );
		} catch ( Throwable ) {
			return $content;
		}
	}

	public function registerFeaturedMeta(): void {
		register_post_meta(
			'post',
			self::FEATURED_META,
			array(
				'type'              => 'boolean',
				'single'            => true,
				'default'           => false,
				'show_in_rest'      => true,
				'sanitize_callback' => static fn( mixed $value ): bool => (bool) $value,
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
			)
		);
	}

	public function addFeaturedMetaBox(): void {
		add_meta_box(
			'wp24h-editorial-featured',
			'Destaque editorial',
			array( $this, 'renderFeaturedMetaBox' ),
			'post',
			'side',
			'high'
		);
	}

	public function renderFeaturedMetaBox( \WP_Post $post ): void {
		wp_nonce_field( 'wp24h_save_editorial_featured', 'wp24h_editorial_featured_nonce' );
		$is_featured = '1' === (string) get_post_meta( $post->ID, self::FEATURED_META, true );
		?>
		<label><input type="checkbox" name="wp24h_editorial_featured" value="1"<?php checked( $is_featured ); ?>> Usar como destaque principal do blog</label>
		<p class="description">Ao marcar este artigo, o destaque anterior será removido.</p>
		<?php
	}

	public function saveFeaturedMeta( int $post_id, \WP_Post $post ): void {
		$nonce = isset( $_POST['wp24h_editorial_featured_nonce'] )
			? sanitize_text_field( wp_unslash( $_POST['wp24h_editorial_featured_nonce'] ) )
			: '';

		if (
			'post' !== ( $post->post_type ?? 'post' )
			|| '' === $nonce
			|| ! wp_verify_nonce( $nonce, 'wp24h_save_editorial_featured' )
			|| ! current_user_can( 'edit_post', $post_id )
			|| wp_is_post_autosave( $post_id )
			|| wp_is_post_revision( $post_id )
		) {
			return;
		}

		if ( empty( $_POST['wp24h_editorial_featured'] ) ) {
			delete_post_meta( $post_id, self::FEATURED_META );
			return;
		}

		$other_ids = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'post__not_in'   => array( $post_id ),
				'meta_key'       => self::FEATURED_META,
				'meta_value'     => '1',
			)
		);

		foreach ( $other_ids as $other_id ) {
			if ( (int) $other_id !== $post_id ) {
				delete_post_meta( (int) $other_id, self::FEATURED_META );
			}
		}

		update_post_meta( $post_id, self::FEATURED_META, 1 );
	}
}
