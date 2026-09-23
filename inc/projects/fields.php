<?php
/**
 * Project field registry.
 *
 * One array is the source of truth for every project field. It drives
 * register_post_meta(), the editor sidebar, the source adapters' output
 * mapping, and the field resolver. Adding a field is a one-line change here.
 *
 * Every field `k` stores at meta key `project_k`. For manual fields that is
 * the value; for derived fields it is an optional override that wins when
 * non-empty.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The registry.
 *
 * Each entry: label, type ('text'|'url'), group ('source'|'editorial'|
 * 'release'|'requirements'|'links'), manual (bool), and an optional
 * 'format' callback applied to the resolved value for display.
 *
 * @return array<string, array{label:string,type:string,group:string,manual:bool,format?:string}>
 */
function tufte_blocks_project_fields(): array {
	return array(
		// Manual: identify the sources the sync job runs on.
		'wporg_slug'     => array(
			'label'  => __( 'WordPress.org slug', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'source',
			'manual' => true,
		),
		'github_url'     => array(
			'label'  => __( 'GitHub repository URL', 'tufte-blocks' ),
			'type'   => 'url',
			'group'  => 'source',
			'manual' => true,
		),
		// Manual: editorial.
		'tagline'        => array(
			'label'  => __( 'Tagline', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'editorial',
			'manual' => true,
		),
		'demo_url'       => array(
			'label'  => __( 'Demo URL', 'tufte-blocks' ),
			'type'   => 'url',
			'group'  => 'editorial',
			'manual' => true,
		),
		// Derived: release.
		'version'        => array(
			'label'  => __( 'Version', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'release',
			'manual' => false,
		),
		'release_date'   => array(
			'label'  => __( 'Released', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'release',
			'manual' => false,
			'format' => 'tufte_blocks_project_format_month_year',
		),
		'license'        => array(
			'label'  => __( 'License', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'release',
			'manual' => false,
		),
		// Derived: requirements.
		'requires_wp'    => array(
			'label'  => __( 'WordPress', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'requirements',
			'manual' => false,
			'format' => 'tufte_blocks_project_format_or_higher',
		),
		'tested_up_to'   => array(
			'label'  => __( 'Tested to', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'requirements',
			'manual' => false,
		),
		'requires_php'   => array(
			'label'  => __( 'PHP', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'requirements',
			'manual' => false,
			'format' => 'tufte_blocks_project_format_or_higher',
		),
		// Derived: links.
		'link_download'  => array(
			'label'  => __( 'Download', 'tufte-blocks' ),
			'type'   => 'url',
			'group'  => 'links',
			'manual' => false,
		),
		'link_directory' => array(
			'label'  => __( 'Plugin directory', 'tufte-blocks' ),
			'type'   => 'url',
			'group'  => 'links',
			'manual' => false,
		),
		'link_support'   => array(
			'label'  => __( 'Support', 'tufte-blocks' ),
			'type'   => 'url',
			'group'  => 'links',
			'manual' => false,
		),
		'link_translate' => array(
			'label'  => __( 'Translate', 'tufte-blocks' ),
			'type'   => 'url',
			'group'  => 'links',
			'manual' => false,
		),
	);
}

/**
 * Meta key for a registry key.
 *
 * @param string $key Registry key.
 * @return string
 */
function tufte_blocks_project_meta_key( string $key ): string {
	return 'project_' . $key;
}

/**
 * Keys of derived (synced) fields only.
 *
 * @return string[]
 */
function tufte_blocks_project_derived_keys(): array {
	return array_keys(
		array_filter(
			tufte_blocks_project_fields(),
			static fn( array $field ): bool => ! $field['manual']
		)
	);
}

/**
 * Sanitize a field value according to its registry type.
 *
 * Unknown keys sanitize as text. Never returns null.
 *
 * @param string $key   Registry key.
 * @param mixed  $value Raw value.
 * @return string
 */
function tufte_blocks_project_sanitize_field( string $key, $value ): string {
	$fields = tufte_blocks_project_fields();
	$type   = $fields[ $key ]['type'] ?? 'text';
	$value  = is_scalar( $value ) ? trim( (string) $value ) : '';

	if ( '' === $value ) {
		return '';
	}

	return 'url' === $type ? esc_url_raw( $value ) : sanitize_text_field( $value );
}

/**
 * Register one post meta key per registry field, plus the hidden source cache.
 *
 * @since 1.5.0
 * @return void
 */
function tufte_blocks_project_register_meta(): void {
	$auth = static fn(): bool => current_user_can( 'edit_posts' );

	foreach ( tufte_blocks_project_fields() as $key => $field ) {
		register_post_meta(
			'project',
			tufte_blocks_project_meta_key( $key ),
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => static fn( $value ): string => tufte_blocks_project_sanitize_field( $key, $value ),
				'auth_callback'     => $auth,
			)
		);
	}

	// Hidden, never edited by hand, written only by the sync job.
	register_post_meta(
		'project',
		'_project_source_cache',
		array(
			'show_in_rest'  => false,
			'single'        => true,
			'type'          => 'array',
			'default'       => array(),
			'auth_callback' => '__return_false',
		)
	);
}
add_action( 'init', 'tufte_blocks_project_register_meta' );

/**
 * Format "6.4" as "6.4 or higher". Leaves anything that is not a bare
 * version number alone, so overrides typed as prose pass through.
 *
 * @param string $value Resolved value.
 * @return string
 */
function tufte_blocks_project_format_or_higher( string $value ): string {
	if ( preg_match( '/^\d+(\.\d+)*$/', $value ) ) {
		/* translators: %s: version number */
		return sprintf( __( '%s or higher', 'tufte-blocks' ), $value );
	}
	return $value;
}

/**
 * Format any parseable date as "Sep 2026". Unparseable values pass through.
 *
 * @param string $value Resolved value.
 * @return string
 */
function tufte_blocks_project_format_month_year( string $value ): string {
	$timestamp = strtotime( $value );
	if ( false === $timestamp ) {
		return $value;
	}
	// Format in UTC: a month-only value like "Sep 2026" parses as the 1st at
	// midnight UTC, and a site timezone west of UTC would shift it to August.
	return wp_date( 'M Y', $timestamp, new DateTimeZone( 'UTC' ) );
}
