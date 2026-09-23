<?php
/**
 * Project fields.
 *
 * Meta registration for the Block Bindings API. Moved verbatim in 1.5.0.
 *
 * @package Tufte_Blocks
 * @since 1.3.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register project meta fields for Block Bindings API.
 *
 * @since 1.3.0
 * @return void
 */
function tufte_blocks_register_project_meta(): void {
	$meta_args = array(
		'show_in_rest'  => true,
		'single'        => true,
		'type'          => 'string',
		'default'       => '',
		'auth_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
	);

	register_post_meta( 'project', 'project_github_url', $meta_args );
	register_post_meta( 'project', 'project_demo_url', $meta_args );
}
add_action( 'init', 'tufte_blocks_register_project_meta' );
