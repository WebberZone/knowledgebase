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
 * @param	array $args   Parameters array
 * @return	string	Formatted output
 */
function wzkb_knowledge( $args = array() ) {

	$defaults = array(
		'category' => false, // Create a knowledgebase for subcategories of this parent ID
	);

	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	$output = '<div class="wzkb">';

	// Are we trying to display a category?
	$level = ( 0 < $args['category'] ) ? 1 : 0;
	$termID = ( 0 < $args['category'] ) ? $args['category'] : 0;

	$output .= wzkb_looper( $termID, $level );

	$output .= '</div>'; // End wzkb_section
	$output .= '<div class="wzkb_clear"></div>';

	/**
	 * Filter the formatted shortcode output.
	 *
	 * @since	1.0.0
	 *
	 * @param	string	$output	Formatted HTML output
	 * @param	array	$args	Parameters array
	 */
	return apply_filters( 'wzkb_knowledge', $output, $args );

}


/**
 * Creates the knowledgebase loop.
 *
 * @since	1.0.0
 *
 * @param	int $termID    Term ID
 * @param	int $level      Level of the loop
 * @return	string	Formatted output
 */
function wzkb_looper( $termID, $level ) {

	$divclasses = array( 'wzkb_section', 'wzkb-section-level-' . $level );
	if ( 1 === $level ) {
		$divclasses[] = 'section group';
	} elseif ( 2 === $level ) {
		$divclasses[] = 'col span_1_of_2';
	}

	/**
	 * Filter to add more classes if needed.
	 *
	 * @since	1.1.0
	 *
	 * @param	array	$divclasses	Current array of classes
	 * @param	int		$level		Level of the loop
	 */
	$divclasses = apply_filters( 'wzkb_loop_div_class', $divclasses, $level );

	$output = '<div class="' . implode( ' ', $divclasses ) . '">';

	$term = get_term( $termID, 'wzkb_category' );

	if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
		$output .= wzkb_article_header( $term, $level );
		$output .= wzkb_list_posts_by_term( $term $level );
	}

	$output .= '<div class="wzkb_section_wrapper">';

	// Get Knowledge Base Sections
	$sections = get_terms( 'wzkb_category', array(
		'orderby'    => 'name',
		'hide_empty' => 1,
		'parent' => $termID,
	) );

	if ( ! empty( $sections ) && ! is_wp_error( $sections ) ) {

		$level++;

		foreach ( $sections as $section ) {
			$output .= wzkb_looper( $section->term_id, $level );
		}
	}

	$output .= '</div>'; // End wzkb_section_wrapper
	$output .= '</div>'; // End wzkb_section

	/**
	 * Filter the formatted shortcode output.
	 *
	 * @since	1.0.0
	 *
	 * @param	string	$output	Formatted HTML output
	 * @param	int		$term_id	Term ID
	 * @param	int		$level		Level of the loop
	 */
	return apply_filters( 'wzkb_looper', $output, $term, $level );

}


/**
 * Returns query results for a specific term.
 *
 * @since	1.1.0
 *
 * @param	object $term   The Term
 * @return	object	Query results for the give term
 */
function wzkb_query_posts( $term ) {

	// Get the term children for the current term
	$termchildren = get_term_children( $term->term_id, 'wzkb_category' );

	// Get all the posts for the current term excluding posts located in its child terms
	$args = array(
		'posts_per_page' => -1,
		'tax_query' => array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'wzkb_category',
				'field' => 'id',
				'terms' => $term->term_id,
			),
			array(
				'taxonomy' => 'wzkb_category',
				'field' => 'id',
				'terms' => $termchildren,
				'operator' => 'NOT IN',
			)
		),
	);

	$query = new WP_Query( $args );

	/**
	 * Filters query results of the specific term.
	 *
	 * @since	1.1.0
	 *
	 * @param	object	$query	Query results for the give term
	 * @param	array	$args	Arguments for WP_Query
	 * @param	object	$term	The Term
	 */
	return apply_filters( 'wzkb_query_posts', $query, $args, $term );

}


/**
 * Formatted output of posts for a given term.
 *
 * @since	1.1.0
 *
 * @param	object $term   Current term
 * @param	int    $level  Current level in the recursive loop
 * @return	string	Formatted output of posts for a given term
 */
function wzkb_list_posts_by_term( $term, $level ) {

	$output = '';

	$query = wzkb_query_posts( $term );

	if ( $query->have_posts() ) :

		$output .= wzkb_article_loop( $term $level, $query );

		wp_reset_postdata();

	endif;

	/**
	 * Filter the header of the article list.
	 *
	 * @since	1.1.0
	 *
	 * @param	string	$output	Formatted footer output
	 * @param	object	$term	Current term
	 * @param	int		$level	Current level in the recursive loop
	 * @param	object	$query	Query results object
	 */
	return apply_filters( 'wzkb_list_posts_by_term', $output, $term, $level, $query );

}

/**
 * Header of the articles list.
 *
 * @since	1.1.0
 *
 * @param	object $term   Current term
 * @param	int    $level  Current level in the recursive loop
 * @return	string	Formatted footer output
 */
function wzkb_article_header( $term, $level ) {

	$output = '
	<h3 class="wzkb_section_name wzkb-section-name-level-' . $level . '">
		<a href="' . get_term_link( $term ) . '" title="' . $term->name . '" >' . $term->name . '</a>
	</h3>
	';

	/**
	 * Filter the header of the article list.
	 *
	 * @since	1.1.0
	 *
	 * @param	string	$output	Formatted footer output
	 * @param	object	$term	Current term
	 * @param	int		$level	Current level in the recursive loop
	 * @param	object	$query	Query results object
	 */
	return apply_filters( 'wzkb_article_header', $output, $term, $level );

}


/**
 * Creates the list of articles for a particular query results object.
 *
 * @since	1.1.0
 *
 * @param	object $term   Current term
 * @param	int    $level  Current level in the recursive loop
 * @param	object $query  Query results object
 * @return	string	Formatted ul loop
 */
function wzkb_article_loop( $term, $level, $query ) {

	$output = '<ul class="wzkb-articles-list term-' . $term->term_id . '">';

	while ( $query->have_posts() ) : $query->the_post();

		$output .= '<li class="wzkb-article-name post-' . get_the_ID() . '">';
		$output .= '<a href="' . get_permalink( get_the_ID() ) . '" rel="bookmark" title="' . get_the_title( get_the_ID() ) . '">' . get_the_title( get_the_ID() ) . '</a>';
		$output .= '</li>';

	endwhile;

	$output .= '</ul>';

	/**
	 * Filters formatted articles list.
	 *
	 * @since	1.1.0
	 *
	 * @param	string	$output	Formatted ul loop
	 * @param	object	$term	Current term
	 * @param	int		$level	Current level in the recursive loop
	 * @param	object	$query	Query results object
	 */
	return apply_filters( 'wzkb_article_loop', $output, $term, $level, $query );

}


/**
 * Footer of the articles list.
 *
 * @since	1.1.0
 *
 * @param	object $term   Current term
 * @param	int    $level  Current level in the recursive loop
 * @param	object $query  Query results object
 * @return	string	Formatted footer output
 */
function wzkb_article_footer( $term, $level, $query ) {

	$output = '';

	if ( ( $level < 2 ) && ( $query->found_posts > 5 ) ) {

		$output .= '
		<p class="wzkb-article-footer">' . __( 'Read more articles in ', 'wzkb' ) . '
			<a href="' . get_term_link( $term ) . '" title="' . $term->name . '" >' . $term->name . '</a> &raquo;
		</p>
		';

	}

	/**
	 * Filter the footer of the article list.
	 *
	 * @since	1.1.0
	 *
	 * @param	string	$output	Formatted footer output
	 * @param	string	$output	Formatted ul loop
	 * @param	object	$term	Current term
	 * @param	int		$level	Current level in the recursive loop
	 */
	return apply_filters( 'wzkb_article_footer', $output, $term, $level, $query );

}
