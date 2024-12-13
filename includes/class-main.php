<?php
/**
 * Main plugin class.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base;

use WebberZone\Knowledge_Base\Admin\Activator;

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
	 * @var object Admin.
	 */
	public $admin;

	/**
	 * Shortcodes.
	 *
	 * @since 2.3.0
	 *
	 * @var object Shortcodes.
	 */
	public $shortcodes;

	/**
	 * Styles.
	 *
	 * @since 2.3.0
	 *
	 * @var object Styles.
	 */
	public $styles;

	/**
	 * Language Handler.
	 *
	 * @since 2.3.0
	 *
	 * @var object Language Handler.
	 */
	public $language;

	/**
	 * Display.
	 *
	 * @since 2.3.0
	 *
	 * @var object Display.
	 */
	public $display;

	/**
	 * Template Handler.
	 *
	 * @since 4.0.0
	 *
	 * @var object Template Handler.
	 */
	public $template_handler;

	/**
	 * CPT.
	 *
	 * @since 2.3.0
	 *
	 * @var object CPT.
	 */
	public $cpt;

	/**
	 * Feed.
	 *
	 * @since 2.3.0
	 *
	 * @var object Feed.
	 */
	public $feed;

	/**
	 * Search.
	 *
	 * @since 2.3.0
	 *
	 * @var object Search.
	 */
	public $search;

	/**
	 * Blocks.
	 *
	 * @since 2.3.0
	 *
	 * @var object Blocks.
	 */
	public $blocks;

	/**
	 * Related articles.
	 *
	 * @since 2.3.0
	 *
	 * @var object Related articles.
	 */
	public $related_articles;

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

		if ( is_admin() ) {
			$this->admin = new Admin\Admin();
		}
	}

	/**
	 * Run the hooks.
	 *
	 * @since 2.3.0
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'initiate_plugin' ) );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
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
