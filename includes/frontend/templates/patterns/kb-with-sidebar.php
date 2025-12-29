<?php
/**
 * Title: Knowledge Base with Sidebar
 * Slug: knowledgebase/kb-with-sidebar
 * Categories: knowledgebase
 * Description: Two-column layout with Knowledge Base content and sidebar navigation.
 *
 * @package WebberZone\Knowledge_Base
 */

?>
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
	<!-- wp:column {"width":"66.66%"} -->
	<div class="wp-block-column" style="flex-basis:66.66%">
		<!-- wp:knowledgebase/search /-->

		<!-- wp:knowledgebase/breadcrumb /-->

		<!-- wp:knowledgebase/knowledgebase /-->
	</div>
	<!-- /wp:column -->

	<!-- wp:column {"width":"33.33%"} -->
	<div class="wp-block-column" style="flex-basis:33.33%">
		<!-- wp:pattern {"slug":"knowledgebase/kb-sidebar"} /-->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->
