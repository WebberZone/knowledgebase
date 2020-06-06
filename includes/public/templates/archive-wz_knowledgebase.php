<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase.
/**
 * The template for displaying archive pages
 *
 * Used to display the KB archives if no archive template is found in the theme folder.
 *
 * If you'd like to further customize these archive views, you may create a
 * archive-wz_knowledgebase.php file in your theme's folder
 *
 * @link  https://webberzone.com
 * @since 1.9.0
 *
 * @package WZKB
 */

/* This plugin uses the Archive file of TwentySeventeen theme as an example */
get_header();

wp_enqueue_style( 'wzkb_styles' );

?>
<div class="wrap">
	<div id="wzkb-content-primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<?php wzkb_get_search_form(); ?>
			<?php if ( have_posts() ) : ?>

				<header class="page-header">
					<h1 class="page-title"><?php echo wzkb_get_option( 'kb_title' );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
				</header><!-- .page-header -->

				<?php
				wzkb_breadcrumb();

				echo wzkb_knowledge(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				// If no content, include the "No posts found" template.
			else :
				esc_html_e( 'No results found', 'wzkb' );

			endif;
			?>
		</main><!-- .site-main -->
	</div><!-- .content-area -->

	<?php
	if ( wzkb_get_option( 'show_sidebar' ) ) {
		include_once 'sidebar-primary.php';
	}
	?>
</div><!-- .wrap -->

<?php
get_footer();


