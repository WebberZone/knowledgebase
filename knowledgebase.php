<?php
/**
 * Knowledgebase
 *
 * Knowledgebase is a simple WordPress plugin that let's you create a knowledgebase
 * or FAQ section on your WordPress website.
 *
 * @link			https://webberzone.com
 * @since			1.0.0-beta20150518
 * @package			WZKB
 *
 * @wordpress-plugin
 * Plugin Name:		Knowledgebase
 * Plugin URI:		https://github.com/WebberZone/knowledgebase
 * Description:		A simple WordPress plugin to create a Knowledgebase.
 * Version:			1.0.0
 * Author:			WebberZone
 * Author URI:		https://webberzone.com
 * License:			GPL-2.0+
 * License URI:		http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:		wzkb
 * Domain Path:		/languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Holds the filesystem directory path (with trailing slash) for WZKB
 *
 * @since	1.0.0
 *
 * @var string
 */
$wzkb_path = plugin_dir_path( __FILE__ );

/**
 * Holds the URL for WZKB
 *
 * @since	1.0.0
 *
 * @var string
 */
$wzkb_url = plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) );


/**
 * Runs on Plugin activation.
 *
 * @since	1.0.0
 */
function wzkb_plugin_activate() {

    // Register types to register the rewrite rules
    wzkb_register_post_type();

    // Then flush them
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wzkb_plugin_activate');


/**
 * Runs on Plugin deactivation.
 *
 * @since	1.0.0
 */
function wzkb_plugin_deactivate() {

    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wzkb_plugin_deactivate');


/*----------------------------------------------------------------------------*
 * Include files
 *----------------------------------------------------------------------------*/

	require_once( plugin_dir_path( __FILE__ ) . 'public/public.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/custom-post-type.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/main.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/shortcode.php' );


/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() || strstr( $_SERVER['PHP_SELF'], 'wp-admin/' ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/admin.php' );

} // End admin.inc

