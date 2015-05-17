<?php
/**
 * Knowledgebase Shortcode.
 *
 * @link       https://webberzone.com
 * @since      1.0.0
 *
 * @package    WZKB
 */


function wzkb_knowledgebase() {

	wp_enqueue_style( 'wpkb_styles' );
	wp_enqueue_style( 'dashicons' );


	// Get Knowledge Base Sections
	$kb_sections = get_terms( 'wzkb_category', array(
	    'orderby'    => 'count',
	    'hide_empty' => 0,
//		'parent' => 0,
	) );

	$output = '';

	$output .= '<div class="wzkb">';

	if ( ! empty( $kb_sections ) && ! is_wp_error( $kb_sections ) ) {


		// For each knowledge base section
		foreach ( $kb_sections as $section ) {

			$output .= '<div class="wzkb_section">';

			// Display Section Name
//			$output .= '<h4 class="wzkb-section-name"><a href="' . get_term_link( $section ) . '" title="' . $section->name . '" ><span class="dashicons dashicons-category"></span>' . $section->name . '</a></h4>';
			$output .= '<h4 class="wzkb-section-name">' . $section->name . '</h4>';

			$output .= '<ul class="wzkb-articles-list">';

			// Fetch posts in the section
			$kb_args = array(
				'post_type' => 'wz_knowledgebase',
				'posts_per_page'=> 5,
				'tax_query' => array(
					array(
						'taxonomy' => 'wzkb_category',
						'terms'    => $section,
					),
				),
			);

			$query = new WP_Query( $kb_args );

			if ( $query->have_posts() ) :

				while ( $query->have_posts() ) : $query->the_post();

					$output .=  '<li class="wzkb-article-name">';
					$output .=  '<a href="'. get_permalink( get_the_ID() ) .'" rel="bookmark" title="'. get_the_title( get_the_ID() ) .'">'. get_the_title( get_the_ID() ) .'</a>';
					$output .=  '</li>';

				endwhile;

				wp_reset_postdata();

			else :

				$output .= '<p>No Articles Found</p>';

			endif;

			$output .=  '</ul></div>';


		}

	}

	$output .= '<div class="wzkb_clear"></div>';
	$output .= "</div>";

	return apply_filters( 'wzkb_knowledgebase', $output );
}
// Create shortcode
add_shortcode( 'knowledgebase', 'wzkb_knowledgebase' );
