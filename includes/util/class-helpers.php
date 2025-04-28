<?php
/**
 * Helpers class.
 *
 * @since 2.3.0
 *
 * @package Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Util;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Helpers class.
 *
 * @since 2.3.0
 */
class Helpers {

	/**
	 * Constructor class.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
	}

	/**
	 * Get the link to Better Search homepage.
	 *
	 * @since 2.3.0
	 *
	 * @return string HTML markup.
	 */
	public static function get_credit_link() {

		$output = '<div class="wzkb_credit" style="text-align:center;border-top:1px dotted #000;display:block;margin-top:5px;"><small>';

		/* translators: 1: Opening a tag and Better Search, 2: Closing a tag. */
		$output .= sprintf( __( 'Powered by %1$s plugin%2$s', 'knowledgebase' ), '<a href="https://webberzone.com/plugins/knowledgebase/" rel="nofollow">Knowledge Base', '</a></small></div>' );

		return $output;
	}

	/**
	 * Sanitize args.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Array of arguments.
	 * @return array Sanitized array of arguments.
	 */
	public static function sanitize_args( $args ): array {
		foreach ( $args as $key => $value ) {
			if ( is_string( $value ) ) {
				$args[ $key ] = wp_kses_post( $value );
			}
		}
		return $args;
	}
}
