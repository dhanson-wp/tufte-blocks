<?php
/**
 * Tufte Blocks Theme Functions
 *
 * @package Tufte_Blocks
 * @since 1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define theme version for cache busting.
 */
define( 'TUFTE_BLOCKS_VERSION', '1.10.0' );

/**
 * Linkbacks: link webmentions and pingbacks to the post they came from.
 *
 * @since 1.7.0
 */
require_once get_template_directory() . '/inc/linkbacks.php';
require_once get_template_directory() . '/inc/microformats.php';
require_once get_template_directory() . '/inc/formats.php';
require_once get_template_directory() . '/inc/performance.php';

/**
 * Register custom block styles.
 *
 * @since 1.0.0
 * @return void
 */
function tufte_blocks_register_block_styles(): void {
	// Quote: Epigraph style (right-aligned with gold border).
	register_block_style(
		'core/quote',
		array(
			'name'  => 'epigraph',
			'label' => __( 'Epigraph', 'tufte-blocks' ),
		)
	);

	// Quote: Large style (wide width, larger text).
	register_block_style(
		'core/quote',
		array(
			'name'  => 'large',
			'label' => __( 'Large', 'tufte-blocks' ),
		)
	);

	// Button: Link style (text link with arrow).
	register_block_style(
		'core/button',
		array(
			'name'  => 'link',
			'label' => __( 'Link', 'tufte-blocks' ),
		)
	);

	// Separator: Ornament style (section symbol).
	register_block_style(
		'core/separator',
		array(
			'name'  => 'ornament',
			'label' => __( 'Ornament', 'tufte-blocks' ),
		)
	);

	// Post Featured Image: Tufte Hero style (margin figure).
	register_block_style(
		'core/post-featured-image',
		array(
			'name'  => 'tufte-hero',
			'label' => __( 'Tufte Hero', 'tufte-blocks' ),
		)
	);
}
add_action( 'init', 'tufte_blocks_register_block_styles' );

/**
 * Theme stylesheets, keyed by handle.
 *
 * Shared by the frontend enqueue and the editor styles so both load the same files.
 *
 * @since 1.9.4
 * @return array<string, string> Handle => file name in assets/css/.
 */
function tufte_blocks_stylesheets(): array {
	return array(
		'tufte-blocks-navigation' => 'navigation.css', // Navigation styles.
		'tufte-blocks-buttons'    => 'buttons.css',    // Button styles.
		'tufte-blocks-links'      => 'links.css',      // Link styles.
		'tufte-blocks-components' => 'components.css', // Quotes, details, inline code, etc.
		'tufte-blocks-patterns'   => 'patterns.css',   // Epigraph, etc.
		'tufte-blocks-formats'    => 'formats.css',    // Post format presentation.
		'tufte-layout'            => 'layout.css',     // Left margin, responsive padding.
		'tufte-blocks-comments'   => 'comments.css',   // Comment threads.
	);
}

/**
 * Enqueue theme stylesheets on the frontend.
 *
 * Uses wp_enqueue_scripts rather than enqueue_block_assets: the latter also
 * loads into the editor's outer admin document, where these unscoped rules
 * leak into plugin UI (e.g. Jetpack Social's placeholder chips).
 *
 * @since 1.0.0
 * @return void
 */
function tufte_blocks_enqueue_styles(): void {
	$theme_path = get_template_directory_uri() . '/assets/css/';

	foreach ( tufte_blocks_stylesheets() as $handle => $file ) {
		wp_enqueue_style( $handle, $theme_path . $file, array(), TUFTE_BLOCKS_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'tufte_blocks_enqueue_styles' );

/**
 * Add editor styles.
 *
 * Editor styles are scoped to the editor canvas, so the theme's CSS styles
 * post content without reaching sidebars, modals, or other plugin UI.
 * editor.css loads last so its editor-only overrides still win.
 *
 * @since 1.0.0
 * @return void
 */
function tufte_blocks_editor_styles(): void {
	$styles = array();
	foreach ( tufte_blocks_stylesheets() as $file ) {
		$styles[] = 'assets/css/' . $file;
	}
	$styles[] = 'assets/css/editor.css';

	add_editor_style( $styles );
}
add_action( 'after_setup_theme', 'tufte_blocks_editor_styles' );

/**
 * Remove core and remote block patterns.
 *
 * Disables WordPress core block patterns and remote patterns from WordPress.org
 * so only theme-registered patterns appear in the inserter.
 *
 * @since 1.0.0
 * @return void
 */
function tufte_blocks_disable_core_and_remote_patterns(): void {
	remove_theme_support( 'core-block-patterns' );
}
add_action( 'after_setup_theme', 'tufte_blocks_disable_core_and_remote_patterns' );

add_filter( 'should_load_remote_block_patterns', '__return_false' );

/**
 * Register pattern categories.
 *
 * @since 1.0.0
 * @return void
 */
function tufte_blocks_register_pattern_categories(): void {
	register_block_pattern_category(
		'tufte-query',
		array(
			'label'       => __( 'Query Loops', 'tufte-blocks' ),
			'description' => __( 'Post listing patterns with various layouts.', 'tufte-blocks' ),
		)
	);

	register_block_pattern_category(
		'tufte-cta',
		array(
			'label'       => __( 'Calls to Action', 'tufte-blocks' ),
			'description' => __( 'Newsletter signups, link in bio, and CTAs.', 'tufte-blocks' ),
		)
	);

	register_block_pattern_category(
		'tufte-content',
		array(
			'label'       => __( 'Content', 'tufte-blocks' ),
			'description' => __( 'Content display patterns like FAQs and testimonials.', 'tufte-blocks' ),
		)
	);

	register_block_pattern_category(
		'tufte-academic',
		array(
			'label'       => __( 'Academic', 'tufte-blocks' ),
			'description' => __( 'Publications, CV, syllabus, and research patterns.', 'tufte-blocks' ),
		)
	);

	register_block_pattern_category(
		'tufte-layout',
		array(
			'label'       => __( 'Layout', 'tufte-blocks' ),
			'description' => __( 'Structural patterns like heroes and section breaks.', 'tufte-blocks' ),
		)
	);
}
add_action( 'init', 'tufte_blocks_register_pattern_categories' );
