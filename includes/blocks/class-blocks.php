<?php
/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package WebberZone\KnowledgeBase
 */

namespace WebberZone\Knowledge_Base\Blocks;

use WebberZone\Knowledge_Base\Util\Hook_Registry;
use WebberZone\Knowledge_Base\Frontend\Display;

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
		Hook_Registry::add_action( 'init', array( $this, 'register_blocks' ) );
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
			'kb'         => 'render_kb_block',
			'alerts'     => 'render_alerts_block',
			'articles'   => 'render_articles_block',
			'breadcrumb' => 'render_breadcrumb_block',
			'sections'   => 'render_sections_block',
			'products'   => 'render_products_block',
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
				unset( $attributes[ $js_attr ] );
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
		 * Filters arguments passed to wzkb_knowledge for the block.
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
			'term_id'       => 'termID',
			'show_excerpt'  => 'showExcerpt',
			'show_heading'  => 'showHeading',
			'heading_level' => 'headingLevel',
		);

		$attributes = $this->map_attributes( $attributes, $mappings );

		$limit = (int) ( ! empty( $attributes['limit'] ) ? $attributes['limit'] : wzkb_get_option( 'limit', 5 ) );

		$show_excerpt = isset( $attributes['show_excerpt'] ) ? (bool) $attributes['show_excerpt'] : false;

		if ( empty( $attributes['term_id'] ) ) {
			return __( 'Enter a section ID.', 'knowledgebase' );
		}

		$term = get_term( (int) $attributes['term_id'], 'wzkb_category' );

		if ( empty( $term ) || is_wp_error( $term ) ) {
			return __( 'Section not found.', 'knowledgebase' );
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

		$show_heading  = isset( $attributes['show_heading'] ) ? (bool) $attributes['show_heading'] : true;
		$heading_level = isset( $attributes['heading_level'] ) ? sanitize_html_class( $attributes['heading_level'] ) : 'h2';

		if ( $show_heading ) {
			$heading       = $term->name;
			$list_of_posts = '<' . $heading_level . '>' . esc_html( $heading ) . '</' . $heading_level . '>' . $list_of_posts;
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

	/**
	 * Renders the `knowledgebase/sections` block on server.
	 *
	 * @since 2.3.0
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the sections list.
	 */
	public function render_sections_block( $attributes ) {
		$mappings = array(
			'term_id'        => 'termID',
			'before_li_item' => 'beforeLiItem',
			'after_li_item'  => 'afterLiItem',
		);

		$attributes = $this->map_attributes( $attributes, $mappings );

		$arguments = array(
			'is_block'       => 1,
			'depth'          => ( ! empty( $attributes['depth'] ) ) ? (int) $attributes['depth'] : 0,
			'before_li_item' => ( ! empty( $attributes['before_li_item'] ) ) ? $attributes['before_li_item'] : '',
			'after_li_item'  => ( ! empty( $attributes['after_li_item'] ) ) ? $attributes['after_li_item'] : '',
		);

		$term_id = ! empty( $attributes['term_id'] ) ? (int) $attributes['term_id'] : 0;

		/**
		 * Filters arguments passed to wzkb_categories_list for the block.
		 *
		 * @since 2.3.0
		 *
		 * @param array $arguments  Knowledge Base block options array.
		 * @param array $attributes Block attributes array.
		 */
		$arguments = apply_filters( 'wzkb_sections_block_options', $arguments, $attributes );

		$wrapper_attributes = get_block_wrapper_attributes();

		$categories_list = wzkb_categories_list( $term_id, 0, $arguments );

		if ( empty( $categories_list ) && wp_is_serving_rest_request() ) {
			return __( 'No sections found. This message is only displayed in the editor and not on the frontend.', 'knowledgebase' );
		}

		$output = sprintf(
			'<div %1$s>%2$s%3$s</div>',
			$wrapper_attributes,
			! empty( $attributes['title'] ) ? '<h2>' . esc_html( $attributes['title'] ) . '</h2>' : '',
			$categories_list
		);

		return $output;
	}

	/**
	 * Renders the `knowledgebase/products` block on server.
	 *
	 * @since 3.0.0
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the rendered product sections list.
	 */
	public function render_products_block( $attributes ) {
		$mappings = array(
			'product_id'     => 'productId',
			'depth'          => 'depth',
			'before_li_item' => 'beforeLiItem',
			'after_li_item'  => 'afterLiItem',
		);

		$attributes = $this->map_attributes( $attributes, $mappings );

		$arguments = array(
			'is_block'       => 1,
			'depth'          => ( ! empty( $attributes['depth'] ) ) ? (int) $attributes['depth'] : 0,
			'before_li_item' => ( ! empty( $attributes['before_li_item'] ) ) ? $attributes['before_li_item'] : '',
			'after_li_item'  => ( ! empty( $attributes['after_li_item'] ) ) ? $attributes['after_li_item'] : '',
		);

		$product_id = ! empty( $attributes['product_id'] ) ? (int) $attributes['product_id'] : 0;

		/**
		 * Filters arguments passed to wzkb_get_product_sections_list for the block.
		 *
		 * @since 3.0.0
		 *
		 * @param array $arguments  Knowledge Base block options array.
		 * @param array $attributes Block attributes array.
		 */
		$arguments = apply_filters( 'wzkb_products_block_options', $arguments, $attributes );

		$wrapper_attributes = get_block_wrapper_attributes();

		$output = sprintf(
			'<div %1$s>%2$s%3$s</div>',
			$wrapper_attributes,
			! empty( $attributes['title'] ) ? '<h2 class="wzkb-products-title">' . esc_html( $attributes['title'] ) . '</h2>' : '',
			wzkb_get_product_sections_list( $product_id, $arguments )
		);

		return $output;
	}
}
