<?php
/**
 * Main plugin class.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base;

use WebberZone\Knowledge_Base\Admin\Admin;
use WebberZone\Knowledge_Base\Pro\Pro;
use WebberZone\Knowledge_Base\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * @since 2.3.0
 */
final class Main {
	/**
	 * The single instance of the class.
	 *
	 * @var Main
	 */
	private static $instance;

	/**
	 * Admin.
	 *
	 * @since 2.3.0
	 *
	 * @var Admin|null Admin instance.
	 */
	public ?Admin $admin = null;

	/**
	 * Pro features class.
	 *
	 * @since 3.0.0
	 *
	 * @var Pro|null Pro instance.
	 */
	public ?Pro $pro = null;

	/**
	 * Shortcodes.
	 *
	 * @since 2.3.0
	 *
	 * @var Frontend\Shortcodes Shortcodes handler.
	 */
	public Frontend\Shortcodes $shortcodes;

	/**
	 * Styles.
	 *
	 * @since 2.3.0
	 *
	 * @var Frontend\Styles_Handler Styles handler.
	 */
	public Frontend\Styles_Handler $styles;

	/**
	 * Language Handler.
	 *
	 * @since 2.3.0
	 *
	 * @var Frontend\Language_Handler Language handler.
	 */
	public Frontend\Language_Handler $language;

	/**
	 * Display.
	 *
	 * @since 2.3.0
	 *
	 * @var Frontend\Display Display handler.
	 */
	public Frontend\Display $display;

	/**
	 * Template Handler.
	 *
	 * @since 4.0.0
	 *
	 * @var Frontend\Template_Handler Template handler.
	 */
	public Frontend\Template_Handler $template_handler;

	/**
	 * CPT.
	 *
	 * @since 2.3.0
	 *
	 * @var CPT CPT handler.
	 */
	public CPT $cpt;

	/**
	 * Feed.
	 *
	 * @since 2.3.0
	 *
	 * @var Frontend\Feed Feed handler.
	 */
	public Frontend\Feed $feed;

	/**
	 * Search.
	 *
	 * @since 2.3.0
	 *
	 * @var Frontend\Search Search handler.
	 */
	public Frontend\Search $search;

	/**
	 * Blocks.
	 *
	 * @since 2.3.0
	 *
	 * @var Blocks\Blocks Blocks handler.
	 */
	public Blocks\Blocks $blocks;

	/**
	 * Related articles.
	 *
	 * @since 2.3.0
	 *
	 * @var Frontend\Related Related articles handler.
	 */
	public Frontend\Related $related_articles;

	/**
	 * Gets the instance of the class.
	 *
	 * @since 2.3.0
	 *
	 * @return Main
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @since 2.3.0
	 */
	private function __construct() {
		// Do nothing.
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since 2.3.0
	 */
	private function init() {
		$this->cpt              = new CPT();
		$this->language         = new Frontend\Language_Handler();
		$this->template_handler = new Frontend\Template_Handler();
		$this->styles           = new Frontend\Styles_Handler();
		$this->display          = new Frontend\Display();
		$this->related_articles = new Frontend\Related();
		$this->search           = new Frontend\Search();
		$this->shortcodes       = new Frontend\Shortcodes();
		$this->feed             = new Frontend\Feed();
		$this->blocks           = new Blocks\Blocks();

		$this->hooks();

		// Conditionally load Pro if available.
		if ( class_exists( 'WebberZone\\Knowledge_Base\\Pro\\Pro' ) ) {
			$this->pro = new Pro();
		}

		if ( is_admin() ) {
			$this->admin = new Admin();
		}
	}

	/**
	 * Run the hooks.
	 *
	 * @since 2.3.0
	 */
	public function hooks() {
		Hook_Registry::add_action( 'init', array( $this, 'initiate_plugin' ) );
		Hook_Registry::add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	/**
	 * Initialise the plugin translations and media.
	 *
	 * @since 2.3.0
	 */
	public function initiate_plugin() {
		Frontend\Media_Handler::add_image_sizes();
	}

	/**
	 * Initialise the Better Search widgets.
	 *
	 * @since 2.3.0
	 */
	public function register_widgets() {
		register_widget( '\WebberZone\Knowledge_Base\Widgets\Articles_Widget' );
		register_widget( '\WebberZone\Knowledge_Base\Widgets\Sections_Widget' );
		register_widget( '\WebberZone\Knowledge_Base\Widgets\Breadcrumb_Widget' );
	}
}
