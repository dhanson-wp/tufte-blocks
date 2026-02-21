<?php
/**
 * Title: About Card
 * Slug: tufte-blocks/about-card
 * Categories: tufte-content, featured
 * Description: A compact author/contact card with photo, bio, and links. For about pages, author bios on posts, or team listings.
 * Keywords: about, author, bio, contact, card, profile
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"className":"tufte-about-card","layout":{"type":"constrained","contentSize":"700px"}} -->
<div class="wp-block-group tufte-about-card">
	<!-- wp:group {"className":"tufte-about-row","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"2.5rem"}}} -->
	<div class="wp-block-group tufte-about-row">
		<!-- wp:image {"sizeSlug":"full","className":"tufte-about-photo","width":200,"style":{"dimensions":{"aspectRatio":"3/4"},"color":{"duotone":"var:preset|duotone|tufte-portrait"}},"linkDestination":"none"} -->
		<figure class="wp-block-image size-full is-resized tufte-about-photo"><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/placeholder-portrait.jpg" alt="" width="200"/></figure>
		<!-- /wp:image -->

		<!-- wp:group {"className":"tufte-about-info","layout":{"type":"flex","orientation":"vertical"},"style":{"spacing":{"blockGap":"0"}}} -->
		<div class="wp-block-group tufte-about-info">
			<!-- wp:heading {"level":3,"className":"tufte-about-name"} -->
			<h3 class="wp-block-heading tufte-about-name">Dr. Eleanor Thornfield</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"tufte-about-role"} -->
			<p class="tufte-about-role">Professor of Information Design, Yale University</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"tufte-about-bio"} -->
			<p class="tufte-about-bio">Eleanor studies how the design of information shapes understanding. Her work spans data visualization, document design, and the cognitive effects of typography. She is the author of <em>Clear Thinking Made Visible</em> (2023) and lives in New Haven with two cats and more books than shelf space.</p>
			<!-- /wp:paragraph -->

			<!-- wp:group {"className":"tufte-about-links","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal"},"style":{"spacing":{"blockGap":"1.25rem"}}} -->
			<div class="wp-block-group tufte-about-links">
				<!-- wp:paragraph -->
				<p><a href="#">Email</a></p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph -->
				<p><a href="#">ORCID</a></p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph -->
				<p><a href="#">Scholar</a></p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph -->
				<p><a href="#">CV (PDF)</a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
