<?php
/**
 * Title: Newsletter Sign-up
 * Slug: tufte-blocks/newsletter-cta
 * Categories: call-to-action
 * Description: A restrained call-to-action for newsletter subscriptions. Centered, clear, no gimmicks.
 * Keywords: newsletter, subscribe, email, signup, cta
 * Block Types: core/group
 * Viewport Width: 1200
 */
?>
<!-- wp:group {"className":"tufte-newsletter-cta","style":{"border":{"width":"1px","color":"var:preset|color|border","radius":"4px"},"spacing":{"padding":{"top":"3rem","right":"3rem","bottom":"3rem","left":"3rem"},"blockGap":"0"}},"layout":{"type":"constrained","contentSize":"600px"}} -->
<div class="wp-block-group tufte-newsletter-cta has-border-color" style="border-color:var(--wp--preset--color--border);border-width:1px;border-radius:4px;padding-top:3rem;padding-right:3rem;padding-bottom:3rem;padding-left:3rem">

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"0.7rem","letterSpacing":"0.1em","textTransform":"uppercase","fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"bottom":"1rem"}}},"fontFamily":"monospace","textColor":"primary"} -->
<p class="has-text-align-center has-primary-color has-text-color has-monospace-font-family" style="margin-bottom:1rem;font-size:0.7rem;font-style:normal;font-weight:400;letter-spacing:0.1em;text-transform:uppercase">Newsletter</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontWeight":"400","fontSize":"1.5rem"},"spacing":{"margin":{"bottom":"0.75rem"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:0.75rem;font-size:1.5rem;font-weight:400">Marginalia</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"0.95rem"},"spacing":{"margin":{"bottom":"1.75rem"}}},"textColor":"secondary"} -->
<p class="has-text-align-center has-secondary-color has-text-color" style="margin-bottom:1.75rem;font-size:0.95rem">Occasional notes on typography, evidence, and the design of information. Published when there's something worth saying.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"bottom":"1rem"}}}} -->
<div class="wp-block-buttons" style="margin-bottom:1rem">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Subscribe</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"0.78rem"}},"textColor":"secondary"} -->
<p class="has-text-align-center has-secondary-color has-text-color" style="font-size:0.78rem">No spam. Unsubscribe anytime. Roughly once a month.</p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
