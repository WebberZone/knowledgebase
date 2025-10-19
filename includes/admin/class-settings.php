<?php
/**
 * Register Settings.
 *
 * @since 2.3.0
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Admin;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to register the settings.
 *
 * @since 2.3.0
 */
class Settings {


	/**
	 * Admin Dashboard.
	 *
	 * @since 2.3.0
	 *
	 * @var object Admin Dashboard.
	 */
	public $admin_dashboard;

	/**
	 * Settings API.
	 *
	 * @since 2.3.0
	 *
	 * @var object Settings API.
	 */
	public $settings_api;

	/**
	 * Statistics table.
	 *
	 * @since 2.3.0
	 *
	 * @var object Statistics table.
	 */
	public $statistics;

	/**
	 * Activator class.
	 *
	 * @since 2.3.0
	 *
	 * @var object Activator class.
	 */
	public $activator;

	/**
	 * Admin Columns.
	 *
	 * @since 2.3.0
	 *
	 * @var object Admin Columns.
	 */
	public $admin_columns;

	/**
	 * Metabox functions.
	 *
	 * @since 2.3.0
	 *
	 * @var object Metabox functions.
	 */
	public $metabox;

	/**
	 * Import Export functions.
	 *
	 * @since 2.3.0
	 *
	 * @var object Import Export functions.
	 */
	public $import_export;

	/**
	 * Tools page.
	 *
	 * @since 2.3.0
	 *
	 * @var object Tools page.
	 */
	public $tools_page;

	/**
	 * Settings Page in Admin area.
	 *
	 * @since 2.3.0
	 *
	 * @var string Settings Page.
	 */
	public $settings_page;

	/**
	 * Prefix which is used for creating the unique filters and actions.
	 *
	 * @since 2.3.0
	 *
	 * @var string Prefix.
	 */
	public static $prefix;

	/**
	 * Settings Key.
	 *
	 * @since 2.3.0
	 *
	 * @var string Settings Key.
	 */
	public $settings_key;

	/**
	 * The slug name to refer to this menu by (should be unique for this menu).
	 *
	 * @since 2.3.0
	 *
	 * @var string Menu slug.
	 */
	public $menu_slug;

	/**
	 * Main constructor class.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		$this->settings_key = 'wzkb_settings';
		self::$prefix       = 'wzkb';
		$this->menu_slug    = 'wzkb-settings';

		Hook_Registry::add_action( 'admin_menu', array( $this, 'initialise_settings' ) );
		Hook_Registry::add_action( 'admin_head', array( $this, 'admin_head' ), 11 );
		Hook_Registry::add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 11, 2 );
		Hook_Registry::add_filter( 'plugin_action_links_' . plugin_basename( WZKB_PLUGIN_FILE ), array( $this, 'plugin_actions_links' ) );
		Hook_Registry::add_filter( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 99 );

		Hook_Registry::add_filter( self::$prefix . '_settings_sanitize', array( $this, 'change_settings_on_save' ), 99 );
	}

	/**
	 * Initialise the settings API.
	 *
	 * @since 2.3.0
	 */
	public function initialise_settings() {
		$props = array(
			'default_tab'       => 'general',
			'help_sidebar'      => $this->get_help_sidebar(),
			'help_tabs'         => $this->get_help_tabs(),
			'admin_footer_text' => $this->get_admin_footer_text(),
			'menus'             => $this->get_menus(),
		);

		$args = array(
			'props'               => $props,
			'translation_strings' => $this->get_translation_strings(),
			'settings_sections'   => $this->get_settings_sections(),
			'registered_settings' => $this->get_registered_settings(),
			'upgraded_settings'   => array(),
		);

		$this->settings_api = new Settings\Settings_API( $this->settings_key, self::$prefix, $args );
	}

	/**
	 * Get settings defaults.
	 *
	 * @since 3.0.0
	 *
	 * @return array Default settings.
	 */
	public static function settings_defaults() {
		$defaults = array();

		// Get all registered settings.
		$settings = self::get_registered_settings();

		// Loop through each section.
		foreach ( $settings as $section => $section_settings ) {
			// Loop through each setting in the section.
			foreach ( $section_settings as $setting ) {
				if ( isset( $setting['id'] ) ) {
					// When checkbox is set to true, set this to 1.
					if ( 'checkbox' === $setting['type'] && ! empty( $setting['options'] ) ) {
						$defaults[ $setting['id'] ] = 1;
					} elseif ( in_array( $setting['type'], array( 'textarea', 'css', 'html', 'text', 'url', 'csv', 'color', 'numbercsv', 'postids', 'posttypes', 'number', 'wysiwyg', 'file', 'password' ), true ) && isset( $setting['default'] ) ) {
						$defaults[ $setting['id'] ] = $setting['default'];
					} elseif ( in_array( $setting['type'], array( 'multicheck', 'radio', 'select', 'radiodesc', 'thumbsizes', 'repeater' ), true ) && isset( $setting['default'] ) ) {
						$defaults[ $setting['id'] ] = $setting['default'];
					} else {
						$defaults[ $setting['id'] ] = '';
					}
				}
			}
		}

		/**
		 * Filter the default settings array.
		 *
		 * @since 1.0.0
		 *
		 * @param array $defaults Default settings.
		 */
		return apply_filters( self::$prefix . '_settings_defaults', $defaults );
	}

	/**
	 * Array containing the translation strings.
	 *
	 * @since 1.8.0
	 *
	 * @return array Translation strings.
	 */
	public function get_translation_strings() {
		$strings = array(
			'page_title'           => esc_html__( 'Knowledge Base Settings', 'knowledgebase' ),
			'menu_title'           => esc_html__( 'Settings', 'knowledgebase' ),
			'page_header'          => esc_html__( 'Knowledge Base Settings', 'knowledgebase' ),
			'reset_message'        => esc_html__( 'Settings have been reset to their default values. Reload this page to view the updated settings.', 'knowledgebase' ),
			'success_message'      => esc_html__( 'Settings updated.', 'knowledgebase' ),
			'save_changes'         => esc_html__( 'Save Changes', 'knowledgebase' ),
			'reset_settings'       => esc_html__( 'Reset all settings', 'knowledgebase' ),
			'reset_button_confirm' => esc_html__( 'Do you really want to reset all these settings to their default values?', 'knowledgebase' ),
			'checkbox_modified'    => esc_html__( 'Modified from default setting', 'knowledgebase' ),
			'button_label'         => esc_html__( 'Choose File', 'knowledgebase' ),
			'previous_saved'       => esc_html__( 'Previously saved', 'knowledgebase' ),
		);

		/**
		 * Filter the array containing the settings' sections.
		 *
		 * @since 2.2.0
		 *
		 * @param array $strings Translation strings.
		 */
		return apply_filters( self::$prefix . '_translation_strings', $strings );
	}

	/**
	 * Get the admin menus.
	 *
	 * @return array Admin menus.
	 */
	public function get_menus() {
		$menus = array();

		// Settings menu.
		$menus[] = array(
			'settings_page' => true,
			'type'          => 'submenu',
			'parent_slug'   => 'edit.php?post_type=wz_knowledgebase',
			'page_title'    => esc_html__( 'Knowledge Base Settings', 'knowledgebase' ),
			'menu_title'    => esc_html__( 'Settings', 'knowledgebase' ),
			'menu_slug'     => $this->menu_slug,
		);

		return $menus;
	}

	/**
	 * Array containing the settings' sections.
	 *
	 * @since 2.3.0
	 *
	 * @return array Settings array
	 */
	public static function get_settings_sections() {
		$sections = array(
			'general' => __( 'General', 'knowledgebase' ),
			'output'  => __( 'Output', 'knowledgebase' ),
			'styles'  => __( 'Styles', 'knowledgebase' ),
			'pro'     => __( 'Pro', 'knowledgebase' ),
		);

		/**
		 * Filter the array containing the settings' sections.
		 *
		 * @since 2.2.0
		 *
		 * @param array $sections Array of settings' sections
		 */
		return apply_filters( self::$prefix . '_settings_sections', $sections );
	}


	/**
	 * Retrieve the array of plugin settings
	 *
	 * @since 2.3.0
	 *
	 * @return array Settings array
	 */
	public static function get_registered_settings() {
		$settings = array();
		$sections = self::get_settings_sections();

		foreach ( $sections as $section => $value ) {
			$method_name = 'settings_' . $section;
			if ( method_exists( __CLASS__, $method_name ) ) {
				$settings[ $section ] = self::$method_name();
			}
		}

		/**
		 * Filters the settings array
		 *
		 * @since 2.2.0
		 *
		 * @param array $Knowledgebase_setings Settings array
		 */
		return apply_filters( self::$prefix . '_registered_settings', $settings );
	}

	/**
	 * Retrieve the array of General settings
	 *
	 * @since 2.3.0
	 *
	 * @return array General settings array
	 */
	public static function settings_general() {
		$settings = array(
			'multi_product_header' => array(
				'id'      => 'multi_product_header',
				'name'    => '<h3>' . esc_html__( 'Multi-Product Mode', 'knowledgebase' ) . '</h3>',
				'desc'    => '',
				'type'    => 'header',
				'default' => '',
			),
			'multi_product'        => array(
				'id'      => 'multi_product',
				'name'    => esc_html__( 'Enable Multi-Product Mode', 'knowledgebase' ),
				'desc'    => esc_html__(
					'Enable this option to use a dedicated “Products” menu to organize your knowledge base articles and sections by product. This system allows you to assign each article or section to one or more products, making it easier to manage documentation for different software, hardware, or service lines. If your knowledge base does not need this level of organization, you can leave this option disabled. This is a transitional feature for advanced organization and future compatibility.',
					'knowledgebase'
				),
				'type'    => 'checkbox',
				'default' => false,
			),
			'permalink_header'     => array(
				'id'   => 'permalink_header',
				'name' => '<h3>' . esc_html__( 'Permalinks', 'knowledgebase' ) . '</h3>',
				'desc' => esc_html__( 'The following settings affect the permalinks of the knowledge base. These are set when registering the custom post type and taxonomy. Please visit the Permalinks page in the Settings menu to refresh permalinks if you get 404 errors.', 'knowledgebase' ),
				'type' => 'header',
			),
			'kb_slug'              => array(
				'id'          => 'kb_slug',
				'name'        => esc_html__( 'Knowledge Base slug', 'knowledgebase' ),
				'desc'        => esc_html__( 'This will set the opening path of the URL of the knowledge base and is set when registering the custom post type', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => 'knowledgebase',
				'field_class' => 'large-text',
			),
			'product_slug'         => array(
				'id'          => 'product_slug',
				'name'        => esc_html__( 'Product slug', 'knowledgebase' ),
				'desc'        => esc_html__( 'This slug forms part of the URL for product pages when Multi-Product Mode is enabled. The value is used when registering the custom taxonomy.', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => 'kb/product',
				'field_class' => 'large-text',
			),
			'category_slug'        => array(
				'id'          => 'category_slug',
				'name'        => esc_html__( 'Section slug', 'knowledgebase' ),
				'desc'        => esc_html__( 'Each section is a section of the knowledge base. This setting is used when registering the custom section and forms a part of the URL when browsing section archives', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => 'kb/section',
				'field_class' => 'large-text',
			),
			'tag_slug'             => array(
				'id'          => 'tag_slug',
				'name'        => esc_html__( 'Tags slug', 'knowledgebase' ),
				'desc'        => esc_html__( 'Each article can have multiple tags. This setting is used when registering the custom tag and forms a part of the URL when browsing tag archives', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => 'kb/tags',
				'field_class' => 'large-text',
			),
			'article_permalink'    => array(
				'id'          => 'article_permalink',
				'name'        => esc_html__( 'Article Permalink Structure', 'knowledgebase' ),
				'desc'        => esc_html__( 'Structure for article URLs. Default: %postname%', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => '%postname%',
				'field_class' => 'large-text',
			),
			'performance_header'   => array(
				'id'   => 'performance_header',
				'name' => '<h3>' . esc_html__( 'Performance', 'knowledgebase' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'cache'                => array(
				'id'      => 'cache',
				'name'    => esc_html__( 'Enable cache', 'knowledgebase' ),
				'desc'    => esc_html__( 'Cache the output of the queries to speed up retrieval of the knowledgebase. Recommended for large knowledge bases', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'cache_expiry'         => array(
				'id'      => 'cache_expiry',
				'name'    => esc_html__( 'Cache Time', 'knowledgebase' ),
				'desc'    => esc_html__( 'How long should the knowledge base be cached for. Default is 1 day.', 'knowledgebase' ),
				'type'    => 'select',
				'default' => DAY_IN_SECONDS,
				'options' => array(
					0                    => esc_html__( 'No expiry', 'knowledgebase' ),
					HOUR_IN_SECONDS      => esc_html__( '1 Hour', 'knowledgebase' ),
					6 * HOUR_IN_SECONDS  => esc_html__( '6 Hours', 'knowledgebase' ),
					12 * HOUR_IN_SECONDS => esc_html__( '12 Hours', 'knowledgebase' ),
					DAY_IN_SECONDS       => esc_html__( '1 Day', 'knowledgebase' ),
					3 * DAY_IN_SECONDS   => esc_html__( '3 Days', 'knowledgebase' ),
					WEEK_IN_SECONDS      => esc_html__( '1 Week', 'knowledgebase' ),
					2 * WEEK_IN_SECONDS  => esc_html__( '2 Weeks', 'knowledgebase' ),
					MONTH_IN_SECONDS     => esc_html__( '30 Days', 'knowledgebase' ),
					2 * MONTH_IN_SECONDS => esc_html__( '60 Days', 'knowledgebase' ),
					3 * MONTH_IN_SECONDS => esc_html__( '90 Days', 'knowledgebase' ),
					YEAR_IN_SECONDS      => esc_html__( '1 Year', 'knowledgebase' ),
				),
				'pro'     => true,
			),
			'uninstall_header'     => array(
				'id'      => 'uninstall_header',
				'name'    => '<h3>' . esc_html__( 'Uninstall options', 'knowledgebase' ) . '</h3>',
				'desc'    => '',
				'type'    => 'header',
				'default' => '',
			),
			'uninstall_options'    => array(
				'id'      => 'uninstall_options',
				'name'    => esc_html__( 'Delete options on uninstall', 'knowledgebase' ),
				'desc'    => esc_html__( 'Check this box to delete the settings on this page when the plugin is deleted via the Plugins page in your WordPress Admin', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'uninstall_data'       => array(
				'id'      => 'uninstall_data',
				'name'    => esc_html__( 'Delete all content on uninstall', 'knowledgebase' ),
				'desc'    => esc_html__( 'Check this box to delete all the posts, categories and tags created by the plugin. There is no way to restore the data if you choose this option', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'feed_header'          => array(
				'id'      => 'feed_header',
				'name'    => '<h3>' . esc_html__( 'Feed options', 'knowledgebase' ) . '</h3>',
				'desc'    => '',
				'type'    => 'header',
				'default' => '',
			),
			'include_in_feed'      => array(
				'id'      => 'include_in_feed',
				'name'    => esc_html__( 'Include in feed', 'knowledgebase' ),
				'desc'    => esc_html__( 'Adds the knowledge base articles to the main RSS feed for your site', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'disable_kb_feed'      => array(
				'id'      => 'disable_kb_feed',
				'name'    => esc_html__( 'Disable KB feed', 'knowledgebase' ),
				/* translators: 1: Opening link tag, 2: Closing link tag. */
				'desc'    => sprintf( esc_html__( 'The knowledge base articles have a default feed. This option will disable the feed. You might need to %1$srefresh your permalinks%2$s when changing this option.', 'knowledgebase' ), '<a href="' . admin_url( 'options-permalink.php' ) . '" target="_blank">', '</a>' ),
				'type'    => 'checkbox',
				'default' => false,
			),
		);

		/**
		 * Filters the General settings array
		 *
		 * @since 2.2.0
		 *
		 * @param array $settings General Settings array
		 */
		return apply_filters( self::$prefix . '_settings_general', $settings );
	}


	/**
	 * Retrieve the array of Output settings
	 *
	 * @since 2.3.0
	 *
	 * @return array Output settings array
	 */
	public static function settings_output() {

		$settings = array(
			'kb_title'              => array(
				'id'          => 'kb_title',
				'name'        => esc_html__( 'Knowledge base title', 'knowledgebase' ),
				'desc'        => esc_html__( 'This will be displayed as the title of the archive title as well as on other relevant places.', 'knowledgebase' ),
				'type'        => 'text',
				'options'     => 'Knowledge Base',
				'field_class' => 'large-text',
			),
			'category_level'        => array(
				'id'      => 'category_level',
				'name'    => esc_html__( 'First section level', 'knowledgebase' ),
				'desc'    => esc_html__( 'This option allows you to create multi-level knowledge bases. This works in conjunction with the inbuilt styles. Set to 1 to lay out the top level sections in a grid. Set to 2 to lay out the second level categories in the grid. This is great if you have multiple products and want to create separate knowledge bases for each of them. The default option is 2 and was the behaviour of this plugin before v1.5.0.', 'knowledgebase' ),
				'type'    => 'number',
				'default' => '2',
				'size'    => 'small',
				'min'     => '1',
				'max'     => '5',
			),
			'show_article_count'    => array(
				'id'      => 'show_article_count',
				'name'    => esc_html__( 'Show article count', 'knowledgebase' ),
				'desc'    => esc_html__( 'If selected, the number of articles will be displayed in an orange circle next to the header. You can override the color by styling wzkb_section_count', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'show_excerpt'          => array(
				'id'      => 'show_excerpt',
				'name'    => esc_html__( 'Show excerpt', 'knowledgebase' ),
				'desc'    => esc_html__( 'Select to include the post excerpt after the article link', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'clickable_section'     => array(
				'id'      => 'clickable_section',
				'name'    => esc_html__( 'Link section title', 'knowledgebase' ),
				'desc'    => esc_html__( 'If selected, the title of each section of the knowledgebase will be linked to its own page', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'show_empty_sections'   => array(
				'id'      => 'show_empty_sections',
				'name'    => esc_html__( 'Show empty sections', 'knowledgebase' ),
				'desc'    => esc_html__( 'If selected, sections with no articles will also be displayed', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'limit'                 => array(
				'id'      => 'limit',
				'name'    => esc_html__( 'Max articles per section', 'knowledgebase' ),
				'desc'    => esc_html__( 'Enter the number of articles that should be displayed in each section when viewing the knowledge base. After this limit is reached, the footer is displayed with the more link to view the category.', 'knowledgebase' ),
				'type'    => 'number',
				'default' => '5',
				'size'    => 'small',
				'min'     => '1',
				'max'     => '500',
			),
			'show_sidebar'          => array(
				'id'      => 'show_sidebar',
				'name'    => esc_html__( 'Show sidebar', 'knowledgebase' ),
				'desc'    => esc_html__( 'Add the sidebar of your theme into the inbuilt templates for archive, sections and search. Activate this option if your theme does not already include this.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'show_related_articles' => array(
				'id'      => 'show_related_articles',
				'name'    => esc_html__( 'Show related articles', 'knowledgebase' ),
				'desc'    => esc_html__( 'Add related articles at the bottom of the knowledge base article. Only works when using the inbuilt template.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
			),
		);

		/**
		 * Filters the Output settings array
		 *
		 * @since 2.2.0
		 *
		 * @param array $settings Output Settings array
		 */
		return apply_filters( self::$prefix . '_settings_output', $settings );
	}


	/**
	 * Retrieve the array of Styles settings
	 *
	 * @since 2.3.0
	 *
	 * @return array Styles settings array
	 */
	public static function settings_styles() {
		$settings = array(
			'include_styles' => array(
				'id'      => 'include_styles',
				'name'    => esc_html__( 'Include inbuilt styles', 'knowledgebase' ),
				'desc'    => esc_html__( 'Uncheck this to disable this plugin from adding the inbuilt styles. You will need to add your own CSS styles if you disable this option', 'knowledgebase' ),
				'type'    => 'checkbox',
				'options' => true,
			),
			'kb_style'       => array(
				'id'      => 'kb_style',
				'name'    => esc_html__( 'Knowledge Base Style', 'knowledgebase' ),
				'desc'    => esc_html__( 'Select a visual style for your knowledge base display. Premium styles are available in the Pro version.', 'knowledgebase' ),
				'type'    => 'select',
				'options' => self::get_kb_styles(),
				'default' => 'classic',
			),
			'columns'        => array(
				'id'      => 'columns',
				'name'    => esc_html__( 'Number of columns', 'knowledgebase' ),
				'desc'    => esc_html__( 'Set number of columns to display the knowledge base archives. Works with all styles except Legacy.', 'knowledgebase' ),
				'type'    => 'number',
				'options' => '2',
				'size'    => 'small',
				'min'     => '1',
				'max'     => '5',
			),
			'custom_css'     => array(
				'id'          => 'custom_css',
				'name'        => esc_html__( 'Custom CSS', 'knowledgebase' ),
				'desc'        => esc_html__( 'Enter any custom valid CSS without any wrapping &lt;style&gt; tags', 'knowledgebase' ),
				'type'        => 'css',
				'options'     => '',
				'field_class' => 'codemirror_css',
			),
		);

		/**
		 * Filters the Styles settings array
		 *
		 * @since 2.2.0
		 *
		 * @param array $settings Styles settings array
		 */
		return apply_filters( self::$prefix . '_settings_styles', $settings );
	}

	/**
	 * Retrieve the array of Pro settings
	 *
	 * @since 3.0.0
	 *
	 * @return array Pro settings array
	 */
	public static function settings_pro() {
		$settings = array(
			'rating_header'             => array(
				'id'   => 'rating_header',
				'name' => '<h3>' . esc_html__( 'Article Rating', 'knowledgebase' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'rating_system'             => array(
				'id'      => 'rating_system',
				'name'    => esc_html__( 'Enable Rating System', 'knowledgebase' ),
				'desc'    => esc_html__( 'Allow visitors to rate the quality of knowledge base articles.', 'knowledgebase' ),
				'type'    => 'select',
				'default' => 'disabled',
				'options' => array(
					'disabled' => esc_html__( 'Disabled', 'knowledgebase' ),
					'binary'   => esc_html__( 'Useful / Not Useful', 'knowledgebase' ),
					'scale'    => esc_html__( '1-5 Star Rating', 'knowledgebase' ),
				),
				'pro'     => true,
			),
			'rating_tracking_method'    => array(
				'id'      => 'rating_tracking_method',
				'name'    => esc_html__( 'Vote Tracking Method', 'knowledgebase' ),
				/* translators: %s: URL to rating system documentation */
				'desc'    => sprintf(
					/* translators: %1$s: Opening link tag, %2$s: Closing link tag. */
					esc_html__( 'Choose how to prevent duplicate votes. Each method has different privacy implications. %1$sLearn more about tracking methods and GDPR compliance%2$s.', 'knowledgebase' ),
					'<a href="https://webberzone.com/support/knowledgebase/rating-system/" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'type'    => 'select',
				'default' => 'cookie',
				'options' => array(
					'none'           => esc_html__( 'No Tracking (allows multiple votes)', 'knowledgebase' ),
					'cookie'         => esc_html__( 'Cookie Only (requires consent)', 'knowledgebase' ),
					'ip'             => esc_html__( 'IP Address Only (stores personal data)', 'knowledgebase' ),
					'cookie_ip'      => esc_html__( 'Cookie + IP Address (requires both)', 'knowledgebase' ),
					'logged_in_only' => esc_html__( 'Logged-in Users Only (best for authenticated sites)', 'knowledgebase' ),
				),
				'pro'     => true,
			),
			'show_rating_stats'         => array(
				'id'      => 'show_rating_stats',
				'name'    => esc_html__( 'Show Rating Statistics', 'knowledgebase' ),
				'desc'    => esc_html__( 'Display the average rating and vote count below the rating buttons.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'options' => true,
				'pro'     => true,
			),
			'beacon_header'             => array(
				'id'   => 'beacon_header',
				'name' => '<h3>' . esc_html__( 'Beacon Help Widget', 'knowledgebase' ) . '</h3>',
				'desc' => esc_html__( 'A floating help widget that provides self-service support with search, suggested articles, and contact form.', 'knowledgebase' ),
				'type' => 'header',
			),
			'beacon_enabled'            => array(
				'id'      => 'beacon_enabled',
				'name'    => esc_html__( 'Enable Beacon', 'knowledgebase' ),
				'desc'    => esc_html__( 'Display a floating help widget on your site for self-service support.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => false,
				'pro'     => true,
			),
			'beacon_display_location'   => array(
				'id'      => 'beacon_display_location',
				'name'    => esc_html__( 'Display Location', 'knowledgebase' ),
				'desc'    => esc_html__( 'Choose where the beacon appears on your site.', 'knowledgebase' ),
				'type'    => 'select',
				'default' => 'kb_only',
				'options' => array(
					'kb_only'  => esc_html__( 'Knowledge Base Only', 'knowledgebase' ),
					'sitewide' => esc_html__( 'Entire Site', 'knowledgebase' ),
				),
				'pro'     => true,
			),
			'beacon_position'           => array(
				'id'      => 'beacon_position',
				'name'    => esc_html__( 'Button Position', 'knowledgebase' ),
				'desc'    => esc_html__( 'Choose where the beacon button appears on the screen.', 'knowledgebase' ),
				'type'    => 'select',
				'default' => 'right',
				'options' => array(
					'right' => esc_html__( 'Bottom Right', 'knowledgebase' ),
					'left'  => esc_html__( 'Bottom Left', 'knowledgebase' ),
				),
				'pro'     => true,
			),
			'beacon_button_style'       => array(
				'id'      => 'beacon_button_style',
				'name'    => esc_html__( 'Button Style', 'knowledgebase' ),
				'desc'    => esc_html__( 'Choose how the beacon button is displayed.', 'knowledgebase' ),
				'type'    => 'select',
				'default' => 'icon',
				'options' => array(
					'icon'          => esc_html__( 'Icon Only', 'knowledgebase' ),
					'text'          => esc_html__( 'Text Only', 'knowledgebase' ),
					'icon_and_text' => esc_html__( 'Icon and Text', 'knowledgebase' ),
				),
				'pro'     => true,
			),
			'beacon_button_text'        => array(
				'id'          => 'beacon_button_text',
				'name'        => esc_html__( 'Button Text', 'knowledgebase' ),
				'desc'        => esc_html__( 'Text to display on the beacon button (when text style is selected).', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => __( 'Help', 'knowledgebase' ),
				'field_class' => 'regular-text',
				'pro'         => true,
			),
			'beacon_color'              => array(
				'id'          => 'beacon_color',
				'name'        => esc_html__( 'Beacon Color', 'knowledgebase' ),
				'desc'        => esc_html__( 'Primary color for the beacon button and interface elements.', 'knowledgebase' ),
				'type'        => 'color',
				'default'     => '#617DEC',
				'field_class' => 'color-field',
				'pro'         => true,
			),
			'beacon_hover_color'        => array(
				'id'          => 'beacon_hover_color',
				'name'        => esc_html__( 'Beacon Hover Color', 'knowledgebase' ),
				'desc'        => esc_html__( 'Hover color for buttons and interactive elements.', 'knowledgebase' ),
				'type'        => 'color',
				'default'     => '#4c63d2',
				'field_class' => 'color-field',
				'pro'         => true,
			),
			'beacon_text_color'         => array(
				'id'          => 'beacon_text_color',
				'name'        => esc_html__( 'Beacon Text Color', 'knowledgebase' ),
				'desc'        => esc_html__( 'Text color for the beacon button and interface elements.', 'knowledgebase' ),
				'type'        => 'color',
				'default'     => '#ffffff',
				'field_class' => 'color-field',
				'pro'         => true,
			),
			'beacon_hover_text_color'   => array(
				'id'          => 'beacon_hover_text_color',
				'name'        => esc_html__( 'Beacon Hover Text Color', 'knowledgebase' ),
				'desc'        => esc_html__( 'Text color for the beacon button on hover.', 'knowledgebase' ),
				'type'        => 'color',
				'default'     => '#ffffff',
				'field_class' => 'color-field',
				'pro'         => true,
			),
			'beacon_panel_bg_color'     => array(
				'id'          => 'beacon_panel_bg_color',
				'name'        => esc_html__( 'Panel Background Color', 'knowledgebase' ),
				'desc'        => esc_html__( 'Background color for the beacon panel.', 'knowledgebase' ),
				'type'        => 'color',
				'default'     => '#ffffff',
				'field_class' => 'color-field',
				'pro'         => true,
			),
			'beacon_panel_text_color'   => array(
				'id'          => 'beacon_panel_text_color',
				'name'        => esc_html__( 'Panel Text Color', 'knowledgebase' ),
				'desc'        => esc_html__( 'Default text color within the beacon panel.', 'knowledgebase' ),
				'type'        => 'color',
				'default'     => '#1a1a1a',
				'field_class' => 'color-field',
				'pro'         => true,
			),
			'beacon_link_hover_color'   => array(
				'id'          => 'beacon_link_hover_color',
				'name'        => esc_html__( 'Link Hover Background', 'knowledgebase' ),
				'desc'        => esc_html__( 'Background color when hovering over beacon links and list items.', 'knowledgebase' ),
				'type'        => 'color',
				'default'     => '#f3f4f6',
				'field_class' => 'color-field',
				'pro'         => true,
			),
			'beacon_greeting'           => array(
				'id'          => 'beacon_greeting',
				'name'        => esc_html__( 'Greeting Message', 'knowledgebase' ),
				'desc'        => esc_html__( 'Welcome message shown when the beacon opens.', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => __( 'Hi! How can we help you?', 'knowledgebase' ),
				'field_class' => 'large-text',
				'pro'         => true,
			),
			'beacon_search_placeholder' => array(
				'id'          => 'beacon_search_placeholder',
				'name'        => esc_html__( 'Search Placeholder', 'knowledgebase' ),
				'desc'        => esc_html__( 'Placeholder text for the search input field.', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => __( 'Search for answers...', 'knowledgebase' ),
				'field_class' => 'large-text',
				'pro'         => true,
			),
			'beacon_contact_enabled'    => array(
				'id'      => 'beacon_contact_enabled',
				'name'    => esc_html__( 'Enable Contact Form', 'knowledgebase' ),
				'desc'    => esc_html__( 'Allow visitors to send messages through the beacon.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
				'pro'     => true,
			),
			'beacon_contact_email'      => array(
				'id'          => 'beacon_contact_email',
				'name'        => esc_html__( 'Contact Email', 'knowledgebase' ),
				'desc'        => esc_html__( 'Email address where beacon contact form submissions will be sent.', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => get_option( 'admin_email' ),
				'field_class' => 'regular-text',
				'pro'         => true,
			),
			'beacon_show_on_mobile'     => array(
				'id'      => 'beacon_show_on_mobile',
				'name'    => esc_html__( 'Show on Mobile', 'knowledgebase' ),
				'desc'    => esc_html__( 'Display the beacon on mobile devices.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
				'pro'     => true,
			),
			'beacon_enable_animation'   => array(
				'id'      => 'beacon_enable_animation',
				'name'    => esc_html__( 'Enable Animations', 'knowledgebase' ),
				'desc'    => esc_html__( 'Enable smooth animations and transitions for the beacon.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
				'pro'     => true,
			),
		);

		/**
		 * Filters the Pro settings array
		 *
		 * @since 3.0.0
		 *
		 * @param array $settings Pro Settings array
		 */
		return apply_filters( self::$prefix . '_settings_pro', $settings );
	}

	/**
	 * Get available KB styles.
	 *
	 * Returns free styles by default. Pro and other extensions can add their styles via filter.
	 *
	 * @since 3.0.0
	 *
	 * @return array Array of style options.
	 */
	public static function get_kb_styles() {
		// Free styles only.
		$styles = array(
			'legacy'  => esc_html__( 'Legacy', 'knowledgebase' ),
			'classic' => esc_html__( 'Classic', 'knowledgebase' ),
		);

		/**
		 * Filter available KB styles.
		 *
		 * Allows Pro or other extensions to add their styles to the dropdown.
		 *
		 * @since 3.0.0
		 *
		 * @param array $styles Array of style options (key => label).
		 */
		return apply_filters( 'wzkb_kb_styles', $styles );
	}

	/**
	 * Adding WordPress plugin action links.
	 *
	 * @since 3.0.0
	 *
	 * @param array $links Array of links.
	 * @return array
	 */
	public function plugin_actions_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'edit.php?post_type=wz_knowledgebase&amp;page=' . $this->menu_slug ) . '">' . esc_html__( 'Settings', 'knowledgebase' ) . '</a>',
			),
			$links
		);
	}

	/**
	 * Add meta links on Plugins page.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $links Array of Links.
	 * @param string $file Current file.
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {

		if ( false !== strpos( $file, 'knowledgebase.php' ) ) {
			$new_links = array(
				'support'    => '<a href = "https://wordpress.org/support/plugin/knowledgebase">' . esc_html__( 'Support', 'knowledgebase' ) . '</a>',
				'donate'     => '<a href = "https://ajaydsouza.com/donate/">' . esc_html__( 'Donate', 'knowledgebase' ) . '</a>',
				'contribute' => '<a href = "https://github.com/WebberZone/knowledgebase">' . esc_html__( 'Contribute', 'knowledgebase' ) . '</a>',
			);

			$links = array_merge( $links, $new_links );
		}
		return $links;
	}

	/**
	 * Get the help sidebar content to display on the plugin settings page.
	 *
	 * @since 1.8.0
	 */
	public function get_help_sidebar() {
		$help_sidebar =
			/* translators: 1: Plugin support site link. */
			'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%s">support site</a>.', 'knowledgebase' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
			/* translators: 1: WordPress.org support forums link. */
			'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%s">WordPress.org support forums</a>.', 'knowledgebase' ), esc_url( 'https://wordpress.org/support/plugin/knowledgebase' ) ) . '</p>' .
			'<p>' . sprintf(
				/* translators: 1: Github issues link, 2: Github plugin page link. */
				__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'knowledgebase' ),
				esc_url( 'https://github.com/WebberZone/knowledgebase/issues' ),
				esc_url( 'https://github.com/WebberZone/knowledgebase' )
			) . '</p>';

		/**
		 * Filter to modify the help sidebar content.
		 *
		 * @since 2.3.0
		 *
		 * @param string $help_sidebar Help sidebar content.
		 */
		return apply_filters( self::$prefix . '_settings_help', $help_sidebar );
	}

	/**
	 * Get the help tabs to display on the plugin settings page.
	 *
	 * @since 2.3.0
	 */
	public function get_help_tabs() {
		$help_tabs = array(
			array(
				'id'      => 'wzkb-settings-general',
				'title'   => __( 'General', 'knowledgebase' ),
				'content' =>
				'<p>' . __( 'This screen provides the basic settings for configuring your knowledge base.', 'knowledgebase' ) . '</p>' .
					'<p>' . __( 'Set the knowledge base slugs which drive what the urls are for the knowledge base homepage, articles, categories and tags.', 'knowledgebase' ) . '</p>',
			),
			array(
				'id'      => 'wzkb-settings-styles',
				'title'   => __( 'Styles', 'knowledgebase' ),
				'content' =>
				'<p>' . __( 'This screen provides options to control the look and feel of the knowledge base.', 'knowledgebase' ) . '</p>' .
					'<p>' . __( 'Disable the styles included within the plugin and/or add your own CSS styles to customize this.', 'knowledgebase' ) . '</p>',
			),
		);

		/**
		 * Filter to add more help tabs.
		 *
		 * @since 2.2.0
		 *
		 * @param array $help_tabs Associative array of help tabs.
		 */
		return apply_filters( self::$prefix . '_settings_help_tabs', $help_tabs );
	}

	/**
	 * Add CSS to admin head.
	 *
	 * @since 2.2.0
	 */
	public function admin_head() {
		if ( ! is_customize_preview() ) {
			$css = '
				<style type="text/css">
					a.wzkb_button {
						background: green;
						padding: 10px;
						color: white;
						text-decoration: none;
						text-shadow: none;
						border-radius: 3px;
						transition: all 0.3s ease 0s;
						border: 1px solid green;
					}
					a.wzkb_button:hover {
						box-shadow: 3px 3px 10px #666;
					}
				</style>';

			echo $css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Add footer text on the plugin page.
	 *
	 * @since 2.0.0
	 */
	public static function get_admin_footer_text() {
		return sprintf(
			/* translators: 1: Opening achor tag with Plugin page link, 2: Closing anchor tag, 3: Opening anchor tag with review link. */
			__( 'Thank you for using %1$sWebberZone Knowledge_Base%2$s! Please %3$srate us%2$s on %3$sWordPress.org%2$s', 'knowledgebase' ),
			'<a href="https://webberzone.com/plugins/knowledgebase/" target="_blank">',
			'</a>',
			'<a href="https://wordpress.org/support/plugin/knowledgebase/reviews/#new-post" target="_blank">'
		);
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 2.3.0
	 *
	 * @param string $hook Current hook.
	 */
	public function admin_enqueue_scripts( $hook ) {

		if ( ! isset( $this->settings_api->settings_page ) || $hook !== $this->settings_api->settings_page ) {
			return;
		}
		wp_enqueue_script( 'wzkb-admin' );
		wp_enqueue_style( 'wzkb-admin-ui' );
	}

	/**
	 * Modify settings when they are being saved.
	 *
	 * @since 2.3.0
	 *
	 * @param  array $settings Settings array.
	 * @return array Sanitized settings array.
	 */
	public function change_settings_on_save( $settings ) {

		// Delete the cache.
		\WebberZone\Knowledge_Base\Util\Cache::delete();

		flush_rewrite_rules( true );

		return $settings;
	}
}
