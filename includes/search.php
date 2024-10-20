<?php
/**
 * Search for Knowledge Base articles.
 *
 * @link  https://webberzone.com
 * @since 1.0.0
 *
 * @package    WZKB
 * @subpackage WZKB/search
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Display custom search form for WZKB.
 *
 * @since 1.1.0
 *
 * @param  boolean $echo_output Default to echo and not return the form.
 * @return void|string|null   String when retrieving, null when displaying or if searchform.php exists.
 */
function wzkb_get_search_form( $echo_output = true ) {

	$form = '<form role="search" method="get" class="wzkb-search-form" action="' . esc_url( home_url( '/' ) ) . '">'
		. '<label>'
		. '<span class="screen-reader-text">' . _x( 'Search for:', 'label', 'knowledgebase' ) . '</span>'
		. '<input type="search" class="wzkb-search-field" placeholder="' . esc_attr_x( 'Search the knowledgebase &hellip;', 'placeholder', 'knowledgebase' ) . '" value="' . get_search_query() . '" name="s" title="' . esc_attr_x( 'Search for:', 'label', 'knowledgebase' ) . '" />'
		. '</label>'
		. '<input type="hidden" name="post_type" value="wz_knowledgebase">'
		. '<input type="submit" class="wzkb-search-submit" value="' . esc_attr_x( 'Search', 'submit button', 'knowledgebase' ) . '" />'
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

	if ( $echo_output ) {
		echo trim( $result ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return trim( $result );
	}
}
