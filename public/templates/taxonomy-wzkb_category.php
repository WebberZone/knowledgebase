<?php
/**
 * The template for displaying taxonomy archives
 *
 * Used to display custom taxonomy archives if no archive template is found in the theme folder.
 *
 * @link       https://webberzone.com
 * @since      1.1.0
 *
 * @package    WZKB
 */


global $wp_query;
$term = $wp_query->get_queried_object();

/* This plugin uses the Archive file of TwentyFifteen theme as an example */
get_header();

// Hide the first level header when displaying the category archives
$custom_css = '
    .wzkb-section-name-level-1 {
        display: none;
    }
';
wp_add_inline_style( 'wzkb_styles', $custom_css );


?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : ?>

			<header class="page-header"><h1 class="page-title">
				<?php
					echo $term->name;
				?>
			</h1></header><!-- .page-header -->

			<?php
				wzkb_get_search_form();

				echo do_shortcode( "[knowledgebase category='{$term->term_id}']" );

			// If no content, include the "No posts found" template.
		else :
			get_template_part( 'content', 'none' );

		endif;
		?>

		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php get_footer();


