<?php
/**
 * The file holds the main plugin function.
 *
 * @link       	https://webberzone.com
 * @since      	1.0.0
 *
 * @package    	WZKB
 * @subpackage 	WZKB/main
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * The main function to generate the output.
 *
 * @since	1.0.0
 *
 * @param	array	$args		Parameters array
 * @return	$output	Formatted shortcode output
 */
function wzkb_knowledge2( $args = array() ) {

	$defaults = array(
		'category' => FALSE,		// Create a knowledgebase for subcategories of this parent ID
	);

	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	// Set parent category if defined
	$parent = ( 0 < $args['category'] ) ? ( $args['category'] ) : 0;

	$output = '';

	$output .= '<div class="wzkb">';

	// Get Knowledge Base Sections
	$kb_master_sections = get_terms( 'wzkb_category', array(
	    'orderby'    => 'name',
	    'hide_empty' => 0,
		'parent' => $parent,
	) );

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

					$section_children = get_terms( 'wzkb_category', array(
					    'orderby'    => 'name',
					    'hide_empty' => 0,
						'child_of' => $section->term_id,
					) );
					if ( ! empty( $section_children ) && ! is_wp_error( $section_children ) ) {
						$section_children = wp_list_pluck( $section_children, 'term_id' );
					} else {
						$section_children = array();
					}


					$output .= '<div class="wzkb_section">';

					// Display Section Name
					$output .= '<h4 class="wzkb-section-name">' . $section->name . '</h4>';

					// Fetch posts in the section
					$kb_args = array(
						'post_type' => 'wz_knowledgebase',
						'posts_per_page'=> 5,
						'tax_query' => array(
							'relation' => 'AND',
							array(
								'taxonomy' => 'wzkb_category',
								'terms'    => $section,
								'include_children' => false,
							),
							array(
								'taxonomy' => 'wzkb_category',
								'field'    => 'term_id',
								'terms'    => $section_children,
								'operator' => 'NOT IN',
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

	return apply_filters( 'wzkb_knowledge', $output, $args );
}


/**
 * The main function to generate the output.
 *
 * @since	1.0.0
 *
 * @param	array	$args	Parameters array
 * @return	$output	Formatted shortcode output
 */
function wzkb_knowledge( $args = array() ) {

	$defaults = array(
		'category' => FALSE,		// Create a knowledgebase for subcategories of this parent ID
	);

	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	// Set parent category if defined
	$parent = ( 0 < $args['category'] ) ? ( $args['category'] ) : 0;

	$output = '';

	$output .= '<div class="wzkb">';

	// Get Knowledge Base Sections
	$kb_master_sections = get_terms( 'wzkb_category', array(
	    'orderby'    => 'name',
	    'hide_empty' => 0,
		'parent' => $parent,
	) );

	if ( ! empty( $kb_master_sections ) && ! is_wp_error( $kb_master_sections ) ) {

		foreach ( $kb_master_sections as $kb_master_section ) {

			$output .= '<div class="wzkb_master_section">';

			$output .= '<h4 class="wzkb-master-section-name">
							<a href="' . get_term_link( $kb_master_section ) . '" title="' . $kb_master_section->name . '" >' . $kb_master_section->name . '</a>
						</h4>';

			$output .= wzkb_looper( $kb_master_section->term_id, 1 );

			$output .= '</div>';

		}
	}

	$output .=  '</div>';	// End wzkb_section
	$output .= '<div class="wzkb_clear"></div>';

	return apply_filters( 'wzkb_knowledge', $output, $args );

}

/**
 * Creates the knowledgebase loop.
 *
 * @since	1.0.0
 *
 * @param	int	$term_id	Term ID
 * @param	int	$level		Level of the loop
 * @return	string	Formatted output
 */
function wzkb_looper( $term_id, $level ) {

	// Get Knowledge Base Sections
	$children = get_terms( 'wzkb_category', array(
	    'orderby'    => 'name',
	    'hide_empty' => 0,
		'parent' => $term_id,
	) );

	$output = '';

	if ( ! empty( $children ) && ! is_wp_error( $children ) ) {

		$output .= '<ul class="wzkb_section_wrapper wzkb-section-wrapper-level-' . $level . '">';

		foreach ( $children as $child ) {

			$tax_query = array(
							array(
								'taxonomy' => 'wzkb_category',
								'field'    => 'term_id',
								'terms'    => $child->term_id,
								'include_children' => false,
							),
						);

			if ( 1 < $level ) {

				$immediate_children['relation'] = 'AND';

				$immediate_children = get_terms( 'wzkb_category', array(
				    'orderby'    => 'name',
				    'hide_empty' => 0,
					'child_of' => $child->term_id,
				) );

				$tax_query[] = 	array(
									'taxonomy' => 'wzkb_category',
									'field'    => 'term_id',
									'terms'    => wp_list_pluck( $immediate_children, 'term_id' ),
									'operator' => 'NOT IN',
								);

			}


			$output .= '<li class="wzkb_section wzkb-section-level-' . $level . ' kb-list-item-' . $child->term_id . '">';

				// Display Section Name
				$output .= '<h4 class="wzkb_section_name wzkb-section-name-level-' . $level . '">' . $child->name . '</h4>';

				// Fetch posts in the section
				$kb_args = array(
					'post_type' => 'wz_knowledgebase',
					'posts_per_page'=> 5,
					'tax_query' => $tax_query,
				);

				$query = new WP_Query( $kb_args );

				if ( $query->have_posts() ) :

					$output .= '<ul class="wzkb-articles-list">';

					while ( $query->have_posts() ) : $query->the_post();

						$output .=  '<li class="wzkb-article-name">';
						$output .=  '<a href="' . get_permalink( get_the_ID() ) . '" rel="bookmark" title="' . get_the_title( get_the_ID() ) . '">' . get_the_title( get_the_ID() ) . '</a>';
						$output .=  '</li>';

					endwhile;

					$output .=  '</ul>';

			$output .= wzkb_looper( $child->term_id, $level + 1 );

					$output .= '<p class="wzkb-article-footer">' . __( "Read more articles in ", 'wzkb' ) . '
									<a href="' . get_term_link( $child ) . '" title="' . $child->name . '" >' . $child->name . '</a> &raquo;
								</h4>';

					wp_reset_postdata();

				else :

					$output .= '<p>No Articles Found</p>';

				endif;

			$output .= '</li>';

		}

		$output .= '</ul>';

	}

	return $output;

}
