<?php
/**
 * Archive template.
 *
 * @link       https://webberzone.com
 * @since      1.0.0
 *
 * @package    WZKB
 */

$terms = get_terms( 'wzkb_category', array(
    'orderby'    => 'name',
    'hide_empty' => 0,
	'parent' => 0,
) );



get_header();

?>

	<section id="primary" class="content-area">
		<div id="content" class="wpkb_page">

            <header class="archive-header">
                <h1 class="archive-title">
                    <?php post_type_archive_title(); ?>
                </h1>
            </header><!-- .archive-header -->

			<?php
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {

				foreach( $terms as $term ) {

				    // Define the query
				    $args = array(
				        'post_type' => 'wz_knowledgebase',
				        'category' => $term->slug,
				        'posts_per_page' => 5,
				    );
				    $query = new WP_Query( $args );

				    // output the term name in a heading tag
				    echo'<h2>' . $term->name . '</h2>';

				    // output the post titles in a list
				    echo '<ul>';

				        if ( $query->have_posts() ) :
					        // Start the Loop
					        while ( $query->have_posts() ) : $query->the_post(); ?>

					        <li class="animal-listing" id="post-<?php the_ID(); ?>">
					            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					        </li>

					        <?php endwhile;

						    echo '</ul>';

							echo '<p>More from: <a href="' . esc_url( get_term_link( $term ) ) . '">' . $term->name . '</a></p>';

						    // use reset postdata to restore orginal query
						    wp_reset_postdata();

						endif;

				}

			}
			?>

		</div><!-- #content -->
	</section><!-- #primary -->

<?php get_footer(); ?>