<?php
declare(strict_types=1);

// ---- wordpress.org ----------------------------------------------------------

$org = tufte_blocks_project_map_wporg( tufte_fixture_json( 'wporg-scroll-indicator.json' ) );
tufte_assert_same( '1.0.2', $org['version'], 'wporg: version' );
tufte_assert_same( '2026-09-14 7:08pm GMT', $org['release_date'], 'wporg: release_date is last_updated, unformatted' );
tufte_assert_same( '6.4', $org['requires_wp'], 'wporg: requires_wp' );
tufte_assert_same( '7.0.4', $org['tested_up_to'], 'wporg: tested_up_to' );
tufte_assert_same( '7.4', $org['requires_php'], 'wporg: requires_php' );
tufte_assert_same( 'https://downloads.wordpress.org/plugin/scroll-indicator.1.0.2.zip', $org['link_download'], 'wporg: link_download' );
tufte_assert_same( 'https://wordpress.org/plugins/scroll-indicator/', $org['link_directory'], 'wporg: link_directory synthesised from slug' );
tufte_assert_same( 'https://wordpress.org/support/plugin/scroll-indicator/', $org['link_support'], 'wporg: link_support' );
tufte_assert_same( 'https://translate.wordpress.org/projects/wp-plugins/scroll-indicator/', $org['link_translate'], 'wporg: link_translate synthesised from slug' );
tufte_assert_same( 'https://ps.w.org/scroll-indicator/assets/banner-1544x500.png?rev=3695697', $org['banner'], 'wporg: high banner kept for sideload' );
tufte_assert_same( false, array_key_exists( 'license', $org ), 'wporg: no license key (the API does not publish one)' );

$sparse = tufte_blocks_project_map_wporg( array( 'slug' => 'x' ) );
tufte_assert_same( '', $sparse['version'], 'wporg sparse: missing version is empty string' );
tufte_assert_same( 'https://wordpress.org/plugins/x/', $sparse['link_directory'], 'wporg sparse: directory link still synthesised' );
tufte_assert_same( '', $sparse['banner'], 'wporg sparse: no banner is empty string' );

$noslug = tufte_blocks_project_map_wporg( array() );
tufte_assert_same( '', $noslug['link_directory'], 'wporg no slug: no synthesised links' );
tufte_assert_same( '', $noslug['link_translate'], 'wporg no slug: no translate link' );

$err = tufte_blocks_project_fetch_wporg( '' );
tufte_assert_same( true, is_wp_error( $err ), 'wporg fetch: empty slug is a WP_Error without a request' );
