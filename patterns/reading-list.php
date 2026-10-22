<?php
/**
 * Title: Reading List / Bibliography
 * Slug: tufte-blocks/reading-list
 * Categories: tufte-content, text
 * Description: Curated book and article recommendations with personal annotations. Perfect for course reading lists or "what I'm reading" pages.
 * Keywords: reading, bibliography, books, recommendations, list
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"className":"tufte-reading-list","layout":{"type":"constrained","contentSize":"700px"}} -->
<div class="wp-block-group tufte-reading-list">
	<!-- wp:group {"className":"tufte-reading-item","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"1.5rem"}}} -->
	<div class="wp-block-group tufte-reading-item">
		<!-- wp:paragraph {"className":"tufte-book-number"} -->
		<p class="tufte-book-number">01</p>
		<!-- /wp:paragraph -->

		<!-- wp:group {"className":"tufte-book-info","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","orientation":"vertical"}} -->
		<div class="wp-block-group tufte-book-info">
			<!-- wp:paragraph {"className":"tufte-book-title"} -->
			<p class="tufte-book-title"><a href="#"><?php echo esc_html__( 'The Visual Display of Quantitative Information', 'tufte-blocks' ); ?></a></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"tufte-book-author"} -->
			<p class="tufte-book-author"><?php echo esc_html__( 'Edward Tufte, 2001', 'tufte-blocks' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"tufte-book-note"} -->
			<p class="tufte-book-note"><?php echo esc_html__( 'The foundational text. If you read one book on information design, make it this one. Chapter 4 on data-ink is worth the price alone.', 'tufte-blocks' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"tufte-reading-item","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"1.5rem"}}} -->
	<div class="wp-block-group tufte-reading-item">
		<!-- wp:paragraph {"className":"tufte-book-number"} -->
		<p class="tufte-book-number">02</p>
		<!-- /wp:paragraph -->

		<!-- wp:group {"className":"tufte-book-info","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","orientation":"vertical"}} -->
		<div class="wp-block-group tufte-book-info">
			<!-- wp:paragraph {"className":"tufte-book-title"} -->
			<p class="tufte-book-title"><a href="#"><?php echo esc_html__( 'The Elements of Typographic Style', 'tufte-blocks' ); ?></a></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"tufte-book-author"} -->
			<p class="tufte-book-author"><?php echo esc_html__( 'Robert Bringhurst, 2004', 'tufte-blocks' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"tufte-book-note"} -->
			<p class="tufte-book-note"><?php echo esc_html__( 'The poet\'s guide to typography. Bringhurst treats type as a living art form with roots in calligraphy, history, and ecology.', 'tufte-blocks' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"tufte-reading-item","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"1.5rem"}}} -->
	<div class="wp-block-group tufte-reading-item">
		<!-- wp:paragraph {"className":"tufte-book-number"} -->
		<p class="tufte-book-number">03</p>
		<!-- /wp:paragraph -->

		<!-- wp:group {"className":"tufte-book-info","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","orientation":"vertical"}} -->
		<div class="wp-block-group tufte-book-info">
			<!-- wp:paragraph {"className":"tufte-book-title"} -->
			<p class="tufte-book-title"><a href="#"><?php echo esc_html__( 'Thinking with Type', 'tufte-blocks' ); ?></a></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"tufte-book-author"} -->
			<p class="tufte-book-author"><?php echo esc_html__( 'Ellen Lupton, 2010', 'tufte-blocks' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"tufte-book-note"} -->
			<p class="tufte-book-note"><?php echo esc_html__( 'More practical than Bringhurst, more contemporary in its examples. Excellent for students starting out in design.', 'tufte-blocks' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
