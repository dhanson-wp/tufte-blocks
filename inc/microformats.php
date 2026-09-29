<?php
/**
 * Microformats2 markup for posts: h-entry and h-feed.
 *
 * Other sites read these classes when they receive your webmentions, so a
 * reply shows your name, date, and content instead of a bare link. Nothing
 * here changes how a page looks. Only posts are marked up; pages and
 * projects keep their plain markup.
 *
 * @package Tufte_Blocks
 * @since 1.8.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post ID a block is rendering, when it belongs to a post.
 *
 * @param WP_Block $instance Block instance.
 * @return int Zero when the block isn't rendering a post.
 */
function tufte_blocks_mf2_post_id( WP_Block $instance ): int {
	$post_id = (int) ( $instance->context['postId'] ?? 0 );

	return $post_id && 'post' === get_post_type( $post_id ) ? $post_id : 0;
}

/**
 * Add classes to the first tag, or to the first link inside it.
 *
 * @param string   $html    Block HTML.
 * @param string[] $classes Classes to add.
 * @param string   $tag     Tag to target. Empty targets the first tag.
 * @return string
 */
function tufte_blocks_mf2_add_classes( string $html, array $classes, string $tag = '' ): string {
	$processor = new WP_HTML_Tag_Processor( $html );
	if ( ! $processor->next_tag( '' === $tag ? null : $tag ) ) {
		return $html;
	}

	foreach ( $classes as $class_name ) {
		$processor->add_class( $class_name );
	}

	return $processor->get_updated_html();
}

/**
 * Mark each post in a query loop as an h-entry.
 *
 * The single post template sets h-entry on its own wrapper, since it doesn't
 * print post classes.
 *
 * @param string[] $classes Post classes.
 * @return string[]
 */
function tufte_blocks_mf2_post_class( array $classes ): array {
	if ( 'post' === get_post_type() && ! is_singular() ) {
		$classes[] = 'h-entry';
	}

	return $classes;
}
add_filter( 'post_class', 'tufte_blocks_mf2_post_class' );

/**
 * A list of posts is an h-feed.
 *
 * @param string   $block_content Rendered core/post-template.
 * @param array    $block         Parsed block.
 * @param WP_Block $instance      Block instance.
 * @return string
 */
function tufte_blocks_mf2_feed( string $block_content, array $block, WP_Block $instance ): string {
	if ( 'post' !== ( $instance->context['query']['postType'] ?? 'post' ) ) {
		return $block_content;
	}

	return tufte_blocks_mf2_add_classes( $block_content, array( 'h-feed' ) );
}
add_filter( 'render_block_core/post-template', 'tufte_blocks_mf2_feed', 10, 3 );

/**
 * Post title: p-name, and u-url when it links to the post.
 *
 * @param string   $block_content Rendered core/post-title.
 * @param array    $block         Parsed block.
 * @param WP_Block $instance      Block instance.
 * @return string
 */
function tufte_blocks_mf2_title( string $block_content, array $block, WP_Block $instance ): string {
	if ( ! tufte_blocks_mf2_post_id( $instance ) ) {
		return $block_content;
	}

	$block_content = tufte_blocks_mf2_add_classes( $block_content, array( 'p-name' ) );

	return str_contains( $block_content, '<a ' )
		? tufte_blocks_mf2_add_classes( $block_content, array( 'u-url' ), 'a' )
		: $block_content;
}
add_filter( 'render_block_core/post-title', 'tufte_blocks_mf2_title', 10, 3 );

/**
 * Post body on a single post: e-content.
 *
 * @param string   $block_content Rendered core/post-content.
 * @param array    $block         Parsed block.
 * @param WP_Block $instance      Block instance.
 * @return string
 */
function tufte_blocks_mf2_content( string $block_content, array $block, WP_Block $instance ): string {
	if ( ! is_singular( 'post' ) || ! tufte_blocks_mf2_post_id( $instance ) ) {
		return $block_content;
	}

	return tufte_blocks_mf2_add_classes( $block_content, array( 'e-content' ) );
}
add_filter( 'render_block_core/post-content', 'tufte_blocks_mf2_content', 10, 3 );

/**
 * Post date: dt-published, plus the entry's permalink and author.
 *
 * The permalink and author card are hidden, because the theme doesn't print
 * an author byline. They're for parsers, not readers.
 *
 * @param string   $block_content Rendered core/post-date.
 * @param array    $block         Parsed block.
 * @param WP_Block $instance      Block instance.
 * @return string
 */
function tufte_blocks_mf2_date( string $block_content, array $block, WP_Block $instance ): string {
	$post_id = tufte_blocks_mf2_post_id( $instance );
	if ( ! $post_id || ! str_contains( $block_content, '<time' ) ) {
		return $block_content;
	}

	$block_content = tufte_blocks_mf2_add_classes( $block_content, array( 'dt-published' ), 'time' );

	$author_id = (int) get_post_field( 'post_author', $post_id );
	$name      = (string) get_the_author_meta( 'display_name', $author_id );
	$url       = (string) get_the_author_meta( 'url', $author_id );
	if ( '' === $name ) {
		$name = get_bloginfo( 'name' );
	}
	if ( '' === $url ) {
		$url = $author_id ? get_author_posts_url( $author_id ) : home_url( '/' );
	}

	/**
	 * Filters the URL the entry's author h-card points to.
	 *
	 * @param string $url       Author URL. Defaults to the profile Website, then the author archive.
	 * @param int    $author_id Author user ID.
	 */
	$url = (string) apply_filters( 'tufte_blocks_author_url', $url, $author_id );

	$hidden = sprintf(
		'<span hidden><a class="u-url" href="%1$s"></a><span class="p-author h-card"><a class="u-url p-name" href="%2$s">%3$s</a></span></span>',
		esc_url( get_permalink( $post_id ) ),
		esc_url( $url ),
		esc_html( $name )
	);

	$end = strrpos( $block_content, '</div>' );

	return false === $end
		? $block_content . $hidden
		: substr_replace( $block_content, $hidden, $end, 0 );
}
add_filter( 'render_block_core/post-date', 'tufte_blocks_mf2_date', 10, 3 );
