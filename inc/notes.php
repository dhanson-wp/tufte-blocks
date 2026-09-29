<?php
/**
 * Notes: short posts in the Status format.
 *
 * Notes are regular posts, so they keep one URL scheme, comments, and
 * webmentions. They stay out of everything that carries the blog and the
 * newsletter: the blog loop, archives, search, and the main feed. They show up
 * in a query block whose Post format filter is Status, on the Status archive,
 * and in that archive's own feed (/type/status/feed/).
 *
 * @package Tufte_Blocks
 * @since 1.9.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tax query clause that matches notes.
 *
 * @param string $operator IN to include notes, NOT IN to leave them out.
 * @return array
 */
function tufte_blocks_notes_clause( string $operator ): array {
	return array(
		'taxonomy' => 'post_format',
		'field'    => 'slug',
		'terms'    => array( 'post-format-status' ),
		'operator' => $operator,
	);
}

/**
 * Add a tax query clause without disturbing existing ones.
 *
 * @param array $tax_query Existing tax query.
 * @param array $clause    Clause to add.
 * @return array
 */
function tufte_blocks_notes_and( $tax_query, array $clause ): array {
	$combined = array( 'relation' => 'AND' );
	if ( is_array( $tax_query ) && $tax_query ) {
		$combined[] = $tax_query;
	}
	$combined[] = $clause;

	return $combined;
}

/**
 * Leave notes out of the main blog, archive, search, and feed queries.
 *
 * @param WP_Query $query Query.
 * @return void
 */
function tufte_blocks_notes_exclude_main( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || $query->is_singular() || $query->is_tax( 'post_format' ) ) {
		return;
	}

	if ( $query->is_home() || $query->is_feed() || $query->is_archive() || $query->is_search() ) {
		$query->set( 'tax_query', tufte_blocks_notes_and( $query->get( 'tax_query' ), tufte_blocks_notes_clause( 'NOT IN' ) ) );
	}
}
add_action( 'pre_get_posts', 'tufte_blocks_notes_exclude_main' );

/**
 * Query blocks leave notes out unless the block asks for the Status format.
 *
 * Set the Post format filter to Status on a query block to make a notes list.
 * Query blocks that inherit the main query are handled above.
 *
 * @param array    $query Query vars.
 * @param WP_Block $block Post template block.
 * @return array
 */
function tufte_blocks_notes_query_block( array $query, WP_Block $block ): array {
	$settings = (array) ( $block->context['query'] ?? array() );
	$type     = $query['post_type'] ?? 'post';

	if ( ! empty( $settings['inherit'] ) || ( 'post' !== $type && array( 'post' ) !== $type ) ) {
		return $query;
	}

	if ( in_array( 'status', (array) ( $settings['format'] ?? array() ), true ) ) {
		return $query;
	}

	$query['tax_query'] = tufte_blocks_notes_and( $query['tax_query'] ?? array(), tufte_blocks_notes_clause( 'NOT IN' ) );

	return $query;
}
add_filter( 'query_loop_block_query_vars', 'tufte_blocks_notes_query_block', 10, 2 );

/**
 * Notes have no visible title. The generated one is for the admin and the feed.
 *
 * @param string $block_content Rendered core/post-title.
 * @return string
 */
function tufte_blocks_notes_hide_title( string $block_content ): string {
	return has_post_format( 'status' ) ? '' : $block_content;
}
add_filter( 'render_block_core/post-title', 'tufte_blocks_notes_hide_title' );

/**
 * Old note URLs (/sn/123/) go to the note's new address.
 *
 * @return void
 */
function tufte_blocks_notes_redirect_old_urls(): void {
	if ( ! is_404() ) {
		return;
	}

	$path = (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), PHP_URL_PATH );
	if ( ! preg_match( '#^/sn/\d+/?$#', $path ) ) {
		return;
	}

	$found = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'meta_key'       => '_tufte_migrated_from', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => trailingslashit( $path ), // phpcs:ignore WordPress.DB.SlowDBQuery
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	if ( $found ) {
		wp_safe_redirect( get_permalink( $found[0] ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'tufte_blocks_notes_redirect_old_urls' );
