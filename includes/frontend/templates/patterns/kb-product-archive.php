<?php
/**
 * Title: Knowledge Base Product Archive
 * Slug: knowledgebase/kb-product-archive
 * Categories: knowledgebase
 * Description: Knowledge Base product archive layout with search and articles.
 *
 * @package WebberZone\Knowledge_Base
 */

?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
	<!-- wp:knowledgebase/search /-->

	<!-- wp:spacer {"height":"var:preset|spacing|30"} -->
	<div style="height:var(--wp--preset--spacing--30)" aria-hidden="true" class="wp-block-spacer"></div>
	<!-- /wp:spacer -->

	<!-- wp:knowledgebase/knowledgebase {"product":"-1"} /-->
</div>
<!-- /wp:group -->
