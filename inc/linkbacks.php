<?php
/**
 * Linkbacks in the comment thread: webmentions, pingbacks, and trackbacks.
 *
 * A linkback's Reply link is broken. The Webmention plugin filters
 * get_comment_link() to the remote source URL, so the reply link sends
 * readers to the other site with ?replytocom appended. On linkbacks, the
 * Reply link becomes a "Read on example.com" link to the post that
 * mentioned this one, in the same pill style.
 *
 * @package Tufte_Blocks
 * @since 1.7.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL of the post a linkback came from.
 *
 * @param WP_Comment $comment Comment.
 * @return string Empty string for ordinary comments.
 */
function tufte_blocks_linkback_source_url( WP_Comment $comment ): string {
	if ( function_exists( 'is_webmention' ) && function_exists( 'get_url_from_webmention' ) && is_webmention( $comment ) ) {
		return (string) get_url_from_webmention( $comment );
	}

	if ( in_array( $comment->comment_type, array( 'pingback', 'trackback' ), true ) ) {
		return (string) $comment->comment_author_url;
	}

	return '';
}

/**
 * Swap the Reply link on linkbacks for a link to the source post.
 *
 * @param string   $block_content Rendered core/comment-reply-link.
 * @param array    $block         Parsed block.
 * @param WP_Block $instance      Block instance, with commentId in context.
 * @return string
 */
function tufte_blocks_linkback_reply_link( string $block_content, array $block, WP_Block $instance ): string {
	$comment_id = (int) ( $instance->context['commentId'] ?? 0 );
	$comment    = $comment_id ? get_comment( $comment_id ) : null;
	if ( ! $comment instanceof WP_Comment ) {
		return $block_content;
	}

	$url  = tufte_blocks_linkback_source_url( $comment );
	$host = $url ? wp_parse_url( $url, PHP_URL_HOST ) : '';
	if ( ! $host ) {
		return $block_content;
	}

	// Keep the block wrapper (and its font classes); replace only the link.
	$wrapper_end = strpos( $block_content, '>' );
	if ( false === $wrapper_end || ! str_starts_with( $block_content, '<div' ) ) {
		return $block_content;
	}

	return substr( $block_content, 0, $wrapper_end + 1 )
		. sprintf(
			'<a class="linkback-source-link" href="%1$s" rel="nofollow ugc">%2$s <span aria-hidden="true">&#8599;</span></a>',
			esc_url( $url ),
			/* translators: %s: domain of the site that linked to this post. */
			esc_html( sprintf( __( 'Read on %s', 'tufte-blocks' ), preg_replace( '/^www\./', '', $host ) ) )
		)
		. '</div>';
}
add_filter( 'render_block_core/comment-reply-link', 'tufte_blocks_linkback_reply_link', 10, 3 );
