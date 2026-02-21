<?php
/**
 * Title: Testimonial / Epigraph
 * Slug: tufte-blocks/testimonial-epigraph
 * Categories: text, featured
 * Description: A centered blockquote for opening essays, endorsements, or memorable passages.
 * Keywords: quote, epigraph, testimonial, blockquote
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"className":"tufte-epigraph","style":{"spacing":{"padding":{"top":"2rem","bottom":"2rem"}}},"layout":{"type":"constrained","contentSize":"520px"}} -->
<div class="wp-block-group tufte-epigraph" style="padding-top:2rem;padding-bottom:2rem">
	<!-- wp:paragraph {"className":"tufte-epigraph-quote-mark","align":"center","textColor":"primary"} -->
	<p class="tufte-epigraph-quote-mark has-text-align-center has-primary-color has-text-color">"</p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"className":"tufte-epigraph-quote","align":"center"} -->
	<p class="tufte-epigraph-quote has-text-align-center">The minimum we should hope for with any display technology is that it should do no harm to the content.</p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"className":"tufte-epigraph-attribution","align":"center","textColor":"secondary"} -->
	<p class="tufte-epigraph-attribution has-text-align-center has-secondary-color has-text-color">— <strong>Edward Tufte</strong>, The Cognitive Style of PowerPoint</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
