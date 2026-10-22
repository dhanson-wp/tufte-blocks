<?php
/**
 * Title: Syllabus / Course Listing
 * Slug: tufte-blocks/syllabus
 * Categories: tufte-content, tufte-academic
 * Description: Structured course schedule with week numbers, topics, and completion status. For faculty sharing syllabi or project roadmaps.
 * Keywords: syllabus, course, schedule, academic, weeks, roadmap
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"className":"tufte-syllabus","layout":{"type":"constrained","contentSize":"700px"}} -->
<div class="wp-block-group tufte-syllabus">
	<!-- wp:group {"className":"tufte-syllabus-item","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"1.25rem"}}} -->
	<div class="wp-block-group tufte-syllabus-item">
		<!-- wp:paragraph {"className":"tufte-week-num"} -->
		<p class="tufte-week-num"><?php echo esc_html__( 'W01', 'tufte-blocks' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:group {"className":"tufte-syllabus-content-row","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"1.25rem"}}} -->
		<div class="wp-block-group tufte-syllabus-content-row">
			<!-- wp:group {"className":"tufte-week-content","layout":{"type":"flex","orientation":"vertical"},"style":{"spacing":{"blockGap":"0"}}} -->
			<div class="wp-block-group tufte-week-content">
				<!-- wp:paragraph {"className":"tufte-week-title"} -->
				<p class="tufte-week-title"><?php echo esc_html__( 'Introduction: What Is Information Design?', 'tufte-blocks' ); ?></p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"tufte-week-detail"} -->
				<p class="tufte-week-detail"><?php echo esc_html__( 'Read: Tufte, Chapters 1–3. Lupton, pp. 1–28.', 'tufte-blocks' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:paragraph {"className":"tufte-week-status is-complete"} -->
			<p class="tufte-week-status is-complete"><?php echo esc_html__( 'Complete', 'tufte-blocks' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"tufte-syllabus-item","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"1.25rem"}}} -->
	<div class="wp-block-group tufte-syllabus-item">
		<!-- wp:paragraph {"className":"tufte-week-num"} -->
		<p class="tufte-week-num"><?php echo esc_html__( 'W02', 'tufte-blocks' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:group {"className":"tufte-syllabus-content-row","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"1.25rem"}}} -->
		<div class="wp-block-group tufte-syllabus-content-row">
			<!-- wp:group {"className":"tufte-week-content","layout":{"type":"flex","orientation":"vertical"},"style":{"spacing":{"blockGap":"0"}}} -->
			<div class="wp-block-group tufte-week-content">
				<!-- wp:paragraph {"className":"tufte-week-title"} -->
				<p class="tufte-week-title"><?php echo esc_html__( 'Typography as Interface', 'tufte-blocks' ); ?></p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"tufte-week-detail"} -->
				<p class="tufte-week-detail"><?php echo esc_html__( 'Read: Bringhurst, Chapters 1–4. Assignment 1 due.', 'tufte-blocks' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:paragraph {"className":"tufte-week-status is-complete"} -->
			<p class="tufte-week-status is-complete"><?php echo esc_html__( 'Complete', 'tufte-blocks' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"tufte-syllabus-item","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"1.25rem"}}} -->
	<div class="wp-block-group tufte-syllabus-item">
		<!-- wp:paragraph {"className":"tufte-week-num"} -->
		<p class="tufte-week-num"><?php echo esc_html__( 'W03', 'tufte-blocks' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:group {"className":"tufte-syllabus-content-row","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"1.25rem"}}} -->
		<div class="wp-block-group tufte-syllabus-content-row">
			<!-- wp:group {"className":"tufte-week-content","layout":{"type":"flex","orientation":"vertical"},"style":{"spacing":{"blockGap":"0"}}} -->
			<div class="wp-block-group tufte-week-content">
				<!-- wp:paragraph {"className":"tufte-week-title"} -->
				<p class="tufte-week-title"><?php echo esc_html__( 'The Data-Ink Ratio', 'tufte-blocks' ); ?></p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"tufte-week-detail"} -->
				<p class="tufte-week-detail"><?php echo esc_html__( 'Read: Tufte, Chapters 4–6. In-class workshop.', 'tufte-blocks' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:paragraph {"className":"tufte-week-status"} -->
			<p class="tufte-week-status"><?php echo esc_html__( 'Upcoming', 'tufte-blocks' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"tufte-syllabus-item","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"1.25rem"}}} -->
	<div class="wp-block-group tufte-syllabus-item">
		<!-- wp:paragraph {"className":"tufte-week-num"} -->
		<p class="tufte-week-num"><?php echo esc_html__( 'W04', 'tufte-blocks' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:group {"className":"tufte-syllabus-content-row","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"1.25rem"}}} -->
		<div class="wp-block-group tufte-syllabus-content-row">
			<!-- wp:group {"className":"tufte-week-content","layout":{"type":"flex","orientation":"vertical"},"style":{"spacing":{"blockGap":"0"}}} -->
			<div class="wp-block-group tufte-week-content">
				<!-- wp:paragraph {"className":"tufte-week-title"} -->
				<p class="tufte-week-title"><?php echo esc_html__( 'Small Multiples &amp; Narrative Sequence', 'tufte-blocks' ); ?></p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"tufte-week-detail"} -->
				<p class="tufte-week-detail"><?php echo esc_html__( 'Read: Tufte, Chapter 8. Assignment 2 due.', 'tufte-blocks' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:paragraph {"className":"tufte-week-status"} -->
			<p class="tufte-week-status"><?php echo esc_html__( 'Upcoming', 'tufte-blocks' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"tufte-syllabus-item","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"1.25rem"}}} -->
	<div class="wp-block-group tufte-syllabus-item">
		<!-- wp:paragraph {"className":"tufte-week-num"} -->
		<p class="tufte-week-num"><?php echo esc_html__( 'W05', 'tufte-blocks' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:group {"className":"tufte-syllabus-content-row","layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"1.25rem"}}} -->
		<div class="wp-block-group tufte-syllabus-content-row">
			<!-- wp:group {"className":"tufte-week-content","layout":{"type":"flex","orientation":"vertical"},"style":{"spacing":{"blockGap":"0"}}} -->
			<div class="wp-block-group tufte-week-content">
				<!-- wp:paragraph {"className":"tufte-week-title"} -->
				<p class="tufte-week-title"><?php echo esc_html__( 'Color, Contrast, and Accessibility', 'tufte-blocks' ); ?></p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"tufte-week-detail"} -->
				<p class="tufte-week-detail"><?php echo esc_html__( 'Read: WCAG guidelines. Guest lecture: Maria Chen.', 'tufte-blocks' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:paragraph {"className":"tufte-week-status"} -->
			<p class="tufte-week-status"><?php echo esc_html__( 'Upcoming', 'tufte-blocks' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
