<?php
/**
 * Title: Knowledge Base Single Article
 * Slug: knowledgebase/kb-single-article
 * Categories: knowledgebase
 * Description: Single knowledge base article layout with breadcrumb and related articles.
 *
 * @package WebberZone\Knowledge_Base
 */

?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
	<!-- wp:knowledgebase/breadcrumb /-->

	<!-- wp:spacer {"height":"var:preset|spacing|30"} -->
	<div style="height:var(--wp--preset--spacing--30)" aria-hidden="true" class="wp-block-spacer"></div>
	<!-- /wp:spacer -->

	<!-- wp:post-title {"level":1} /-->

	<!-- wp:post-content {"layout":{"type":"constrained"}} /-->

	<!-- wp:spacer {"height":"var:preset|spacing|40"} -->
	<div style="height:var(--wp--preset--spacing--40)" aria-hidden="true" class="wp-block-spacer"></div>
	<!-- /wp:spacer -->

	<!-- wp:knowledgebase/related {"title":"Related Articles"} /-->
</div>
<!-- /wp:group -->
