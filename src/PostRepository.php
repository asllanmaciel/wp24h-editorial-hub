<?php

declare( strict_types=1 );

namespace WP24H\EditorialHub;

use WP_Query;

class PostRepository implements EditorialDataSource {
	private const PAGE_SIZE = 8;

	/** @return array<string,mixed> */
	public function home( string $search, string $topic, int $page ): array {
		$page        = max( 1, $page );
		$has_filters = '' !== $search || '' !== $topic;
		$is_filtered = $has_filters || 1 < $page;
		$hero        = null;
		$secondary   = array();
		$excluded    = array();

		if ( ! $has_filters ) {
			$featured = $this->query(
				array(
					'posts_per_page' => 1,
					'meta_key'       => '_wp24h_editorial_featured',
					'meta_value'     => '1',
				)
			);

			if ( ! $featured ) {
				$featured = $this->query( array( 'posts_per_page' => 1 ) );
			}

			if ( $featured ) {
				$hero       = PostPresenter::fromPost( $featured[0] );
				$excluded[] = $featured[0]->ID;
			}

			$secondary_posts = $this->query(
				array(
					'posts_per_page' => 2,
					'post__not_in'   => $excluded,
				)
			);
			$secondary       = array_map( array( PostPresenter::class, 'fromPost' ), $secondary_posts );
			$excluded        = array_merge( $excluded, array_map( static fn( $post ): int => (int) $post->ID, $secondary_posts ) );
		}

		$feed_args = array(
			'posts_per_page' => self::PAGE_SIZE,
			'paged'          => $page,
		);

		if ( $excluded ) {
			$feed_args['post__not_in'] = $excluded;
		}
		if ( '' !== $search ) {
			$feed_args['s'] = $search;
		}
		if ( '' !== $topic ) {
			$feed_args['category_name'] = $topic;
		}

		$feed_query = $this->makeQuery( $feed_args );
		$posts      = array_map( array( PostPresenter::class, 'fromPost' ), $feed_query->posts );
		$pagination_args = array( 'hub_page' => '__wp24h_page__' );
		if ( '' !== $search ) {
			$pagination_args['hub_search'] = $search;
		}
		if ( '' !== $topic ) {
			$pagination_args['hub_topic'] = $topic;
		}
		$pagination_base = str_replace(
			'__wp24h_page__',
			'%#%',
			add_query_arg( $pagination_args, home_url( '/blog/' ) )
		);

		$pagination = 1 < $feed_query->max_num_pages
			? (string) paginate_links(
				array(
					'base'      => $pagination_base,
					'current'   => $page,
					'total'     => $feed_query->max_num_pages,
					'prev_text' => '← Anterior',
					'next_text' => 'Próxima →',
				)
			)
			: '';

		return array(
			'hero'            => $hero,
			'secondary'       => $secondary,
			'posts'           => $posts,
			'categories'      => $this->categories( $search ),
			'active_category' => $topic,
			'search'          => $search,
			'pagination'      => $pagination,
			'courses_url'     => home_url( '/cursos/' ),
			'is_filtered'     => $is_filtered,
		);
	}

	/** @return list<object> */
	private function query( array $args ): array {
		$query = $this->makeQuery( $args );
		return $query->posts;
	}

	private function makeQuery( array $args ): WP_Query {
		$query = new WP_Query(
			array_merge(
				array(
					'post_type'           => 'post',
					'post_status'         => 'publish',
					'ignore_sticky_posts' => true,
					'orderby'             => 'date',
					'order'               => 'DESC',
				),
				$args
			)
		);
		wp_reset_postdata();

		return $query;
	}

	/** @return list<array{name:string,slug:string,url:string}> */
	private function categories( string $search ): array {
		$home = home_url( '/blog/' );
		$all_url = '' === $search ? $home : add_query_arg( 'hub_search', $search, $home );
		$output = array( array( 'name' => 'Todos', 'slug' => '', 'url' => $all_url ) );
		$terms  = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );

		if ( is_wp_error( $terms ) ) {
			return $output;
		}

		foreach ( array_slice( $terms, 0, 9 ) as $term ) {
			$args = array( 'hub_topic' => $term->slug );
			if ( '' !== $search ) {
				$args['hub_search'] = $search;
			}
			$output[] = array( 'name' => $term->name, 'slug' => $term->slug, 'url' => add_query_arg( $args, $home ) );
		}

		return $output;
	}
}
