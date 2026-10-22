<?php
/**
 * Title: 404 content
 * Slug: tufte-blocks/404-content
 * Inserter: no
 * Description: The heading and message shown when a page isn't found.
 *
 * @package Tufte_Blocks
 */

?>
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php echo esc_html__( 'Page Not Found', 'tufte-blocks' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"secondary"} -->
<p class="has-secondary-color has-text-color"><?php echo esc_html__( 'The page you\'re looking for doesn\'t exist or has been moved.', 'tufte-blocks' ); ?> <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html__( 'Return home', 'tufte-blocks' ); ?></a>.</p>
<!-- /wp:paragraph -->
