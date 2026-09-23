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

// ---- GitHub -----------------------------------------------------------------

tufte_assert_same( array( 'dhanson-wp', 'tufte-blocks' ), tufte_blocks_project_parse_github_url( 'https://github.com/dhanson-wp/tufte-blocks' ), 'github url: owner/repo' );
tufte_assert_same( array( 'dhanson-wp', 'tufte-blocks' ), tufte_blocks_project_parse_github_url( 'https://github.com/dhanson-wp/tufte-blocks.git/' ), 'github url: strips .git and trailing slash' );
tufte_assert_same( null, tufte_blocks_project_parse_github_url( 'https://gitlab.com/x/y' ), 'github url: other host is null' );
tufte_assert_same( null, tufte_blocks_project_parse_github_url( '' ), 'github url: empty is null' );

$theme = tufte_blocks_project_map_github(
	tufte_fixture_json( 'github-repo-tufte-blocks.json' ),
	tufte_blocks_parse_file_header( tufte_fixture( 'header-style-css.txt' ) ),
	tufte_fixture_json( 'github-release-tufte-blocks.json' )
);
tufte_assert_same( '1.4.4', $theme['version'], 'github theme: version from style.css header, not the v1.3.0 release' );
tufte_assert_same( '2026-02-27T18:49:41Z', $theme['release_date'], 'github theme: release_date from release published_at' );
tufte_assert_same( '6.4', $theme['requires_wp'], 'github theme: requires_wp from header' );
tufte_assert_same( '6.9', $theme['tested_up_to'], 'github theme: tested_up_to from header' );
tufte_assert_same( '7.4', $theme['requires_php'], 'github theme: requires_php from header' );
tufte_assert_same( 'GNU General Public License v2 or later', $theme['license'], 'github theme: header License beats GitHub GPL-3.0' );
tufte_assert_same( 'https://github.com/dhanson-wp/tufte-blocks/releases/download/v1.3.0/tufte-blocks-1.3.0.zip', $theme['link_download'], 'github theme: first release asset' );
tufte_assert_same( 'https://github.com/dhanson-wp/tufte-blocks/issues', $theme['link_support'], 'github theme: issues url' );
tufte_assert_same( false, array_key_exists( 'link_directory', $theme ), 'github: no link_directory' );
tufte_assert_same( false, array_key_exists( 'link_translate', $theme ), 'github: no link_translate' );

$plugin = tufte_blocks_project_map_github(
	tufte_fixture_json( 'github-repo-scroll-indicator.json' ),
	tufte_blocks_parse_file_header( tufte_fixture( 'header-plugin-php.txt' ) ),
	array()
);
tufte_assert_same( '1.0.1', $plugin['version'], 'github plugin: version from plugin header' );
tufte_assert_same( '', $plugin['release_date'], 'github plugin: no release means empty date' );
tufte_assert_same( '', $plugin['link_download'], 'github plugin: no release means empty download' );
tufte_assert_same( 'GPLv2 or later', $plugin['license'], 'github plugin: header license' );
tufte_assert_same( '', $plugin['tested_up_to'], 'github plugin: missing header field stays empty' );

$nolicense = tufte_blocks_project_map_github(
	tufte_fixture_json( 'github-repo-tufte-blocks.json' ),
	tufte_blocks_parse_file_header( "/*\nTheme Name: X\nVersion: 9\n*/" ),
	array()
);
tufte_assert_same( 'GPL-3.0', $nolicense['license'], 'github: spdx_id used when header has no License' );

$noassert = tufte_blocks_project_map_github(
	tufte_fixture_json( 'github-repo-scroll-indicator.json' ),
	tufte_blocks_parse_file_header( "/*\nPlugin Name: X\n*/" ),
	array()
);
tufte_assert_same( '', $noassert['license'], 'github: NOASSERTION is ignored' );

$noassets = tufte_blocks_project_map_github(
	tufte_fixture_json( 'github-repo-tufte-blocks.json' ),
	array(),
	array( 'tag_name' => 'v2', 'published_at' => '2026-01-01T00:00:00Z', 'assets' => array() )
);
tufte_assert_same( '', $noassets['link_download'], 'github: release with no assets gives empty download' );
tufte_assert_same( '2026-01-01T00:00:00Z', $noassets['release_date'], 'github: release date still taken' );

$err = tufte_blocks_project_fetch_github( 'not a url' );
tufte_assert_same( true, is_wp_error( $err ), 'github fetch: unparseable url is a WP_Error without a request' );
