<?php
// phpcs:ignoreFile
/**
 * PHPStan bootstrap file for Knowledge Base Pro.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace {
	if ( ! defined( 'WZKB_VERSION' ) ) {
		define( 'WZKB_VERSION', '0.0.0' );
	}

	if ( ! defined( 'WZKB_PLUGIN_FILE' ) ) {
		define( 'WZKB_PLUGIN_FILE', '' );
	}

	if ( ! defined( 'WZKB_PLUGIN_DIR' ) ) {
		define( 'WZKB_PLUGIN_DIR', '' );
	}

	if ( ! defined( 'WZKB_PLUGIN_URL' ) ) {
		define( 'WZKB_PLUGIN_URL', '' );
	}

	if ( ! defined( 'WZKB_DEFAULT_THUMBNAIL_URL' ) ) {
		define( 'WZKB_DEFAULT_THUMBNAIL_URL', '' );
	}

	// Polylang stubs — provide type signatures for static analysis.
	if ( ! function_exists( 'pll_get_term' ) ) {
		/**
		 * Get the translated term ID in a given language.
		 *
		 * @param int    $term_id Term ID.
		 * @param string $lang    Language slug. Defaults to current language.
		 * @return int|false Translated term ID or false if not found.
		 */
		function pll_get_term( int $term_id, string $lang = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			return false;
		}
	}

	if ( ! function_exists( 'pll_current_language' ) ) {
		/**
		 * Get the current language.
		 *
		 * @param string $field Field to return ('slug', 'name', etc.). Defaults to 'slug'.
		 * @return string|false Language field value or false if no language is set.
		 */
		function pll_current_language( string $field = 'slug' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
			return false;
		}
	}
}

// When running on the free plugin (includes/pro/ removed by sync), define Pro class stubs
// so PHPStan can resolve the GitHub classes referenced from shared admin code.
namespace WebberZone\Knowledge_Base\Pro\GitHub {
	if ( ! is_dir( __DIR__ . '/includes/pro' ) ) {
		class API { // phpcs:ignore
			public function with_pat( string $pat ): self { return $this; } // phpcs:ignore
			/** @return string|\WP_Error */
			public function request( string $url, array $extra_args = array() ) { return ''; } // phpcs:ignore
			/** @return array|\WP_Error */
			public function validate_token() { return array(); } // phpcs:ignore
			/** @return array|\WP_Error */
			public function request_raw( string $url, string $method = 'GET', array $extra = array() ) { return array(); } // phpcs:ignore
		}
		class GitHub { // phpcs:ignore
			public static function normalize_mapping( array $row ): array { return $row; } // phpcs:ignore
		}
	}
}

namespace WebberZone\Knowledge_Base\Pro {
	if ( ! is_dir( __DIR__ . '/includes/pro' ) ) {
		class Pro {} // phpcs:ignore
	}
}
