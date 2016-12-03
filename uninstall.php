<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link  https://webberzone.com
 * @since 1.0.0
 *
 * @package WZKB
 * @subpackage WZKB/Uninstall
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

if ( is_multisite() ) {

	// Get all blogs in the network and activate plugin on each one.
	$blog_ids = $wpdb->get_col( "
		SELECT blog_id FROM $wpdb->blogs
		WHERE archived = '0' AND spam = '0' AND deleted = '0'
	" );

	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		wzkb_delete_data();
		restore_current_blog();
	}
} else {
	wzkb_delete_data();
}


function wzkb_delete_data() {

	$settings = get_option( 'wzkb_settings' );

	if ( $settings['uninstall_options'] ) {

		delete_option( 'wzkb_settings' );

	}

	if ( $settings['uninstall_data'] ) {

		$wzkbs = get_posts( array( 'post_type' => 'wz_knowledgebase' ) );

		foreach ( $wzkbs as $wzkb ) {
			wp_delete_post( $wzkb->ID );
		}
	}

}

