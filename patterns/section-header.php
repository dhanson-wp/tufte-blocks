<?php
/**
 * Title: Section Header
 * Slug: tufte-blocks/section-header
 * Categories: text, featured
 * Description: An eyebrow label, heading, and description paragraph for introducing content sections.
 * Keywords: section, header, heading, eyebrow, label, intro
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"className":"tufte-section-header","layout":{"type":"constrained"}} -->
<div class="wp-block-group tufte-section-header">
	<!-- wp:paragraph {"className":"tufte-eyebrow","textColor":"primary"} -->
	<p class="tufte-eyebrow has-primary-color has-text-color">Research Areas</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">What We Study</h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"tufte-section-desc","textColor":"secondary"} -->
	<p class="tufte-section-desc has-secondary-color has-text-color">Our work spans data visualization, document design, and the cognitive effects of typography on comprehension and recall.</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
