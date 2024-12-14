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
	 * Converts Unicode sequence to character.
	 *
	 * @since 2.3.0
	 *
	 * @param string $unicode Unicode sequence (e.g., '\2192' for →).
	 * @return string Converted character.
	 */
	private static function unicode_to_char( $unicode ) {
		// Remove backslash if present.
		$unicode = ltrim( $unicode, '\\' );

		// Convert Unicode sequence to character.
		return html_entity_decode( '&#x' . $unicode . ';', ENT_COMPAT, 'UTF-8' );
	}

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
			'separator' => '»',
		);

		// Parse incoming $args into an array and merge it with $defaults.
		$args = wp_parse_args( $args, $defaults );

		// Convert Unicode sequence if provided.
		if ( strpos( $args['separator'], '\\' ) === 0 ) {
			$args['separator'] = self::unicode_to_char( $args['separator'] );
		}

		// Return if not a WZKB post type archive or single page.
		if ( ( ! is_admin() && ! wp_is_json_request() ) &&
		! is_post_type_archive( 'wz_knowledgebase' ) &&
		! is_singular( 'wz_knowledgebase' ) &&
		! is_tax( 'wzkb_category' ) &&
		! is_tax( 'wzkb_tag' )
		) {
			return '';
		}

		$output  = '<nav class="wzkb_breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'knowledgebase' ) . '">';
		$output .= '<ol class="wzkb_breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">';

		// First output the link to home page.
		$output .= '<li class="wzkb_breadcrumb-item" data-separator="' . esc_attr( $args['separator'] ) . '" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
		$output .= '<a href="' . esc_url( home_url() ) . '" itemprop="item">';
		$output .= '<span itemprop="name">' . esc_html__( 'Home', 'knowledgebase' ) . '</span>';
		$output .= '</a>';
		$output .= '<meta itemprop="position" content="1" />';
		$output .= '</li>';

		// Link to the knowledge base.
		$output .= '<li class="wzkb_breadcrumb-item" data-separator="' . esc_attr( $args['separator'] ) . '" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
		$output .= '<a href="' . esc_url( wzkb_get_kb_url() ) . '" itemprop="item">';
		$output .= '<span itemprop="name">' . esc_html( wzkb_get_option( 'kb_title' ) ) . '</span>';
		$output .= '</a>';
		$output .= '<meta itemprop="position" content="2" />';
		$output .= '</li>';

		// Output the category or tag.
		if ( is_tax( 'wzkb_category' ) || is_tax( 'wzkb_tag' ) ) {
			$tax     = get_queried_object();
			$output .= self::get_hierarchical_term_trail( $tax, $args, 3 );
		}

		// Output link to single post.
		if ( is_singular( 'wz_knowledgebase' ) ) {
			$post = get_queried_object();

			$terms = get_the_terms( $post, 'wzkb_category' );
			if ( is_array( $terms ) && ! empty( $terms ) ) {
				$tax     = $terms[0];
				$output .= self::get_hierarchical_term_trail( $tax, $args, 3 );
			}

			$output .= '<li class="wzkb_breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
			$output .= '<a href="' . esc_url( get_permalink( $post ) ) . '" itemprop="item">';
			$output .= '<span itemprop="name">' . esc_html( $post->post_title ) . '</span>';
			$output .= '</a>';
			$output .= '<meta itemprop="position" content="4" />';
			$output .= '</li>';
		}

		$output .= '</ol>';
		$output .= '</nav>';

		/**
		 * Filter the formatted shortcode output.
		 *
		 * @since 1.6.0
		 *
		 * @param string $output Formatted HTML output.
		 * @param array  $args   Parameters array.
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
	 * @param int      $position Current position in breadcrumb.
	 * @return string HTML output.
	 */
	private static function get_hierarchical_term_trail( \WP_Term $taxonomy, $args = array(), $position = 2 ) {
		$defaults = array(
			'separator' => '»',
		);

		$args = wp_parse_args( $args, $defaults );

		$output  = '<li class="wzkb_breadcrumb-item" data-separator="' . esc_attr( $args['separator'] ) . '" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
		$output .= '<a href="' . esc_url( get_term_link( $taxonomy ) ) . '" itemprop="item" title="' . esc_attr( $taxonomy->name ) . '">';
		$output .= '<span itemprop="name">' . esc_html( $taxonomy->name ) . '</span>';
		$output .= '</a>';
		$output .= '<meta itemprop="position" content="' . intval( $position ) . '" />';
		$output .= '</li>';

		if ( ! empty( $taxonomy->parent ) ) {
			$output = self::get_hierarchical_term_trail(
				get_term( $taxonomy->parent, $taxonomy->taxonomy ),
				$args,
				$position - 1
			) . $output;
		}

		return $output;
	}
}
