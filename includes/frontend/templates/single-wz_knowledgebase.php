<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase.
/**
 * The template for displaying single articles
 *
 * Used to display KB articles if no single template is found in the theme folder.
 *
 * If you'd like to further customize the single views, you may create a
 * single-wz_knowledgebase.php file in your theme's folder
 *
 * @package WebberZone\Knowledge_Base
 */

/* This plugin uses the Archive file of TwentySeventeen theme as an example */
get_header();

if ( wzkb_get_option( 'include_styles' ) ) {
	wp_enqueue_style( 'wz-knowledgebase-styles' );
}
?>
<div class="wrap">
	<div id="wzkb-content-primary" class="content-area">
		<main id="main" class="site-main" role="main">
		<?php wzkb_search_form(); ?>
		<?php
		if ( have_posts() ) :
			wzkb_breadcrumb();

			while ( have_posts() ) :
				the_post();
				?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="page-header">
					<?php the_title( '<h1 class="page-title">', '</h1>' ); ?>
				</header>

				<div class="page-content">
					<?php
					the_content(
						sprintf(
							wp_kses(
								/* translators: %s: Name of current post. Only visible to screen readers */
								__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'knowledgebase' ),
								array(
									'span' => array(
										'class' => array(),
									),
								)
							),
							get_the_title()
						)
					);
					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . __( 'Pages:', 'knowledgebase' ),
							'after'  => '</div>',
						)
					);

					if ( wzkb_get_option( 'show_related_articles' ) ) {
						/**
						 * Filters the arguments array before being sent to wzkb_related_articles().
						 *
						 * @since 2.1.0
						 *
						 * @param array $args Arguments array.
						 * @param int   $id   Post ID.
						 */
						$related_articles_args = apply_filters( 'wzkb_related_articles_args', array(), get_the_ID() );
						wzkb_related_articles( $related_articles_args );
					}
					?>
				</div><!-- .page-content -->
				<div class="page-meta">

				<?php
				// Edit post link.
				edit_post_link(
					sprintf(
						wp_kses(
							/* translators: %s: Name of current post. Only visible to screen readers. */
							__( 'Edit <span class="screen-reader-text">%s</span>', 'knowledgebase' ),
							array(
								'span' => array(
									'class' => array(),
								),
							)
						),
						get_the_title()
					),
					'<span class="edit-link">',
					'</span>'
				);
				?>

				</div><!-- .meta-info -->
			</article><!-- #post-${ID} -->

				<?php

				// Previous/next post navigation.
				the_post_navigation(
					array(
						'next_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Next Post', 'knowledgebase' ) . '</span> ' .
							'<span class="screen-reader-text">' . __( 'Next post:', 'knowledgebase' ) . '</span> <br/>' .
							'<span class="post-title">%title</span>',
						'prev_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Previous Post', 'knowledgebase' ) . '</span> ' .
							'<span class="screen-reader-text">' . __( 'Previous post:', 'knowledgebase' ) . '</span> <br/>' .
							'<span class="post-title">%title</span>',
					)
				);

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}

			endwhile;
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
