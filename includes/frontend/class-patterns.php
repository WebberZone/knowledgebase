<?php
/**
 * Block Patterns Registration for Knowledge Base.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

/**
 * Class Patterns
 *
 * Registers block patterns for Knowledge Base layouts.
 *
 * @since 3.0.0
 */
class Patterns {

	/**
	 * Constructor for Patterns class.
	 */
	public function __construct() {
		Hook_Registry::add_action( 'init', array( $this, 'register_patterns' ) );
		Hook_Registry::add_action( 'init', array( $this, 'register_pattern_categories' ) );
	}

	/**
	 * Register Knowledge Base block pattern categories.
	 *
	 * @since 3.0.0
	 */
	public function register_pattern_categories() {
		register_block_pattern_category(
			'knowledgebase',
			array(
				'label' => __( 'Knowledge Base', 'knowledgebase' ),
			)
		);
	}

	/**
	 * Register Knowledge Base block patterns.
	 *
	 * @since 3.0.0
	 */
	public function register_patterns() {
		$patterns_dir = __DIR__ . '/templates/patterns';

		if ( ! is_dir( $patterns_dir ) ) {
			return;
		}

		$pattern_files = glob( $patterns_dir . '/*.php' );

		if ( empty( $pattern_files ) ) {
			return;
		}

		foreach ( $pattern_files as $pattern_file ) {
			// Validate the file exists and is readable.
			if ( ! file_exists( $pattern_file ) || ! is_readable( $pattern_file ) ) {
				continue;
			}

			// Ensure it's a PHP file (extra safety).
			if ( pathinfo( $pattern_file, PATHINFO_EXTENSION ) !== 'php' ) {
				continue;
			}

			$pattern_data = get_file_data(
				$pattern_file,
				array(
					'title'       => 'Title',
					'slug'        => 'Slug',
					'description' => 'Description',
					'categories'  => 'Categories',
				)
			);

			if ( empty( $pattern_data['slug'] ) ) {
				continue;
			}

			ob_start();
			include $pattern_file;
			$content = ob_get_clean();

			register_block_pattern(
				$pattern_data['slug'],
				array(
					'title'       => $pattern_data['title'],
					'description' => $pattern_data['description'],
					'content'     => $content,
					'categories'  => array_map( 'trim', explode( ',', $pattern_data['categories'] ) ),
				)
			);
		}
	}
}
