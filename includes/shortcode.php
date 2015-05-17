<?php
/**
 * Knowledgebase Shortcode.
 *
 * @link       	https://webberzone.com
 * @since      	1.0.0
 *
 * @package    	WZKB
 * @subpackage 	WZKB/shortcode
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


function wzkb_shortcode( $atts, $content = null ) {

	wp_enqueue_style( 'wpkb_styles' );
	wp_enqueue_style( 'dashicons' );

    $a = shortcode_atts( array(
		'category' => false,		// Create a knowledgebase for subcategories of this parent ID
	), $atts, 'knowledgebase' );

	$parent = ( $a['category'] ) ? ( $a['category'] ) : 0;

	// Get Knowledge Base Sections
	$kb_master_sections = get_terms( 'wzkb_category', array(
	    'orderby'    => 'name',
	    'hide_empty' => 0,
		'parent' => $parent,
	) );

	$output = '';

	$output .= '<div class="wzkb">';

	if ( ! empty( $kb_master_sections ) && ! is_wp_error( $kb_master_sections ) ) {

		foreach ( $kb_master_sections as $kb_master_section ) {

			$kb_sections = get_terms( 'wzkb_category', array(
			    'orderby'    => 'name',
			    'hide_empty' => 0,
				'child_of' => $kb_master_section->term_id,
			) );


			if ( ! empty( $kb_sections ) && ! is_wp_error( $kb_sections ) ) {

				$output .= '<div class="wzkb_master_section">';

				$output .= '<h4 class="wzkb-master-section-name">
								<a href="' . get_term_link( $kb_master_section ) . '" title="' . $kb_master_section->name . '" >' . $kb_master_section->name . '</a>
							</h4>';

				// For each knowledge base section
				foreach ( $kb_sections as $section ) {

					$output .= '<div class="wzkb_section">';

					// Display Section Name
					$output .= '<h4 class="wzkb-section-name">' . $section->name . '</h4>';

					// Fetch posts in the section
					$kb_args = array(
						'post_type' => 'wz_knowledgebase',
						'posts_per_page'=> 5,
						'tax_query' => array(
							array(
								'taxonomy' => 'wzkb_category',
								'terms'    => $section,
								'include_children' => false,
							),
						),
					);

					$query = new WP_Query( $kb_args );

					if ( $query->have_posts() ) :

						$output .= '<ul class="wzkb-articles-list">';

						while ( $query->have_posts() ) : $query->the_post();

							$output .=  '<li class="wzkb-article-name">';
							$output .=  '<a href="'. get_permalink( get_the_ID() ) .'" rel="bookmark" title="'. get_the_title( get_the_ID() ) .'">'. get_the_title( get_the_ID() ) .'</a>';
							$output .=  '</li>';

						endwhile;

						$output .=  '</ul>';

						$output .= '<p class="wzkb-article-footer">' . __( "Read more articles in ", 'wzkb' ) . '
										<a href="' . get_term_link( $section ) . '" title="' . $section->name . '" >' . $section->name . '</a> &raquo;
									</h4>';

						wp_reset_postdata();

					else :

						$output .= '<p>No Articles Found</p>';

					endif;

					$output .=  '</div>';	// End wzkb_section


				}

				$output .= '</div>';	// End wzkb_master_section

			} else {

					$output .= '<div class="wzkb_section">';

					// Display Section Name
					$output .= '<h4 class="wzkb-section-name">' . $kb_master_section->name . '</h4>';

					// Fetch posts in the section
					$kb_args = array(
						'post_type' => 'wz_knowledgebase',
						'posts_per_page'=> 5,
						'tax_query' => array(
							array(
								'taxonomy' => 'wzkb_category',
								'terms'    => $kb_master_section,
								'include_children' => false,
							),
						),
					);

					$query = new WP_Query( $kb_args );

					if ( $query->have_posts() ) :

						$output .= '<ul class="wzkb-articles-list">';

						while ( $query->have_posts() ) : $query->the_post();

							$output .=  '<li class="wzkb-article-name">';
							$output .=  '<a href="'. get_permalink( get_the_ID() ) .'" rel="bookmark" title="'. get_the_title( get_the_ID() ) .'">'. get_the_title( get_the_ID() ) .'</a>';
							$output .=  '</li>';

						endwhile;

						$output .=  '</ul>';

						$output .= '<p class="wzkb-article-footer">' . __( "Read more articles in ", 'wzkb' ) . '
										<a href="' . get_term_link( $kb_master_section ) . '" title="' . $kb_master_section->name . '" >' . $kb_master_section->name . '</a> &raquo;
									</h4>';

						wp_reset_postdata();

					else :

						$output .= '<p>No Articles Found</p>';

					endif;

					$output .=  '</div>';	// End wzkb_section

			}

		}
	}

	$output .= '<div class="wzkb_clear"></div>';
	$output .= "</div>";

	return apply_filters( 'wzkb_shortcode', $output );
}
add_shortcode( 'knowledgebase', 'wzkb_shortcode' );
