<?php
/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package WZKB
 */

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * @since 2.0.0
 */
function wzkb_block_init() {
	// Skip block registration if Gutenberg is not enabled/merged.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$block_js = 'block.min.js';
	wp_register_script( // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
		'wzkb-block-editor',
		plugins_url( $block_js, __FILE__ ),
		array(
			'wp-blocks',
			'wp-i18n',
			'wp-element',
			'wp-components',
			'wp-block-editor',
			'wp-editor',
		),
		filemtime( plugin_dir_path( __FILE__ ) . $block_js )
	);

	wp_register_style(
		'wzkb-block-editor',
		WZKB_PLUGIN_URL . 'includes/public/css/wzkb-styles.min.css',
		array( 'wp-edit-blocks' ),
		'1.0'
	);

	wp_add_inline_style( 'wzkb-block-editor', esc_html( wzkb_get_option( 'custom_css' ) ) );

	register_block_type(
		'knowledgebase/knowledgebase',
		array(
			'editor_script'   => 'wzkb-block-editor',
			'editor_style'    => 'wzkb-block-editor',
			'render_callback' => 'render_wzkb_block',
			'attributes'      => array(
				'className'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'category'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'other_attributes' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
		)
	);

	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations( 'wzkb-block-editor', 'knowledgebase' );
	}
}
add_action( 'init', 'wzkb_block_init' );

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

	$attributes['extra_class'] = $attributes['className'];

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

