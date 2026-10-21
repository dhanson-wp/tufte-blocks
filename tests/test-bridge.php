<?php
// Bridge release: the theme steps aside when Tufte Projects is active, and
// no longer carries leftover one-time init routines.

if ( defined( 'TUFTE_PROJECTS_VERSION' ) ) {
	tufte_assert_same( false, function_exists( 'tufte_blocks_project_sync_all' ), 'bridge: theme copy of Projects code did not load' );
	tufte_assert_same( true, post_type_exists( 'project' ), 'bridge: plugin registered the project post type' );
} else {
	echo "SKIP  bridge: Tufte Projects is not active\n";
}

// A saved page template must survive init (the old temporary reset deleted it).
$tufte_template_id = wp_insert_post(
	array(
		'post_type'    => 'wp_template',
		'post_name'    => 'page',
		'post_title'   => 'Page',
		'post_status'  => 'publish',
		'post_content' => '<!-- wp:post-content /-->',
	)
);
wp_set_object_terms( $tufte_template_id, get_stylesheet(), 'wp_theme' );
do_action( 'init' );
tufte_assert_same( true, is_int( $tufte_template_id ) && $tufte_template_id > 0 && null !== get_post( $tufte_template_id ), 'bridge: saved page template survives init' );
if ( $tufte_template_id ) {
	wp_delete_post( (int) $tufte_template_id, true );
}
