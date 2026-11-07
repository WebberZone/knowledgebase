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
	 * Sanitize args.
	 *
	 * @since 2.3.1
	 *
	 * @param array $args Array of arguments.
	 * @return array Sanitized array of arguments.
	 */
	public static function sanitize_args( $args ): array {
		foreach ( $args as $key => $value ) {
			if ( is_string( $value ) ) {
				switch ( $key ) {
					case 'class':
					case 'className':
					case 'extra_class':
						$classes           = explode( ' ', $value );
						$sanitized_classes = array_map( 'sanitize_html_class', $classes );
						$args[ $key ]      = implode( ' ', $sanitized_classes );
						break;
					default:
						$args[ $key ] = wp_kses_post( $value );
						break;
				}
			}
		}
		return $args;
	}
}
