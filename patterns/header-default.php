<?php
/**
 * Title: Header — Default
 * Slug: tufte-blocks/header-default
 * Categories: header
 * Block Types: core/template-part/header
 * Description: Minimal header with site title and navigation. Clean and functional.
 * Keywords: header, navigation, site title
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"tagName":"header","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"default"}} -->
<header class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
  <!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left","verticalAlignment":"center"}} -->
  <div class="wp-block-group">
    <!-- wp:site-title {"level":0} /-->
    <!-- wp:navigation {"overlayBackgroundColor":"background","overlayTextColor":"foreground","style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"flex","justifyContent":"left","verticalAlignment":"center"}} /-->
  </div>
  <!-- /wp:group -->
</header>
<!-- /wp:group -->
