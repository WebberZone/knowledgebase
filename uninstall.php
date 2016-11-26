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

$settings = get_option( 'wzkb_settings' );

if ( $settings['uninstall_options'] ) {

	delete_option( 'wzkb_settings' );

}

if ( $settings['uninstall_data'] ) {

	$wzkbs = get_posts( array( 'post_type' => 'products' ) );

	foreach ( $wzkbs as $wzkb ) {
		wp_delete_post( $wzkb->ID );
	}
}

