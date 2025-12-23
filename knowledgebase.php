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
 * Version: 3.0.0-beta2
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
	define( 'WZKB_VERSION', '3.0.0-beta2' );
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

if ( ! defined( 'WZKB_DEFAULT_THUMBNAIL_URL' ) ) {
	/**
	 * Holds the default thumbnail URL for Knowledge Base.
	 *
	 * @since 3.0.0
	 *
	 * @var string $wzkb_default_thumbnail_url Default thumbnail URL.
	 */
	define( 'WZKB_DEFAULT_THUMBNAIL_URL', WZKB_PLUGIN_URL . 'includes/frontend/images/default-thumb.png' );
}


if ( ! function_exists( __NAMESPACE__ . '\wzkb_deactivate_other_instances' ) ) {
	/**
	 * Deactivate other instances of WZKB when this plugin is activated.
	 *
	 * @param string $plugin The plugin being activated.
	 * @param bool   $network_wide Whether the plugin is being activated network-wide.
	 */
	function wzkb_deactivate_other_instances( $plugin, $network_wide = false ) {
		$free_plugin = 'knowledgebase/knowledgebase.php';
		$pro_plugin  = 'knowledgebase-pro/knowledgebase.php';

		// Only proceed if one of our plugins is being activated.
		if ( ! in_array( $plugin, array( $free_plugin, $pro_plugin ), true ) ) {
			return;
		}

		$plugins_to_deactivate = array();
		$deactivated_plugin    = '';

		// If pro is being activated, deactivate free.
		if ( $pro_plugin === $plugin ) {
			if ( is_plugin_active( $free_plugin ) || ( $network_wide && is_plugin_active_for_network( $free_plugin ) ) ) {
				$plugins_to_deactivate[] = $free_plugin;
				$deactivated_plugin      = 'WebberZone Knowledge Base';
			}
		}

		// If free is being activated, deactivate pro.
		if ( $free_plugin === $plugin ) {
			if ( is_plugin_active( $pro_plugin ) || ( $network_wide && is_plugin_active_for_network( $pro_plugin ) ) ) {
				$plugins_to_deactivate[] = $pro_plugin;
				$deactivated_plugin      = 'WebberZone Knowledge Base Pro';
			}
		}

		if ( ! empty( $plugins_to_deactivate ) ) {
			deactivate_plugins( $plugins_to_deactivate, false, $network_wide );
			set_transient( 'wzkb_deactivated_notice', $deactivated_plugin, 1 * HOUR_IN_SECONDS );
		}
	}
	add_action( 'activated_plugin', __NAMESPACE__ . '\wzkb_deactivate_other_instances', 10, 2 );
}

// Show admin notice about automatic deactivation.
if ( ! has_action( 'admin_notices', __NAMESPACE__ . '\wzkb_show_deactivation_notice' ) ) {
	add_action(
		'admin_notices',
		function () {
			$deactivated_plugin = get_transient( 'wzkb_deactivated_notice' );
			if ( $deactivated_plugin ) {
				/* translators: %s: Name of the deactivated plugin */
				$message = sprintf( __( "WebberZone Knowledge Base and WebberZone Knowledge Base Pro should not be active at the same time. We've automatically deactivated %s.", 'knowledgebase' ), $deactivated_plugin );
				?>
			<div class="updated" style="border-left: 4px solid #ffba00;">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
				<?php
				delete_transient( 'wzkb_deactivated_notice' );
			}
		}
	);
}

if ( ! function_exists( __NAMESPACE__ . '\wzkb_freemius' ) ) {
	// Finally load Freemius integration.
	require_once plugin_dir_path( __FILE__ ) . 'load-freemius.php';
}

// Load the autoloader.
require_once WZKB_PLUGIN_DIR . 'includes/autoloader.php';

if ( ! function_exists( __NAMESPACE__ . '\load' ) ) {
	/**
	 * The main function responsible for returning the one true WebberZone Knowledge Base instance to functions everywhere.
	 *
	 * @since 2.3.0
	 */
	function load() {
		wzkb();
	}
	add_action( 'plugins_loaded', __NAMESPACE__ . '\load' );
}

if ( ! function_exists( 'wzkb' ) ) {
	/**
	 * Get the main WebberZone Knowledge Base instance.
	 *
	 * @since 3.0.0
	 * @return Main Main instance.
	 */
	function wzkb() {
		return Main::get_instance();
	}
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
$wzkb_settings = \wzkb_get_settings();
