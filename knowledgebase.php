<?php
/**
 * WebberZone Knowledge Base
 *
 * WebberZone Knowledge Base let's you create a knowledge base
 * or FAQ section on your WordPress website.
 *
 * @package   WZKB
 * @author    Ajay D'Souza
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2015-2024 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name: WebberZone Knowledge Base
 * Plugin URI: https://github.com/WebberZone/knowledgebase
 * Description: Fastest way to create a highly-flexible multi-product knowledge base.
 * Version: 2.2.1
 * Author: WebberZone
 * Author URI: https://webberzone.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: knowledgebase
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
 * @var string $wzkb_plugin_dir Plugin folder path
 */
if ( ! defined( 'WZKB_PLUGIN_DIR' ) ) {
	define( 'WZKB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Holds the filesystem directory path (with trailing slash) for WZKB
 *
 * @since 1.2.0
 *
 * @var string $wzkb_plugin_url Plugin folder URL
 */
if ( ! defined( 'WZKB_PLUGIN_URL' ) ) {
	define( 'WZKB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Holds the filesystem directory path (with trailing slash) for WZKB
 *
 * @since 1.2.0
 *
 * @var string $wzkb_plugin_file Plugin Root File
 */
if ( ! defined( 'WZKB_PLUGIN_FILE' ) ) {
	define( 'WZKB_PLUGIN_FILE', __FILE__ );
}


/*
 *----------------------------------------------------------------------------
 * Include files
 *----------------------------------------------------------------------------
 */

	require_once WZKB_PLUGIN_DIR . 'includes/admin/settings/class-settings-api.php';
	require_once WZKB_PLUGIN_DIR . 'includes/admin/settings/class-knowledgebase-settings.php';
	require_once WZKB_PLUGIN_DIR . 'includes/admin/settings/options-api.php';
	require_once WZKB_PLUGIN_DIR . 'includes/public/public.php';
	require_once WZKB_PLUGIN_DIR . 'includes/public/related.php';
	require_once WZKB_PLUGIN_DIR . 'includes/activate-deactivate.php';
	require_once WZKB_PLUGIN_DIR . 'includes/custom-post-type.php';
	require_once WZKB_PLUGIN_DIR . 'includes/main.php';
	require_once WZKB_PLUGIN_DIR . 'includes/shortcode.php';
	require_once WZKB_PLUGIN_DIR . 'includes/search.php';
	require_once WZKB_PLUGIN_DIR . 'includes/feed.php';
	require_once WZKB_PLUGIN_DIR . 'includes/breadcrumbs.php';
	require_once WZKB_PLUGIN_DIR . 'includes/widgets/class-wzkb-breadcrumb-widget.php';
	require_once WZKB_PLUGIN_DIR . 'includes/widgets/class-wzkb-sections-widget.php';
	require_once WZKB_PLUGIN_DIR . 'includes/widgets/class-wzkb-articles-widget.php';
	require_once WZKB_PLUGIN_DIR . 'includes/blocks/register-blocks.php';
	require_once WZKB_PLUGIN_DIR . 'includes/deprecated.php';


/*
 *----------------------------------------------------------------------------
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------
 */

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

	include_once WZKB_PLUGIN_DIR . 'includes/admin/admin.php';
	include_once WZKB_PLUGIN_DIR . 'includes/admin/modules/cache.php';

}

/**
 * WZKB Settings
 *
 * @since 1.5.0
 *
 * @var array WZKB Settings
 */
global $wzkb_settings;
$wzkb_settings = wzkb_get_settings();
