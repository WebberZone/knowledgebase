<?php
/**
 * Functions dealing with styles.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 2.3.0
 */
class Styles_Handler {

	/**
	 * Constructor class.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
	}

	/**
	 * Enqueue styles.
	 */
	public function register_styles() {

		$rtl_suffix = is_rtl() ? '-rtl' : '';
		$min_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$kb_style   = wzkb_get_option( 'kb_style', 'classic' );

		// Get style file location (allow Pro or other extensions to override).
		$style_url = $this->get_style_url( $kb_style, $rtl_suffix, $min_suffix );

		// Register the selected style (each style is complete and standalone).
		wp_register_style(
			'wz-knowledgebase-styles',
			$style_url,
			array( 'dashicons' ),
			WZKB_VERSION
		);

		$should_enqueue = false;

		// Check if styles should be enqueued.
		if ( is_singular() ) {
			$id = get_the_ID();
			if ( has_block( 'knowledgebase/knowledgebase', $id ) && wzkb_get_option( 'include_styles' ) ) {
				$should_enqueue = true;
			}
		}
		if ( wzkb_get_option( 'include_styles' ) ) {
			if ( is_singular( 'wz_knowledgebase' ) || is_post_type_archive( 'wz_knowledgebase' ) || ( is_tax( 'wzkb_category' ) && ! is_search() ) || ( is_tax( 'wzkb_product' ) && ! is_search() ) ) {
				$should_enqueue = true;
			}
		}

		// Only enqueue and add inline styles if needed.
		if ( $should_enqueue ) {
			wp_enqueue_style( 'wz-knowledgebase-styles' );

			// Add body class for selected style.
			add_filter( 'body_class', array( $this, 'add_style_body_class' ) );

			// Add custom CSS.
			$custom_css = wzkb_get_option( 'custom_css' );
			if ( ! empty( $custom_css ) ) {
				wp_add_inline_style( 'wz-knowledgebase-styles', esc_html( $custom_css ) );
			}

			// Inject CSS variable for columns setting.
			$columns     = absint( wzkb_get_option( 'columns', 2 ) );
			$columns_css = '.wzkb { --wzkb-columns: ' . $columns . '; }';
			wp_add_inline_style( 'wz-knowledgebase-styles', $columns_css );

			// Add custom styles for taxonomy archives.
			if ( is_tax( 'wzkb_category' ) ) {
				$taxonomy_css = '.wzkb-section-name-level-1 { display: none; }';
				wp_add_inline_style( 'wz-knowledgebase-styles', $taxonomy_css );
			}

			// Sidebar styles - use body class for more reliable targeting.
			if ( wzkb_get_option( 'show_sidebar' ) ) {
				$extra_styles = 'body.wzkb-sidebar-enabled .wrap{display:grid;grid-template-columns:1fr 300px;gap:30px;align-items:start;max-width:100%;}body.wzkb-sidebar-enabled #wzkb-content-primary{width:100%;max-width:none;min-width:0;}body.wzkb-sidebar-enabled #wzkb-sidebar-primary{width:100%;max-width:none;min-width:0;}';
				wp_add_inline_style( 'wz-knowledgebase-styles', $extra_styles );
			}
		}
	}

	/**
	 * Add body class for selected KB style.
	 *
	 * @since 2.3.0
	 *
	 * @param array $classes Array of body classes.
	 * @return array Modified array of body classes.
	 */
	public function add_style_body_class( $classes ) {
		$kb_style  = wzkb_get_option( 'kb_style', 'classic' );
		$classes[] = 'wzkb-style-' . sanitize_html_class( $kb_style );

		// Add sidebar class if enabled.
		if ( wzkb_get_option( 'show_sidebar' ) ) {
			$classes[] = 'wzkb-sidebar-enabled';
		}

		return $classes;
	}

	/**
	 * Get style URL for a given style name.
	 *
	 * Checks multiple directories in priority order:
	 * 1. Pro directory (via filter)
	 * 2. Free styles directory
	 *
	 * Falls back to 'classic' style if requested style doesn't exist.
	 *
	 * @since 2.3.0
	 *
	 * @param string $style_name Style name (e.g., 'classic', 'modern').
	 * @param string $rtl_suffix RTL suffix ('-rtl' or '').
	 * @param string $min_suffix Minification suffix ('.min' or '').
	 * @return string Style URL.
	 */
	public function get_style_url( $style_name, $rtl_suffix = '', $min_suffix = '.min' ) {
		$filename = $style_name . $rtl_suffix . $min_suffix . '.css';

		// Default to free styles directory.
		$style_url = plugins_url( 'css/styles/' . $filename, __FILE__ );

		/**
		 * Filter the style URL.
		 *
		 * Allows Pro or other extensions to provide their own style files.
		 *
		 * @since 2.3.0
		 *
		 * @param string $style_url  The style URL.
		 * @param string $style_name The style name.
		 * @param string $filename   The complete filename with suffixes.
		 */
		$style_url = apply_filters( 'wzkb_style_url', $style_url, $style_name, $filename );

		// Validate that the file exists, fall back to classic if not.
		$style_path = $this->url_to_path( $style_url );
		if ( ! file_exists( $style_path ) && 'classic' !== $style_name ) {
			// Recursively call with 'classic' as fallback.
			return $this->get_style_url( 'classic', $rtl_suffix, $min_suffix );
		}

		return $style_url;
	}

	/**
	 * Convert URL to file path.
	 *
	 * @since 2.3.0
	 *
	 * @param string $url File URL.
	 * @return string File path.
	 */
	private function url_to_path( $url ) {
		$upload_dir = wp_upload_dir();

		// Handle plugin directory URLs.
		if ( false !== strpos( $url, plugins_url() ) ) {
			return str_replace( plugins_url(), WP_PLUGIN_DIR, $url );
		}

		// Handle content directory URLs.
		if ( false !== strpos( $url, content_url() ) ) {
			return str_replace( content_url(), WP_CONTENT_DIR, $url );
		}

		return $url;
	}
}
