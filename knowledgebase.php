<?php
/**
 * WebberZone Knowledge Base let's you create a knowledge base
 * or FAQ section on your WordPress website.
 *
 * @package   WebberZone\Knowledge_Base
 * @author    Ajay D'Souza
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2015-2025 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name: WebberZone Knowledge Base
 * Plugin URI: https://github.com/WebberZone/knowledgebase
 * Description: Create a multi-product knowledge base on your WordPress site.
 * Version: 2.3.1
 * Author: WebberZone
 * Author URI: https://webberzone.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: knowledgebase
 * Domain Path: /languages
 */

namespace WebberZone\Knowledge_Base;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'WZKB_VERSION' ) ) {
	/**
	 * Plugin version
	 *
	 * @since 2.3.0
	 *
	 * @var string $wzkb_version Plugin version
	 */
	define( 'WZKB_VERSION', '2.3.1' );
}

if ( ! defined( 'WZKB_PLUGIN_DIR' ) ) {
	/**
	 * Holds the filesystem directory path (with trailing slash) for WZKB
	 *
	 * @since 1.2.0
	 *
	 * @var string $wzkb_plugin_dir Plugin folder path
	 */
	define( 'WZKB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WZKB_PLUGIN_URL' ) ) {
	/**
	 * Holds the filesystem directory path (with trailing slash) for WZKB
	 *
	 * @since 1.2.0
	 *
	 * @var string $wzkb_plugin_url Plugin folder URL
	 */
	define( 'WZKB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'WZKB_PLUGIN_FILE' ) ) {
	/**
	 * Holds the filesystem directory path (with trailing slash) for WZKB
	 *
	 * @since 1.2.0
	 *
	 * @var string $wzkb_plugin_file Plugin Root File
	 */
	define( 'WZKB_PLUGIN_FILE', __FILE__ );
}

// Load the autoloader.
require_once WZKB_PLUGIN_DIR . 'includes/autoloader.php';

if ( ! function_exists( __NAMESPACE__ . '\load' ) ) {
	/**
	 * The main function responsible for returning the one true WebberZone Snippetz instance to functions everywhere.
	 *
	 * @since 2.3.0
	 */
	function load() {
		Main::get_instance();
	}
	add_action( 'plugins_loaded', __NAMESPACE__ . '\load' );
}

// Register the activation hook.
register_activation_hook( __FILE__, __NAMESPACE__ . '\Admin\Activator::activate' );

// Register the deactivation hook.
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\Admin\Activator::deactivate' );

/*
 *----------------------------------------------------------------------------
 * Include files
 *----------------------------------------------------------------------------
 */
require_once WZKB_PLUGIN_DIR . 'includes/options-api.php';
require_once WZKB_PLUGIN_DIR . 'includes/functions.php';

/**
 * WZKB Settings
 *
 * @since 1.5.0
 *
 * @var array WZKB Settings
 */
global $wzkb_settings;
$wzkb_settings = wzkb_get_settings();
