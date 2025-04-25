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

		wp_register_style(
			'wz-knowledgebase-styles',
			plugins_url( 'css/wzkb-styles' . $rtl_suffix . $min_suffix . '.css', __FILE__ ),
			array( 'dashicons' ),
			'1.0'
		);

		if ( is_singular() ) {
			$id = get_the_ID();
			if ( has_block( 'knowledgebase/knowledgebase', $id ) && wzkb_get_option( 'include_styles' ) ) {
				wp_enqueue_style( 'wz-knowledgebase-styles' );
			}
		}
		if ( wzkb_get_option( 'include_styles' ) ) {
			if ( is_singular( 'wz_knowledgebase' ) || is_post_type_archive( 'wz_knowledgebase' ) || ( is_tax( 'wzkb_category' ) && ! is_search() ) ) {
				wp_enqueue_style( 'wz-knowledgebase-styles' );
			}
		}

		wp_add_inline_style( 'wz-knowledgebase-styles', esc_html( wzkb_get_option( 'custom_css' ) ) );

		// Add custom styles for taxonomy archives.
		if ( is_tax( 'wzkb_category' ) ) {
			$custom_css = '
				.wzkb-section-name-level-1 {
					display: none;
				}
			';
			wp_add_inline_style( 'wz-knowledgebase-styles', $custom_css );
		}

		if ( wzkb_get_option( 'show_sidebar' ) ) {
			$extra_styles = '#wzkb-sidebar-primary{width:25%;}#wzkb-content-primary{width:75%;float:left;}';
			wp_add_inline_style( 'wz-knowledgebase-styles', $extra_styles );
		}
	}
}
