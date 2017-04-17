<?php
/**
 * Knowledgebase
 *
 * Knowledgebase is a simple WordPress plugin that let's you create a knowledgebase
 * or FAQ section on your WordPress website.
 *
 * @package   WZKB
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2009-2016 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name: Knowledgebase
 * Plugin URI: https://github.com/WebberZone/knowledgebase
 * Description: A simple WordPress plugin to create a Knowledgebase.
 * Version: 1.4.0-beta1
 * Author: WebberZone
 * Author URI: https://webberzone.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wzkb
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Holds the filesystem directory path (with trailing slash) for WZKB
 *
 * @since 1.2.0
 *
 * @var string Plugin folder path
 */
if ( ! defined( 'WZKB_PLUGIN_DIR' ) ) {
	define( 'WZKB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Holds the filesystem directory path (with trailing slash) for WZKB
 *
 * @since 1.2.0
 *
 * @var string Plugin folder URL
 */
if ( ! defined( 'WZKB_PLUGIN_URL' ) ) {
	define( 'WZKB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Holds the filesystem directory path (with trailing slash) for WZKB
 *
 * @since 1.2.0
 *
 * @var string Plugin Root File
 */
if ( ! defined( 'WZKB_PLUGIN_FILE' ) ) {
	define( 'WZKB_PLUGIN_FILE', __FILE__ );
}


global $wzkb_options;
$wzkb_options = wzkb_get_settings();


/**
 * Get Settings.
 *
 * Retrieves all plugin settings
 *
 * @since  1.2.0
 * @return array wzkb settings
 */
function wzkb_get_settings() {

	$settings = get_option( 'wzkb_settings' );

	/**
	 * Settings array
	 *
	 * Retrieves all plugin settings
	 *
	 * @since 1.2.0
	 * @param array $settings Settings array
	 */
	return apply_filters( 'wzkb_get_settings', $settings );
}


/*
 ----------------------------------------------------------------------------*
 * Include files
 *----------------------------------------------------------------------------
 */

	require_once WZKB_PLUGIN_DIR . 'includes/admin/register-settings.php';
	require_once WZKB_PLUGIN_DIR . 'public/public.php';
	require_once WZKB_PLUGIN_DIR . 'includes/activate-deactivate.php';
	require_once WZKB_PLUGIN_DIR . 'includes/custom-post-type.php';
	require_once WZKB_PLUGIN_DIR . 'includes/main.php';
	require_once WZKB_PLUGIN_DIR . 'includes/shortcode.php';
	require_once WZKB_PLUGIN_DIR . 'includes/search.php';
	require_once WZKB_PLUGIN_DIR . 'includes/feed.php';


/*
 ----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------
 */

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

	include_once WZKB_PLUGIN_DIR . 'includes/admin/admin.php';
	include_once WZKB_PLUGIN_DIR . 'includes/admin/settings-page.php';
	include_once WZKB_PLUGIN_DIR . 'includes/admin/save-settings.php';
	include_once WZKB_PLUGIN_DIR . 'includes/admin/help-tab.php';

}

