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
use WebberZone\Knowledge_Base\Frontend\Related;
use WebberZone\Knowledge_Base\Frontend\Search;

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
		Hook_Registry::add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_settings' ) );
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
			'related'    => 'render_related_block',
			'search'     => 'render_search_block',
		);

		/**
		 * Filter the list of blocks to register.
		 *
		 * Allows Pro (or other extensions) to add additional blocks.
		 *
		 * @since 3.0.0
		 *
		 * @param array $blocks Associative array of block slug => render callback (string method name or callable).
		 */
		$blocks = apply_filters( 'wzkb_register_blocks', $blocks );

		foreach ( $blocks as $block_name => $render_callback ) {
			/**
			 * Filter the path to the block metadata directory for a given block.
			 *
			 * @since 3.0.0
			 *
			 * @param string $path       Default path within the free plugin.
			 * @param string $block_name Block slug.
			 */
			$metadata_path = apply_filters( 'wzkb_block_metadata_path', __DIR__ . "/build/$block_name/", $block_name );

			/**
			 * Filter the render callback for a given block.
			 *
			 * @since 3.0.0
			 *
			 * @param callable $callback   Default callback (instance method on this class) or provided callable.
			 * @param string   $block_name Block slug.
			 * @param object   $instance   This Blocks instance.
			 */
			$default_callback = is_string( $render_callback ) ? array( $this, $render_callback ) : $render_callback;
			$callback         = apply_filters( 'wzkb_block_render_callback', $default_callback, $block_name, $this );

			register_block_type_from_metadata(
				$metadata_path,
				array(
					'render_callback' => $callback,
				)
			);
		}
	}

	/**
	 * Enqueue the editor settings script so KB blocks can access shared options.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public function enqueue_editor_settings() {
		$settings = \wzkb_get_settings();
		$settings = apply_filters( 'wzkb_block_editor_settings', $settings );

		$handle = 'wzkb-editor-settings';
		wp_register_script( $handle, false, array( 'wp-block-editor' ), WZKB_VERSION, true );
		wp_localize_script( $handle, 'wzkbKB', array( 'settings' => $settings ) );
		wp_enqueue_script( $handle );
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

		$wrapper_attributes = get_block_wrapper_attributes();
		$output             = sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attributes,
			wzkb_knowledge( $arguments )
		);

		return $output;
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

		$wrapper_attributes = get_block_wrapper_attributes();
		$output             = sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attributes,
			$list_of_posts
		);

		return $output;
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

	/**
	 * Renders the `knowledgebase/related` block on server.
	 *
	 * @since 3.0.0
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the related articles list.
	 */
	public function render_related_block( $attributes ) {
		// Only display on single KB articles on the frontend.
		if ( ! is_singular( 'wz_knowledgebase' ) && ! wp_is_serving_rest_request() ) {
			return '';
		}

		// Map block attributes (camelCase) to function arguments (snake_case).
		// Sanitize title at source. Allow empty title (user may intentionally leave it blank).
		$title = isset( $attributes['title'] ) ? sanitize_text_field( $attributes['title'] ) : __( 'Related Articles', 'knowledgebase' );

		// Validate heading level against whitelist.
		$heading_level = ! empty( $attributes['headingLevel'] ) ? $attributes['headingLevel'] : 'h3';
		if ( ! in_array( $heading_level, array( 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ) {
			$heading_level = 'h3';
		}

		$args = array(
			'numberposts'  => ! empty( $attributes['limit'] ) ? (int) $attributes['limit'] : 5,
			'post'         => get_post(),
			'show_thumb'   => isset( $attributes['showThumb'] ) ? (bool) $attributes['showThumb'] : true,
			'show_excerpt' => isset( $attributes['showExcerpt'] ) ? (bool) $attributes['showExcerpt'] : false,
			'show_date'    => isset( $attributes['showDate'] ) ? (bool) $attributes['showDate'] : true,
			'title'        => $title,
			'heading_tag'  => $heading_level,
		);

		// Get related articles using the Related class.
		$output = Related::get_related_articles( $args );

		// Show editor notice when no posts found (editor context).
		if ( empty( $output ) && wp_is_serving_rest_request() ) {
			return '<div class="wp-block-notice"><p>' . esc_html__( 'No related articles found. This block displays related articles for the current knowledge base article.', 'knowledgebase' ) . '</p></div>';
		}

		// Wrap output with block wrapper attributes for proper block styling.
		if ( ! empty( $output ) ) {
			$wrapper_attributes = get_block_wrapper_attributes();
			$output             = '<div ' . $wrapper_attributes . '>' . $output . '</div>';
		}

		/**
		 * Filters the related articles block output.
		 *
		 * @since 3.0.0
		 *
		 * @param string $output     Related articles HTML.
		 * @param array  $attributes Block attributes.
		 */
		return apply_filters( 'wzkb_related_block_output', $output, $attributes );
	}

	/**
	 * Renders the `knowledgebase/search` block on server.
	 *
	 * @since 3.0.0
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the search form HTML.
	 */
	public function render_search_block( $attributes ) {
		$wrapper_attributes = get_block_wrapper_attributes();

		// Map block attributes to function arguments.
		// Sanitize user input at source.
		$placeholder = ! empty( $attributes['placeholder'] ) ? sanitize_text_field( $attributes['placeholder'] ) : __( 'Search the knowledgebase…', 'knowledgebase' );
		$button_text = ! empty( $attributes['buttonText'] ) ? sanitize_text_field( $attributes['buttonText'] ) : __( 'Search', 'knowledgebase' );

		$args = array(
			'placeholder' => $placeholder,
			'button_text' => $button_text,
		);

		$form = Search::get_search_form( $args );

		/**
		 * Filters the search form output for the block.
		 *
		 * @since 3.0.0
		 *
		 * @param string $form       Search form HTML.
		 * @param array  $attributes Block attributes.
		 */
		$form = apply_filters( 'wzkb_search_block_form', $form, $attributes );

		$output = sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attributes,
			$form
		);

		return $output;
	}
}
