<?php
/**
 * Knowledgebase
 *
 * Knowledgebase is a simple WordPress plugin that let's you create a knowledgebase
 * or FAQ section on your WordPress website.
 *
 * @link			https://webberzone.com
 * @since			1.0.0
 * @package			WZKB
 *
 * @wordpress-plugin
 * Plugin Name:		Knowledgebase
 * Plugin URI:		https://github.com/WebberZone/knowledgebase
 * Description:		A simple WordPress plugin to create a Knowledgebase.
 * Version:			1.1.0-beta20150628
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
 * Fired for each blog when the plugin is activated.
 *
 * @since	1.0.0
 *
 * @param    boolean    $network_wide    True if WPMU superadmin uses
 *                                       "Network Activate" action, false if
 *                                       WPMU is disabled or plugin is
 *                                       activated on an individual blog.
 */
function wzkb_plugin_activate( $network_wide ) {
    global $wpdb;

    if ( is_multisite() && $network_wide ) {

        // Get all blogs in the network and activate plugin on each one
        $blog_ids = $wpdb->get_col( "
        	SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0' AND deleted = '0'
		" );
        foreach ( $blog_ids as $blog_id ) {
        	switch_to_blog( $blog_id );
			wzkb_single_activate();
        }

        // Switch back to the current blog
        restore_current_blog();

    } else {
        wzkb_single_activate();
    }
}
register_activation_hook( __FILE__, 'wzkb_plugin_activate');


/**
 * Runs on Plugin activation.
 *
 * @since	1.1.0
 */
function wzkb_single_activate() {

    // Register types to register the rewrite rules
    wzkb_register_post_type();

    // Then flush them
    global $wp_rewrite;
	$wp_rewrite->init();
	flush_rewrite_rules( false );

}


/**
 * Fired when a new site is activated with a WPMU environment.
 *
 * @since 2.0.0
 *
 * @param    int    $blog_id    ID of the new blog.
 */
function wzkb_activate_new_site( $blog_id ) {

	if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
		return;
	}

	switch_to_blog( $blog_id );
	wzkb_single_activate();
	restore_current_blog();

}
add_action( 'wpmu_new_blog', 'wzkb_activate_new_site' );


/**
 * Runs on Plugin deactivation.
 *
 * @since	1.0.0
 */
function wzkb_plugin_deactivate( $network_wide ) {

    global $wpdb;

    if ( is_multisite() && $network_wide ) {

        // Get all blogs in the network and activate plugin on each one
        $blog_ids = $wpdb->get_col( "
        	SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0' AND deleted = '0'
		" );
        foreach ( $blog_ids as $blog_id ) {
        	switch_to_blog( $blog_id );
        	global $wp_rewrite;
        	$wp_rewrite->init();
			flush_rewrite_rules();
        }

        // Switch back to the current blog
        restore_current_blog();

    }

    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wzkb_plugin_deactivate');


/*----------------------------------------------------------------------------*
 * Include files
 *----------------------------------------------------------------------------*/

	require_once( plugin_dir_path( __FILE__ ) . 'public/public.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/custom-post-type.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/main.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/shortcode.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/search.php' );


/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() || strstr( $_SERVER['PHP_SELF'], 'wp-admin/' ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/admin.php' );

} // End admin.inc

