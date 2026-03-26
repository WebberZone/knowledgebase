<?php
/**
 * Custom walker for wzkb_category checkbox lists with hierarchical labels.
 *
 * @since 3.0.0
 *
 * @package WebberZone\\Knowledge_Base\\Admin
 */

namespace WebberZone\Knowledge_Base\Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Custom walker for wzkb_category checkbox lists (Quick Edit, term metaboxes).
 *
 * Mirrors the label format used by Walker_Category_Dropdown: shows the full
 * product > section hierarchy path via wzkb_get_term_hierarchy_path() so
 * child terms are unambiguously identified without relying on visual context.
 *
 * @since 3.0.0
 */
class Walker_Section_Checklist extends \Walker_Category_Checklist {

	/**
	 * Start element output.
	 *
	 * @param string $output            Passed by reference.
	 * @param object $data_object       Term object.
	 * @param int    $depth             Depth of term in tree.
	 * @param array  $args              Walker args.
	 * @param int    $current_object_id Current post/object ID.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$term     = $data_object;
		$taxonomy = ! empty( $args['taxonomy'] ) ? $args['taxonomy'] : 'category';
		$name     = 'tax_input[' . $taxonomy . ']';

		$args['popular_cats']  = ! empty( $args['popular_cats'] ) ? array_map( 'intval', $args['popular_cats'] ) : array();
		$class                 = in_array( $term->term_id, $args['popular_cats'], true ) ? ' class="popular-category"' : '';
		$args['selected_cats'] = ! empty( $args['selected_cats'] ) ? array_map( 'intval', $args['selected_cats'] ) : array();

		$is_selected   = in_array( $term->term_id, $args['selected_cats'], true );
		$li_attributes = $is_selected ? ' aria-checked="true"' : '';
		$checked       = checked( $is_selected, true, false );
		$disabled      = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
		$checkbox_id   = 'in-' . $taxonomy . '-' . $term->term_id;
		$pad           = str_repeat( '&nbsp;', $depth * 3 );
		$label         = esc_html( wzkb_get_term_hierarchy_path( $term, true, ' > ' ) );

		$output .= "\n<li id='{$taxonomy}-{$term->term_id}'{$class}{$li_attributes}>" .
			"<label class='selectit' for='{$checkbox_id}'>" .
			"<input value='{$term->term_id}' type='checkbox' name='{$name}[]' id='{$checkbox_id}'{$disabled}{$checked} /> " .
			$pad . $label .
			'</label>';
	}
}
