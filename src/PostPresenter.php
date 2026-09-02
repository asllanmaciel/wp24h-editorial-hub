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
	 * @return array{id:int,title:string,url:string,excerpt:string,image:string,image_alt:string,category:array{name:string,slug:string},date:string,reading_time:int,image_lazy:bool}
	 */
	public static function fromPost( WP_Post $post ): array {
		$categories = get_the_category( $post->ID );
		$category   = $categories[0] ?? null;
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
			'date'         => get_the_date( 'j M Y', $post ),
			'reading_time' => self::readingTime( $post->post_content ),
			'image_lazy'   => false,
		);
	}
}
