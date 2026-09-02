<?php

declare( strict_types=1 );

namespace WP24H\EditorialHub;

interface EditorialDataSource {
	/** @return array<string,mixed> */
	public function home( string $search, string $topic, int $page ): array;
}
