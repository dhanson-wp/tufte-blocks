<?php
/**
 * WordPress file header parser.
 *
 * Reads the standard header block from a style.css or main plugin file and
 * returns the six fields the project sync cares about. Missing fields are
 * empty strings, never wrong values. This is the one piece of real string
 * handling in the feature and it is covered by tests/test-header-parser.php.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse a WordPress file header.
 *
 * Mirrors core's get_file_data(): a header line is `Label: value` at the
 * start of a line, optionally preceded by whitespace, `*`, `#`, or `/`.
 * Only the first 8 KB is inspected, as core does.
 *
 * @param string $contents Raw file contents.
 * @return array{name:string,version:string,requires_wp:string,tested_up_to:string,requires_php:string,license:string}
 */
function tufte_blocks_parse_file_header( string $contents ): array {
	$labels = array(
		'name'         => array( 'Theme Name', 'Plugin Name' ),
		'version'      => array( 'Version' ),
		'requires_wp'  => array( 'Requires at least' ),
		'tested_up_to' => array( 'Tested up to' ),
		'requires_php' => array( 'Requires PHP' ),
		'license'      => array( 'License' ),
	);

	$head = str_replace( "\r", "\n", substr( $contents, 0, 8 * 1024 ) );
	$out  = array();

	foreach ( $labels as $key => $names ) {
		$out[ $key ] = '';
		foreach ( $names as $name ) {
			// `License:` must not match `License URI:`, so require the colon right after the label.
			if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $name, '/' ) . ':(.*)$/mi', $head, $match ) ) {
				$out[ $key ] = trim( preg_replace( '/\s*(?:\*\/|\?>).*/', '', $match[1] ) );
				break;
			}
		}
	}

	return $out;
}
