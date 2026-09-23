<?php
declare(strict_types=1);

$base = array( 'fetched_at' => '', 'wporg' => array( 'version' => '1.0.2' ), 'github' => array( 'version' => '1.0.1' ), 'errors' => array() );

$after_error = tufte_blocks_project_merge_source( $base, 'wporg', new WP_Error( 'x', 'boom' ) );
tufte_assert_same( array( 'version' => '1.0.2' ), $after_error['wporg'], 'merge: a failed fetch keeps the previous values' );
tufte_assert_same( 'boom', $after_error['errors']['wporg'], 'merge: the error is recorded' );

$after_ok = tufte_blocks_project_merge_source( $after_error, 'wporg', array( 'version' => '1.0.3' ) );
tufte_assert_same( array( 'version' => '1.0.3' ), $after_ok['wporg'], 'merge: a good fetch replaces values' );
tufte_assert_same( false, isset( $after_ok['errors']['wporg'] ), 'merge: a good fetch clears the error' );

$removed = tufte_blocks_project_merge_source( $base, 'github', null );
tufte_assert_same( null, $removed['github'], 'merge: removing the identifier drops that source' );
tufte_assert_same( array( 'version' => '1.0.2' ), $removed['wporg'], 'merge: the other source is untouched' );
