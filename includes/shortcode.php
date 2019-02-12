<?php
/**
 * Knowledge Base Shortcodes
 *
 * @link  https://webberzone.com
 * @since 1.0.0
 *
 * @package    WZKB
 * @subpackage WZKB/shortcode
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Create the shortcode to display the KB using [knowledgebase].
 *
 * @since 1.0.0
 *
 * @param  array  $atts    Shortcode attributes array.
 * @param  string $content Content to wrap in the Shortcode.
 * @return string $output Formatted shortcode output
 */
function wzkb_shortcode( $atts, $content = null ) {

	wp_enqueue_style( 'wzkb_styles' );
	wp_enqueue_style( 'dashicons' );

	$atts = shortcode_atts(
		array(
			'category' => false,
		),
		$atts,
		'knowledgebase'
	);

	$output = wzkb_knowledge( $atts );

	/**
	 * Filters knowledgebase shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @return string $output  Formatted shortcode output
	 * @param  array $att  Shortcode attributes array
	 * @param  string $content Content to wrap in the Shortcode
	 */
	return apply_filters( 'wzkb_shortcode', $output, $atts, $content );
}
add_shortcode( 'knowledgebase', 'wzkb_shortcode' );


/**
 * Create the shortcode to display the search form using [kbsearch].
 *
 * @since 1.2.0
 *
 * @param  array  $atts    Shortcode attributes array.
 * @param  string $content Content to wrap in the Shortcode.
 * @return string $output Formatted shortcode output
 */
function wzkb_shortcode_search( $atts, $content = null ) {

	$atts = shortcode_atts(
		array(
			'echo' => false,
		),
		$atts,
		'kbsearch'
	);

	$output = wzkb_get_search_form( $atts['echo'] );

	/**
	 * Filters knowledge base search form shortcode.
	 *
	 * @since 1.2.0
	 *
	 * @return string $output  Formatted shortcode output
	 * @param  array $att  Shortcode attributes array
	 * @param  string $content Content to wrap in the Shortcode
	 */
	return apply_filters( 'wzkb_shortcode_search', $output, $atts, $content );
}
add_shortcode( 'kbsearch', 'wzkb_shortcode_search' );


/**
 * Create the shortcode to display the breadcrumb using [kbbreadcrumb].
 *
 * @since 1.6.0
 *
 * @param  array  $atts    Shortcode attributes array.
 * @param  string $content Content to wrap in the Shortcode.
 * @return string $output Formatted shortcode output
 */
function wzkb_shortcode_breadcrumb( $atts, $content = null ) {

	$atts = shortcode_atts(
		array(
			'separator' => ' &raquo; ', // Separator.
		),
		$atts,
		'kbbreadcrumb'
	);

	$output = wzkb_get_breadcrumb( $atts );

	/**
	 * Filters knowledge base breadcrumb shortcode.
	 *
	 * @since 1.6.0
	 *
	 * @return string $output  Formatted shortcode output
	 * @param  array $att  Shortcode attributes array
	 * @param  string $content Content to wrap in the Shortcode
	 */
	return apply_filters( 'wzkb_shortcode_breadcrumb', $output, $atts, $content );
}
add_shortcode( 'kbbreadcrumb', 'wzkb_shortcode_breadcrumb' );


/**
 * Create the shortcode to display alerts using [kbalert].
 *
 * @since 1.7.0
 *
 * @param  array  $atts    Shortcode attributes array.
 * @param  string $content Content to wrap in the Shortcode.
 * @return string $output Formatted shortcode output
 */
function wzkb_shortcode_alert( $atts, $content = null ) {

	wp_enqueue_style( 'wzkb_styles' );

	$atts = shortcode_atts(
		array(
			'type'  => 'primary',
			'class' => 'alert',
			'text'  => '',
		),
		$atts,
		'kbalert'
	);

	$output = wzkb_get_alert( $atts, $content );

	/**
	 * Filters knowledge base breadcrumb shortcode.
	 *
	 * @since 1.7.0
	 *
	 * @return string $output  Formatted shortcode output
	 * @param  array $att  Shortcode attributes array
	 * @param  string $content Content to wrap in the Shortcode
	 */
	return apply_filters( 'wzkb_shortcode_alert', $output, $atts, $content );
}
add_shortcode( 'kbalert', 'wzkb_shortcode_alert' );


