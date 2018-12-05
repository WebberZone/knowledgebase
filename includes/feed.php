<?php
/**
 * Knowledge Base Feed functions
 *
 * @link  https://webberzone.com
 * @since 1.4.0
 *
 * @package    WZKB
 * @subpackage WZKB/shortcode
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Include KB articles in the main feed.
 *
 * @since 1.4.0
 *
 * @param object $query Query object.
 * @return object Filtered Query
 */
function wzkb_in_feed( $query ) {

	if ( isset( $query['feed'] ) && wzkb_get_option( 'include_in_feed', false ) ) {
		if ( isset( $query['post_type'] ) ) {

			if ( isset( $_SERVER['REQUEST_URI'] ) && false !== strpos( esc_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ), wzkb_get_option( 'kb_slug' ) ) ) {  // Input var okay; sanitization okay.
				$query['post_type'] = array( 'wz_knowledgebase' );
			} else {
				$query['post_type'] = array_merge( (array) $query['post_type'], array( 'wz_knowledgebase' ) );
			}
		} else {
			$query['post_type'] = array( 'post', 'wz_knowledgebase' );
		}
	}
	return $query;
}
add_filter( 'request', 'wzkb_in_feed', 11 );

