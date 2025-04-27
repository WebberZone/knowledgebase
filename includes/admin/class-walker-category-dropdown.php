<?php
/**
 * Custom walker to enhance parent dropdown with product and ID.
 *
 * @since 3.0.0
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Custom walker to enhance parent dropdown with product and ID.
 *
 * @since 3.0.0
 */
class Walker_Category_Dropdown extends \Walker_CategoryDropdown {
	/**
	 * Start element output.
	 *
	 * @param string $output   Output HTML.
	 * @param object $category Term object.
	 * @param int    $depth    Depth of term in tree.
	 * @param array  $args     Args.
	 * @param int    $id       Term ID.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		$pad = str_repeat( '&nbsp;', $depth * 3 );

		// Get product name for this section.
		$product_id   = get_term_meta( $category->term_id, 'product_id', true );
		$product_name = '';
		if ( $product_id ) {
			$product = get_term( $product_id, 'wzkb_product' );
			if ( $product && ! is_wp_error( $product ) ) {
				$product_name = $product->name;
			}
		}

		// Build hierarchy path.
		$path = $this->get_section_hierarchy_path( $category );

		$label = trim(
			sprintf(
				'%1$s%2$s (ID: %3$d)',
				$product_name ? $product_name . ' > ' : '',
				$path,
				$category->term_id
			)
		);

		$output .= "\t<option class=\"level-$depth\" value=\"" . esc_attr( $category->term_id ) . '"';
		if ( isset( $args['selected'] ) && (int) $category->term_id === (int) $args['selected'] ) {
			$output .= ' selected="selected"';
		}
		$output .= '>';
		$output .= $pad . esc_html( $label );
		$output .= "</option>\n";
	}

	/**
	 * Get the full section hierarchy path for a term.
	 *
	 * @param object $term Term object.
	 * @return string Hierarchy path.
	 */
	private function get_section_hierarchy_path( $term ) {
		$ancestors = get_ancestors( $term->term_id, $term->taxonomy );
		$ancestors = array_reverse( $ancestors );
		$names     = array();
		foreach ( $ancestors as $ancestor_id ) {
			$ancestor = get_term( $ancestor_id, $term->taxonomy );
			if ( $ancestor && ! is_wp_error( $ancestor ) ) {
				$names[] = $ancestor->name;
			}
		}
		$names[] = $term->name;
		return implode( ' > ', $names );
	}
}
