<?php
/**
 * "Project details" sidebar panel: enqueue and config.
 *
 * Loads only when editing a project. The JS is vanilla wp.element (no JSX,
 * no build) and reads the field registry from window.tufteProjectFields.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue the panel script on project edit screens.
 *
 * @return void
 */
function tufte_blocks_project_enqueue_editor(): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'project' !== $screen->post_type || 'post' !== $screen->base ) {
		return;
	}

	wp_enqueue_script(
		'tufte-blocks-project-fields',
		get_template_directory_uri() . '/assets/js/project-fields.js',
		array( 'wp-plugins', 'wp-editor', 'wp-components', 'wp-element', 'wp-data', 'wp-core-data', 'wp-api-fetch', 'wp-i18n' ),
		TUFTE_BLOCKS_VERSION,
		true
	);

	$fields = array();
	foreach ( tufte_blocks_project_fields() as $key => $field ) {
		$fields[] = array(
			'key'     => $key,
			'metaKey' => tufte_blocks_project_meta_key( $key ),
			'label'   => $field['label'],
			'type'    => $field['type'],
			'group'   => $field['group'],
			'manual'  => $field['manual'],
		);
	}

	wp_add_inline_script(
		'tufte-blocks-project-fields',
		'window.tufteProjectFields = ' . wp_json_encode(
			array(
				'fields'   => $fields,
				'restBase' => 'tufte-blocks/v1/projects',
			)
		) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'tufte_blocks_project_enqueue_editor' );
