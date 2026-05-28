<?php
/**
 * Title: Newsletter Callout
 * Slug: tufte-blocks/newsletter-callout
 * Categories: tufte-cta
 * Description: § ornament, heading, short description, and monospace caption. Drop into the left column of a newsletter section beside an existing subscribe form.
 * Keywords: newsletter, callout, reading notes, section symbol, ornament
 */
?>
<!-- wp:paragraph {"style":{"typography":{"fontSize":"3rem","lineHeight":"1"}},"textColor":"primary"} -->
<p class="has-primary-color has-text-color" style="font-size:3rem;line-height:1">§</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.2rem","fontWeight":"400"},"spacing":{"margin":{"bottom":"0.5rem"}}}} -->
<h3 class="wp-block-heading" style="margin-bottom:0.5rem;font-size:1.2rem;font-weight:400"><?php echo esc_html__( 'Reading Notes, Dispatched Weekly', 'tufte-blocks' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.9rem"}},"textColor":"secondary"} -->
<p class="has-secondary-color has-text-color" style="font-size:0.9rem"><?php echo esc_html__( 'Curated links, writing-in-progress, and occasional commentary. One email a week. No noise.', 'tufte-blocks' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"typography":{"fontFamily":"var:preset|font-family|monospace","fontSize":"0.68rem"}},"textColor":"secondary"} -->
<p class="has-secondary-color has-text-color" style="font-family:var(--wp--preset--font-family--monospace);font-size:0.68rem"><?php echo esc_html__( 'No spam. Unsubscribe anytime.', 'tufte-blocks' ); ?></p>
<!-- /wp:paragraph -->
