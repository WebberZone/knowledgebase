<?php
/**
 * Site Verification class.
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
 * Site Verification class.
 *
 * @since 2.0.0
 */
class Site_Verification {

	/**
	 * Main constructor.
	 */
	public function __construct() {
		Hook_Registry::add_action( 'wp_head', array( $this, 'site_verification' ) );
	}

	/**
	 * Site Verification.
	 *
	 * @since 2.0.0
	 */
	public function site_verification() {

		$this->google();
		$this->bing();
		$this->pinterest();
		$this->facebook_domain();

		/**
		 * Site Verification action.
		 *
		 * @since 1.2.0
		 */
		do_action( 'ata_site_verification' );
	}

	/**
	 * Get meta tag for site verification.
	 *
	 * @param string $name    Meta name.
	 * @param string $content Meta content.
	 */
	public function get_meta_tag( $name, $content ) {
		?>
		<meta name="<?php echo esc_attr( $name ); ?>" content="<?php echo esc_attr( $content ); ?>" />
		<?php
	}

	/**
	 * Google Site Verification.
	 *
	 * @since 2.0.0
	 */
	public function google() {

		$verification_code = ata_get_option( 'google_verification', '' );
		if ( $verification_code ) {
			$this->get_meta_tag( 'google-site-verification', $verification_code );
		}
	}

	/**
	 * Bing Site Verification.
	 *
	 * @since 2.0.0
	 */
	public function bing() {

		$verification_code = ata_get_option( 'bing_verification', '' );
		if ( $verification_code ) {
			$this->get_meta_tag( 'msvalidate.01', $verification_code );
		}
	}

	/**
	 * Pinterest Site Verification.
	 *
	 * @since 2.0.0
	 */
	public function pinterest() {

		$verification_code = ata_get_option( 'pinterest_verification', '' );
		if ( $verification_code ) {
			$this->get_meta_tag( 'p:domain_verify', $verification_code );
		}
	}

	/**
	 * Facebook Domain Verification.
	 *
	 * @since 2.0.0
	 */
	public function facebook_domain() {
		$verification_code = ata_get_option( 'facebook_domain_verification', '' );
		if ( $verification_code ) {
			$this->get_meta_tag( 'facebook-domain-verification', $verification_code );
		}
	}
}
