<?php
/**
 * The template for displaying search results
 *
 * Used to display custom post type archives if no archive template is found in the theme folder.
 *
 * @link       https://webberzone.com
 * @since      1.1.0
 *
 * @package    WZKB
 */

$paged = 1;
if ( get_query_var('paged') ) {
	$paged = get_query_var('paged');
}
if ( get_query_var('page') ) {
	$paged = get_query_var('page');
}

$args = array(
	'post_type' => 'wz_knowledgebase',
	's' => get_search_query(),
	'paged' => $paged,
);

$query = new WP_Query( $args );

/* This plugin uses the Archive file of wzkb theme as an example */
get_header();

?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<header class="page-header">
			<h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'wzkb' ), get_search_query() ); ?> </h1>
		</header><!-- .page-header -->

		<?php wzkb_get_search_form(); ?>

		<?php if ( $query->have_posts() ) : ?>

			<?php while ( $query->have_posts() ) : $query->the_post(); ?>

				<header class="entry-header">
					<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
				</header><!-- .entry-header -->

				<div class="entry-summary">
					<?php the_excerpt(); ?>
				</div><!-- .entry-summary -->

			<?php endwhile; ?>

		<nav class="pagination">
			<?php
			echo paginate_links( array(
				'format' => '?paged=%#%',
				'current' => max( 1, get_query_var('paged') ),
				'total' => $query->max_num_pages
			) );
			?>
		</nav>

			<?php wp_reset_postdata();

		// If no content, include the "No posts found" template.
		else :
			_e( 'No results found', 'wzkb' );

		endif;
		?>

		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php get_footer();


