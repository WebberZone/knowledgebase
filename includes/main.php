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

			$output .= '<h3 class="wzkb-master-section-name">
							<a href="' . get_term_link( $kb_master_section ) . '" title="' . $kb_master_section->name . '" >' . $kb_master_section->name . '</a>
						</h3>';

			$output .= wzkb_looper( $kb_master_section, 1 );

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
 * @param	int		$term_id	Term ID
 * @param	int		$level		Level of the loop
 * @param	bool	$processed	Flag to indicate current term is processed
 * @return	string	Formatted output
 */
function wzkb_looper( $term, $level, $processed = false ) {

	// Get Knowledge Base Sections
	$children = get_terms( 'wzkb_category', array(
	    'orderby'    => 'name',
	    'hide_empty' => 0,
		'parent' => $term->term_id,
	) );

	$loop = 0;

	$output = '';

	if ( ! empty( $children ) && ! is_wp_error( $children ) ) {

		$output .= '<ul class="wzkb_section_wrapper wzkb-section-wrapper-level-' . $level . '">';

		foreach ( $children as $child ) {

				$immediate_children = get_terms( 'wzkb_category', array(
				    'orderby'    => 'name',
				    'hide_empty' => 0,
					'child_of' => $child->term_id,
				) );

				$tax_query = array(
								'relation' => 'AND',
								array(
									'taxonomy' => 'wzkb_category',
									'field'    => 'term_id',
									'terms'    => $child->term_id,
									'include_children' => false,
								),
								array(
									'taxonomy' => 'wzkb_category',
									'field'    => 'term_id',
									'terms'    => wp_list_pluck( $immediate_children, 'term_id' ),
									'operator' => 'NOT IN',
								),
							);

			$output .= '<li class="wzkb_section wzkb-section-level-' . $level . ' kb-list-item-' . $child->term_id . '">';

				// Fetch posts in the section
				$kb_args = array(
					'post_type' => 'wz_knowledgebase',
					'posts_per_page'=> 5,
					'tax_query' => $tax_query,
				);

				$query = new WP_Query( $kb_args );

				$count = count( $query );

				if ( $query->have_posts() ) :

					// Display Section Name
					$output .= '<h4 class="wzkb_section_name wzkb-section-name-level-' . $level . '">
									<a href="' . get_term_link( $child ) . '" title="' . $child->name . '" >' . $child->name . '</a>
								</h4>';

					$output .= '<ul class="wzkb-articles-list">';

					while ( $query->have_posts() ) : $query->the_post();

						$output .=  '<li class="wzkb-article-name">';
						$output .=  '	<a href="' . get_permalink( get_the_ID() ) . '" rel="bookmark" title="' . get_the_title( get_the_ID() ) . '">' . get_the_title( get_the_ID() ) . '</a>';
						$output .=  '</li>';

					endwhile;

					$output .=  '</ul>';

					$output .= wzkb_looper( $child, $level + 1, true );

					if ( ( $level < 2 ) && ( $query->found_posts > 5 ) ) {

						$output .= '<p class="wzkb-article-footer">' . __( "Read more articles in ", 'wzkb' ) . '
										<a href="' . get_term_link( $child ) . '" title="' . $child->name . '" >' . $child->name . '</a> &raquo;
									</p>';

					}

					wp_reset_postdata();

				else :

//					$output .= '<p>No Articles Found</p>';

				endif;

			$output .= '</li>';

		}

		$output .= '</ul>';

	} elseif ( empty( $children ) ) {

		if ( ! $processed ) {

			$tax_query = array(
							array(
								'taxonomy' => 'wzkb_category',
								'field'    => 'term_id',
								'terms'    => $term->term_id,
								'include_children' => false,
							),
						);

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

				$output .= '<p class="wzkb-article-footer">' . __( "Read more articles in ", 'wzkb' ) . '
								<a href="' . get_term_link( $term ) . '" title="' . $term->name . '" >' . $term->name . '</a> &raquo;
							</p>';

				wp_reset_postdata();

			else :

				$output .= '<p>No Articles Found</p>';

			endif;

		}

	}

	return $output;

}
