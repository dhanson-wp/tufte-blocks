<?php
/**
 * Title: Project FAQ
 * Slug: tufte-blocks/project-faq
 * Categories: tufte-projects
 * Post Types: project
 * Description: Heading and four expandable questions for a project page.
 * Keywords: project, faq, questions, details
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"tagName":"section","className":"tufte-project-faq","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
<section class="wp-block-group tufte-project-faq">
	<!-- wp:heading {"level":2,"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
	<h2 class="wp-block-heading" style="margin-bottom:var(--wp--preset--spacing--50)">Before you ask.</h2>
	<!-- /wp:heading -->

	<!-- wp:details -->
	<details class="wp-block-details"><summary>Does it need a JavaScript animation library?</summary>
	<!-- wp:paragraph -->
	<p>No. Animations are CSS. The front-end script only powers click-to-scroll and automatic hide-on-scroll.</p>
	<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details -->
	<details class="wp-block-details"><summary>Can I change the icon color?</summary>
	<!-- wp:paragraph -->
	<p>Yes. The block uses core text color support, so color comes from the normal block controls and your theme palette.</p>
	<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details -->
	<details class="wp-block-details"><summary>Does it respect reduced-motion preferences?</summary>
	<!-- wp:paragraph -->
	<p>Yes. Animated effects only run when the visitor has not requested reduced motion.</p>
	<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details -->
	<details class="wp-block-details"><summary>What happens with JavaScript off?</summary>
	<!-- wp:paragraph -->
	<p>The icon and optional text still render. Only click-to-scroll and hide-on-scroll require JavaScript.</p>
	<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->
</section>
<!-- /wp:group -->
