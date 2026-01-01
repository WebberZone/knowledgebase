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
	 * Centralized attribute mappings for blocks.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	private static $block_mappings = array(
		'kb'       => array(
			'show_article_count'  => 'showArticleCount',
			'show_excerpt'        => 'showExcerpt',
			'clickable_section'   => 'hasClickableSection',
			'show_empty_sections' => 'showEmptySections',
			'extra_class'         => 'className',
			'product'             => 'productId',
			'show_heading'        => 'showHeading',
			'link_heading'        => 'linkHeading',
			'heading_level'       => 'headingLevel',
		),
		'articles' => array(
			'term_id'       => 'termID',
			'product_id'    => 'productId',
			'show_excerpt'  => 'showExcerpt',
			'show_heading'  => 'showHeading',
			'link_heading'  => 'linkHeading',
			'heading_level' => 'headingLevel',
		),
		'sections' => array(
			'term_id'        => 'termID',
			'before_li_item' => 'beforeLiItem',
			'after_li_item'  => 'afterLiItem',
		),
		'products' => array(
			'product_id'     => 'productId',
			'before_li_item' => 'beforeLiItem',
			'after_li_item'  => 'afterLiItem',
		),
	);

	/**
	 * Initialize the class and set up hooks
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'init', array( $this, 'register_blocks' ) );
		Hook_Registry::add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_settings' ) );
		Hook_Registry::add_filter( 'block_categories_all', array( $this, 'register_block_category' ) );
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
	 * Register custom block category for Knowledge Base blocks.
	 *
	 * @since 3.0.0
	 *
	 * @param array $categories Array of block categories.
	 * @return array Modified array of block categories.
	 */
	public function register_block_category( $categories ) {

		$insert_after = 'widgets';
		$position     = 0;

		foreach ( $categories as $index => $category ) {
			if ( isset( $category['slug'] ) && $insert_after === $category['slug'] ) {
				$position = $index + 1;
				break;
			}
		}

		// Insert the custom category at the desired position.
		$custom_category = array(
			'slug'  => 'knowledgebase',
			'title' => __( 'Knowledge Base', 'knowledgebase' ),
			'icon'  => 'book',
		);

		array_splice( $categories, $position, 0, array( $custom_category ) );

		return $categories;
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
	 * Map JavaScript attributes to PHP attributes.
	 *
	 * @since 2.3.0
	 *
	 * @param array  $attributes Block attributes array.
	 * @param array  $mappings  Attribute mappings array.
	 * @param string $block_type Block type for using centralized mappings.
	 * @return array             Modified attributes array with mapped values.
	 */
	private function map_attributes( $attributes, $mappings = array(), $block_type = '' ) {
		// Use centralized mappings if block_type is provided and mappings are not.
		if ( ! empty( $block_type ) && empty( $mappings ) && isset( self::$block_mappings[ $block_type ] ) ) {
			$mappings = self::$block_mappings[ $block_type ];
		}

		foreach ( $mappings as $php_attr => $js_attr ) {
			if ( isset( $attributes[ $js_attr ] ) ) {
				$attributes[ $php_attr ] = $attributes[ $js_attr ];
				if ( $php_attr !== $js_attr ) {
					unset( $attributes[ $js_attr ] );
				}
			}
		}
		return $attributes;
	}

	/**
	 * Generate heading HTML for blocks.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $category     Category ID.
	 * @param int    $product      Product ID.
	 * @param bool   $show_heading Whether to show heading.
	 * @param bool   $link_heading Whether to link heading.
	 * @param string $heading_level Heading level (h1-h6, p).
	 * @return string Heading HTML or empty string.
	 */
	private function generate_block_heading( $category, $product, $show_heading, $link_heading, $heading_level ) {
		if ( ! $show_heading ) {
			return '';
		}

		// Fetch term based on category or product.
		if ( 0 !== $category ) {
			$term = get_term( $category, 'wzkb_category' );
		} elseif ( 0 !== $product ) {
			$term = get_term( $product, 'wzkb_product' );
		} else {
			$term = null;
		}

		// Check if term is valid.
		if ( is_wp_error( $term ) || ! $term ) {
			return '';
		}

		// Create heading content.
		$term_name   = esc_html( $term->name );
		$heading_tag = \in_array( $heading_level, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p' ), true ) ? $heading_level : 'h2';

		if ( $link_heading ) {
			$term_link       = get_term_link( $term );
			$heading_content = ! is_wp_error( $term_link ) ? '<a href="' . esc_url( $term_link ) . '">' . $term_name . '</a>' : $term_name;
		} else {
			$heading_content = $term_name;
		}

		return sprintf(
			'<%1$s>%2$s</%1$s>',
			$heading_tag,
			$heading_content
		);
	}

	/**
	 * Render a block with heading and wrapper.
	 *
	 * @since 3.0.0
	 *
	 * @param callable $content_renderer Callable that returns the block content.
	 * @param array    $heading_args    Heading arguments.
	 * @param array    $wrapper_attrs   Block wrapper attributes.
	 * @return string Rendered block output.
	 */
	private function render_block_with_heading( $content_renderer, $heading_args, $wrapper_attrs = array() ) {
		// Generate heading HTML.
		$heading_html = $this->generate_block_heading(
			$heading_args['category'],
			$heading_args['product'],
			$heading_args['show_heading'],
			$heading_args['link_heading'],
			$heading_args['heading_level']
		);

		// Check for invalid heading target with clearer intent.
		$invalid_heading_target =
			$heading_args['show_heading']
			&& empty( $heading_html )
			&& ( 0 !== $heading_args['category'] || 0 !== $heading_args['product'] );

		if ( $invalid_heading_target ) {
			return esc_html__( 'Invalid section or product selected.', 'knowledgebase' );
		}

		// Get content from renderer.
		$content = call_user_func( $content_renderer );

		// Prepend heading if it exists.
		if ( ! empty( $heading_html ) ) {
			$content = $heading_html . $content;
		}

		// Get wrapper attributes if not provided.
		if ( empty( $wrapper_attrs ) ) {
			$wrapper_attrs = get_block_wrapper_attributes();
		}

		return sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attrs,
			$content
		);
	}

	/**
	 * Build arguments for KB block.
	 *
	 * @since 3.0.0
	 *
	 * @param array $attributes Block attributes.
	 * @return array Built arguments array.
	 */
	private function build_kb_arguments( $attributes ) {
		$args = array_merge(
			$attributes,
			array(
				'is_block' => 1,
			)
		);

		$args = wp_parse_args( $attributes['other_attributes'], $args );

		// Convert category and product to integers if set.
		if ( isset( $args['category'] ) ) {
			$args['category'] = intval( $args['category'] );
		}
		if ( isset( $args['product'] ) ) {
			$args['product'] = intval( $args['product'] );
		}

		// Auto-detect context when block is used in templates without explicit attributes.
		if ( empty( $args['category'] ) && empty( $args['product'] ) ) {
			$queried_object = get_queried_object();

			// Check if we're on a taxonomy page.
			if ( $queried_object instanceof \WP_Term ) {
				if ( 'wzkb_category' === $queried_object->taxonomy ) {
					$args['category'] = -1; // Auto-detect current category.
				} elseif ( 'wzkb_product' === $queried_object->taxonomy ) {
					$args['product'] = -1; // Auto-detect current product.
				}
			}
		}

		// Normalize layout rules via Display.
		$args = Display::normalize_arguments( $args );

		return $args;
	}

	/**
	 * Build arguments for Articles block.
	 *
	 * @since 3.0.0
	 *
	 * @param array $attributes Block attributes.
	 * @return array Built arguments array.
	 */
	private function build_articles_arguments( $attributes ) {
		$args = array(
			'category'     => isset( $attributes['term_id'] ) ? (int) $attributes['term_id'] : 0,
			'product'      => isset( $attributes['product_id'] ) ? (int) $attributes['product_id'] : 0,
			'show_excerpt' => isset( $attributes['show_excerpt'] ) ? (bool) $attributes['show_excerpt'] : false,
			'limit'        => isset( $attributes['limit'] ) ? (int) $attributes['limit'] : -1,
			'depth'        => isset( $attributes['depth'] ) ? (int) $attributes['depth'] : -1,
			'is_block'     => 1,
		);

		// Normalize layout rules via Display.
		$args = Display::normalize_arguments( $args );

		return $args;
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
		// Remap selected attributes from JS to PHP using centralized mappings.
		$attributes = $this->map_attributes( $attributes, array(), 'kb' );

		// Build arguments using dedicated method.
		$arguments = $this->build_kb_arguments( $attributes );

		/**
		 * Filters arguments passed to wzkb_knowledge for the block.
		 *
		 * @since 2.0.0
		 *
		 * @param array $arguments  Knowledge Base block options array.
		 * @param array $attributes Block attributes array.
		 */
		$arguments = apply_filters( 'wzkb_block_options', $arguments, $attributes );

		// Prepare heading arguments with null coalescing for safety.
		$heading_args = array(
			'category'      => $arguments['category'],
			'product'       => $arguments['product'],
			'show_heading'  => $attributes['show_heading'] ?? false,
			'link_heading'  => $attributes['link_heading'] ?? false,
			'heading_level' => $attributes['heading_level'] ?? 'h2',
		);

		// Create content renderer.
		$content_renderer = function () use ( $arguments ) {
			return wzkb_knowledge( $arguments );
		};

		// Render using unified helper.
		return $this->render_block_with_heading( $content_renderer, $heading_args );
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
		$attributes = $this->map_attributes( $attributes, array(), 'articles' );

		// Build arguments using dedicated method.
		$kb_args = $this->build_articles_arguments( $attributes );

		// Exit if both term_id and product_id are empty.
		if ( 0 === $kb_args['category'] && 0 === $kb_args['product'] ) {
			return esc_html__( 'Please select a Product or Section.' );
		}

		// Prepare heading arguments with null coalescing for safety.
		$heading_args = array(
			'category'      => $kb_args['category'],
			'product'       => $kb_args['product'],
			'show_heading'  => $attributes['show_heading'] ?? false,
			'link_heading'  => $attributes['link_heading'] ?? false,
			'heading_level' => $attributes['heading_level'] ?? 'h2',
		);

		// Create content renderer.
		$content_renderer = function () use ( $kb_args ) {
			return wzkb_knowledge( $kb_args );
		};

		// Render using unified helper.
		return $this->render_block_with_heading( $content_renderer, $heading_args );
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
	public function render_breadcrumb_block( $attributes ) {
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
		$attributes = $this->map_attributes( $attributes, array(), 'sections' );

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

		$sections_output = Display::get_sections_tree( $term_id, $arguments );

		if ( empty( $sections_output ) && wp_is_serving_rest_request() ) {
			return __( 'No sections found. This message is only displayed in the editor and not on the frontend.', 'knowledgebase' );
		}

		$output = sprintf(
			'<div %1$s>%2$s%3$s</div>',
			$wrapper_attributes,
			! empty( $attributes['title'] ) ? '<h2>' . esc_html( $attributes['title'] ) . '</h2>' : '',
			$sections_output
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
		$attributes = $this->map_attributes( $attributes, array(), 'products' );

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

		$content = ( $product_id > 0 ) ? wzkb_get_product_sections_list( $product_id, $arguments ) : Display::get_sections_tree( 0, $arguments );

		$output = sprintf(
			'<div %1$s>%2$s%3$s</div>',
			$wrapper_attributes,
			! empty( $attributes['title'] ) ? '<h2 class="wzkb-products-title">' . esc_html( $attributes['title'] ) . '</h2>' : '',
			$content
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
