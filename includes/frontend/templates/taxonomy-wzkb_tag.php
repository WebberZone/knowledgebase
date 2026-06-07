<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase.
/**
 * The template for displaying tag archives
 *
 * Used to display KB tag archives if no archive template is found in the theme folder.
 *
 * If you'd like to further customize these archive views, you may create a
 * taxonomy-wzkb_tag.php file in your theme's folder
 *
 * @package WebberZone\Knowledge_Base
 */

global $wp_query;
$wzkb_current_taxonomy = $wp_query->get_queried_object();

get_header();

if ( wzkb_get_option( 'include_styles' ) ) {
	wp_enqueue_style( 'wz-knowledgebase-styles' );
}
?>
<a href="#main" class="skip-link screen-reader-text"><?php esc_html_e( 'Skip to content', 'knowledgebase' ); ?></a>
<div class="wrap wzkb-wrap">
	<div id="wzkb-content-primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<?php wzkb_breadcrumb(); ?>
			<?php wzkb_search_form(); ?>

			<?php if ( $wzkb_current_taxonomy ) : ?>

				<header class="page-header">
					<h1 class="page-title"><?php echo esc_html( $wzkb_current_taxonomy->name ); ?></h1>
					<?php if ( ! empty( $wzkb_current_taxonomy->description ) ) : ?>
						<div class="taxonomy-description"><?php echo wp_kses_post( $wzkb_current_taxonomy->description ); ?></div>
					<?php endif; ?>
					<?php
					$wzkb_term_header_image = apply_filters( 'wzkb_term_archive_header_image', '', $wzkb_current_taxonomy );
					if ( $wzkb_term_header_image ) :
						?>
						<?php echo $wzkb_term_header_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
				</header><!-- .page-header -->

				<?php if ( have_posts() ) : ?>

					<div class="wzkb-search-results-grid">

					<?php
					while ( have_posts() ) :
						the_post();
						$wzkb_thumb = wzkb_get_the_post_thumbnail(
							array(
								'post'          => get_post(),
								'size'          => 'medium',
								'class'         => 'wzkb-search-result-thumb',
								'thumb_default' => '',
							)
						);
						?>

						<article class="wzkb-search-result-card">
							<?php if ( $wzkb_thumb ) : ?>
							<a href="<?php the_permalink(); ?>" class="wzkb-search-result-thumb-link">
								<?php echo $wzkb_thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
							<?php endif; ?>
							<?php the_title( sprintf( '<h2 class="wzkb-search-result-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
							<div class="wzkb-article-excerpt post-<?php the_ID(); ?>">
								<?php echo wp_kses_post( get_the_excerpt() ); ?>
							</div>
						</article>

					<?php endwhile; ?>

					</div><!-- .wzkb-search-results-grid -->

					<nav class="pagination">
						<?php
						echo paginate_links( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'format'  => '?paged=%#%',
								'current' => max( 1, get_query_var( 'paged' ) ),
								'total'   => $wp_query->max_num_pages,
							)
						);
						?>
					</nav>

				<?php else : ?>
					<?php get_template_part( 'content', 'none' ); ?>
				<?php endif; ?>

			<?php else : ?>
				<?php esc_html_e( 'No tag found', 'knowledgebase' ); ?>
			<?php endif; ?>
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
