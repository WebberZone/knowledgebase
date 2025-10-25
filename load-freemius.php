<?php
/**
 * Initializes Freemius SDK for WebberZone Knowledge Base Pro.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( __NAMESPACE__ . '\wzkb_freemius' ) ) {
	/**
	 * Initialize Freemius SDK.
	 */
	function wzkb_freemius() {
		global $wzkb_freemius;
		if ( ! isset( $wzkb_freemius ) ) {
			// Activate multisite network integration.
			if ( ! defined( 'WP_FS__PRODUCT_21392_MULTISITE' ) ) {
				define( 'WP_FS__PRODUCT_21392_MULTISITE', true );
			}
			// Include Freemius SDK.
			require_once __DIR__ . '/vendor/freemius/start.php';
			$wzkb_freemius = \fs_dynamic_init(
				array(
					'id'                  => '21392',
					'slug'                => 'knowledgebase',
					'premium_slug'        => 'knowledgebase-pro',
					'type'                => 'plugin',
					'public_key'          => 'pk_1f07ee1df929932e63005f9866901',
					'is_premium'          => true,
					'premium_suffix'      => 'Pro',
					'has_premium_version' => true,
					'has_addons'          => false,
					'has_paid_plans'      => true,
					'wp_org_gatekeeper'   => 'OA7#BoRiBNqdf52FvzEf!!074aRLPs8fspif$7K1#4u4Csys1fQlCecVcUTOs2mcpeVHi#C2j9d09fOTvbC0HloPT7fFee5WdS3G',
					'trial'               => array(
						'days'               => 14,
						'is_require_payment' => true,
					),
					'menu'                => array(
						'slug'       => 'edit.php?post_type=wz_knowledgebase',
						'first-path' => 'edit.php?post_type=wz_knowledgebase&page=wzkb-setup',
						'contact'    => false,
						'support'    => false,
					),
				)
			);
		}
		$wzkb_freemius->add_filter( 'plugin_icon', __NAMESPACE__ . '\\wzkb_freemius_get_plugin_icon' );
		$wzkb_freemius->add_filter( 'after_uninstall', __NAMESPACE__ . '\\wzkb_freemius_uninstall' );
		return $wzkb_freemius;
	}

	/**
	 * Get the plugin icon.
	 *
	 * @return string
	 */
	function wzkb_freemius_get_plugin_icon() {
		return __DIR__ . '/includes/admin/images/wzkb-icon.jpg';
	}

	/**
	 * Uninstall the plugin.
	 */
	function wzkb_freemius_uninstall() {
		require_once __DIR__ . '/uninstaller.php';
	}

	// Init Freemius.
	wzkb_freemius();
	// Signal that SDK was initiated.
	do_action( 'wzkb_freemius_loaded' );
}
