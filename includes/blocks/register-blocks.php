<?php
/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package WZKB
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @since 2.1.0
 */
function wzkb_register_blocks() {
	// Register Knowledge Base block.
	register_block_type_from_metadata(
		__DIR__ . '/build/kb/',
		array(
			'render_callback' => 'render_wzkb_block',
		)
	);
	// Register Knowledge Base Alerts block.
	register_block_type_from_metadata(
		__DIR__ . '/build/alerts/',
		array(
			'render_callback' => 'render_wzkb_alerts_block',
		)
	);
}
add_action( 'init', 'wzkb_register_blocks' );


/**
 * Renders the `knowledgebase/knowledgebase` block on server.
 *
 * @since 2.0.0
 *
 * @param array $attributes The block attributes.
 *
 * @return string Returns the post content with latest posts added.
 */
function render_wzkb_block( $attributes ) {

	// Remap selected attributes from JS to PHP.
	$mappings = array(
		'show_article_count'  => 'showArticleCount',
		'show_excerpt'        => 'showExcerpt',
		'clickable_section'   => 'hasClickableSection',
		'show_empty_sections' => 'showEmptySections',
		'extra_class'         => 'className',
	);

	foreach ( $mappings as $php_attr => $js_attr ) {
		if ( isset( $attributes[ $js_attr ] ) ) {
			$attributes[ $php_attr ] = $attributes[ $js_attr ];
		}
	}

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
 * @since 2.2.1
 *
 * @param array  $attributes The block attributes.
 * @param string $content The block content.
 * @param array  $block The block object.
 *
 * @return string Returns the post content with latest posts added.
 */
function render_wzkb_alerts_block( $attributes, $content, $block ) {
	return wp_kses_post( $content );
}
