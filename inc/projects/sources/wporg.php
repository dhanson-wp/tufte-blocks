<?php
/**
 * wordpress.org plugin directory adapter.
 *
 * Knows nothing about posts. fetch() does the network call, map() turns an
 * API response array into registry field values. Only map() is tested.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User agent for all outbound project requests.
 *
 * @return string
 */
function tufte_blocks_project_user_agent(): string {
	return 'tufte-blocks/' . TUFTE_BLOCKS_VERSION . ' (+https://derekhanson.blog; project sync)';
}

/**
 * Fetch plugin information from api.wordpress.org.
 *
 * @param string $slug Plugin slug, e.g. "scroll-indicator".
 * @return array|WP_Error Field values from map(), or an error.
 */
function tufte_blocks_project_fetch_wporg( string $slug ) {
	$slug = sanitize_title( $slug );
	if ( '' === $slug ) {
		return new WP_Error( 'tufte_wporg_no_slug', 'No wordpress.org slug.' );
	}

	$url = add_query_arg(
		array(
			'action'        => 'plugin_information',
			'request[slug]' => $slug,
		),
		'https://api.wordpress.org/plugins/info/1.2/'
	);

	$response = wp_remote_get(
		$url,
		array(
			'timeout'    => 10,
			'user-agent' => tufte_blocks_project_user_agent(),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		return new WP_Error( 'tufte_wporg_http', sprintf( 'wordpress.org returned HTTP %d for %s.', $code, $slug ) );
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) || empty( $data['slug'] ) ) {
		return new WP_Error( 'tufte_wporg_body', sprintf( 'wordpress.org returned no plugin data for %s.', $slug ) );
	}

	return tufte_blocks_project_map_wporg( $data );
}

/**
 * Map a plugin_information response to registry field values.
 *
 * Pure. Missing keys become empty strings. Also returns 'banner' (the high
 * resolution banner URL) for the one-time featured image sideload; it is not
 * a registry field.
 *
 * @param array $data Decoded API response.
 * @return array<string,string>
 */
function tufte_blocks_project_map_wporg( array $data ): array {
	$slug = isset( $data['slug'] ) ? sanitize_title( (string) $data['slug'] ) : '';
	$str  = static fn( string $key ): string => isset( $data[ $key ] ) && is_scalar( $data[ $key ] ) ? trim( (string) $data[ $key ] ) : '';

	return array(
		'version'        => $str( 'version' ),
		'release_date'   => $str( 'last_updated' ),
		'requires_wp'    => $str( 'requires' ),
		'tested_up_to'   => $str( 'tested' ),
		'requires_php'   => $str( 'requires_php' ),
		'link_download'  => $str( 'download_link' ),
		'link_directory' => $slug ? 'https://wordpress.org/plugins/' . $slug . '/' : '',
		'link_support'   => $str( 'support_url' ),
		'link_translate' => $slug ? 'https://translate.wordpress.org/projects/wp-plugins/' . $slug . '/' : '',
		'banner'         => isset( $data['banners']['high'] ) && is_string( $data['banners']['high'] ) ? $data['banners']['high'] : '',
	);
}
