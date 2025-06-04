<?php
/**
 * Breadcrumbs module.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

use WebberZone\Knowledge_Base\Util\Helpers;

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
	 * Get the product term for the current context.
	 *
	 * @return \WP_Term|false
	 */
	private static function get_product_for_context() {
		if ( is_tax( 'wzkb_category' ) || is_tax( 'wzkb_tag' ) ) {
			$tax = get_queried_object();
			if ( $tax && isset( $tax->term_id ) ) {
				$product_id = get_term_meta( $tax->term_id, 'product_id', true );
				if ( $product_id ) {
					$product = get_term( $product_id, 'wzkb_product' );
					if ( $product && ! is_wp_error( $product ) ) {
						return $product;
					}
				}
			}
		}
		if ( is_singular( 'wz_knowledgebase' ) ) {
			$post  = get_queried_object();
			$terms = get_the_terms( $post, 'wzkb_category' );
			if ( is_array( $terms ) && ! empty( $terms ) ) {
				$primary_term = $terms[0];
				$ancestor     = $primary_term;
				while ( $ancestor->parent ) {
					$ancestor = get_term( $ancestor->parent, $ancestor->taxonomy );
				}
				$product_id = get_term_meta( $ancestor->term_id, 'product_id', true );
				if ( $product_id ) {
					$product = get_term( $product_id, 'wzkb_product' );
					if ( $product && ! is_wp_error( $product ) ) {
						return $product;
					}
				}
			}
		}
		return false;
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

		$args = wp_parse_args( $args, $defaults );
		$args = Helpers::sanitize_args( $args );

		if ( strpos( $args['separator'], '\\' ) === 0 ) {
			$args['separator'] = self::unicode_to_char( $args['separator'] );
		}

		if ( ( ! is_admin() && ! wp_is_json_request() ) &&
		! is_post_type_archive( 'wz_knowledgebase' ) &&
		! is_singular( 'wz_knowledgebase' ) &&
		! is_tax( 'wzkb_category' ) &&
		! is_tax( 'wzkb_tag' )
		) {
			return '';
		}

		$items   = array();
		$items[] = array(
			'url'      => home_url(),
			'label'    => esc_html__( 'Home', 'knowledgebase' ),
			'position' => 1,
			'current'  => false,
		);
		$items[] = array(
			'url'      => wzkb_get_kb_url(),
			'label'    => esc_html( wzkb_get_option( 'kb_title' ) ),
			'position' => 2,
			'current'  => false,
		);

		$position = 3;
		if ( wzkb_get_option( 'multi_product' ) ) {
			$product = self::get_product_for_context();
			if ( $product ) {
				$items[] = array(
					'url'      => get_term_link( $product ),
					'label'    => esc_html( $product->name ),
					'position' => $position,
					'current'  => false,
				);
				++$position;
			}
		}

		if ( is_tax( 'wzkb_category' ) || is_tax( 'wzkb_tag' ) ) {
			$tax   = get_queried_object();
			$trail = self::get_hierarchical_term_trail_array( $tax, $args, $position );
			foreach ( $trail as $item ) {
				$items[] = $item;
				++$position;
			}
		}

		if ( is_singular( 'wz_knowledgebase' ) ) {
			$post  = get_queried_object();
			$terms = get_the_terms( $post, 'wzkb_category' );
			if ( is_array( $terms ) && ! empty( $terms ) ) {
				$tax   = $terms[0];
				$trail = self::get_hierarchical_term_trail_array( $tax, $args, $position );
				foreach ( $trail as $item ) {
					$items[] = $item;
					++$position;
				}
			}
			$items[] = array(
				'url'      => get_permalink( $post ),
				'label'    => esc_html( $post->post_title ),
				'position' => $position,
				'current'  => true,
			);
		}

		$items = apply_filters( 'wzkb_breadcrumb_items', $items, $args );

		$output  = '<nav class="wzkb_breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'knowledgebase' ) . '">';
		$output .= '<ol class="wzkb_breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">';
		$sep     = esc_attr( $args['separator'] );
		foreach ( $items as $item ) {
			$output .= '<li class="wzkb_breadcrumb-item" data-separator="' . $sep . '" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
			$output .= '<a href="' . esc_url( $item['url'] ) . '" itemprop="item"' . ( ! empty( $item['current'] ) ? ' aria-current="page"' : '' ) . '>';
			$output .= '<span itemprop="name">' . $item['label'] . '</span>';
			$output .= '</a>';
			$output .= '<meta itemprop="position" content="' . intval( $item['position'] ) . '" />';
			$output .= '</li>';
		}
		$output .= '</ol>';
		$output .= '</nav>';

		return apply_filters( 'wzkb_get_breadcrumb', $output, $args );
	}

	/**
	 * Returns the hierarchical term trail as an array of breadcrumb items.
	 *
	 * @param \WP_Term $taxonomy Taxonomy object.
	 * @param array    $args     Parameters array.
	 * @param int      $position Current position in breadcrumb.
	 * @return array Array of breadcrumb items.
	 */
	private static function get_hierarchical_term_trail_array( \WP_Term $taxonomy, $args = array(), $position = 2 ) {
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
			$trail     = array_merge(
				self::get_hierarchical_term_trail_array(
					get_term( $taxonomy->parent, $taxonomy->taxonomy ),
					$args,
					$position
				),
			);
			$position += count( $trail );
		}
		$trail[] = array(
			'url'      => get_term_link( $taxonomy ),
			'label'    => esc_html( $taxonomy->name ),
			'position' => $position,
			'current'  => false,
		);
		return $trail;
	}
}
