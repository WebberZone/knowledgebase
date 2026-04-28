<?php
/**
 * Language handler
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Language handler class.
 *
 * Handles i18n, WPML integration, and Polylang integration.
 *
 * @since 2.3.0
 */
class Language_Handler {

	/**
	 * Constructor.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Initialises text domain for l10n.
	 *
	 * @since 2.3.0
	 *
	 * @return void
	 */
	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'knowledgebase', false, WZKB_PLUGIN_DIR . '/languages/' );
	}

	/**
	 * Translate a term ID to the current language.
	 *
	 * Widget and shortcode settings store the term ID saved by the admin in the
	 * default language. On a non-default-language page the stored ID must be
	 * resolved to the equivalent term in the current language before use.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $term_id  Term ID in the default language.
	 * @param string $taxonomy Taxonomy slug.
	 * @return int Translated term ID, or the original if no translation exists.
	 */
	public static function get_translated_term_id( int $term_id, string $taxonomy ): int {
		if ( ! $term_id ) {
			return $term_id;
		}

		// WPML.
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return (int) apply_filters( 'wpml_object_id', $term_id, $taxonomy, true );
		}

		// Polylang.
		if ( defined( 'POLYLANG_VERSION' ) ) {
			$translated = pll_get_term( $term_id );
			return $translated ? (int) $translated : $term_id;
		}

		return $term_id;
	}

	/**
	 * Return the current language slug, or an empty string when no multilingual plugin is active.
	 *
	 * @since 3.0.0
	 *
	 * @return string Language slug (e.g. 'en', 'fr') or '' if not multilingual.
	 */
	public static function get_current_language(): string {
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return (string) apply_filters( 'wpml_current_language', '' );
		}

		if ( defined( 'POLYLANG_VERSION' ) ) {
			return (string) pll_current_language();
		}

		return '';
	}
}
