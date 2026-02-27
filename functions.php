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
define( 'TUFTE_BLOCKS_VERSION', '1.3.0' );

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
 * Shortcode: current year (for footer copyright).
 *
 * @since 1.0.0
 * @return string Current 4-digit year.
 */
function tufte_current_year_shortcode(): string {
	return (string) gmdate( 'Y' );
}
add_shortcode( 'current_year', 'tufte_current_year_shortcode' );

/**
 * Enqueue theme stylesheets for frontend and editor.
 *
 * @since 1.0.0
 * @return void
 */
function tufte_blocks_enqueue_styles(): void {
	$theme_path = get_template_directory_uri() . '/assets/css/';
	$version    = TUFTE_BLOCKS_VERSION;

	// Navigation styles.
	wp_enqueue_style(
		'tufte-blocks-navigation',
		$theme_path . 'navigation.css',
		array(),
		$version
	);

	// Button styles.
	wp_enqueue_style(
		'tufte-blocks-buttons',
		$theme_path . 'buttons.css',
		array(),
		$version
	);

	// Link styles.
	wp_enqueue_style(
		'tufte-blocks-links',
		$theme_path . 'links.css',
		array(),
		$version
	);

	// Component styles (quotes, details, etc.).
	wp_enqueue_style(
		'tufte-blocks-components',
		$theme_path . 'components.css',
		array(),
		$version
	);

	// Pattern styles (epigraph, etc.).
	wp_enqueue_style(
		'tufte-blocks-patterns',
		$theme_path . 'patterns.css',
		array(),
		$version
	);

	// Tufte layout (left margin, responsive padding).
	wp_enqueue_style(
		'tufte-layout',
		$theme_path . 'layout.css',
		array(),
		$version
	);
}
add_action( 'enqueue_block_assets', 'tufte_blocks_enqueue_styles' );

/**
 * Add editor-specific styles.
 *
 * Ensures proper color visibility in the block editor.
 *
 * @since 1.0.0
 * @return void
 */
function tufte_blocks_editor_styles(): void {
	add_editor_style( 'assets/css/editor.css' );
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
 * TEMPORARY: Reset page template database override.
 *
 * The page template was saved to the database with incorrect post-content
 * attributes. This deletes the DB record so WordPress falls back to the theme
 * file (templates/page.html).
 *
 * Load any page once, then REMOVE this block immediately.
 */
add_action( 'init', function (): void {
	$query = new WP_Query(
		array(
			'post_type'      => 'wp_template',
			'post_name__in'  => array( 'page' ),
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'tax_query'      => array(
				array(
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => get_stylesheet(),
				),
			),
		)
	);
	$posts = $query->posts;
	if ( ! empty( $posts ) ) {
		wp_delete_post( (int) $posts[0], true );
	}
} );

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

/**
 * One-time migration: clear duplicated Tufte layout CSS from Additional CSS.
 *
 * After moving layout rules to assets/css/layout.css, clear them from
 * Appearance → Editor → Styles → Additional CSS to avoid duplication.
 * Only runs when Tufte layout rules are present. Remove this block after
 * the migration has run once.
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'init', function (): void {
	if ( get_option( 'tufte_blocks_layout_css_migrated', false ) ) {
		return;
	}

	$css = wp_get_custom_css( get_stylesheet() );
	if ( empty( trim( $css ) ) ) {
		update_option( 'tufte_blocks_layout_css_migrated', true );
		return;
	}

	// Clear Additional CSS if it contains the Tufte layout block (avoid duplication).
	if ( str_contains( $css, 'TUFTE LEFT MARGIN' ) ) {
		wp_update_custom_css_post( '', array( 'stylesheet' => get_stylesheet() ) );
	}

	update_option( 'tufte_blocks_layout_css_migrated', true );
} );
