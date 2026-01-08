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

		// Build hierarchy path.
		$path = wzkb_get_term_hierarchy_path( $category, true, ' > ' );

		$label = trim(
			sprintf(
				'%1$s (ID: %2$d)',
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
}
