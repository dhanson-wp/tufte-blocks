<?php
/**
 * Tests for inc/formats.php: post format support and the status title filter.
 *
 * Run through tests/run.php, which provides the assert helpers.
 *
 * @package Tufte_Blocks
 */

tufte_assert_same( true, current_theme_supports( 'post-formats' ), 'theme supports post formats' );

$tufte_formats_support = get_theme_support( 'post-formats' );
tufte_assert_same(
	function_exists( 'tufte_blocks_post_formats' ) ? tufte_blocks_post_formats() : null,
	is_array( $tufte_formats_support ) ? $tufte_formats_support[0] : null,
	'theme support list matches tufte_blocks_post_formats()'
);
tufte_assert_same(
	array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ),
	function_exists( 'tufte_blocks_post_formats' ) ? tufte_blocks_post_formats() : null,
	'tufte_blocks_post_formats() lists the core formats the theme supports'
);

$tufte_status_id   = wp_insert_post(
	array(
		'post_title'  => 'Format test status',
		'post_status' => 'publish',
		'post_type'   => 'post',
	)
);
$tufte_standard_id = wp_insert_post(
	array(
		'post_title'  => 'Format test standard',
		'post_status' => 'publish',
		'post_type'   => 'post',
	)
);
set_post_format( $tufte_status_id, 'status' );

tufte_assert_same(
	true,
	in_array( 'format-status', get_post_class( '', $tufte_status_id ), true ),
	'status posts carry the format-status class'
);

if ( function_exists( 'tufte_blocks_format_hide_title' ) ) {
	$GLOBALS['post'] = get_post( $tufte_status_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $GLOBALS['post'] );
	tufte_assert_same( '', tufte_blocks_format_hide_title( '<h2>x</h2>' ), 'status post title is hidden' );

	$GLOBALS['post'] = get_post( $tufte_standard_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $GLOBALS['post'] );
	tufte_assert_same( '<h2>x</h2>', tufte_blocks_format_hide_title( '<h2>x</h2>' ), 'standard post title is kept' );
	wp_reset_postdata();
} else {
	tufte_assert_same( true, false, 'tufte_blocks_format_hide_title() exists' );
}

/**
 * Render a core/post-date block for a post inside a query-loop-style context.
 *
 * @param int  $post_id Post ID.
 * @param bool $is_link Whether the date block links itself.
 * @return string
 */
function tufte_formats_test_render_date( int $post_id, bool $is_link ): string {
	$block = new WP_Block(
		array(
			'blockName' => 'core/post-date',
			'attrs'     => array( 'isLink' => $is_link ),
		),
		array(
			'postId'   => $post_id,
			'postType' => 'post',
		)
	);
	return $block->render();
}

$tufte_status_permalink = get_permalink( $tufte_status_id );
$tufte_status_date      = tufte_formats_test_render_date( $tufte_status_id, false );
tufte_assert_same(
	1,
	preg_match( '#<a\b[^>]*href="' . preg_quote( $tufte_status_permalink, '#' ) . '"[^>]*>\s*<time\b#', $tufte_status_date ),
	'status post date link wraps the time element'
);
tufte_assert_same(
	0,
	preg_match( '#<a\b(?:(?!</a>).)*<a\b#s', $tufte_status_date ),
	'status post date has no nested links'
);
tufte_assert_same(
	true,
	str_contains( $tufte_status_date, 'u-url' ) && str_contains( $tufte_status_date, 'dt-published' ),
	'status post date keeps its microformats'
);
tufte_assert_same(
	0,
	preg_match( '#<a\\b[^>]*>\\s*<time\\b#', tufte_formats_test_render_date( $tufte_standard_id, false ) ),
	'standard post date does not gain a link around the time'
);
tufte_assert_same(
	1,
	preg_match( '#<a\\b[^>]*href="' . preg_quote( $tufte_status_permalink, '#' ) . '"[^>]*>\\s*<time\\b#', tufte_formats_test_render_date( $tufte_status_id, true ) ),
	'status post date with isLink true stays linked'
);
tufte_assert_same(
	0,
	preg_match( '#<a\b(?:(?!</a>).)*<a\b#s', tufte_formats_test_render_date( $tufte_status_id, true ) ),
	'status post date with isLink true has no nested links'
);

if ( defined( 'TUFTE_FORMATS_VERSION' ) ) {
	echo "SKIP  Tufte Formats is active; it owns the home query\n";
} else {
	$tufte_home_query = new WP_Query(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => 50,
		)
	);
	tufte_assert_same(
		true,
		in_array( $tufte_status_id, $tufte_home_query->posts, true ),
		'status post stays in the home query without Tufte Formats'
	);
}

tufte_assert_same( false, function_exists( 'tufte_blocks_notes_exclude_main' ), 'theme no longer defines the notes query exclusion' );
tufte_assert_same( false, function_exists( 'tufte_blocks_notes_query_block' ), 'theme no longer defines the notes query block filter' );

if ( defined( 'TUFTE_PROJECTS_VERSION' ) ) {
	echo "SKIP  Tufte Projects is active; it registers the project post type\n";
} else {
	tufte_assert_same( false, post_type_exists( 'project' ), 'theme does not register the project post type' );
}

wp_delete_post( $tufte_status_id, true );
wp_delete_post( $tufte_standard_id, true );
