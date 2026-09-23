<?php
/**
 * Project post type and taxonomies.
 *
 * Moved verbatim from functions.php in 1.5.0. No behavior change.
 *
 * @package Tufte_Blocks
 * @since 1.3.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Projects custom post type.
 *
 * @since 1.3.0
 * @return void
 */
function tufte_blocks_register_project_post_type(): void {
	register_post_type(
		'project',
		array(
			'labels'        => array(
				'name'                  => __( 'Projects', 'tufte-blocks' ),
				'singular_name'         => __( 'Project', 'tufte-blocks' ),
				'add_new_item'          => __( 'Add New Project', 'tufte-blocks' ),
				'edit_item'             => __( 'Edit Project', 'tufte-blocks' ),
				'new_item'              => __( 'New Project', 'tufte-blocks' ),
				'view_item'             => __( 'View Project', 'tufte-blocks' ),
				'view_items'            => __( 'View Projects', 'tufte-blocks' ),
				'search_items'          => __( 'Search Projects', 'tufte-blocks' ),
				'not_found'             => __( 'No projects found.', 'tufte-blocks' ),
				'not_found_in_trash'    => __( 'No projects found in Trash.', 'tufte-blocks' ),
				'all_items'             => __( 'All Projects', 'tufte-blocks' ),
				'archives'              => __( 'Project Archives', 'tufte-blocks' ),
				'attributes'            => __( 'Project Attributes', 'tufte-blocks' ),
				'item_published'        => __( 'Project published.', 'tufte-blocks' ),
				'item_updated'          => __( 'Project updated.', 'tufte-blocks' ),
			),
			'public'        => true,
			'has_archive'   => 'projects',
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-portfolio',
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions', 'page-attributes' ),
			'rewrite'       => array(
				'slug'       => 'projects',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'tufte_blocks_register_project_post_type' );

/**
 * Register project taxonomies.
 *
 * @since 1.3.0
 * @return void
 */
function tufte_blocks_register_project_taxonomies(): void {
	register_taxonomy(
		'project_type',
		'project',
		array(
			'labels'       => array(
				'name'          => __( 'Project Types', 'tufte-blocks' ),
				'singular_name' => __( 'Project Type', 'tufte-blocks' ),
				'add_new_item'  => __( 'Add New Project Type', 'tufte-blocks' ),
				'edit_item'     => __( 'Edit Project Type', 'tufte-blocks' ),
				'search_items'  => __( 'Search Project Types', 'tufte-blocks' ),
				'all_items'     => __( 'All Project Types', 'tufte-blocks' ),
			),
			'public'       => true,
			'hierarchical' => false,
			'show_in_rest' => true,
			'rewrite'      => array(
				'slug'       => 'project-type',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'project_tool',
		'project',
		array(
			'labels'       => array(
				'name'          => __( 'Tools', 'tufte-blocks' ),
				'singular_name' => __( 'Tool', 'tufte-blocks' ),
				'add_new_item'  => __( 'Add New Tool', 'tufte-blocks' ),
				'edit_item'     => __( 'Edit Tool', 'tufte-blocks' ),
				'search_items'  => __( 'Search Tools', 'tufte-blocks' ),
				'all_items'     => __( 'All Tools', 'tufte-blocks' ),
			),
			'public'       => true,
			'hierarchical' => false,
			'show_in_rest' => true,
			'rewrite'      => array(
				'slug'       => 'project-tool',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'tufte_blocks_register_project_taxonomies' );

/**
 * Flush rewrite rules once after CPT registration.
 *
 * @since 1.3.0
 * @return void
 */
add_action( 'init', function (): void {
	if ( get_option( 'tufte_blocks_projects_rewrite_flushed', false ) ) {
		return;
	}
	flush_rewrite_rules();
	update_option( 'tufte_blocks_projects_rewrite_flushed', true );
}, 99 );
