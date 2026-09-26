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

// TranslatePress has no official PHPStan stub package, so declare the minimal surface the
// Knowledge Base language handler touches.
namespace {
	if ( ! class_exists( 'TRP_Translate_Press' ) ) {
		class TRP_Translate_Press {
			/**
			 * Runtime surface varies by TranslatePress version, so callers guard it.
			 *
			 * @return mixed
			 */
			public static function get_trp_instance() {
				return new self();
			}

			/**
			 * @param string $component Component name.
			 * @return object|null
			 */
			public function get_component( $component ) {
				unset( $component );
				return null;
			}
		}
	}

	if ( ! function_exists( 'trp_translate' ) ) {
		/**
		 * TranslatePress translation stub for static analysis.
		 *
		 * @param string      $content                  Content to translate.
		 * @param string|null $language                 Target language code.
		 * @param bool        $prevent_over_translation Whether to wrap the output.
		 * @return string
		 */
		function trp_translate( $content, $language = null, $prevent_over_translation = true ) {
			unset( $language, $prevent_over_translation );
			return (string) $content;
		}
	}
}

// WordPress 7.0 AI Client stubs, for the Ask the docs module.
namespace {
	if ( ! class_exists( 'WP_AI_Client_Prompt_Builder' ) ) {
		class WP_AI_Client_Prompt_Builder {
			/**
			 * @param string $instruction System instruction.
			 * @return self
			 */
			public function using_system_instruction( string $instruction ) {
				unset( $instruction );
				return $this;
			}

			/**
			 * @param array<string, mixed>|null $schema JSON schema.
			 * @return self
			 */
			public function as_json_response( ?array $schema = null ) {
				unset( $schema );
				return $this;
			}

			/**
			 * @param string $provider Provider ID.
			 * @return self
			 */
			public function using_provider( string $provider ) {
				unset( $provider );
				return $this;
			}

			/**
			 * @param mixed ...$models Model preferences.
			 * @return self
			 */
			public function using_model_preference( ...$models ) {
				unset( $models );
				return $this;
			}

			/**
			 * @return bool
			 */
			public function is_supported_for_text_generation() {
				return false;
			}

			/**
			 * @return string|\WP_Error
			 */
			public function generate_text() {
				return '';
			}
		}
	}

	if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
		/**
		 * @param mixed $prompt Prompt.
		 * @return \WP_AI_Client_Prompt_Builder
		 */
		function wp_ai_client_prompt( $prompt = null ) {
			unset( $prompt );
			return new \WP_AI_Client_Prompt_Builder();
		}
	}
}
