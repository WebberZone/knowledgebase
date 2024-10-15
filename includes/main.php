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
 * @since 2.1.0 Added additional parameters that can be passed.
 *
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type int    $category            Create a knowledge base for this category.
 *     @type bool   $is_shortcode        Is this created using the shortcode?
 *     @type bool   $is_block            Is this created using the block?
 *     @type string $extra_class         Space separated list of classes for the wrapping `div`.
 *     @type bool   $show_article_count  Show article count?
 *     @type bool   $show_excerpt        Show excerpt?
 *     @type bool   $show_empty_sections Show empty sections?
 *     @type int    $limit               Number of articles to display in each group.
 *     @type int    $columns             Number of columns to display the knowledge base.
 * }
 * @return string Knowledge Base output.
 */
function wzkb_knowledge( $args = array() ) {

	$defaults = array(
		'category'            => 0,
		'is_shortcode'        => 0,
		'is_block'            => 0,
		'extra_class'         => '',
		'show_article_count'  => wzkb_get_option( 'show_article_count' ),
		'show_excerpt'        => wzkb_get_option( 'show_excerpt' ),
		'clickable_section'   => wzkb_get_option( 'clickable_section' ),
		'show_empty_sections' => wzkb_get_option( 'show_empty_sections' ),
		'limit'               => wzkb_get_option( 'limit' ),
		'columns'             => wzkb_get_option( 'columns' ),
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	// Set defaults if variables are empty.
	$args['limit']   = ( ! empty( absint( $args['limit'] ) ) ) ? absint( $args['limit'] ) : wzkb_get_option( 'limit' );
	$args['columns'] = ( ! empty( absint( $args['columns'] ) ) ) ? absint( $args['columns'] ) : wzkb_get_option( 'columns' );

	// Set default classes.
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

	$output = '<div class="wzkb ' . esc_attr( $div_classes ) . '">';

	// Are we trying to display a category?
	$category       = absint( $args['category'] );
	$level          = ( 0 < $category ) ? 1 : 0;
	$term_id        = ( 0 < $category ) ? $category : 0;
	$nested_wrapper = ( isset( $args['nested_wrapper'] ) ) ? $args['nested_wrapper'] : true;

	$output .= wzkb_looper( $term_id, $level, $nested_wrapper, $args );

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
 * @since 2.1.0 Added new arguments $args.
 *
 * @param  int   $term_id Term ID.
 * @param  int   $level   Level of the loop.
 * @param  bool  $nested  Run recursive loops before closing HTML wrappers.
 * @param  array $args    Parameters array. See wzkb_knowledge() for list of accepted args.
 * @return string Formatted output
 */
function wzkb_looper( $term_id, $level, $nested = true, $args = array() ) {

	$divclasses     = array( 'wzkb_section', 'wzkb-section-level-' . $level );
	$category_level = (int) wzkb_get_option( 'category_level' );

	if ( ( $category_level - 1 ) === $level ) {
		$divclasses[] = 'section group';
	} elseif ( $category_level === $level ) {
		$divclasses[] = 'col span_1_of_' . $args['columns'];
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
		$output .= wzkb_article_header( $term, $level, $args );
		$output .= wzkb_list_posts_by_term( $term, $level, $args );
	} elseif ( $level > 0 ) {
			/* translators: Section ID. */
			return sprintf( __( '%s is not enter a valid section ID', 'knowledgebase' ), $term_id );
	}

	$output .= '<div class="wzkb_section_wrapper">';

	// Get Knowledge Base Sections.
	$sections = get_terms(
		array(
			'taxonomy'   => 'wzkb_category',
			'orderby'    => 'slug',
			'hide_empty' => $args['show_empty_sections'] ? 0 : 1,
			'parent'     => $term_id,
		)
	);

	if ( ! $nested ) {
		$output .= '</div>'; // End wzkb_section_wrapper.
		$output .= '</div>'; // End wzkb_section.
	}

	if ( ! empty( $sections ) && ! is_wp_error( $sections ) ) {

		++$level;

		foreach ( $sections as $section ) {
			$output .= wzkb_looper( $section->term_id, $level, $nested, $args );
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
	 * @param string   $output Formatted HTML output
	 * @param \WP_Term $term   Term ID
	 * @param int      $level  Level of the loop
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
 * @since 2.1.0 Added new arguments $args.
 *
 * @param  object $term  Current term.
 * @param  int    $level Current level in the recursive loop.
 * @param  array  $args  Parameters array. See wzkb_knowledge() for list of accepted args.
 * @return string Formatted output of posts for a given term
 */
function wzkb_list_posts_by_term( $term, $level, $args = array() ) {

	$output = '';

	$query = wzkb_query_posts( $term );

	if ( $query->have_posts() ) {

		$output .= wzkb_article_loop( $term, $level, $query, $args );
		$output .= wzkb_article_footer( $term, $level, $query, $args );

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
 * @since 2.1.0 Added new arguments $args.
 *
 * @param  object $term  Current term.
 * @param  int    $level Current level in the recursive loop.
 * @param  array  $args  Parameters array. See wzkb_knowledge() for list of accepted args.
 * @return string Formatted footer output
 */
function wzkb_article_header( $term, $level, $args = array() ) {

	$output = '<h3 class="wzkb_section_name wzkb-section-name-level-' . $level . '">';

	if ( $args['clickable_section'] ) {
		$output .= '<a href="' . get_term_link( $term ) . '" title="' . $term->name . '" >' . $term->name . '</a>';
	} else {
		$output .= $term->name;
	}

	if ( $level >= (int) wzkb_get_option( 'category_level' ) && $args['show_article_count'] ) {
		$output .= '<div class="wzkb_section_count">' . $term->count . '</div>';
	}

	$output .= '</h3> ';

	/**
	 * Filter the header of the article list.
	 *
	 * @since 1.1.0
	 *
	 * @param string $output Formatted footer output.
	 * @param object $term Current term.
	 * @param int  $level Current level in the recursive loop.
	 * @param array $args Parameters array.
	 */
	return apply_filters( 'wzkb_article_header', $output, $term, $level, $args );
}


/**
 * Creates the list of articles for a particular query results object.
 *
 * @since 1.1.0
 * @since 2.1.0 Added new arguments $args.
 *
 * @param  object $term  Current term.
 * @param  int    $level Current level in the recursive loop.
 * @param  object $query Query results object.
 * @param  array  $args  Parameters array. See wzkb_knowledge() for list of accepted args.
 * @return string Formatted ul loop
 */
function wzkb_article_loop( $term, $level, $query, $args = array() ) {

	$limit = 0;

	$output = '<ul class="wzkb-articles-list term-' . $term->term_id . '">';

	while ( $query->have_posts() ) :
		$query->the_post();

		$output .= '<li class="wzkb-article-name post-' . get_the_ID() . '">';
		$output .= '<a href="' . get_permalink( get_the_ID() ) . '" rel="bookmark" title="' . get_the_title( get_the_ID() ) . '">' . get_the_title( get_the_ID() ) . '</a>';
		if ( $args['show_excerpt'] ) {
			$output .= '<div class="wzkb-article-excerpt post-' . get_the_ID() . '" >' . get_the_excerpt( get_the_ID() ) . '</div>';
		}
		$output .= '</li>';

		++$limit;

		if ( $limit >= $args['limit'] && ! is_tax( 'wzkb_category', $term->term_id ) ) {
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
 * @since 2.1.0 Added new arguments $args.
 *
 * @param  object $term  Current term.
 * @param  int    $level Current level in the recursive loop.
 * @param  object $query Query results object.
 * @param  array  $args  Parameters array. See wzkb_knowledge() for list of accepted args.
 * @return string Formatted footer output
 */
function wzkb_article_footer( $term, $level, $query, $args = array() ) {

	$output = '';

	if ( $query->found_posts > $args['limit'] && ! is_tax( 'wzkb_category', $term->term_id ) ) {

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
	 * @param string $output Formatted footer output.
	 * @param object $term Current term.
	 * @param int    $level Current level in the recursive loop.
	 * @param object $query Query results object.
	 */
	return apply_filters( 'wzkb_article_footer', $output, $term, $level, $query );
}

/**
 * Get the meta key based on a list of parameters.
 *
 * @since 1.8.0
 *
 * @param mixed $attr Data used to create the meta key. Array of attributes preferred.
 * @return string Cache meta key
 */
function wzkb_cache_get_key( $attr ) {

	$meta_key = '_wzkb_cache_' . md5( wp_json_encode( $attr ) );

	return $meta_key;
}

/**
 * Get a hierarchical list of WZ Knowledge Base sections.
 *
 * @param  int   $term_id Term ID.
 * @param  int   $level   Level of the loop.
 * @param  array $args    Array or arguments.
 * @return string HTML output with the categories.
 */
function wzkb_categories_list( $term_id, $level = 0, $args = array() ) {

	$defaults = array(
		'depth'          => 0,  // Depth of nesting.
		'before_li_item' => '', // Before list item - just after <li>.
		'after_li_item'  => '', // Before list item - just before </li>.
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	// Get Knowledge Base Sections.
	$sections = get_terms(
		array(
			'taxonomy'   => 'wzkb_category',
			'orderby'    => 'slug',
			'hide_empty' => wzkb_get_option( 'show_empty_sections' ) ? 0 : 1,
			'parent'     => $term_id,
		)
	);

	$output = '';

	if ( ! empty( $sections ) && ! is_wp_error( $sections ) ) {

		$output .= '<ul class="wzkb_terms_widget wzkb_term_' . $term_id . ' wzkb_ul_level_' . $level . '">';

		++$level;

		foreach ( $sections as $term ) {

			$term_link = get_term_link( $term );

			// If there was an error, continue to the next term.
			if ( is_wp_error( $term_link ) ) {
				continue;
			}

			$output .= '<li class="wzkb_cat_' . $term->term_id . '">' . $args['before_li_item'];
			$output .= '<a href="' . esc_url( $term_link ) . '" title="' . esc_attr( $term->name ) . '" >' . $term->name . '</a>';
			$output .= wzkb_categories_list( $term->term_id, $level, $args );
			$output .= $args['after_li_item'] . '</li>';

			// Exit the loop if we are at the depth.
			if ( 0 < $args['depth'] && $level >= $args['depth'] ) {
				break;
			}
		}
		$output .= '</ul>';
	}

	return $output;
}

/**
 * Initialise the widget.
 *
 * @since 1.9.0
 */
function register_wzkb_widgets() {
	register_widget( 'WZKB_Breadcrumb_Widget' );
	register_widget( 'WZKB_Sections_Widget' );
	register_widget( 'WZKB_Articles_Widget' );
}
add_action( 'widgets_init', 'register_wzkb_widgets' );
