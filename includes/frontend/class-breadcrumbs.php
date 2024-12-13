<?php
/**
 * Breadcrumbs module.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Breadcrumbs Class.
 *
 * @since 2.3.0
 */
class Breadcrumbs {

	/**
	 * Creates the breadcrumb.
	 *
	 * @since 2.3.0
	 *
	 * @param  array $args Parameters array.
	 * @return string|bool Formatted HTML output. False if not a WZKB post type archive or post.
	 */
	public static function get_breadcrumb( $args = array() ) {

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
		$output .= '<a href="' . wzkb_get_kb_url() . '" >' . wzkb_get_option( 'kb_title' ) . '</a>';

		// Output the category or tag.
		if ( is_tax( 'wzkb_category' ) || is_tax( 'wzkb_tag' ) ) {
			$tax = get_queried_object();

			$output .= $args['separator'];
			$output .= self::get_hierarchical_term_trail( $tax, $args );
		}

		// Output link to single post.
		if ( is_singular( 'wz_knowledgebase' ) ) {
			$post = get_queried_object();

			$terms = get_the_terms( $post, 'wzkb_category' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$tax     = $terms[0];
				$output .= $args['separator'];
				$output .= self::get_hierarchical_term_trail( $tax, $args );
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
	 * Generates the HTML for the taxonomy and its children for the breadcrumb.
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Term $taxonomy Taxonomy object.
	 * @param array    $args     Parameters array.
	 * @return string HTML output
	 */
	private static function get_hierarchical_term_trail( \WP_Term $taxonomy, $args = array() ) {

		$defaults = array(
			'separator' => ' &raquo; ', // Separator.
		);

		// Parse incomming $args into an array and merge it with $defaults.
		$args = wp_parse_args( $args, $defaults );

		$output = '<a href="' . get_term_link( $taxonomy ) . '" title="' . $taxonomy->name . '" >' . $taxonomy->name . '</a>';

		if ( ! empty( $taxonomy->parent ) ) {
			$output = self::get_hierarchical_term_trail(
				get_term( $taxonomy->parent, $taxonomy->taxonomy ),
				$args
			) . $args['separator'] . $output;
		}

		return $output;
	}
}
