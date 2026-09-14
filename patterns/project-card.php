<?php
/**
 * Title: Project Card
 * Slug: tufte-blocks/project-card
 * Categories: tufte-projects
 * Description: A project showcase card with icon, title, type pill, tools, and description.
 * Keywords: project, card, portfolio, showcase
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"className":"tufte-project-card","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}},"border":{"width":"1px","color":"var:preset|color|border","radius":"4px"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
<div class="wp-block-group tufte-project-card" style="border-color:var(--wp--preset--color--border);border-width:1px;border-radius:4px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">

	<!-- wp:image {"width":"48px","height":"48px","className":"tufte-project-icon","style":{"border":{"radius":"4px"}}} -->
	<figure class="wp-block-image is-resized tufte-project-icon" style="border-radius:4px"><img alt="" style="width:48px;height:48px"/></figure>
	<!-- /wp:image -->

	<!-- wp:group {"className":"tufte-project-info","style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"flex","orientation":"vertical"}} -->
	<div class="wp-block-group tufte-project-info">

		<!-- wp:group {"className":"tufte-project-title-row","style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
		<div class="wp-block-group tufte-project-title-row">
			<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"baseline"},"style":{"spacing":{"blockGap":"var:preset|spacing|30"}}} -->
			<div class="wp-block-group">
				<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.1rem","fontWeight":"400"}}} -->
				<h3 class="wp-block-heading" style="font-size:1.1rem;font-weight:400">Project Name</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"tufte-project-pills tufte-eyebrow","textColor":"primary","style":{"typography":{"fontSize":"0.7rem"}}} -->
				<p class="tufte-project-pills tufte-eyebrow has-primary-color has-text-color" style="font-size:0.7rem">Plugin</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:paragraph {"textColor":"secondary","style":{"typography":{"fontSize":"0.82rem"}}} -->
			<p class="has-secondary-color has-text-color" style="font-size:0.82rem">Claude · Cursor</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:paragraph {"textColor":"secondary","style":{"typography":{"fontSize":"0.92rem"}}} -->
		<p class="has-secondary-color has-text-color" style="font-size:0.92rem">A brief description of the project and what it does.</p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
