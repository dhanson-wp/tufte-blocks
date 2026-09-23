<?php
declare(strict_types=1);

$style = tufte_blocks_parse_file_header( tufte_fixture( 'header-style-css.txt' ) );
tufte_assert_same( '1.4.4', $style['version'], 'style.css: version' );
tufte_assert_same( '6.4', $style['requires_wp'], 'style.css: requires at least' );
tufte_assert_same( '6.9', $style['tested_up_to'], 'style.css: tested up to' );
tufte_assert_same( '7.4', $style['requires_php'], 'style.css: requires php' );
tufte_assert_same( 'GNU General Public License v2 or later', $style['license'], 'style.css: license' );
tufte_assert_same( 'Tufte Blocks', $style['name'], 'style.css: theme name' );

$plugin = tufte_blocks_parse_file_header( tufte_fixture( 'header-plugin-php.txt' ) );
tufte_assert_same( '1.0.1', $plugin['version'], 'plugin.php: version' );
tufte_assert_same( '6.4', $plugin['requires_wp'], 'plugin.php: requires at least' );
tufte_assert_same( '', $plugin['tested_up_to'], 'plugin.php: missing tested up to is empty' );
tufte_assert_same( 'GPLv2 or later', $plugin['license'], 'plugin.php: license with leading asterisks and padding' );
tufte_assert_same( 'Scroll Indicator', $plugin['name'], 'plugin.php: plugin name' );

$crlf = str_replace( "\n", "\r\n", tufte_fixture( 'header-style-css.txt' ) );
$win  = tufte_blocks_parse_file_header( $crlf );
tufte_assert_same( '1.4.4', $win['version'], 'CRLF: version has no trailing CR' );
tufte_assert_same( 'GNU General Public License v2 or later', $win['license'], 'CRLF: license has no trailing CR' );

$none = tufte_blocks_parse_file_header( "/*\nTheme Name: Bare\n*/\n" );
tufte_assert_same( '', $none['version'], 'missing fields: version empty' );
tufte_assert_same( '', $none['requires_wp'], 'missing fields: requires_wp empty' );
tufte_assert_same( '', $none['license'], 'missing fields: license empty' );

$junk = tufte_blocks_parse_file_header( "<html><body>404 Not Found</body></html>" );
tufte_assert_same( '', $junk['version'], 'not a header: version empty' );
tufte_assert_same( '', $junk['name'], 'not a header: name empty' );
tufte_assert_same( array( 'name', 'version', 'requires_wp', 'tested_up_to', 'requires_php', 'license' ), array_keys( $junk ), 'always returns all six keys' );

$tricky = tufte_blocks_parse_file_header( "/*\nVersion: 2.0.0\nDescription: Version: not this\n*/" );
tufte_assert_same( '2.0.0', $tricky['version'], 'takes the first match at line start only' );

$empty = tufte_blocks_parse_file_header( '' );
tufte_assert_same( '', $empty['version'], 'empty input: version empty' );
