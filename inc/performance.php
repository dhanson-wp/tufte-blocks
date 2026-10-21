<?php
/**
 * Front-end performance tweaks.
 *
 * @package Tufte_Blocks
 * @since 1.9.1
 */

defined( 'ABSPATH' ) || exit;

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
