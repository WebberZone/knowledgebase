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

	wp_register_style( 'wpkb_styles', plugin_dir_url( __FILE__ ) . 'css/styles.css', false, false );

}
add_action( 'wp_enqueue_scripts', 'wpkb_enqueue_styles' );


/**
 * Replace the archive temlate for the knowledgebase. Functions archive_template.
 *
 * @since	1.0.0
 *
 * @param	string	$archive_template	Default Archive Template location
 * @return	string	Modified Archive Template location
 */
function wzkb_archive_template( $archive_template ) {
	global $post;

	if ( is_post_type_archive ( 'wz_knowledgebase' ) ) {
		$archive_template = plugin_dir_path( __FILE__ ) . 'public/templates/archive-template.php';
	}
	return $archive_template;
}
//add_filter( 'archive_template', 'wzkb_archive_template' ) ;


