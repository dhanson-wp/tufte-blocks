<?php
/**
 * Title: Footer — Default
 * Slug: tufte-blocks/footer-default
 * Categories: footer
 * Block Types: core/template-part/footer
 * Description: A quiet, functional footer with site title, tagline, navigation links, and copyright.
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
			<!-- wp:paragraph {"textColor":"secondary","fontSize":"small"} -->
			<p class="has-secondary-color has-text-color has-small-font-size">A typography-first theme inspired by Edward Tufte.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
		<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left"}} -->
		<div class="wp-block-group">
			<!-- wp:paragraph {"style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.05em"}},"textColor":"secondary","fontSize":"small","fontFamily":"monospace"} -->
			<p class="has-secondary-color has-text-color has-monospace-font-family has-small-font-size" style="letter-spacing:0.05em;text-transform:uppercase"><a href="#">About</a></p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.05em"}},"textColor":"secondary","fontSize":"small","fontFamily":"monospace"} -->
			<p class="has-secondary-color has-text-color has-monospace-font-family has-small-font-size" style="letter-spacing:0.05em;text-transform:uppercase"><a href="#">Contact</a></p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.05em"}},"textColor":"secondary","fontSize":"small","fontFamily":"monospace"} -->
			<p class="has-secondary-color has-text-color has-monospace-font-family has-small-font-size" style="letter-spacing:0.05em;text-transform:uppercase"><a href="#">RSS</a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
	<!-- wp:group {"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}},"layout":{"type":"default"}} -->
	<div class="wp-block-group" style="margin-top:var(--wp--preset--spacing--50)">
		<!-- wp:paragraph {"align":"left","textColor":"secondary","fontSize":"small","fontFamily":"monospace"} -->
		<p class="has-text-align-left has-secondary-color has-text-color has-monospace-font-family has-small-font-size">© [current_year]</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</footer>
<!-- /wp:group -->
