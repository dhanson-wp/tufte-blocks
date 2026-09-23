<?php
/**
 * Project sync job.
 *
 * Twice-daily WP-Cron event that runs the source adapters for every
 * published project and writes _project_source_cache. Also a REST route for
 * the sidebar's "Refresh now" button and a WP-CLI command for debugging.
 * The render path never calls anything in this file.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const TUFTE_BLOCKS_PROJECT_SYNC_HOOK = 'tufte_blocks_projects_sync';

/**
 * Read the cache for a project. Always returns the full shape.
 *
 * @param int $post_id Project ID.
 * @return array{fetched_at:string,wporg:?array,github:?array,errors:array}
 */
function tufte_blocks_project_get_cache( int $post_id ): array {
	$cache = get_post_meta( $post_id, '_project_source_cache', true );
	$cache = is_array( $cache ) ? $cache : array();
	return array(
		'fetched_at' => isset( $cache['fetched_at'] ) ? (string) $cache['fetched_at'] : '',
		'wporg'      => isset( $cache['wporg'] ) && is_array( $cache['wporg'] ) ? $cache['wporg'] : null,
		'github'     => isset( $cache['github'] ) && is_array( $cache['github'] ) ? $cache['github'] : null,
		'errors'     => isset( $cache['errors'] ) && is_array( $cache['errors'] ) ? $cache['errors'] : array(),
	);
}

/**
 * Merge one adapter result into a cache array without ever blanking good data.
 *
 * Pure, so it is testable: given the previous cache, a source name, and an
 * adapter result (array, WP_Error, or null for "no identifier"), return the
 * new cache.
 *
 * @param array               $cache  Previous cache (full shape).
 * @param string              $source 'wporg' or 'github'.
 * @param array|WP_Error|null $result Adapter result.
 * @return array
 */
function tufte_blocks_project_merge_source( array $cache, string $source, $result ): array {
	unset( $cache['errors'][ $source ] );

	if ( null === $result ) {
		$cache[ $source ] = null;
		return $cache;
	}

	if ( is_wp_error( $result ) ) {
		$cache['errors'][ $source ] = $result->get_error_message();
		return $cache; // Previous $cache[ $source ] is left intact.
	}

	$cache[ $source ] = $result;
	return $cache;
}

/**
 * Sync one project: run whichever adapters it has identifiers for, write the
 * cache, sideload the banner once.
 *
 * @param int $post_id Project ID.
 * @return array The new cache.
 */
function tufte_blocks_project_sync( int $post_id ): array {
	$cache = tufte_blocks_project_get_cache( $post_id );

	$slug   = (string) get_post_meta( $post_id, tufte_blocks_project_meta_key( 'wporg_slug' ), true );
	$github = (string) get_post_meta( $post_id, tufte_blocks_project_meta_key( 'github_url' ), true );

	$wporg_result = '' === $slug ? null : tufte_blocks_project_fetch_wporg( $slug );
	$cache        = tufte_blocks_project_merge_source( $cache, 'wporg', $wporg_result );

	$github_result = '' === $github ? null : tufte_blocks_project_fetch_github( $github );
	$cache         = tufte_blocks_project_merge_source( $cache, 'github', $github_result );

	foreach ( $cache['errors'] as $source => $message ) {
		error_log( sprintf( '[tufte-blocks] project %d %s sync failed: %s', $post_id, $source, $message ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	$cache['fetched_at'] = gmdate( 'c' );
	update_post_meta( $post_id, '_project_source_cache', $cache );

	if ( is_array( $wporg_result ) && ! empty( $wporg_result['banner'] ) ) {
		tufte_blocks_project_sideload_banner( $post_id, (string) $wporg_result['banner'] );
	}

	return $cache;
}

/**
 * Sync every published project. Cron callback.
 *
 * @return void
 */
function tufte_blocks_project_sync_all(): void {
	$ids = get_posts(
		array(
			'post_type'      => 'project',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	foreach ( $ids as $id ) {
		tufte_blocks_project_sync( (int) $id );
	}
}
add_action( TUFTE_BLOCKS_PROJECT_SYNC_HOOK, 'tufte_blocks_project_sync_all' );

/**
 * Make sure the twice-daily event is scheduled. Themes have no activation
 * hook, so check on init; wp_next_scheduled() is a cheap option read.
 *
 * @return void
 */
function tufte_blocks_project_schedule_sync(): void {
	if ( ! wp_next_scheduled( TUFTE_BLOCKS_PROJECT_SYNC_HOOK ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'twicedaily', TUFTE_BLOCKS_PROJECT_SYNC_HOOK );
	}
}
add_action( 'init', 'tufte_blocks_project_schedule_sync' );

/**
 * Unschedule when the theme is switched away.
 *
 * @return void
 */
function tufte_blocks_project_unschedule_sync(): void {
	wp_clear_scheduled_hook( TUFTE_BLOCKS_PROJECT_SYNC_HOOK );
}
add_action( 'switch_theme', 'tufte_blocks_project_unschedule_sync' );

/**
 * Sideload the .org banner as the featured image, once. If a featured image
 * already exists it is never touched, so a custom image is never reverted.
 *
 * @param int    $post_id Project ID.
 * @param string $url     Banner URL.
 * @return void
 */
function tufte_blocks_project_sideload_banner( int $post_id, string $url ): void {
	if ( has_post_thumbnail( $post_id ) || '' === $url ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attachment_id = media_sideload_image( $url, $post_id, get_the_title( $post_id ) . ' banner', 'id' );
	if ( is_wp_error( $attachment_id ) ) {
		error_log( sprintf( '[tufte-blocks] project %d banner sideload failed: %s', $post_id, $attachment_id->get_error_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return;
	}

	set_post_thumbnail( $post_id, (int) $attachment_id );
}

/**
 * Sidebar-facing summary of a project's synced state: resolved values per
 * derived field, provenance, timestamp, errors.
 *
 * @param int $post_id Project ID.
 * @return array
 */
function tufte_blocks_project_synced_summary( int $post_id ): array {
	$cache    = tufte_blocks_project_get_cache( $post_id );
	$resolved = array();
	foreach ( tufte_blocks_project_derived_keys() as $key ) {
		$resolved[ $key ] = tufte_blocks_project_resolve_field( $key, '', $cache, false );
	}
	return array(
		'fetched_at' => $cache['fetched_at'],
		'has_wporg'  => null !== $cache['wporg'],
		'has_github' => null !== $cache['github'],
		'errors'     => $cache['errors'],
		'resolved'   => $resolved,
	);
}

/**
 * REST: POST /tufte-blocks/v1/projects/{id}/sync, and a read-only field on
 * the project resource so the sidebar can show synced values on load.
 *
 * @return void
 */
function tufte_blocks_project_register_rest(): void {
	register_rest_route(
		'tufte-blocks/v1',
		'/projects/(?P<id>\d+)/sync',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => static fn( WP_REST_Request $request ): bool => current_user_can( 'edit_post', (int) $request['id'] ),
			'callback'            => static function ( WP_REST_Request $request ) {
				$post_id = (int) $request['id'];
				if ( 'project' !== get_post_type( $post_id ) ) {
					return new WP_Error( 'tufte_not_project', 'Not a project.', array( 'status' => 404 ) );
				}
				tufte_blocks_project_sync( $post_id );
				return rest_ensure_response( tufte_blocks_project_synced_summary( $post_id ) );
			},
			'args'                => array(
				'id' => array( 'validate_callback' => static fn( $value ): bool => is_numeric( $value ) ),
			),
		)
	);

	register_rest_field(
		'project',
		'project_synced',
		array(
			'get_callback' => static fn( array $post ): array => tufte_blocks_project_synced_summary( (int) $post['id'] ),
			'schema'       => array(
				'type'     => 'object',
				'context'  => array( 'edit' ),
				'readonly' => true,
			),
		)
	);
}
add_action( 'rest_api_init', 'tufte_blocks_project_register_rest' );

/**
 * WP-CLI: wp tufte-blocks project-sync [<id>]
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'tufte-blocks project-sync',
		static function ( array $args ): void {
			$ids = $args ? array( (int) $args[0] ) : get_posts(
				array(
					'post_type'      => 'project',
					'post_status'    => 'publish',
					'posts_per_page' => 50,
					'fields'         => 'ids',
				)
			);
			foreach ( $ids as $id ) {
				$cache = tufte_blocks_project_sync( (int) $id );
				WP_CLI::log( sprintf( '%d %s', $id, get_the_title( (int) $id ) ) );
				WP_CLI::log( '  fetched_at: ' . $cache['fetched_at'] );
				WP_CLI::log( '  wporg:      ' . ( null === $cache['wporg'] ? '(none)' : wp_json_encode( $cache['wporg'] ) ) );
				WP_CLI::log( '  github:     ' . ( null === $cache['github'] ? '(none)' : wp_json_encode( $cache['github'] ) ) );
				foreach ( $cache['errors'] as $source => $message ) {
					WP_CLI::warning( "  {$source}: {$message}" );
				}
				WP_CLI::log( '  thumbnail:  ' . ( has_post_thumbnail( (int) $id ) ? (string) get_post_thumbnail_id( (int) $id ) : 'none' ) );
			}
			WP_CLI::success( 'Synced ' . count( $ids ) . ' project(s).' );
		}
	);
}
