<?php
/**
 * The template for displaying search results
 *
 * Used to display the KB search results page if no archive template is found in the theme folder.
 *
 * If you'd like to further customize these archive views, you may create a
 * wzkb-search.php (Search results page) in your theme's folder
 *
 * @package WebberZone\Knowledge_Base
 */

$wzkb_current_page = 1;
if ( get_query_var( 'paged' ) ) {
	$wzkb_current_page = get_query_var( 'paged' );
}
if ( get_query_var( 'page' ) ) {
	$wzkb_current_page = get_query_var( 'page' );
}

$wzkb_query_args = array(
	'post_type' => 'wz_knowledgebase',
	's'         => get_search_query(),
	'paged'     => $wzkb_current_page,
);

$wzkb_query = new WP_Query( $wzkb_query_args );

/* This plugin uses the Archive file of TwentyFifteen theme as an example */
get_header();

if ( wzkb_get_option( 'include_styles' ) ) {
	wp_enqueue_style( 'wz-knowledgebase-styles' );
}
?>
<div class="wrap wzkb-wrap">
	<div id="wzkb-content-primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<header class="page-header">
				<h1 class="page-title">
					<?php
						/* translators: 1: Search term. */
						printf( esc_html__( 'Search Results for: %s', 'knowledgebase' ), get_search_query() );
					?>
				</h1>
			</header><!-- .page-header -->

			<?php wzkb_breadcrumb(); ?>
			<?php wzkb_search_form(); ?>

			<?php if ( $wzkb_query->have_posts() ) : ?>

				<div class="wzkb-search-results-grid">

				<?php
				while ( $wzkb_query->have_posts() ) :
					$wzkb_query->the_post();
					$wzkb_thumb = wzkb_get_the_post_thumbnail(
						array(
							'post'  => get_post(),
							'size'  => 'medium',
							'class' => 'wzkb-search-result-thumb',
						)
					);
					?>

					<article class="wzkb-search-result-card">
						<?php if ( $wzkb_thumb ) : ?>
						<a href="<?php the_permalink(); ?>" class="wzkb-search-result-thumb-link">
							<?php echo $wzkb_thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
						<?php endif; ?>
						<div class="wzkb-search-result-body">
							<?php the_title( sprintf( '<h2 class="wzkb-search-result-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
							<div class="wzkb-article-excerpt post-<?php the_ID(); ?>">
							<?php echo wp_kses_post( get_the_excerpt() ); ?>
						</div>
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
								'total'   => $wzkb_query->max_num_pages,
							)
						);
					?>
				</nav>

				<?php
				wp_reset_postdata();

				// If no content, include the "No posts found" template.
			else :
				esc_html_e( 'No results found', 'knowledgebase' );

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


