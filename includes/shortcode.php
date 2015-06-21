<?php
/**
 * Knowledgebase Shortcode.
 *
 * @link       	https://webberzone.com
 * @since      	1.0.0
 *
 * @package    	WZKB
 * @subpackage 	WZKB/shortcode
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Create the shortcode [knowledgebase].
 *
 * @since	1.0.0
 *
 * @param	array	$att		Shortcode attributes array
 * @param	string	$content	Content to wrap in the Shortcode
 * @return	$output	Formatted shortcode output
 */
function wzkb_shortcode( $atts, $content = null ) {

	wp_enqueue_style( 'wzkb_styles' );
	wp_enqueue_style( 'dashicons' );

    $atts = shortcode_atts( array(
		'category' => false,		// Create a knowledgebase for subcategories of this parent ID
	), $atts, 'knowledgebase' );

	$output = wzkb_knowledge( $atts );

	return apply_filters( 'wzkb_shortcode', $output, $atts );
}
add_shortcode( 'knowledgebase', 'wzkb_shortcode' );
