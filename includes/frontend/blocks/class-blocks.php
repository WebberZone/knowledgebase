<?php
/**
 * WebberZone Snippetz Blocks.
 *
 * @since 2.1.0
 *
 * @package WebberZone\Snippetz
 */

namespace WebberZone\Snippetz\Frontend\Blocks;

use WebberZone\Snippetz\Snippets\Functions;
use WebberZone\Snippetz\Util\Helpers;
use WebberZone\Snippetz\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Blocks class.
 *
 * @since 2.1.0
 */
class Blocks {

	/**
	 * Main constructor.
	 */
	public function __construct() {
		Hook_Registry::add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Register blocks.
	 *
	 * @since 2.1.0
	 */
	public function register_blocks() {

		$blocks = array(
			'snippetz' => 'render_snippetz',
		);

		foreach ( $blocks as $block => $function ) {
			register_block_type(
				__DIR__ . "/build/$block",
				array(
					'render_callback' => array( $this, $function ),
				)
			);
		}
	}

	/**
	 * Render snippetz block.
	 *
	 * @since 2.1.0
	 *
	 * @param array $attributes Block attributes.
	 * @return \WP_Post|string `WP_Post` instance on success or error message on failure.
	 */
	public function render_snippetz( $attributes ) {
		$id    = absint( $attributes['snippetId'] );
		$class = isset( $attributes['className'] ) ? $attributes['className'] : '';

		$snippet_type = Functions::get_snippet_type( get_post( $id ) );

		$output = Functions::get_snippet_content(
			$id,
			array(
				'class'    => $class,
				'is_block' => true,
			)
		);

		return $output;
	}
}
