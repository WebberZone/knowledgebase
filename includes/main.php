<?php
/**
 * The file holds the main plugin function.
 *
 * @link  https://webberzone.com
 * @since 1.0.0
 *
 * @package WZKB
 * @subpackage WZKB/main
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * The main function to generate the output.
 *
 * @since 1.0.0
 *
 * @param  array $args Parameters array.
 * @return string Formatted output
 */
function wzkb_knowledge( $args = array() ) {

	$defaults = array(
		'category'     => false, // Create a knowledge base for subcategories of this parent ID.
		'is_shortcode' => 0,
		'is_block'     => 0,
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	$shortcode_class = $args['is_shortcode'] ? 'wzkb_shortcode ' : '';
	$block_class     = $args['is_block'] ? 'wzkb_block ' : '';

	$div_classes = $shortcode_class . $block_class . ' ' . $args['extra_class'];

	/**
	 * Filter the classes added to the div wrapper of the Knowledge Base.
	 *
	 * @since 2.0.0
	 *
	 * @param string $div_classes String with the classes of the div wrapper.
	 */
	$div_classes = apply_filters( 'wzkb_div_class', $div_classes );

	$output = '<div class="wzkb ' . $div_classes . '">';

	// Are we trying to display a category?
	$level          = ( 0 < $args['category'] ) ? 1 : 0;
	$term_id        = ( 0 < $args['category'] ) ? $args['category'] : 0;
	$nested_wrapper = ( isset( $args['nested_wrapper'] ) ) ? $args['nested_wrapper'] : true;

	$output .= wzkb_looper( $term_id, $level, $nested_wrapper );

	$output .= '</div>'; // End wzkb_section.
	$output .= '<div class="wzkb_clear"></div>';

	/**
	 * Filter the formatted shortcode output.
	 *
	 * @since 1.0.0
	 *
	 * @param string $output Formatted HTML output
	 * @param array $args Parameters array
	 */
	return apply_filters( 'wzkb_knowledge', $output, $args );

}


/**
 * Creates the knowledge base loop.
 *
 * @since 1.0.0
 *
 * @param  int  $term_id Term ID.
 * @param  int  $level  Level of the loop.
 * @param  bool $nested Run recursive loops before closing HTML wrappers.
 * @return string Formatted output
 */
function wzkb_looper( $term_id, $level, $nested = true ) {

	$divclasses = array( 'wzkb_section', 'wzkb-section-level-' . $level );

	if ( (int) wzkb_get_option( 'category_level' ) - 1 === $level ) {
		$divclasses[] = 'section group';
	} elseif ( (int) wzkb_get_option( 'category_level' ) === $level ) {
		$divclasses[] = 'col span_1_of_' . wzkb_get_option( 'columns', 2 );
	}

	/**
	 * Filter to add more classes if needed.
	 *
	 * @since 1.1.0
	 *
	 * @param array $divclasses Current array of classes
	 * @param int  $level  Level of the loop
	 * @param int  $term_id  Term ID
	 */
	$divclasses = apply_filters( 'wzkb_loop_div_class', $divclasses, $level, $term_id );

	$output = '<div class="' . implode( ' ', $divclasses ) . '">';

	$term = get_term( $term_id, 'wzkb_category' );

	if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
		$output .= wzkb_article_header( $term, $level );
		$output .= wzkb_list_posts_by_term( $term, $level );
	}

	$output .= '<div class="wzkb_section_wrapper">';

	// Get Knowledge Base Sections.
	$sections = get_terms(
		'wzkb_category',
		array(
			'orderby'    => 'slug',
			'hide_empty' => wzkb_get_option( 'show_empty_sections' ) ? 0 : 1,
			'parent'     => $term_id,
		)
	);

	if ( ! $nested ) {
		$output .= '</div>'; // End wzkb_section_wrapper.
		$output .= '</div>'; // End wzkb_section.
	}

	if ( ! empty( $sections ) && ! is_wp_error( $sections ) ) {

		$level++;

		foreach ( $sections as $section ) {
			$output .= wzkb_looper( $section->term_id, $level );
		}
	}

	if ( $nested ) {
		$output .= '</div>'; // End wzkb_section_wrapper.
		$output .= '</div>'; // End wzkb_section.
	}

	/**
	 * Filter the formatted shortcode output.
	 *
	 * @since 1.0.0
	 *
	 * @param string $output Formatted HTML output
	 * @param int  $term_id Term ID
	 * @param int  $level  Level of the loop
	 */
	return apply_filters( 'wzkb_looper', $output, $term, $level );

}


/**
 * Returns query results for a specific term.
 *
 * @since 1.1.0
 *
 * @param  object $term The Term.
 * @return object Query results for the give term
 */
function wzkb_query_posts( $term ) {

	// Get the term children for the current term.
	$termchildren = get_term_children( $term->term_id, 'wzkb_category' );

	// Get all the posts for the current term excluding posts located in its child terms.
	$args = array(
		'posts_per_page' => -1,
		'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'relation' => 'AND',
			array(
				'taxonomy' => 'wzkb_category',
				'field'    => 'id',
				'terms'    => $term->term_id,
			),
			array(
				'taxonomy' => 'wzkb_category',
				'field'    => 'id',
				'terms'    => $termchildren,
				'operator' => 'NOT IN',
			),
		),
	);

	// Support caching to speed up retrieval.
	if ( ! empty( wzkb_get_option( 'cache' ) ) ) {

		$meta_key = wzkb_cache_get_key( $args );

		$query = get_term_meta( $term->term_id, $meta_key, true );
	}

	if ( empty( $query ) ) {
		$query = new WP_Query( $args );
	}

	// Support caching to speed up retrieval.
	if ( ! empty( wzkb_get_option( 'cache' ) ) ) {
		add_term_meta( $term->term_id, $meta_key, $query, true );
	}

	/**
	 * Filters query results of the specific term.
	 *
	 * @since 1.1.0
	 *
	 * @param object $query Query results for the give term
	 * @param array $args Arguments for WP_Query
	 * @param object $term The Term
	 */
	return apply_filters( 'wzkb_query_posts', $query, $args, $term );

}


/**
 * Formatted output of posts for a given term.
 *
 * @since 1.1.0
 *
 * @param  object $term  Current term.
 * @param  int    $level Current level in the recursive loop.
 * @return string Formatted output of posts for a given term
 */
function wzkb_list_posts_by_term( $term, $level ) {

	$output = '';

	$query = wzkb_query_posts( $term );

	if ( $query->have_posts() ) {

		$output .= wzkb_article_loop( $term, $level, $query );
		$output .= wzkb_article_footer( $term, $level, $query );

		wp_reset_postdata();

	}

	/**
	 * Filter the header of the article list.
	 *
	 * @since 1.1.0
	 *
	 * @param string $output Formatted footer output
	 * @param object $term Current term
	 * @param int  $level Current level in the recursive loop
	 * @param object $query Query results object
	 */
	return apply_filters( 'wzkb_list_posts_by_term', $output, $term, $level, $query );

}

/**
 * Header of the articles list.
 *
 * @since 1.1.0
 *
 * @param  object $term  Current term.
 * @param  int    $level Current level in the recursive loop.
 * @return string Formatted footer output
 */
function wzkb_article_header( $term, $level ) {

	$output = '<h3 class="wzkb_section_name wzkb-section-name-level-' . $level . '">';

	if ( wzkb_get_option( 'clickable_section', true ) ) {
		$output .= '<a href="' . get_term_link( $term ) . '" title="' . $term->name . '" >' . $term->name . '</a>';
	} else {
		$output .= $term->name;
	}

	if ( $level > 1 && wzkb_get_option( 'show_article_count', false ) ) {
		$output .= '<div class="wzkb_section_count">' . $term->count . '</div>';
	}

	$output .= '</h3> ';

	/**
	 * Filter the header of the article list.
	 *
	 * @since 1.1.0
	 *
	 * @param string $output Formatted footer output
	 * @param object $term Current term
	 * @param int  $level Current level in the recursive loop
	 * @param object $query Query results object
	 */
	return apply_filters( 'wzkb_article_header', $output, $term, $level );

}


/**
 * Creates the list of articles for a particular query results object.
 *
 * @since 1.1.0
 *
 * @param  object $term  Current term.
 * @param  int    $level Current level in the recursive loop.
 * @param  object $query Query results object.
 * @return string Formatted ul loop
 */
function wzkb_article_loop( $term, $level, $query ) {

	$limit = 0;

	$output = '<ul class="wzkb-articles-list term-' . $term->term_id . '">';

	while ( $query->have_posts() ) :
		$query->the_post();

		$output .= '<li class="wzkb-article-name post-' . get_the_ID() . '">';
		$output .= '<a href="' . get_permalink( get_the_ID() ) . '" rel="bookmark" title="' . get_the_title( get_the_ID() ) . '">' . get_the_title( get_the_ID() ) . '</a>';
		if ( wzkb_get_option( 'show_excerpt', false ) ) {
			$output .= '<div class="wzkb-article-excerpt post-' . get_the_ID() . '" >' . get_the_excerpt( get_the_ID() ) . '</div>';
		}
		$output .= '</li>';

		$limit++;

		if ( $limit >= wzkb_get_option( 'limit' ) && ! is_tax( 'wzkb_category', $term->term_id ) ) {
			break;
		}

	endwhile;

	$output .= '</ul>';

	/**
	 * Filters formatted articles list.
	 *
	 * @since 1.1.0
	 *
	 * @param string $output Formatted ul loop
	 * @param object $term Current term
	 * @param int  $level Current level in the recursive loop
	 * @param object $query Query results object
	 */
	return apply_filters( 'wzkb_article_loop', $output, $term, $level, $query );

}


/**
 * Footer of the articles list.
 *
 * @since 1.1.0
 *
 * @param  object $term  Current term.
 * @param  int    $level Current level in the recursive loop.
 * @param  object $query Query results object.
 * @return string Formatted footer output
 */
function wzkb_article_footer( $term, $level, $query ) {

	$output = '';

	if ( $query->found_posts > wzkb_get_option( 'limit' ) && ! is_tax( 'wzkb_category', $term->term_id ) ) {

		$excerpt_more = __( 'Read more articles in ', 'wzkb' );

		/**
		 * Filters the string in the "more" link displayed in the trimmed articles list.
		 *
		 * @since 1.9.0
		 *
		 * @param string $excerpt_more The string shown before the more link.
		 */
		$excerpt_more = apply_filters( 'wzkb_excerpt_more', $excerpt_more );

		$output .= '
		<p class="wzkb-article-footer">' . __( 'Read more articles in ', 'wzkb' ) . '
			<a href="' . get_term_link( $term ) . '" title="' . $term->name . '" >' . $term->name . '</a> &raquo;
		</p>
		';
	}

	/**
	 * Filter the footer of the article footer.
	 *
	 * @since 1.1.0
	 *
	 * @param string $output Formatted footer output
	 * @param string $output Formatted ul loop
	 * @param object $term Current term
	 * @param int  $level Current level in the recursive loop
	 */
	return apply_filters( 'wzkb_article_footer', $output, $term, $level, $query );

}

/**
 * Get the meta key based on a list of parameters.
 *
 * @since 1.8.0
 *
 * @param array $attr   Array of attributes.
 * @return string Cache meta key
 */
function wzkb_cache_get_key( $attr ) {

	$meta_key = '_wzkb_cache_' . md5( wp_json_encode( $attr ) );

	return $meta_key;
}
