<?php
/**
 * Front-end performance tweaks.
 *
 * @package Tufte_Blocks
 * @since 1.9.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prerender internal links on hover instead of prefetching on mouse-down.
 *
 * Core ships speculation rules as `prefetch` + `conservative`, which only fires
 * on pointer-down and saves almost nothing. `prerender` + `moderate` starts
 * after a ~200ms hover (or on touch start), so the next page is usually ready
 * by the time the click lands.
 *
 * @since 1.9.1
 * @param array|null $config Speculation rules configuration.
 * @return array|null
 */
function tufte_blocks_speculation_rules_config( $config ) {
	if ( ! is_array( $config ) ) {
		return $config;
	}

	$config['mode']      = 'prerender';
	$config['eagerness'] = 'moderate';

	return $config;
}
add_filter( 'wp_speculation_rules_configuration', 'tufte_blocks_speculation_rules_config' );

/**
 * Preload the body font so `font-display: optional` actually gets to use it.
 *
 * With `optional`, a font that isn't ready within the ~100ms block period is
 * skipped for that page view. Preloading makes the primary face available in
 * time on first visits.
 *
 * @since 1.9.1
 * @return void
 */
function tufte_blocks_preload_fonts(): void {
	if ( is_admin() ) {
		return;
	}

	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff" crossorigin>' . "\n",
		esc_url( get_theme_file_uri( 'assets/fonts/et-book/et-book-roman-line-figures.woff' ) )
	);
}
add_action( 'wp_head', 'tufte_blocks_preload_fonts', 1 );

/**
 * Defer the Threads embed script so it doesn't block parsing.
 *
 * WordPress enqueues it as a plain blocking script when a Threads post is
 * embedded in the content.
 *
 * @since 1.9.1
 * @param string $tag    Script tag HTML.
 * @param string $handle Script handle.
 * @return string
 */
function tufte_blocks_defer_embed_scripts( string $tag, string $handle ): string {
	if ( 'threads-embed-js' !== $handle && 'threads-embed' !== $handle ) {
		return $tag;
	}

	if ( str_contains( $tag, ' defer' ) || str_contains( $tag, ' async' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'tufte_blocks_defer_embed_scripts', 10, 2 );
