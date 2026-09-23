<?php
/**
 * Project field resolver and block bindings source.
 *
 * The only branching logic in the feature lives here: manual override, then
 * wordpress.org, then GitHub, then empty. Templates bind to
 * tufte-blocks/project-field with {"key":"version"} and never learn where
 * the value came from. Nothing here touches the network.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve one field from an override value and a cache array. Pure.
 *
 * @param string $key      Registry key.
 * @param string $override Value of the project_{key} meta (manual value or override).
 * @param array  $cache    Cache in the shape of tufte_blocks_project_get_cache().
 * @param bool   $format   Apply the registry formatter (default true).
 * @return string Empty string when nothing resolves.
 */
function tufte_blocks_project_resolve_field( string $key, string $override, array $cache, bool $format = true ): string {
	$fields = tufte_blocks_project_fields();
	if ( ! isset( $fields[ $key ] ) ) {
		return '';
	}

	$value = trim( $override );

	if ( '' === $value && ! $fields[ $key ]['manual'] ) {
		foreach ( array( 'wporg', 'github' ) as $source ) {
			if ( isset( $cache[ $source ][ $key ] ) && is_scalar( $cache[ $source ][ $key ] ) ) {
				$candidate = trim( (string) $cache[ $source ][ $key ] );
				if ( '' !== $candidate ) {
					$value = $candidate;
					break;
				}
			}
		}
	}

	if ( '' === $value ) {
		return '';
	}

	if ( $format && ! empty( $fields[ $key ]['format'] ) && is_callable( $fields[ $key ]['format'] ) ) {
		$value = (string) call_user_func( $fields[ $key ]['format'], $value );
	}

	return $value;
}

/**
 * Resolve a field for a post.
 *
 * @param int    $post_id Project ID.
 * @param string $key     Registry key.
 * @return string
 */
function tufte_blocks_project_get_field( int $post_id, string $key ): string {
	if ( $post_id <= 0 ) {
		return '';
	}
	$override = get_post_meta( $post_id, tufte_blocks_project_meta_key( $key ), true );
	return tufte_blocks_project_resolve_field(
		$key,
		is_scalar( $override ) ? (string) $override : '',
		tufte_blocks_project_get_cache( $post_id )
	);
}

/**
 * Block bindings callback.
 *
 * @param array    $source_args    Binding args; expects 'key'.
 * @param WP_Block $block_instance The block being rendered.
 * @param string   $attribute_name Bound attribute (unused; the value is the same for any attribute).
 * @return string
 */
function tufte_blocks_project_binding_value( array $source_args, WP_Block $block_instance, string $attribute_name ): string {
	$key     = isset( $source_args['key'] ) ? (string) $source_args['key'] : '';
	$post_id = isset( $block_instance->context['postId'] ) ? (int) $block_instance->context['postId'] : (int) get_the_ID();
	if ( '' === $key ) {
		return '';
	}
	return tufte_blocks_project_get_field( $post_id, $key );
}

/**
 * Register the binding source.
 *
 * @return void
 */
function tufte_blocks_project_register_binding_source(): void {
	register_block_bindings_source(
		'tufte-blocks/project-field',
		array(
			'label'              => __( 'Project field', 'tufte-blocks' ),
			'get_value_callback' => 'tufte_blocks_project_binding_value',
			'uses_context'       => array( 'postId', 'postType' ),
		)
	);
}
add_action( 'init', 'tufte_blocks_project_register_binding_source' );

/**
 * Drop blocks whose project-field binding resolved to nothing.
 *
 * Block Bindings renders an empty element when a value is empty; the design
 * says a button with no URL must not render at all, never fall back to "#".
 * Also drops a details row (core/group.tufte-project-detail) whose value
 * paragraph was dropped, so no orphan label is left behind.
 *
 * @param string   $block_content Rendered HTML.
 * @param array    $block         Parsed block.
 * @param WP_Block $instance      Block instance (for context).
 * @return string
 */
function tufte_blocks_project_filter_empty_bindings( string $block_content, array $block, WP_Block $instance ): string {
	$class_name = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
	if ( 'core/group' === ( $block['blockName'] ?? '' ) && str_contains( $class_name, 'tufte-project-detail' ) ) {
		return str_contains( $block_content, 'tufte-project-value' ) ? $block_content : '';
	}

	$bindings = $block['attrs']['metadata']['bindings'] ?? null;
	if ( ! is_array( $bindings ) ) {
		return $block_content;
	}

	foreach ( $bindings as $attribute => $binding ) {
		if ( ( $binding['source'] ?? '' ) !== 'tufte-blocks/project-field' ) {
			continue;
		}
		$value = tufte_blocks_project_binding_value( (array) ( $binding['args'] ?? array() ), $instance, (string) $attribute );
		if ( '' === $value ) {
			return '';
		}
	}

	return $block_content;
}
add_filter( 'render_block', 'tufte_blocks_project_filter_empty_bindings', 20, 3 );
