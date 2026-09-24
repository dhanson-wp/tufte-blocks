<?php
/**
 * Title: Project Features
 * Slug: tufte-blocks/project-features
 * Categories: tufte-projects
 * Post Types: project
 * Description: Eyebrow, heading, and a grid of six feature cards for a project page.
 * Keywords: project, features, grid, cards
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"tagName":"section","align":"wide","className":"tufte-project-features","style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
<section class="wp-block-group alignwide tufte-project-features">
	<!-- wp:heading {"level":2,"className":"tufte-project-features-title","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|60"}}}} -->
	<h2 class="wp-block-heading tufte-project-features-title" style="margin-bottom:var(--wp--preset--spacing--60)">Editor controls you already know.</h2>
	<!-- /wp:heading -->

	<!-- wp:group {"className":"tufte-project-feature-grid","style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","minimumColumnWidth":"260px"}} -->
	<div class="wp-block-group tufte-project-feature-grid">
		<?php
		$tufte_features = array(
			array( 'Native controls', 'Text color, spacing, typography, and alignment come from core block supports, not a parallel settings panel.' ),
			array( 'Positioning', 'Fixed to the screen, or dragged into place with absolute positioning for hero-style sections.' ),
			array( 'CSS-only motion', 'Animation is CSS and respects <code>prefers-reduced-motion</code>. No animation libraries.' ),
			array( 'Click to scroll', 'A real button element. Click or keyboard-activate it to move down the page; it hides itself once scrolling starts.' ),
			array( 'Five icon styles', 'Mouse, arrow, chevron, dots, and hand, at preset sizes or any custom CSS size value.' ),
			array( 'Works without JS', 'The icon and label still render with JavaScript off. Script only powers click-to-scroll and hide-on-scroll.' ),
		);
		foreach ( $tufte_features as $tufte_feature ) :
			?>
		<!-- wp:group {"className":"tufte-project-feature","style":{"border":{"width":"1px","color":"var:preset|color|border","radius":"4px"},"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
		<div class="wp-block-group tufte-project-feature has-border-color" style="border-color:var(--wp--preset--color--border);border-width:1px;border-radius:4px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">
			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading"><?php echo esc_html( $tufte_feature[0] ); ?></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"fontSize":"small","textColor":"secondary"} -->
			<p class="has-secondary-color has-text-color has-small-font-size"><?php echo wp_kses( $tufte_feature[1], array( 'code' => array() ) ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
		<?php endforeach; ?>
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
