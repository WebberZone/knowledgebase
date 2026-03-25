<?php
/**
 * Search form class
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Search form class.
 *
 * @since 2.3.0
 */
class Search {

	/**
	 * Constructor.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
	}

	/**
	 * Display custom search form for WZKB.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args {
	 *     Optional. Array of parameters.
	 *
	 *     @type string $placeholder Placeholder text for search input. Default "Search the knowledgebase …".
	 *     @type string $button_text Text for submit button. Default "Search".
	 * }
	 * @return void|string|null   String when retrieving, null when displaying or if searchform.php exists.
	 */
	public static function get_search_form( $args = array() ) {
		$defaults = array(
			'placeholder' => _x( 'Search the knowledgebase &hellip;', 'placeholder', 'knowledgebase' ),
			'button_text' => _x( 'Search', 'submit button', 'knowledgebase' ),
		);

		$args = wp_parse_args( $args, $defaults );

		// Generate unique ID for label association.
		$input_id = 'wzkb-search-field-' . uniqid();

		$form = '<form role="search" method="get" class="wzkb-search-form" action="' . esc_url( home_url( '/' ) ) . '">'
			. '<label for="' . esc_attr( $input_id ) . '">'
			. '<span class="screen-reader-text">' . _x( 'Search for:', 'label', 'knowledgebase' ) . '</span>'
			. '</label>'
			. '<input type="search" id="' . esc_attr( $input_id ) . '" class="wzkb-search-field" placeholder="' . esc_attr( $args['placeholder'] ) . '" value="' . get_search_query() . '" name="s" aria-label="' . esc_attr_x( 'Search the knowledgebase', 'aria-label', 'knowledgebase' ) . '" />'
			. '<input type="hidden" name="post_type" value="wz_knowledgebase">'
			. '<input type="submit" class="wzkb-search-submit" value="' . esc_attr( $args['button_text'] ) . '" />'
			. '</form>';

		/**
		 * Filter the HTML output of the search form.
		 *
		 * @since 1.1.0
		 *
		 * @param string|null $form The search form HTML output.
		 */
		$result = apply_filters( 'wzkb_get_search_form', $form );

		if ( null === $result ) {
			$result = $form;
		}

		return trim( $result );
	}
}
