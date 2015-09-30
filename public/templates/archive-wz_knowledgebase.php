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

// Hide the first level header when displaying archives
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
					post_type_archive_title();
				?>
			</h1></header><!-- .page-header -->

			<?php
				wzkb_get_search_form();

				echo do_shortcode( '[knowledgebase]' );

			// If no content, include the "No posts found" template.
		else :
			_e( 'No results found', 'wzkb' );

		endif;
		?>

		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php get_footer();


