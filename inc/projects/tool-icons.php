<?php
/**
 * Tool icons for the project_tool taxonomy.
 *
 * Term meta, admin media uploader, and the frontend filter that prepends
 * an icon to each project_tool term link, plus the bundled icon collection
 * (1.6.0) that backs terms without an uploaded icon.
 *
 * @package Tufte_Blocks
 * @since 1.3.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register tool icon term meta for project_tool taxonomy.
 *
 * @since 1.3.0
 * @return void
 */
function tufte_blocks_register_tool_icon_meta(): void {
	register_term_meta(
		'project_tool',
		'tool_icon',
		array(
			'type'              => 'integer',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'absint',
		)
	);
}
add_action( 'init', 'tufte_blocks_register_tool_icon_meta' );

/**
 * Register the bundled tool SVGs as a "tufte-blocks" icon collection.
 *
 * Makes them available to the core Icon block's picker, and gives
 * project_tool terms a fallback icon when none is uploaded. Needs the
 * Icons API from WordPress 7.1; older versions skip it.
 *
 * @since 1.6.0
 * @return void
 */
function tufte_blocks_register_tool_icon_collection(): void {
	if ( ! function_exists( 'wp_register_icon_collection' ) ) {
		return;
	}

	wp_register_icon_collection(
		'tufte-blocks',
		array(
			'label'       => __( 'Tufte Blocks', 'tufte-blocks' ),
			'description' => __( 'Icons for the tools behind each project.', 'tufte-blocks' ),
		)
	);

	$icons = array(
		'antigravity' => __( 'Antigravity', 'tufte-blocks' ),
		'claude'      => __( 'Claude', 'tufte-blocks' ),
		'cursor'      => __( 'Cursor', 'tufte-blocks' ),
		'gemini'      => __( 'Gemini', 'tufte-blocks' ),
		'telex'       => __( 'Telex', 'tufte-blocks' ),
	);

	foreach ( $icons as $slug => $label ) {
		wp_register_icon(
			'tufte-blocks/' . $slug,
			array(
				'label'     => $label,
				'file_path' => get_template_directory() . '/assets/images/tools/' . $slug . '.svg',
			)
		);
	}
}
add_action( 'init', 'tufte_blocks_register_tool_icon_collection' );

/**
 * Enqueue media uploader on project_tool term screens.
 *
 * @since 1.3.0
 * @return void
 */
function tufte_blocks_tool_admin_scripts(): void {
	$screen = get_current_screen();
	if ( $screen && 'project_tool' === $screen->taxonomy ) {
		wp_enqueue_media();
	}
}
add_action( 'admin_enqueue_scripts', 'tufte_blocks_tool_admin_scripts' );

/**
 * Add icon field to the "Add New Tool" form.
 *
 * @since 1.3.0
 * @return void
 */
function tufte_blocks_tool_add_form_fields(): void {
	?>
	<div class="form-field">
		<label><?php esc_html_e( 'Icon', 'tufte-blocks' ); ?></label>
		<div id="tool-icon-preview"></div>
		<input type="hidden" name="tool_icon" id="tool-icon-id" value="">
		<button type="button" class="button" id="tool-icon-upload"><?php esc_html_e( 'Select Icon', 'tufte-blocks' ); ?></button>
		<button type="button" class="button" id="tool-icon-remove" style="display:none"><?php esc_html_e( 'Remove Icon', 'tufte-blocks' ); ?></button>
		<p class="description"><?php esc_html_e( 'Upload an SVG or image icon for this tool.', 'tufte-blocks' ); ?></p>
	</div>
	<?php
	tufte_blocks_tool_icon_inline_script();
}
add_action( 'project_tool_add_form_fields', 'tufte_blocks_tool_add_form_fields' );

/**
 * Add icon field to the "Edit Tool" form.
 *
 * @since 1.3.0
 * @param WP_Term $term Current term object.
 * @return void
 */
function tufte_blocks_tool_edit_form_fields( WP_Term $term ): void {
	$icon_id  = (int) get_term_meta( $term->term_id, 'tool_icon', true );
	$icon_url = $icon_id ? wp_get_attachment_url( $icon_id ) : '';
	?>
	<tr class="form-field">
		<th scope="row"><label><?php esc_html_e( 'Icon', 'tufte-blocks' ); ?></label></th>
		<td>
			<div id="tool-icon-preview">
				<?php if ( $icon_url ) : ?>
					<img src="<?php echo esc_url( $icon_url ); ?>" style="max-width:48px;max-height:48px;">
				<?php endif; ?>
			</div>
			<input type="hidden" name="tool_icon" id="tool-icon-id" value="<?php echo esc_attr( $icon_id ); ?>">
			<button type="button" class="button" id="tool-icon-upload"><?php esc_html_e( 'Select Icon', 'tufte-blocks' ); ?></button>
			<button type="button" class="button" id="tool-icon-remove" style="<?php echo $icon_id ? '' : 'display:none'; ?>"><?php esc_html_e( 'Remove Icon', 'tufte-blocks' ); ?></button>
			<p class="description"><?php esc_html_e( 'Upload an SVG or image icon for this tool.', 'tufte-blocks' ); ?></p>
		</td>
	</tr>
	<?php
	tufte_blocks_tool_icon_inline_script();
}
add_action( 'project_tool_edit_form_fields', 'tufte_blocks_tool_edit_form_fields' );

/**
 * Save tool icon term meta.
 *
 * @since 1.3.0
 * @param int $term_id Term ID.
 * @return void
 */
function tufte_blocks_save_tool_icon( int $term_id ): void {
	if ( ! isset( $_POST['tool_icon'] ) ) {
		return;
	}

	$icon_id = absint( $_POST['tool_icon'] );
	if ( $icon_id ) {
		update_term_meta( $term_id, 'tool_icon', $icon_id );
	} else {
		delete_term_meta( $term_id, 'tool_icon' );
	}
}
add_action( 'created_project_tool', 'tufte_blocks_save_tool_icon' );
add_action( 'edited_project_tool', 'tufte_blocks_save_tool_icon' );

/**
 * Inline script for the tool icon media uploader.
 *
 * @since 1.3.0
 * @return void
 */
function tufte_blocks_tool_icon_inline_script(): void {
	?>
	<script>
	jQuery( function( $ ) {
		var frame;
		$( '#tool-icon-upload' ).on( 'click', function( e ) {
			e.preventDefault();
			if ( frame ) { frame.open(); return; }
			frame = wp.media( {
				title: '<?php echo esc_js( __( 'Select Tool Icon', 'tufte-blocks' ) ); ?>',
				button: { text: '<?php echo esc_js( __( 'Use as Icon', 'tufte-blocks' ) ); ?>' },
				multiple: false,
			} );
			frame.on( 'select', function() {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				$( '#tool-icon-id' ).val( attachment.id );
				$( '#tool-icon-preview' ).html( '<img src="' + attachment.url + '" style="max-width:48px;max-height:48px;">' );
				$( '#tool-icon-remove' ).show();
			} );
			frame.open();
		} );
		$( '#tool-icon-remove' ).on( 'click', function( e ) {
			e.preventDefault();
			$( '#tool-icon-id' ).val( '' );
			$( '#tool-icon-preview' ).html( '' );
			$( this ).hide();
		} );
	} );
	</script>
	<?php
}

/**
 * Icon markup for a project_tool term.
 *
 * An uploaded icon wins. Otherwise fall back to the bundled icon registered
 * under the term's slug, if there is one.
 *
 * @since 1.6.0
 * @param WP_Term $term Tool term.
 * @return string Icon markup, or an empty string when the term has none.
 */
function tufte_blocks_get_tool_icon_markup( WP_Term $term ): string {
	$icon_id  = (int) get_term_meta( $term->term_id, 'tool_icon', true );
	$icon_url = $icon_id ? wp_get_attachment_url( $icon_id ) : '';

	if ( $icon_url ) {
		return sprintf(
			'<img src="%s" alt="" class="tufte-tool-icon" style="width:1em;height:1em;vertical-align:-0.125em;margin-right:0.25em;">',
			esc_url( $icon_url )
		);
	}

	if ( ! function_exists( 'wp_get_icon' ) ) {
		return '';
	}

	$svg = wp_get_icon(
		'tufte-blocks/' . $term->slug,
		array(
			'size'  => null,
			'class' => 'tufte-tool-icon',
		)
	);
	if ( '' === $svg ) {
		return '';
	}

	// Same sizing as the <img> path; wp_get_icon() cannot set a style attribute.
	$processor = new WP_HTML_Tag_Processor( $svg );
	$processor->next_tag( 'svg' );
	$processor->set_attribute( 'style', 'width:1em;height:1em;vertical-align:-0.125em;margin-right:0.25em;' );

	return $processor->get_updated_html();
}

/**
 * Prepend tool icons to project_tool term links on the frontend.
 *
 * @since 1.3.0
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Block data including attributes.
 * @return string Modified block HTML.
 */
function tufte_blocks_render_tool_icons( string $block_content, array $block ): string {
	if ( 'core/post-terms' !== $block['blockName'] ) {
		return $block_content;
	}

	if ( ( $block['attrs']['term'] ?? '' ) !== 'project_tool' ) {
		return $block_content;
	}

	$post = get_post();
	if ( ! $post || 'project' !== $post->post_type ) {
		return $block_content;
	}

	$terms = get_the_terms( $post->ID, 'project_tool' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return $block_content;
	}

	foreach ( $terms as $term ) {
		$icon = tufte_blocks_get_tool_icon_markup( $term );
		if ( '' === $icon ) {
			continue;
		}

		$block_content = str_replace(
			'>' . esc_html( $term->name ) . '</a>',
			'>' . $icon . esc_html( $term->name ) . '</a>',
			$block_content
		);
	}

	return $block_content;
}
add_filter( 'render_block', 'tufte_blocks_render_tool_icons', 10, 2 );
