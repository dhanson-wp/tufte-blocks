<?php
/**
 * Title: Project Card
 * Slug: tufte-blocks/project-card
 * Categories: tufte-projects
 * Block Types: core/post-template
 * Description: A project card with a full-bleed banner, type eyebrow, title, excerpt, and version line. Matches the projects archive template.
 * Keywords: project, card, portfolio, showcase
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"tagName":"article","className":"tufte-project-card","style":{"border":{"width":"1px","color":"var:preset|color|border","radius":"4px"},"spacing":{"blockGap":"0"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
<article class="wp-block-group tufte-project-card" style="border-color:var(--wp--preset--color--border);border-width:1px;border-radius:4px">

	<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"1544/500","scale":"cover","className":"tufte-project-banner"} /-->

	<!-- wp:group {"className":"tufte-project-body","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
	<div class="wp-block-group tufte-project-body" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">

		<!-- wp:post-terms {"term":"project_type","separator":" · ","className":"tufte-eyebrow tufte-project-type","textColor":"primary"} /-->

		<!-- wp:post-title {"level":2,"isLink":true,"fontSize":"x-large","className":"tufte-project-title"} /-->

		<!-- wp:post-excerpt {"moreText":"","showMoreOnNewLine":false,"excerptLength":30,"textColor":"secondary","fontSize":"small","className":"tufte-project-excerpt"} /-->

		<!-- wp:group {"className":"tufte-project-meta","style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
		<div class="wp-block-group tufte-project-meta">
			<!-- wp:paragraph {"className":"tufte-project-version","metadata":{"bindings":{"content":{"source":"tufte-blocks/project-field","args":{"key":"version"}}}}} -->
			<p class="tufte-project-version">1.0.0</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"tufte-project-date","metadata":{"bindings":{"content":{"source":"tufte-blocks/project-field","args":{"key":"release_date"}}}}} -->
			<p class="tufte-project-date">Jan 2026</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

</article>
<!-- /wp:group -->
