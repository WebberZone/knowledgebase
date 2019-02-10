<?php
/**
 * Knowledge Base Breadcrumbs
 *
 * @link  https://webberzone.com
 * @since 1.6.0
 *
 * @package    WZKB
 * @subpackage WZKB/breadcrumbs
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Creates the breadcrumb.
 *
 * @since 1.6.0
 *
 * @param  array $args Parameters array.
 * @return string|bool Formatted shortcode output. False if not a WZKB post type archive or post.
 */
function wzkb_get_breadcrumb( $args = array() ) {

	$defaults = array(
		'separator' => ' &raquo; ', // Separator.
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	// Return if not a WZKB post type archive or single page.
	if ( ! is_post_type_archive( 'wz_knowledgebase' ) && ! is_singular( 'wz_knowledgebase' ) && ! is_tax( 'wzkb_category' ) && ! is_tax( 'wzkb_tag' ) ) {
		return false;
	}

	$output = '<div class="wzkb_breadcrumb">';

	// First output the link to home page.
	$output .= '<a href="' . get_option( 'home' ) . '">';
	$output .= esc_html__( 'Home', 'knowledgebase' );
	$output .= '</a>';
	$output .= $args['separator'];

	// Link to the knowledge base.
	$output .= '<a href="' . get_post_type_archive_link( 'wz_knowledgebase' ) . '" >' . wzkb_get_option( 'kb_title' ) . '</a>';

	// Output the category or tag.
	if ( is_tax( 'wzkb_category' ) || is_tax( 'wzkb_tag' ) ) {
		$tax = get_queried_object();

		$output .= $args['separator'];
		$output .= wzkb_breadcrumb_tax_loop( $tax, $args );
	}

	// Output link to single post.
	if ( is_singular( 'wz_knowledgebase' ) ) {
		$post = get_queried_object();

		$terms = get_the_terms( $post, 'wzkb_category' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$tax     = $terms[0];
			$output .= $args['separator'];
			$output .= wzkb_breadcrumb_tax_loop( $tax, $args );
		}

		$output .= $args['separator'];
		$output .= '<a href="' . get_permalink( $post ) . '" >' . $post->post_title . '</a>';
	}

	$output .= '</div>'; // End wzkb_breadcrumb.
	$output .= '<div class="wzkb_clear"></div>';

	/**
	 * Filter the formatted shortcode output.
	 *
	 * @since 1.6.0
	 *
	 * @param string $output Formatted HTML output
	 * @param array $args Parameters array
	 */
	return apply_filters( 'wzkb_get_breadcrumb', $output, $args );
}

/**
 * Echo the breadcrumb output.
 *
 * @since 1.6.0
 *
 * @param  array $args Parameters array.
 */
function wzkb_breadcrumb( $args = array() ) {
	echo wzkb_get_breadcrumb( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Generates the HTML for the taxonomy and its children for the breadcrumb.
 *
 * @since 1.6.0
 *
 * @param object $taxonomy Taxonomy object.
 * @param array  $args     Parameters array.
 * @return string HTML output
 */
function wzkb_breadcrumb_tax_loop( $taxonomy, $args = array() ) {

	$output = '<a href="' . get_term_link( $taxonomy ) . '" title="' . $taxonomy->name . '" >' . $taxonomy->name . '</a>';

	if ( ! empty( $taxonomy->parent ) ) {
		$output = wzkb_breadcrumb_tax_loop( get_term( $taxonomy->parent, $taxonomy->taxonomy ), $args ) . $args['separator'] . $output;
	}

	/**
	 * Filters the HTML for the taxonomy and its children for the breadcrumb.
	 *
	 * @since 1.6.0
	 *
	 * @param string $output   HTML output.
	 * @param object $taxonomy Taxonomy object.
	 * @param array  $args     Parameters array.
	 */
	return apply_filters( 'wzkb_breadcrumb_tax_loop', $output, $taxonomy, $args );
}
