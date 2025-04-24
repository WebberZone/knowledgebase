<?php
/**
 * Shortcode module
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 2.3.0
 */
class Shortcodes {

	/**
	 * Constructor class.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		add_shortcode( 'knowledgebase', array( $this, 'knowledgebase' ) );
		add_shortcode( 'kbsearch', array( $this, 'search_form' ) );
		add_shortcode( 'kbbreadcrumb', array( $this, 'breadcrumb' ) );
		add_shortcode( 'kbalert', array( $this, 'alert' ) );
		add_shortcode( 'kb_related_articles', array( $this, 'related_articles' ) );
	}

	/**
	 * Create the shortcode to display the KB using [knowledgebase].
	 *
	 * @since 2.3.0
	 *
	 * @param  array  $atts    Shortcode attributes array.
	 * @param  string $content Content to wrap in the Shortcode.
	 * @return string $output Formatted shortcode output
	 */
	public static function knowledgebase( $atts, $content = null ) {

		if ( wzkb_get_option( 'include_styles' ) ) {
			wp_enqueue_style( 'wz-knowledgebase-styles' );
		}
		$atts = shortcode_atts(
			array(
				'category'     => false,
				'product'      => false,
				'is_shortcode' => 1,
			),
			$atts,
			'knowledgebase'
		);

		$output = wzkb_knowledge( $atts );

		/**
		 * Filters knowledgebase shortcode.
		 *
		 * @since 1.0.0
		 *
		 * @param  string $output  Formatted shortcode output
		 * @param  array $att  Shortcode attributes array
		 * @param  string $content Content to wrap in the Shortcode
		 */
		return apply_filters( 'wzkb_shortcode', $output, $atts, $content );
	}

	/**
	 * Create the shortcode to display the search form using [kbsearch].
	 *
	 * @since 2.3.0
	 *
	 * @param  array  $atts    Shortcode attributes array.
	 * @param  string $content Content to wrap in the Shortcode.
	 * @return string $output Formatted shortcode output
	 */
	public static function search_form( $atts, $content = null ) {

		$atts = shortcode_atts(
			array(
				'echo' => false,
			),
			$atts,
			'kbsearch'
		);

		$output = wzkb_get_search_form();

		/**
		 * Filters knowledge base search form shortcode.
		 *
		 * @since 1.2.0
		 *
		 * @param  string $output  Formatted shortcode output
		 * @param  array $att  Shortcode attributes array
		 * @param  string $content Content to wrap in the Shortcode
		 */
		return apply_filters( 'wzkb_shortcode_search', $output, $atts, $content );
	}


	/**
	 * Create the shortcode to display the breadcrumb using [kbbreadcrumb].
	 *
	 * @since 2.3.0
	 *
	 * @param  array  $atts    Shortcode attributes array.
	 * @param  string $content Content to wrap in the Shortcode.
	 * @return string $output Formatted shortcode output
	 */
	public static function breadcrumb( $atts, $content = null ) {

		$atts = shortcode_atts(
			array(
				'separator' => ' &raquo; ', // Separator.
			),
			$atts,
			'kbbreadcrumb'
		);

		$output = wzkb_get_breadcrumb( $atts );

		/**
		 * Filters knowledge base breadcrumb shortcode.
		 *
		 * @since 1.6.0
		 *
		 * @param  string $output  Formatted shortcode output
		 * @param  array $att  Shortcode attributes array
		 * @param  string $content Content to wrap in the Shortcode
		 */
		return apply_filters( 'wzkb_shortcode_breadcrumb', $output, $atts, $content );
	}

	/**
	 * Create the shortcode to display alerts using [kbalert].
	 *
	 * @since 2.3.0
	 *
	 * @param  array  $atts    Shortcode attributes array.
	 * @param  string $content Content to wrap in the Shortcode.
	 * @return string $output Formatted shortcode output
	 */
	public static function alert( $atts, $content = null ) {

		if ( wzkb_get_option( 'include_styles' ) ) {
			wp_enqueue_style( 'wz-knowledgebase-styles' );
		}

		$atts = shortcode_atts(
			array(
				'type'  => 'primary',
				'class' => 'alert',
				'text'  => '',
			),
			$atts,
			'kbalert'
		);

		$output = wzkb_get_alert( $atts, $content );

		/**
		 * Filters knowledge base breadcrumb shortcode.
		 *
		 * @since 1.7.0
		 *
		 * @param  string $output  Formatted shortcode output
		 * @param  array $att  Shortcode attributes array
		 * @param  string $content Content to wrap in the Shortcode
		 */
		return apply_filters( 'wzkb_shortcode_alert', $output, $atts, $content );
	}


	/**
	 * Create the shortcode to display related articles using [kb_related_articles].
	 *
	 * @since 2.3.0
	 *
	 * @param  array  $atts    Shortcode attributes array.
	 * @param  string $content Content to wrap in the Shortcode.
	 * @return string $output Formatted shortcode output
	 */
	public static function related_articles( $atts, $content = null ) {

		if ( wzkb_get_option( 'include_styles' ) ) {
			wp_enqueue_style( 'wz-knowledgebase-styles' );
		}

		$atts = shortcode_atts(
			array(
				'numberposts' => 5,
				'echo'        => false,
				'post'        => get_post(),
				'exclude'     => array(),
				'show_thumb'  => true,
				'show_date'   => true,
				'title'       => '<h3>' . __( 'Related Articles', 'knowledgebase' ) . '</h3>',
				'thumb_size'  => 'thumbnail',
			),
			$atts,
			'kb_related_articles'
		);

		$output = wzkb_related_articles( $atts );

		/**
		 * Filters knowledge base breadcrumb shortcode.
		 *
		 * @since 2.2.2
		 *
		 * @param  string $output  Formatted shortcode output
		 * @param  array $att  Shortcode attributes array
		 * @param  string $content Content to wrap in the Shortcode
		 */
		return apply_filters( 'wzkb_shortcode_related_articles', $output, $atts, $content );
	}
}
