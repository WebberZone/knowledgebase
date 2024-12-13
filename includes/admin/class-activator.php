<?php
/**
 * Activator class.
 *
 * Handles activation tasks for the Knowledge Base plugin.
 *
 * @since 2.3.0
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Admin;

use WebberZone\Knowledge_Base\CPT;

/**
 * Class Activator
 *
 * Handles activation tasks for the Knowledge Base plugin.
 */
class Activator {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_action( 'wp_initialize_site', array( $this, 'activate_new_site' ) );
	}

	/**
	 * Activation method.
	 *
	 * @since 2.3.0
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses
	 *                              "Network Activate" action, false if
	 *                              WPMU is disabled or plugin is
	 *                              activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		if ( is_multisite() && $network_wide ) {
			$sites = get_sites(
				array(
					'archived' => 0,
					'spam'     => 0,
					'deleted'  => 0,
				)
			);

			foreach ( $sites as $site ) {
				switch_to_blog( (int) $site->blog_id );
				self::single_activate();
			}

			// Switch back to the current blog.
			restore_current_blog();

		} else {
			self::single_activate();
		}
	}

	/**
	 * Activates the plugin on a new site.
	 *
	 * @since 3.3.0
	 *
	 * @param int|\WP_Site $blog The blog ID.
	 */
	public function activate_new_site( $blog ) {
		if ( ! is_plugin_active_for_network( plugin_basename( WZKB_PLUGIN_FILE ) ) ) {
			return;
		}

		if ( ! is_int( $blog ) ) {
			$blog = $blog->id;
		}

		switch_to_blog( $blog );
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Activation method.
	 *
	 * @since 2.3.0
	 */
	public static function single_activate() {
		// Register types to register the rewrite rules.
		CPT::register_post_type();
		CPT::register_taxonomies();

		// Then flush them.
		global $wp_rewrite;
		$wp_rewrite->init();
		flush_rewrite_rules( false );
	}

	/**
	 * Deactivation method.
	 *
	 * @since 2.3.0
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses
	 *                              "Network Activate" action, false if
	 *                              WPMU is disabled or plugin is
	 *                              activated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		if ( is_multisite() && $network_wide ) {
			$sites = get_sites(
				array(
					'archived' => 0,
					'spam'     => 0,
					'deleted'  => 0,
				)
			);

			foreach ( $sites as $site ) {
				switch_to_blog( (int) $site->blog_id );
				global $wp_rewrite;
				$wp_rewrite->init();
				flush_rewrite_rules();
				restore_current_blog();
			}
		}

		// Flush the rewrite rules.
		global $wp_rewrite;
		$wp_rewrite->init();
		flush_rewrite_rules();
	}
}
