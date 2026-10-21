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
