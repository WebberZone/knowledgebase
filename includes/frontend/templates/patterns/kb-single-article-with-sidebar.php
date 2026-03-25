<?php
/**
 * Title: Knowledge Base Single Article with Sidebar
 * Slug: knowledgebase/kb-single-article-with-sidebar
 * Categories: knowledgebase
 * Description: Single knowledge base article layout with sidebar navigation.
 *
 * @package WebberZone\Knowledge_Base
 */

?>
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
	<!-- wp:column {"width":"66.66%"} -->
	<div class="wp-block-column" style="flex-basis:66.66%">
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
	</div>
	<!-- /wp:column -->

	<!-- wp:column {"width":"33.33%"} -->
	<div class="wp-block-column" style="flex-basis:33.33%">
		<!-- wp:pattern {"slug":"knowledgebase/kb-sidebar"} /-->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->
