<?php
declare(strict_types=1);

$cache = array(
	'fetched_at' => '2026-09-14T00:00:00+00:00',
	'wporg'      => array( 'version' => '1.0.2', 'requires_wp' => '6.4', 'release_date' => '2026-09-14 7:08pm GMT' ),
	'github'     => array( 'version' => '1.0.1', 'license' => 'GPLv2 or later', 'link_support' => 'https://github.com/x/y/issues' ),
	'errors'     => array(),
);

tufte_assert_same( '9.9', tufte_blocks_project_resolve_field( 'version', '9.9', $cache ), 'resolve: override beats .org' );
tufte_assert_same( '1.0.2', tufte_blocks_project_resolve_field( 'version', '', $cache ), 'resolve: .org beats GitHub' );
tufte_assert_same( 'GPLv2 or later', tufte_blocks_project_resolve_field( 'license', '', $cache ), 'resolve: GitHub fills what .org lacks' );
tufte_assert_same( '', tufte_blocks_project_resolve_field( 'tested_up_to', '', $cache ), 'resolve: nothing anywhere is empty string' );
tufte_assert_same( '', tufte_blocks_project_resolve_field( 'not_a_field', 'x', $cache ), 'resolve: unregistered key is empty even with a value' );
tufte_assert_same( '', tufte_blocks_project_resolve_field( 'version', '   ', array( 'wporg' => null, 'github' => null ) ), 'resolve: whitespace override does not count' );

tufte_assert_same( '6.4 or higher', tufte_blocks_project_resolve_field( 'requires_wp', '', $cache ), 'resolve: formatter applied by default' );
tufte_assert_same( '6.4', tufte_blocks_project_resolve_field( 'requires_wp', '', $cache, false ), 'resolve: formatter can be skipped' );
tufte_assert_same( 'Sep 2026', tufte_blocks_project_resolve_field( 'release_date', '', $cache ), 'resolve: date formatted Mon YYYY' );
tufte_assert_same( 'Sep 2026', tufte_blocks_project_resolve_field( 'release_date', 'Sep 2026', $cache ), 'resolve: prose override left alone by date formatter' );

// Manual fields resolve from the override slot only (they have no source data).
tufte_assert_same( 'A quiet cue.', tufte_blocks_project_resolve_field( 'tagline', 'A quiet cue.', $cache ), 'resolve: manual field returns its value' );

// Post-backed lookup, using the object cache so no database write is needed.
$fake_id = 987654321;
wp_cache_set(
	$fake_id,
	array(
		'project_version'       => array( '' ),
		'project_tagline'       => array( 'Hello' ),
		'_project_source_cache' => array( serialize( $cache ) ),
	),
	'post_meta'
);
tufte_assert_same( '1.0.2', tufte_blocks_project_get_field( $fake_id, 'version' ), 'get_field: reads cache through post meta' );
tufte_assert_same( 'Hello', tufte_blocks_project_get_field( $fake_id, 'tagline' ), 'get_field: reads manual meta' );
tufte_assert_same( '', tufte_blocks_project_get_field( $fake_id, 'demo_url' ), 'get_field: unset manual meta is empty' );

// Binding source callback signature. Core copies the source's uses_context
// into $block->context inside WP_Block::process_block_bindings(), so a direct
// call has to set it by hand.
$block          = new WP_Block( array( 'blockName' => 'core/paragraph', 'attrs' => array() ), array() );
$block->context = array( 'postId' => $fake_id, 'postType' => 'project' );
tufte_assert_same( '1.0.2', tufte_blocks_project_binding_value( array( 'key' => 'version' ), $block, 'content' ), 'binding: resolves via block context postId' );
tufte_assert_same( '', tufte_blocks_project_binding_value( array(), $block, 'content' ), 'binding: missing key arg is empty' );
tufte_assert_same( true, null !== get_block_bindings_source( 'tufte-blocks/project-field' ), 'binding: source is registered' );

// The real path: core renders a bound paragraph and calls our source with context.
$rendered = ( new WP_Block(
	array(
		'blockName'    => 'core/paragraph',
		'attrs'        => array( 'metadata' => array( 'bindings' => array( 'content' => array( 'source' => 'tufte-blocks/project-field', 'args' => array( 'key' => 'version' ) ) ) ) ),
		'innerHTML'    => '<p>placeholder</p>',
		'innerContent' => array( '<p>placeholder</p>' ),
	),
	array( 'postId' => $fake_id, 'postType' => 'project' )
) )->render();
tufte_assert_same( true, str_contains( $rendered, '>1.0.2<' ), 'binding: core render replaces paragraph content via the source' );

wp_cache_delete( $fake_id, 'post_meta' );
