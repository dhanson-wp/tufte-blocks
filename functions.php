<?php
/**
 * Tufte Blocks Theme Functions
 *
 * @package Tufte_Blocks
 * @since 1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define theme version for cache busting.
 */
define( 'TUFTE_BLOCKS_VERSION', '1.4.4' );

/**
 * Register custom block styles.
 *
 * @since 1.0.0
 * @return void
 */
function tufte_blocks_register_block_styles(): void {
	// Quote: Epigraph style (right-aligned with gold border).
	register_block_style(
		'core/quote',
		array(
			'name'  => 'epigraph',
			'label' => __( 'Epigraph', 'tufte-blocks' ),
		)
	);

	// Quote: Large style (wide width, larger text).
	register_block_style(
		'core/quote',
		array(
			'name'  => 'large',
			'label' => __( 'Large', 'tufte-blocks' ),
		)
	);

	// Button: Link style (text link with arrow).
	register_block_style(
		'core/button',
		array(
			'name'  => 'link',
			'label' => __( 'Link', 'tufte-blocks' ),
		)
	);

	// Separator: Ornament style (section symbol).
	register_block_style(
		'core/separator',
		array(
			'name'  => 'ornament',
			'label' => __( 'Ornament', 'tufte-blocks' ),
		)
	);

	// Post Featured Image: Tufte Hero style (margin figure).
	register_block_style(
		'core/post-featured-image',
		array(
			'name'  => 'tufte-hero',
			'label' => __( 'Tufte Hero', 'tufte-blocks' ),
		)
	);
}
add_action( 'init', 'tufte_blocks_register_block_styles' );

/**
 * Register the Projects custom post type.
 *
 * @since 1.3.0
 * @return void
 */
function tufte_blocks_register_project_post_type(): void {
	register_post_type(
		'project',
		array(
			'labels'        => array(
				'name'                  => __( 'Projects', 'tufte-blocks' ),
				'singular_name'         => __( 'Project', 'tufte-blocks' ),
				'add_new_item'          => __( 'Add New Project', 'tufte-blocks' ),
				'edit_item'             => __( 'Edit Project', 'tufte-blocks' ),
				'new_item'              => __( 'New Project', 'tufte-blocks' ),
				'view_item'             => __( 'View Project', 'tufte-blocks' ),
				'view_items'            => __( 'View Projects', 'tufte-blocks' ),
				'search_items'          => __( 'Search Projects', 'tufte-blocks' ),
				'not_found'             => __( 'No projects found.', 'tufte-blocks' ),
				'not_found_in_trash'    => __( 'No projects found in Trash.', 'tufte-blocks' ),
				'all_items'             => __( 'All Projects', 'tufte-blocks' ),
				'archives'              => __( 'Project Archives', 'tufte-blocks' ),
				'attributes'            => __( 'Project Attributes', 'tufte-blocks' ),
				'item_published'        => __( 'Project published.', 'tufte-blocks' ),
				'item_updated'          => __( 'Project updated.', 'tufte-blocks' ),
			),
			'public'        => true,
			'has_archive'   => 'projects',
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-portfolio',
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions', 'page-attributes' ),
			'rewrite'       => array(
				'slug'       => 'projects',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'tufte_blocks_register_project_post_type' );

/**
 * Register project taxonomies.
 *
 * @since 1.3.0
 * @return void
 */
function tufte_blocks_register_project_taxonomies(): void {
	register_taxonomy(
		'project_type',
		'project',
		array(
			'labels'       => array(
				'name'          => __( 'Project Types', 'tufte-blocks' ),
				'singular_name' => __( 'Project Type', 'tufte-blocks' ),
				'add_new_item'  => __( 'Add New Project Type', 'tufte-blocks' ),
				'edit_item'     => __( 'Edit Project Type', 'tufte-blocks' ),
				'search_items'  => __( 'Search Project Types', 'tufte-blocks' ),
				'all_items'     => __( 'All Project Types', 'tufte-blocks' ),
			),
			'public'       => true,
			'hierarchical' => false,
			'show_in_rest' => true,
			'rewrite'      => array(
				'slug'       => 'project-type',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'project_tool',
		'project',
		array(
			'labels'       => array(
				'name'          => __( 'Tools', 'tufte-blocks' ),
				'singular_name' => __( 'Tool', 'tufte-blocks' ),
				'add_new_item'  => __( 'Add New Tool', 'tufte-blocks' ),
				'edit_item'     => __( 'Edit Tool', 'tufte-blocks' ),
				'search_items'  => __( 'Search Tools', 'tufte-blocks' ),
				'all_items'     => __( 'All Tools', 'tufte-blocks' ),
			),
			'public'       => true,
			'hierarchical' => false,
			'show_in_rest' => true,
			'rewrite'      => array(
				'slug'       => 'project-tool',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'tufte_blocks_register_project_taxonomies' );

/**
 * Register project meta fields for Block Bindings API.
 *
 * @since 1.3.0
 * @return void
 */
function tufte_blocks_register_project_meta(): void {
	$meta_args = array(
		'show_in_rest'  => true,
		'single'        => true,
		'type'          => 'string',
		'default'       => '',
		'auth_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
	);

	register_post_meta( 'project', 'project_github_url', $meta_args );
	register_post_meta( 'project', 'project_demo_url', $meta_args );
}
add_action( 'init', 'tufte_blocks_register_project_meta' );

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
		$icon_id = (int) get_term_meta( $term->term_id, 'tool_icon', true );
		if ( ! $icon_id ) {
			continue;
		}

		$icon_url = wp_get_attachment_url( $icon_id );
		if ( ! $icon_url ) {
			continue;
		}

		$img = sprintf(
			'<img src="%s" alt="" class="tufte-tool-icon" style="width:1em;height:1em;vertical-align:-0.125em;margin-right:0.25em;">',
			esc_url( $icon_url )
		);

		$block_content = str_replace(
			'>' . esc_html( $term->name ) . '</a>',
			'>' . $img . esc_html( $term->name ) . '</a>',
			$block_content
		);
	}

	return $block_content;
}
add_filter( 'render_block', 'tufte_blocks_render_tool_icons', 10, 2 );

/**
 * Flush rewrite rules once after CPT registration.
 *
 * @since 1.3.0
 * @return void
 */
add_action( 'init', function (): void {
	if ( get_option( 'tufte_blocks_projects_rewrite_flushed', false ) ) {
		return;
	}
	flush_rewrite_rules();
	update_option( 'tufte_blocks_projects_rewrite_flushed', true );
}, 99 );

/**
 * Shortcode: current year (for footer copyright).
 *
 * @since 1.0.0
 * @return string Current 4-digit year.
 */
function tufte_current_year_shortcode(): string {
	return (string) gmdate( 'Y' );
}
add_shortcode( 'current_year', 'tufte_current_year_shortcode' );

/**
 * Enqueue theme stylesheets for frontend and editor.
 *
 * @since 1.0.0
 * @return void
 */
function tufte_blocks_enqueue_styles(): void {
	$theme_path = get_template_directory_uri() . '/assets/css/';
	$version    = TUFTE_BLOCKS_VERSION;

	// Navigation styles.
	wp_enqueue_style(
		'tufte-blocks-navigation',
		$theme_path . 'navigation.css',
		array(),
		$version
	);

	// Button styles.
	wp_enqueue_style(
		'tufte-blocks-buttons',
		$theme_path . 'buttons.css',
		array(),
		$version
	);

	// Link styles.
	wp_enqueue_style(
		'tufte-blocks-links',
		$theme_path . 'links.css',
		array(),
		$version
	);

	// Component styles (quotes, details, etc.).
	wp_enqueue_style(
		'tufte-blocks-components',
		$theme_path . 'components.css',
		array(),
		$version
	);

	// Pattern styles (epigraph, etc.).
	wp_enqueue_style(
		'tufte-blocks-patterns',
		$theme_path . 'patterns.css',
		array(),
		$version
	);

	// Tufte layout (left margin, responsive padding).
	wp_enqueue_style(
		'tufte-layout',
		$theme_path . 'layout.css',
		array(),
		$version
	);

	// Comment thread styles.
	wp_enqueue_style(
		'tufte-blocks-comments',
		$theme_path . 'comments.css',
		array(),
		$version
	);
}
add_action( 'enqueue_block_assets', 'tufte_blocks_enqueue_styles' );

/**
 * Add editor-specific styles.
 *
 * Ensures proper color visibility in the block editor.
 *
 * @since 1.0.0
 * @return void
 */
function tufte_blocks_editor_styles(): void {
	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'tufte_blocks_editor_styles' );

/**
 * Remove core and remote block patterns.
 *
 * Disables WordPress core block patterns and remote patterns from WordPress.org
 * so only theme-registered patterns appear in the inserter.
 *
 * @since 1.0.0
 * @return void
 */
function tufte_blocks_disable_core_and_remote_patterns(): void {
	remove_theme_support( 'core-block-patterns' );
}
add_action( 'after_setup_theme', 'tufte_blocks_disable_core_and_remote_patterns' );

add_filter( 'should_load_remote_block_patterns', '__return_false' );

/**
 * TEMPORARY: Reset page template database override.
 *
 * The page template was saved to the database with incorrect post-content
 * attributes. This deletes the DB record so WordPress falls back to the theme
 * file (templates/page.html).
 *
 * Load any page once, then REMOVE this block immediately.
 */
add_action( 'init', function (): void {
	$query = new WP_Query(
		array(
			'post_type'      => 'wp_template',
			'post_name__in'  => array( 'page' ),
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'tax_query'      => array(
				array(
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => get_stylesheet(),
				),
			),
		)
	);
	$posts = $query->posts;
	if ( ! empty( $posts ) ) {
		wp_delete_post( (int) $posts[0], true );
	}
} );

/**
 * Register pattern categories.
 *
 * @since 1.0.0
 * @return void
 */
function tufte_blocks_register_pattern_categories(): void {
	register_block_pattern_category(
		'tufte-query',
		array(
			'label'       => __( 'Query Loops', 'tufte-blocks' ),
			'description' => __( 'Post listing patterns with various layouts.', 'tufte-blocks' ),
		)
	);

	register_block_pattern_category(
		'tufte-cta',
		array(
			'label'       => __( 'Calls to Action', 'tufte-blocks' ),
			'description' => __( 'Newsletter signups, link in bio, and CTAs.', 'tufte-blocks' ),
		)
	);

	register_block_pattern_category(
		'tufte-content',
		array(
			'label'       => __( 'Content', 'tufte-blocks' ),
			'description' => __( 'Content display patterns like FAQs and testimonials.', 'tufte-blocks' ),
		)
	);

	register_block_pattern_category(
		'tufte-academic',
		array(
			'label'       => __( 'Academic', 'tufte-blocks' ),
			'description' => __( 'Publications, CV, syllabus, and research patterns.', 'tufte-blocks' ),
		)
	);

	register_block_pattern_category(
		'tufte-layout',
		array(
			'label'       => __( 'Layout', 'tufte-blocks' ),
			'description' => __( 'Structural patterns like heroes and section breaks.', 'tufte-blocks' ),
		)
	);

	register_block_pattern_category(
		'tufte-projects',
		array(
			'label'       => __( 'Projects', 'tufte-blocks' ),
			'description' => __( 'Project showcase cards and layouts.', 'tufte-blocks' ),
		)
	);
}
add_action( 'init', 'tufte_blocks_register_pattern_categories' );

/**
 * One-time migration: clear duplicated Tufte layout CSS from Additional CSS.
 *
 * After moving layout rules to assets/css/layout.css, clear them from
 * Appearance → Editor → Styles → Additional CSS to avoid duplication.
 * Only runs when Tufte layout rules are present. Remove this block after
 * the migration has run once.
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'init', function (): void {
	if ( get_option( 'tufte_blocks_layout_css_migrated', false ) ) {
		return;
	}

	$css = wp_get_custom_css( get_stylesheet() );
	if ( empty( trim( $css ) ) ) {
		update_option( 'tufte_blocks_layout_css_migrated', true );
		return;
	}

	// Clear Additional CSS if it contains the Tufte layout block (avoid duplication).
	if ( str_contains( $css, 'TUFTE LEFT MARGIN' ) ) {
		wp_update_custom_css_post( '', array( 'stylesheet' => get_stylesheet() ) );
	}

	update_option( 'tufte_blocks_layout_css_migrated', true );
} );
