<?php
/**
 * Loaded via `wp --require=tests/allow-hosts.php` so live sync runs work from
 * the command line. Studio's wp-config.php defines WP_HTTP_BLOCK_EXTERNAL;
 * WordPress still allows the hosts named here. Never loaded by the theme.
 *
 * @package Tufte_Blocks
 */

if ( ! defined( 'WP_ACCESSIBLE_HOSTS' ) ) {
	define( 'WP_ACCESSIBLE_HOSTS', 'api.wordpress.org,downloads.wordpress.org,ps.w.org,api.github.com,raw.githubusercontent.com,github.com,objects.githubusercontent.com,release-assets.githubusercontent.com' );
}
