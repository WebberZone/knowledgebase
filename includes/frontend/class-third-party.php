<?php
/**
 * Class to handle Third Party services.
 *
 * @link  https://webberzone.com
 * @since 2.0.0
 *
 * @package WebberZone\Snippetz
 */

namespace WebberZone\Snippetz\Frontend;

use WebberZone\Snippetz\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle Third Party services.
 *
 * @since 2.0.0
 */
class Third_Party {

	/**
	 * Main constructor.
	 */
	public function __construct() {
		Hook_Registry::add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
	}

	/**
	 * Hooks into wp_enqueue_scripts to add third party scripts.
	 *
	 * @since 2.0.0
	 */
	public function wp_enqueue_scripts() {
		$this->google_analytics();
		$this->statcounter();
	}

	/**
	 * Get Google Analytics code.
	 *
	 * @since 2.0.0
	 */
	public function google_analytics() {

		$gtm_id = ata_get_option( 'ga_uacct' );

		if ( $gtm_id ) {
			wp_enqueue_script( // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
				'wz-snippetz-gtm',
				'https://www.googletagmanager.com/gtm.js?id=' . $gtm_id,
				array(),
				null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
				true
			);
			wp_script_add_data( 'wz-snippetz-gtm', 'async', true );
		}

		$inline_code = "
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '$gtm_id');
        ";
		wp_add_inline_script( 'wz-snippetz-gtm', $inline_code );
	}

	/**
	 * Function to add the necessary code to `wp_footer`.
	 *
	 * @since 2.0.0
	 */
	public function statcounter() {

		$sc_project  = ata_get_option( 'sc_project', '' );
		$sc_security = ata_get_option( 'sc_security', '' );

		if ( $sc_project && $sc_security ) {
			wp_enqueue_script( // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
				'wz-snippetz-statcounter',
				'https://www.statcounter.com/counter/counter.js',
				array(),
				null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
				true
			);
			wp_script_add_data( 'wz-snippetz-statcounter', 'async', true );
		}

		$inline_code = "
        var sc_project=$sc_project; 
        var sc_invisible=1; 
        var sc_security=\"$sc_security\"; 
        ";
		wp_add_inline_script( 'wz-snippetz-statcounter', $inline_code );
	}
}
