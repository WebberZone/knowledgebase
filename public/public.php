<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       	https://webberzone.com
 * @since      	1.0.0
 *
 * @package    	WZKB
 * @subpackage 	WZKB/public
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Initialises text domain for l10n.
 *
 * @since 1.0.0
 */
function wzkb_lang_init() {
	load_plugin_textdomain( 'wzkb', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wzkb_lang_init' );


/**
 * Register Styles and scripts.
 *
 * @since	1.0.0
 */
function wpkb_enqueue_styles() {

	wp_register_style( 'wzkb_styles', plugin_dir_url( __FILE__ ) . 'css/styles.min.css', false, false );
	wp_register_style( 'wzkb_archive_styles', plugin_dir_url( __FILE__ ) . 'css/archive-styles.min.css' );

}
add_action( 'wp_enqueue_scripts', 'wpkb_enqueue_styles' );


/**
 * Replace the archive temlate for the knowledgebase. Functions archive_template.
 *
 * @since	1.0.0
 *
 * @param	string	$template	Default Archive Template location
 * @return	string	Modified Archive Template location
 */
function wzkb_archive_template( $template ) {
	global $post, $wzkb_path;

	if ( is_post_type_archive( 'wz_knowledgebase' ) ) {

		if ( is_search() ) {
			$template_name = 'search-wz_knowledgebase.php';
		} else {
			$template_name = 'archive-wz_knowledgebase.php';
		}

		if ( '' == locate_template( array( $template_name ) ) ) {
			$template = $wzkb_path . 'public/templates/' . $template_name;
		}
	}

	if ( is_tax( 'wzkb_category' ) && ! is_search() ) {

		$template_name = 'taxonomy-wzkb_category.php';

		if ( '' == locate_template( array( $template_name ) ) ) {
			$template = $wzkb_path . 'public/templates/' . $template_name;
		}
	}

	return $template;
}
add_filter( 'template_include', 'wzkb_archive_template' ) ;


/**
 * For knowledgebase search results, set posts_per_page 10.
 *
 * @since	1.1.0
 *
 * @param	object	$query	The search query object
 * @return	object	$query	Updated search query object
 */
function wzkb_posts_per_search_page( $query ) {

	if ( ! is_admin() && is_search() && $query->query_vars['post_type'] == 'wz_knowledgebase' ) {
		$query->query_vars['posts_per_page'] = 10;
	}

    return $query;
}
add_filter( 'pre_get_posts', 'wzkb_posts_per_search_page' );

