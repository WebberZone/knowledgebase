<?php
/**
 * Knowledge Base Feed functions
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Feed Class.
 *
 * @since 2.3.0
 */
class Feed {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'request', array( $this, 'in_feed' ), 11 );
	}

	/**
	 * Include KB articles in the main feed.
	 *
	 * @since 2.3.0
	 *
	 * @param object $query Query object.
	 * @return object Filtered Query
	 */
	public function in_feed( $query ) {
		if ( isset( $query['feed'] ) && wzkb_get_option( 'include_in_feed', false ) ) {
			if ( isset( $query['post_type'] ) ) {

				if ( isset( $_SERVER['REQUEST_URI'] ) && false !== strpos( esc_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ), wzkb_get_option( 'kb_slug' ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
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
}
