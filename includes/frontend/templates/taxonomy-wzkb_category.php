<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase.
/**
 * The template for displaying taxonomy archives
 *
 * Used to display KB section archives if no archive template is found in the theme folder.
 *
 * If you'd like to further customize the single views, you may create a
 * taxonomy-wzkb_category.php file in your theme's folder
 *
 * @package WebberZone\Knowledge_Base
 */

global $wp_query;
$wzkb_current_taxonomy = $wp_query->get_queried_object();

/* This plugin uses the Archive file of TwentyFifteen theme as an example */
get_header();

// Hide the first level header when displaying the category archives.
$wzkb_inline_css = '
	.wzkb-section-name-level-1 {
		display: none;
	}
';
wp_add_inline_style( 'wz-knowledgebase-styles', $wzkb_inline_css );


?>
<a href="#main" class="skip-link screen-reader-text"><?php esc_html_e( 'Skip to content', 'knowledgebase' ); ?></a>
<div class="wrap wzkb-wrap">
	<div id="wzkb-content-primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<?php wzkb_breadcrumb(); ?>
			<?php wzkb_search_form(); ?>

			<header class="page-header">
				<h1 class="page-title"><?php echo esc_html( $wzkb_current_taxonomy->name ); ?></h1>
				<?php if ( ! empty( $wzkb_current_taxonomy->description ) ) : ?>
					<div class="taxonomy-description"><?php echo wp_kses_post( $wzkb_current_taxonomy->description ); ?></div>
				<?php endif; ?>
			</header><!-- .page-header -->

			<?php if ( have_posts() ) : ?>

				<?php
				// Display knowledge base content for this category.
				$wzkb_knowledge_args = array(
					'category'    => $wzkb_current_taxonomy->term_id,
					'extra_class' => 'wzkb-category-archive',
				);
				echo wzkb_knowledge( $wzkb_knowledge_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				// If no content, include the "No posts found" template.
			else :
				get_template_part( 'content', 'none' );

			endif;
			?>
		</main><!-- .site-main -->
	</div><!-- .content-area -->

	<?php
	if ( wzkb_get_option( 'show_sidebar' ) ) {
		include_once 'sidebar-primary.php';
	}
	?>
</div><!-- .wrap.wzkb-wrap -->

<?php
get_footer();
