<?php
/**
 * The template for displaying search results
 *
 * Used to display the KB search results page if no archive template is found in the theme folder.
 *
 * If you'd like to further customize these archive views, you may create a
 * wzkb-search.php (Search results page) in your theme's folder
 *
 * @link  https://webberzone.com
 * @since 1.1.0
 *
 * @package WZKB
 */

$this_page = 1;
if ( get_query_var( 'paged' ) ) {
	$this_page = get_query_var( 'paged' );
}
if ( get_query_var( 'page' ) ) {
	$this_page = get_query_var( 'page' );
}

$args = array(
	'post_type' => 'wz_knowledgebase',
	's'         => get_search_query(),
	'paged'     => $this_page,
);

$query = new WP_Query( $args );

/* This plugin uses the Archive file of TwentyFifteen theme as an example */
get_header();

if ( wzkb_get_option( 'include_styles' ) ) {
	wp_enqueue_style( 'wzkb_styles' );
}
?>
<div class="wrap">
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

			<?php wzkb_get_search_form(); ?>

			<?php if ( $query->have_posts() ) : ?>

				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					?>

					<header class="entry-header">
						<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
					</header><!-- .entry-header -->

					<div class="entry-summary">
						<?php the_excerpt(); ?>
					</div><!-- .entry-summary -->
				<?php endwhile; ?>

				<nav class="pagination">
					<?php
						echo paginate_links( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'format'  => '?paged=%#%',
								'current' => max( 1, get_query_var( 'paged' ) ),
								'total'   => $query->max_num_pages,
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
</div><!-- .wrap -->

<?php
get_footer();


