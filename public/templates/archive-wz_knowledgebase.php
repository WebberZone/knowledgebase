<?php
/**
 * The template for displaying archive pages
 *
 * Used to display custom post type archives if no archive template is found in the theme folder.
 *
 * @link       https://webberzone.com
 * @since      1.1.0
 *
 * @package    WZKB
 */


/* This plugin uses the Archive file of TwentyFifteen theme as an example */
get_header();

wp_enqueue_style( 'wzkb_archive_styles' );
?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : ?>

			<header class="page-header"><h1 class="page-title">
				<?php
					post_type_archive_title();
				?>
			</h1></header><!-- .page-header -->

			<?php
				wzkb_get_search_form();

				echo do_shortcode( '[knowledgebase]' );

		// If no content, include the "No posts found" template.
		else :
			get_template_part( 'content', 'none' );

		endif;
		?>

		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php get_footer();


