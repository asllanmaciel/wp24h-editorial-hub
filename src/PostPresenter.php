<?php

declare( strict_types=1 );

namespace WP24H\EditorialHub;

use WP_Post;

final class PostPresenter {
	private const WORDS_PER_MINUTE = 200;

	public static function readingTime( string $content ): int {
		$text  = trim( wp_strip_all_tags( strip_shortcodes( $content ) ) );
		$words = '' === $text ? 0 : count( preg_split( '/\s+/u', $text ) ?: array() );

		return max( 1, (int) ceil( $words / self::WORDS_PER_MINUTE ) );
	}

	/**
	 * @return array{id:int,title:string,url:string,excerpt:string,image:string,image_alt:string,category:array{name:string,slug:string},series:array{name:string,slug:string,url:string}|null,date:string,reading_time:int,image_lazy:bool}
	 */
	public static function fromPost( WP_Post $post ): array {
		$categories = get_the_category( $post->ID );
		$category   = $categories[0] ?? null;
		$series     = self::series( $post->ID );
		$excerpt    = trim( wp_strip_all_tags( $post->post_excerpt ) );

		if ( '' === $excerpt ) {
			$words   = preg_split( '/\s+/u', trim( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) ) ) ?: array();
			$excerpt = implode( ' ', array_slice( $words, 0, 32 ) );
			$excerpt .= count( $words ) > 32 ? '…' : '';
		}

		return array(
			'id'           => $post->ID,
			'title'        => $post->post_title,
			'url'          => get_permalink( $post->ID ),
			'excerpt'      => $excerpt,
			'image'        => get_the_post_thumbnail_url( $post, 'large' ) ?: '',
			'image_alt'    => $post->post_title,
			'category'     => array(
				'name' => $category->name ?? 'Conteúdo',
				'slug' => $category->slug ?? '',
			),
			'series'       => $series,
			'date'         => get_the_date( 'j M Y', $post ),
			'reading_time' => self::readingTime( $post->post_content ),
			'image_lazy'   => false,
		);
	}

	/** @return array{name:string,slug:string,url:string}|null */
	private static function series( int $postId ): ?array {
		$terms = get_the_terms( $postId, 'series' );
		if ( ! is_array( $terms ) || array() === $terms ) {
			return null;
		}

		usort( $terms, static fn( object $left, object $right ): int => (int) ( $left->term_id ?? 0 ) <=> (int) ( $right->term_id ?? 0 ) );
		$term = $terms[0];
		$name = trim( (string) ( $term->name ?? '' ) );
		$slug = (string) ( $term->slug ?? '' );
		$url  = get_term_link( $term );

		if ( '' === $name || '' === $slug || is_wp_error( $url ) || ! is_string( $url ) || '' === $url ) {
			return null;
		}

		return array( 'name' => $name, 'slug' => $slug, 'url' => $url );
	}
}
