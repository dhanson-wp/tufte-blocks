<?php
/**
 * Project render tweaks that are not bindings.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Append the attachment caption to a project's featured image on the single
 * template. Skips cards (className tufte-project-banner) and posts without a
 * caption. Marks the figure with tufte-project-figure for styling.
 *
 * @param string   $block_content Rendered HTML.
 * @param array    $block         Parsed block.
 * @param WP_Block $instance      Block instance.
 * @return string
 */
function tufte_blocks_project_featured_caption( string $block_content, array $block, WP_Block $instance ): string {
	if ( 'core/post-featured-image' !== ( $block['blockName'] ?? '' ) || '' === $block_content ) {
		return $block_content;
	}
	if ( str_contains( (string) ( $block['attrs']['className'] ?? '' ), 'tufte-project-banner' ) ) {
		return $block_content;
	}
	$post_id = isset( $instance->context['postId'] ) ? (int) $instance->context['postId'] : 0;
	if ( ! $post_id || 'project' !== get_post_type( $post_id ) ) {
		return $block_content;
	}
	$caption = wp_get_attachment_caption( (int) get_post_thumbnail_id( $post_id ) );
	if ( ! $caption ) {
		return $block_content;
	}
	$figcaption = '<figcaption class="wp-element-caption">' . wp_kses_post( $caption ) . '</figcaption>';
	$content    = preg_replace( '/<\/figure>\s*$/', $figcaption . '</figure>', $block_content, 1 );
	return str_replace( 'class="wp-block-post-featured-image', 'class="wp-block-post-featured-image tufte-project-figure', $content ?? $block_content );
}
add_filter( 'render_block', 'tufte_blocks_project_featured_caption', 10, 3 );
