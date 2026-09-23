<?php
/**
 * Title: Project Install Steps
 * Slug: tufte-blocks/project-install-steps
 * Categories: tufte-projects
 * Post Types: project
 * Description: Heading and a numbered list of install steps for a project page.
 * Keywords: project, install, steps
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"tagName":"section","className":"tufte-project-install","style":{"border":{"top":{"color":"var:preset|color|border","width":"1px"}},"spacing":{"padding":{"top":"var:preset|spacing|70"},"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
<section class="wp-block-group tufte-project-install" style="border-top-color:var(--wp--preset--color--border);border-top-width:1px;padding-top:var(--wp--preset--spacing--70)">
	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">Four steps, no configuration.</h2>
	<!-- /wp:heading -->

	<!-- wp:list {"ordered":true,"className":"tufte-project-steps"} -->
	<ol class="wp-block-list tufte-project-steps">
		<!-- wp:list-item -->
		<li>Install from the Plugins screen, or upload the folder to <code>/wp-content/plugins/</code>.</li>
		<!-- /wp:list-item -->

		<!-- wp:list-item -->
		<li>Activate it.</li>
		<!-- /wp:list-item -->

		<!-- wp:list-item -->
		<li>Open the block editor and add the Scroll Indicator block.</li>
		<!-- /wp:list-item -->

		<!-- wp:list-item -->
		<li>Choose an icon style, size, colour, and optional label.</li>
		<!-- /wp:list-item -->
	</ol>
	<!-- /wp:list -->
</section>
<!-- /wp:group -->
