<?php
/**
 * Keep the page cache warm for the pages visitors are most likely to open.
 *
 * The host's page cache keeps anonymous pages for five minutes. On a
 * low-traffic site most visits land after an entry has expired, so they pay
 * for a full PHP render (0.6 to 1.5 seconds). A short recurring request from
 * the server itself keeps those pages fresh.
 *
 * Turn it off with `define( 'TUFTE_BLOCKS_CACHE_WARM', false );` in wp-config.php.
 *
 * @package Tufte_Blocks
 * @since 1.9.2
 */

defined( 'ABSPATH' ) || exit;

const TUFTE_BLOCKS_CACHE_WARM_HOOK = 'tufte_blocks_cache_warm';

/**
 * Whether cache warming should run on this site.
 *
 * Local and staging environments have no page cache worth warming.
 *
 * @since 1.9.2
 * @return bool
 */
function tufte_blocks_cache_warm_enabled(): bool {
	if ( defined( 'TUFTE_BLOCKS_CACHE_WARM' ) && ! TUFTE_BLOCKS_CACHE_WARM ) {
		return false;
	}

	/**
	 * Filters whether the cache warmer runs. Defaults to production only.
	 *
	 * @since 1.9.2
	 * @param bool $enabled Whether warming is enabled.
	 */
	return (bool) apply_filters( 'tufte_blocks_cache_warm_enabled', 'production' === wp_get_environment_type() );
}

/**
 * Add a four-minute schedule, just inside the five-minute cache lifetime.
 *
 * @since 1.9.2
 * @param array $schedules Registered cron schedules.
 * @return array
 */
function tufte_blocks_cache_warm_schedule( array $schedules ): array {
	$schedules['tufte_blocks_four_minutes'] = array(
		'interval' => 4 * MINUTE_IN_SECONDS,
		'display'  => __( 'Every four minutes', 'tufte-blocks' ),
	);

	return $schedules;
}
add_filter( 'cron_schedules', 'tufte_blocks_cache_warm_schedule' );

/**
 * Schedule (or unschedule) the warming event.
 *
 * @since 1.9.2
 * @return void
 */
function tufte_blocks_cache_warm_schedule_event(): void {
	$scheduled = wp_next_scheduled( TUFTE_BLOCKS_CACHE_WARM_HOOK );

	if ( ! tufte_blocks_cache_warm_enabled() ) {
		if ( $scheduled ) {
			wp_clear_scheduled_hook( TUFTE_BLOCKS_CACHE_WARM_HOOK );
		}
		return;
	}

	if ( ! $scheduled ) {
		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'tufte_blocks_four_minutes', TUFTE_BLOCKS_CACHE_WARM_HOOK );
	}
}
add_action( 'init', 'tufte_blocks_cache_warm_schedule_event' );

/**
 * Remove the event when the theme is switched out.
 *
 * @since 1.9.2
 * @return void
 */
function tufte_blocks_cache_warm_cleanup(): void {
	wp_clear_scheduled_hook( TUFTE_BLOCKS_CACHE_WARM_HOOK );
}
add_action( 'switch_theme', 'tufte_blocks_cache_warm_cleanup' );

/**
 * URLs to keep warm: home, the main sections, top-level pages, and recent posts.
 *
 * @since 1.9.2
 * @return string[]
 */
function tufte_blocks_cache_warm_urls(): array {
	$urls = array(
		home_url( '/' ),
		home_url( '/archive/' ),
		home_url( '/archive/notes/' ),
		home_url( '/projects/' ),
	);

	$pages = get_pages(
		array(
			'parent'      => 0,
			'number'      => 10,
			'post_status' => 'publish',
		)
	);
	foreach ( $pages as $page ) {
		$urls[] = get_permalink( $page );
	}

	$posts = get_posts(
		array(
			'numberposts'      => 5,
			'post_status'      => 'publish',
			'fields'           => 'ids',
			'suppress_filters' => true,
		)
	);
	foreach ( $posts as $post_id ) {
		$urls[] = get_permalink( $post_id );
	}

	/**
	 * Filters the URLs the cache warmer requests.
	 *
	 * @since 1.9.2
	 * @param string[] $urls Absolute URLs.
	 */
	$urls = apply_filters( 'tufte_blocks_cache_warm_urls', $urls );

	return array_values( array_unique( array_filter( $urls ) ) );
}

/**
 * Request each URL once, as an anonymous visitor.
 *
 * No cookies and no query string, so the request matches the cache key real
 * visitors use.
 *
 * @since 1.9.2
 * @return void
 */
function tufte_blocks_cache_warm_run(): void {
	if ( ! tufte_blocks_cache_warm_enabled() ) {
		return;
	}

	foreach ( tufte_blocks_cache_warm_urls() as $url ) {
		wp_remote_get(
			$url,
			array(
				'timeout'    => 15,
				'user-agent' => 'TufteBlocksCacheWarm/' . TUFTE_BLOCKS_VERSION,
				'sslverify'  => true,
			)
		);
	}
}
add_action( TUFTE_BLOCKS_CACHE_WARM_HOOK, 'tufte_blocks_cache_warm_run' );
