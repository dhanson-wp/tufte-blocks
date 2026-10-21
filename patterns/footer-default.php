<?php
/**
 * Title: Footer, default
 * Slug: tufte-blocks/footer-default
 * Categories: footer
 * Block Types: core/template-part/footer
 * Description: A quiet, functional footer with site title, tagline, an RSS link, the year, and a theme credit link.
 * Keywords: footer, site, links, copyright
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"tagName":"footer","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}},"border":{"top":{"color":"var:preset|color|border","width":"1px"}}},"layout":{"type":"default"}} -->
<footer class="wp-block-group" style="border-top-color:var(--wp--preset--color--border);border-top-width:1px;padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left","verticalAlignment":"top"}} -->
	<div class="wp-block-group">
		<!-- wp:group {"layout":{"type":"default"}} -->
		<div class="wp-block-group">
			<!-- wp:site-title {"level":0,"style":{"typography":{"fontSize":"1rem"}}} /-->
			<!-- wp:site-tagline {"textColor":"secondary","fontSize":"small"} /-->
		</div>
		<!-- /wp:group -->
		<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left"}} -->
		<div class="wp-block-group">
			<!-- wp:paragraph {"style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.05em"}},"textColor":"secondary","fontSize":"small","fontFamily":"monospace"} -->
			<p class="has-secondary-color has-text-color has-monospace-font-family has-small-font-size" style="letter-spacing:0.05em;text-transform:uppercase"><a href="<?php echo esc_url( get_feed_link() ); ?>"><?php echo esc_html__( 'RSS', 'tufte-blocks' ); ?></a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
	<!-- wp:group {"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left"}} -->
	<div class="wp-block-group" style="margin-top:var(--wp--preset--spacing--50)">
		<!-- wp:paragraph {"textColor":"secondary","fontSize":"small","fontFamily":"monospace"} -->
		<p class="has-secondary-color has-text-color has-monospace-font-family has-small-font-size">© <?php echo esc_html( gmdate( 'Y' ) ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph {"textColor":"secondary","fontSize":"small","fontFamily":"monospace"} -->
		<p class="has-secondary-color has-text-color has-monospace-font-family has-small-font-size"><a href="https://github.com/dhanson-wp/tufte-blocks"><?php echo esc_html__( 'Tufte Blocks theme', 'tufte-blocks' ); ?></a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</footer>
<!-- /wp:group -->
