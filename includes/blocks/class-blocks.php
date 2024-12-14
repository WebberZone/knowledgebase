<?php
/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package WebberZone\KnowledgeBase
 */

namespace WebberZone\Knowledge_Base\Blocks;

use WebberZone\Knowledge_Base\Frontend\Display;
use WebberZone\Knowledge_Base\Frontend\Breadcrumbs;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle Knowledge Base blocks registration and rendering.
 *
 * @since 2.3.0
 */
class Blocks {

	/**
	 * Initialize the class and set up hooks
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 * Behind the scenes, it registers also all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @since 2.3.0
	 */
	public function register_blocks() {
		$blocks = array(
			'kb'          => 'render_kb_block',
			'alerts'      => 'render_alerts_block',
			'kb-articles' => 'render_articles_block',
			'breadcrumb'  => 'render_breadcrumb_block',
		);

		foreach ( $blocks as $block_name => $render_callback ) {
			register_block_type_from_metadata(
				__DIR__ . "/build/$block_name/",
				array(
					'render_callback' => array( $this, $render_callback ),
				)
			);
		}
	}

	/**
	 * Maps JavaScript attribute names to PHP attribute names.
	 *
	 * @since 2.3.0
	 *
	 * @param array $attributes   The block attributes.
	 * @param array $mappings     Array of mappings with PHP attributes as keys and JS attributes as values.
	 * @return array             Modified attributes array with mapped values.
	 */
	private function map_attributes( $attributes, $mappings ) {
		foreach ( $mappings as $php_attr => $js_attr ) {
			if ( isset( $attributes[ $js_attr ] ) ) {
				$attributes[ $php_attr ] = $attributes[ $js_attr ];
			}
		}
		return $attributes;
	}

	/**
	 * Renders the `knowledgebase/knowledgebase` block on server.
	 *
	 * @since 2.3.0
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the post content with latest posts added.
	 */
	public function render_kb_block( $attributes ) {
		// Remap selected attributes from JS to PHP.
		$mappings = array(
			'show_article_count'  => 'showArticleCount',
			'show_excerpt'        => 'showExcerpt',
			'clickable_section'   => 'hasClickableSection',
			'show_empty_sections' => 'showEmptySections',
			'extra_class'         => 'className',
		);

		$attributes = $this->map_attributes( $attributes, $mappings );

		$arguments = array_merge(
			$attributes,
			array(
				'is_block' => 1,
			)
		);

		$arguments = wp_parse_args( $attributes['other_attributes'], $arguments );

		/**
		 * Filters arguments passed to get_wzkb for the block.
		 *
		 * @since 2.0.0
		 *
		 * @param array $arguments  Knowledge Base block options array.
		 * @param array $attributes Block attributes array.
		 */
		$arguments = apply_filters( 'wzkb_block_options', $arguments, $attributes );

		return wzkb_knowledge( $arguments );
	}

	/**
	 * Renders the `knowledgebase/alerts` block on server.
	 *
	 * @since 2.3.0
	 *
	 * @param array  $attributes The block attributes.
	 * @param string $content The block content.
	 *
	 * @return string Returns the post content with latest posts added.
	 */
	public function render_alerts_block( $attributes, $content ) {
		return wp_kses_post( $content );
	}

	/**
	 * Renders the `knowledgebase/articles` block on server.
	 *
	 * @since 2.3.0
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the post content with latest posts added.
	 */
	public function render_articles_block( $attributes ) {
		$mappings = array(
			'term_id'      => 'termID',
			'show_excerpt' => 'showExcerpt',
		);

		$attributes = $this->map_attributes( $attributes, $mappings );

		$limit = (int) ( ! empty( $attributes['limit'] ) ? $attributes['limit'] : wzkb_get_option( 'limit', 5 ) );

		$show_excerpt = isset( $attributes['show_excerpt'] ) ? (bool) $attributes['show_excerpt'] : false;

		if ( empty( $attributes['term_id'] ) ) {
			return '';
		}

		$term = get_term( (int) $attributes['term_id'], 'wzkb_category' );

		if ( empty( $term ) || is_wp_error( $term ) ) {
			return '';
		}

		$list_of_posts = Display::get_posts_by_term(
			$term,
			0,
			array(
				'show_excerpt' => $show_excerpt,
				'limit'        => $limit,
			)
		);

		if ( empty( $list_of_posts ) ) {
			return __( 'No articles found.', 'knowledgebase' );
		}

		return $list_of_posts;
	}

	/**
	 * Render the block.
	 *
	 * @since 2.3.0
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @return string Rendered block output.
	 */
	public static function render_breadcrumb_block( $attributes ) {
		$wrapper_attributes = get_block_wrapper_attributes();

		$output = sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attributes,
			wzkb_get_breadcrumb( $attributes )
		);

		return $output;
	}
}
