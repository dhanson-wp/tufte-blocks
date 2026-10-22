<?php
/**
 * Post formats: core-standard support and the status title filter.
 *
 * The theme declares the core post formats and styles them. Which formats count
 * as notes, and what the blog loop shows, is up to the Tufte Formats plugin.
 *
 * @package Tufte_Blocks
 * @since 2.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post formats the theme supports.
 *
 * @since 2.0.0
 * @return string[]
 */
function tufte_blocks_post_formats(): array {
	return array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' );
}

/**
 * Declare post format support.
 *
 * @since 2.0.0
 * @return void
 */
function tufte_blocks_setup_post_formats(): void {
	add_theme_support( 'post-formats', tufte_blocks_post_formats() );
}
add_action( 'after_setup_theme', 'tufte_blocks_setup_post_formats' );

/**
 * Status and aside posts have no visible title. The generated one is for the admin and the feed.
 *
 * @since 2.0.0
 * @param string $block_content Rendered core/post-title.
 * @return string
 */
function tufte_blocks_format_hide_title( string $block_content ): string {
	return has_post_format( array( 'status', 'aside' ) ) ? '' : $block_content;
}
add_filter( 'render_block_core/post-title', 'tufte_blocks_format_hide_title' );

/**
 * Give status and aside rows a link to the post.
 *
 * With the title hidden, a status or aside post in a loop has nothing to click.
 * Wrap the date in a link to the post when the date block isn't already a link.
 * Standard posts keep the title link and aren't touched.
 *
 * @since 2.0.0
 * @param string   $block_content Rendered core/post-date.
 * @param array    $block         Parsed block.
 * @param WP_Block $instance      Block instance.
 * @return string
 */
function tufte_blocks_format_link_date( string $block_content, array $block, WP_Block $instance ): string {
	if ( ! empty( $block['attrs']['isLink'] ) ) {
		return $block_content;
	}

	$post_id = (int) ( $instance->context['postId'] ?? get_the_ID() );
	if ( ! $post_id || ! has_post_format( array( 'status', 'aside' ), $post_id ) ) {
		return $block_content;
	}

	// On the post's own page the date doesn't need to point at itself.
	if ( is_singular() && get_queried_object_id() === $post_id ) {
		return $block_content;
	}

	$linked = preg_replace(
		'#<time\b[^>]*>.*?</time>#s',
		'<a href="' . esc_url( get_permalink( $post_id ) ) . '">$0</a>',
		$block_content,
		1
	);

	return is_string( $linked ) ? $linked : $block_content;
}
add_filter( 'render_block_core/post-date', 'tufte_blocks_format_link_date', 10, 3 );
