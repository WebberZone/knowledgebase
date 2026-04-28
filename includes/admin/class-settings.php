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
		Hook_Registry::add_action( self::$prefix . '_settings_form_buttons', array( $this, 'render_wizard_button' ), 20 );
	}

	/**
	 * AJAX handler for Tom Select taxonomy searches.
	 *
	 * Used by Settings API and Setup Wizard taxonomy autocomplete fields.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public static function taxonomy_search_tom_select(): void {
		// Verify nonce.
		if ( ! isset( $_REQUEST['nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_send_json_error();
		}

		$nonce_valid = wp_verify_nonce( sanitize_key( $_REQUEST['nonce'] ), self::$prefix . '_taxonomy_search_tom_select' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $nonce_valid ) {
			wp_send_json_error();
		}

		if ( ! isset( $_REQUEST['endpoint'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_send_json_error();
		}

		$endpoint = sanitize_key( $_REQUEST['endpoint'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$term     = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$comma = _x( ',', 'tag delimiter', 'knowledgebase' );
		if ( ',' !== $comma ) {
			$term = str_replace( $comma, ',', $term );
		}
		if ( false !== strpos( $term, ',' ) ) {
			$term_parts = explode( ',', $term );
			$term       = $term_parts[ count( $term_parts ) - 1 ];
		}
		$term = trim( $term );

		$allowed_endpoints = array(
			'category' => 'wzkb_category',
			'product'  => 'wzkb_product',
			'tag'      => 'wzkb_tag',
			'post_tag' => 'wzkb_tag',
		);

		$allowed_taxonomies = array_values( $allowed_endpoints );
		if ( isset( $allowed_endpoints[ $endpoint ] ) ) {
			$taxonomy = $allowed_endpoints[ $endpoint ];
		} elseif ( in_array( $endpoint, $allowed_taxonomies, true ) ) {
			$taxonomy = $endpoint;
		} else {
			wp_send_json_success( array() );
		}
		$tax = get_taxonomy( $taxonomy );
		if ( ! $tax ) {
			wp_send_json_success( array() );
		}

		if ( empty( $tax->cap->assign_terms ) || ! current_user_can( $tax->cap->assign_terms ) ) {
			wp_send_json_error();
		}

		/** This filter has been defined in /wp-admin/includes/ajax-actions.php */
		$term_search_min_chars = (int) apply_filters( 'term_search_min_chars', 2, $tax, $term );
		if ( ( 0 === $term_search_min_chars ) || ( strlen( $term ) < $term_search_min_chars ) ) {
			wp_send_json_success( array() );
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'name__like' => $term,
				'hide_empty' => false,
				'number'     => 20,
			)
		);

		$results = array();
		foreach ( (array) $terms as $found_term ) {
			if ( ! ( $found_term instanceof \WP_Term ) ) {
				continue;
			}

			$results[] = array(
				'value' => sprintf( '%1$s (%2$s:%3$d)', $found_term->name, $found_term->taxonomy, (int) $found_term->term_taxonomy_id ),
				'text'  => $found_term->name,
			);
		}

		wp_send_json_success( $results );
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
		$settings      = self::get_registered_settings();
		$default_types = array(
			'color',
			'css',
			'csv',
			'file',
			'html',
			'multicheck',
			'number',
			'numbercsv',
			'password',
			'postids',
			'posttypes',
			'radio',
			'radiodesc',
			'repeater',
			'select',
			'sensitive',
			'taxonomies',
			'text',
			'textarea',
			'thumbsizes',
			'url',
			'wysiwyg',
		);

		// Loop through each section.
		foreach ( $settings as $section_settings ) {
			// Loop through each setting in the section.
			foreach ( $section_settings as $setting ) {
				if ( ! isset( $setting['id'] ) ) {
					continue;
				}

				$setting_id    = $setting['id'];
				$setting_type  = $setting['type'] ?? '';
				$default_value = '';

				// When checkbox is set to true, set this to 1.
				if ( 'checkbox' === $setting_type ) {
					$default_value = isset( $setting['default'] ) ? (int) (bool) $setting['default'] : 0;
				} elseif ( isset( $setting['default'] ) && in_array( $setting_type, $default_types, true ) ) {
					$default_value = $setting['default'];
				}

				$defaults[ $setting_id ] = $default_value;
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
			'multi_product'      => array(
				'id'      => 'multi_product',
				'name'    => esc_html__( 'Enable Multi-Product Mode', 'knowledgebase' ),
				'desc'    => esc_html__(
					'Enable this option to use a dedicated “Products” menu to organize your knowledge base articles and sections by product. This system allows you to assign each article or section to one or more products, making it easier to manage documentation for different software, hardware, or service lines. If your knowledge base does not need this level of organization, you can leave this option disabled.',
					'knowledgebase'
				),
				'type'    => 'checkbox',
				'default' => false,
			),
			'kb_homepage_mode'   => array(
				'id'      => 'kb_homepage_mode',
				'name'    => esc_html__( 'Use Knowledge Base as Homepage', 'knowledgebase' ),
				'desc'    => esc_html__( 'Enable this option to display the Knowledge Base on the site homepage. The Knowledge Base URL will serve as the homepage, and the Knowledge Base archive URL will redirect to it.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => false,
				'pro'     => true,
			),
			'permalink_header'   => array(
				'id'   => 'permalink_header',
				'name' => '<h3>' . esc_html__( 'Permalinks', 'knowledgebase' ) . '</h3>',
				'desc' => esc_html__( 'The following settings affect the permalinks of the knowledge base. These are set when registering the custom post type and taxonomy. Please visit the Permalinks page in the Settings menu to refresh permalinks if you get 404 errors.', 'knowledgebase' ),
				'type' => 'header',
			),
			'kb_slug'            => array(
				'id'          => 'kb_slug',
				'name'        => esc_html__( 'Knowledge Base slug', 'knowledgebase' ),
				'desc'        => esc_html__( 'This will set the opening path of the URL of the knowledge base and is set when registering the custom post type', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => 'knowledgebase',
				'field_class' => 'large-text',
			),
			'product_slug'       => array(
				'id'          => 'product_slug',
				'name'        => esc_html__( 'Product slug', 'knowledgebase' ),
				'desc'        => esc_html__( 'This slug forms part of the URL for product pages when Multi-Product Mode is enabled. The value is used when registering the custom taxonomy.', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => 'kb/product',
				'field_class' => 'large-text',
			),
			'category_slug'      => array(
				'id'          => 'category_slug',
				'name'        => esc_html__( 'Section slug', 'knowledgebase' ),
				'desc'        => esc_html__( 'Each section is a section of the knowledge base. This setting is used when registering the custom section and forms a part of the URL when browsing section archives', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => 'kb/section',
				'field_class' => 'large-text',
			),
			'tag_slug'           => array(
				'id'          => 'tag_slug',
				'name'        => esc_html__( 'Tags slug', 'knowledgebase' ),
				'desc'        => esc_html__( 'Each article can have multiple tags. This setting is used when registering the custom tag and forms a part of the URL when browsing tag archives', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => 'kb/tags',
				'field_class' => 'large-text',
			),
			'article_permalink'  => array(
				'id'          => 'article_permalink',
				'name'        => esc_html__( 'Article Permalink Structure', 'knowledgebase' ),
				'desc'        => esc_html__( 'Structure for article URLs. Leave empty to use default which is the "Knowledge Base slug/%postname%".', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => '',
				'field_class' => 'large-text',
				'pro'         => true,
			),
			'performance_header' => array(
				'id'   => 'performance_header',
				'name' => '<h3>' . esc_html__( 'Performance', 'knowledgebase' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'cache'              => array(
				'id'      => 'cache',
				'name'    => esc_html__( 'Enable cache', 'knowledgebase' ),
				'desc'    => esc_html__( 'Cache the output of the queries to speed up retrieval of the knowledgebase. Recommended for large knowledge bases', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'cache_expiry'       => array(
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
			'uninstall_header'   => array(
				'id'      => 'uninstall_header',
				'name'    => '<h3>' . esc_html__( 'Uninstall options', 'knowledgebase' ) . '</h3>',
				'desc'    => '',
				'type'    => 'header',
				'default' => '',
			),
			'uninstall_options'  => array(
				'id'      => 'uninstall_options',
				'name'    => esc_html__( 'Delete options on uninstall', 'knowledgebase' ),
				'desc'    => esc_html__( 'Check this box to delete the settings on this page when the plugin is deleted via the Plugins page in your WordPress Admin', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'uninstall_data'     => array(
				'id'      => 'uninstall_data',
				'name'    => esc_html__( 'Delete all content on uninstall', 'knowledgebase' ),
				'desc'    => esc_html__( 'Check this box to delete all the posts, categories and tags created by the plugin. There is no way to restore the data if you choose this option', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'feed_header'        => array(
				'id'      => 'feed_header',
				'name'    => '<h3>' . esc_html__( 'Feed options', 'knowledgebase' ) . '</h3>',
				'desc'    => '',
				'type'    => 'header',
				'default' => '',
			),
			'include_in_feed'    => array(
				'id'      => 'include_in_feed',
				'name'    => esc_html__( 'Include in feed', 'knowledgebase' ),
				'desc'    => esc_html__( 'Adds the knowledge base articles to the main RSS feed for your site', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'disable_kb_feed'    => array(
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
				'desc'        => esc_html__( 'This will be displayed as the archive title and in other relevant places.', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => 'Knowledge Base',
				'field_class' => 'large-text',
			),
			'category_level'        => array(
				'id'      => 'category_level',
				'name'    => esc_html__( 'First section level', 'knowledgebase' ),
				'desc'    => esc_html__( 'Knowledge Base supports an unlimited hierarchy of sections. Set to 1 if using multi-product mode (with sections as the first level for each product). Set to 2 for traditional mode (top-level sections as product categories). This determines which section level is displayed in the grid layout. The default is 2, which was the behavior before version 3.0.', 'knowledgebase' ),
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
				'desc'    => esc_html__( 'If selected, the title of each knowledge base section will link to its own page.', 'knowledgebase' ),
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
				'desc'    => esc_html__( 'Enter the number of articles that should be displayed in each section when viewing the knowledge base. Use -1 to display all articles (no limit). Once this limit is reached, the footer displays a "more link" to view the category.', 'knowledgebase' ),
				'type'    => 'number',
				'default' => 5,
				'size'    => 'small',
				'min'     => -1,
				'max'     => 500,
			),
			'show_sidebar'          => array(
				'id'      => 'show_sidebar',
				'name'    => esc_html__( 'Show sidebar', 'knowledgebase' ),
				'desc'    => esc_html__( 'Add the sidebar of your theme to the built-in templates for archives, sections, and search. This will not work with Block Themes. You will need to select an appropriate block template if you are using a block theme.', 'knowledgebase' ),
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
			'toc_header'            => array(
				'id'   => 'toc_header',
				'name' => '<h3>' . esc_html__( 'Table of Contents', 'knowledgebase' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'show_toc'              => array(
				'id'      => 'show_toc',
				'name'    => esc_html__( 'Show table of contents', 'knowledgebase' ),
				'desc'    => esc_html__( 'Auto-generate a table of contents from headings in article content. Only displays when there are sufficient headings.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'toc_heading_depth'     => array(
				'id'      => 'toc_heading_depth',
				'name'    => esc_html__( 'TOC heading depth', 'knowledgebase' ),
				'desc'    => esc_html__( 'Maximum heading level to include in the table of contents. 2 includes only H2; 3 includes H2 and H3, and so on.', 'knowledgebase' ),
				'type'    => 'number',
				'default' => 4,
				'size'    => 'small',
				'min'     => 2,
				'max'     => 6,
			),
			'toc_min_headings'      => array(
				'id'      => 'toc_min_headings',
				'name'    => esc_html__( 'Minimum headings for TOC', 'knowledgebase' ),
				'desc'    => esc_html__( 'Minimum number of headings required before the table of contents is displayed.', 'knowledgebase' ),
				'type'    => 'number',
				'default' => 3,
				'size'    => 'small',
				'min'     => 1,
				'max'     => 20,
			),
			'toc_title'             => array(
				'id'      => 'toc_title',
				'name'    => esc_html__( 'TOC title', 'knowledgebase' ),
				'desc'    => esc_html__( 'Title displayed above the table of contents. Leave empty to hide the title.', 'knowledgebase' ),
				'type'    => 'text',
				'default' => __( 'Table of Contents', 'knowledgebase' ),
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
			'product_archive_layout' => array(
				'id'      => 'product_archive_layout',
				'name'    => esc_html__( 'Product archive layout', 'knowledgebase' ),
				'desc'    => esc_html__( 'Choose how products are displayed on the main Knowledge Base archive when Multi-Product Mode is enabled. “Sections list” shows each product with its sections listed below. The “Product cards grid” displays products as a grid of cards, allowing visitors to click through to a product page.', 'knowledgebase' ),
				'type'    => 'select',
				'options' => array(
					'sections' => esc_html__( 'Sections list (current behavior)', 'knowledgebase' ),
					'grid'     => esc_html__( 'Product cards grid', 'knowledgebase' ),
				),
				'default' => 'sections',
			),
			'include_styles'         => array(
				'id'      => 'include_styles',
				'name'    => esc_html__( 'Include inbuilt styles', 'knowledgebase' ),
				'desc'    => esc_html__( 'Uncheck this to disable this plugin from adding the inbuilt styles. You will need to add your own CSS styles if you disable this option', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'kb_style'               => array(
				'id'      => 'kb_style',
				'name'    => esc_html__( 'Knowledge Base Style', 'knowledgebase' ),
				'desc'    => esc_html__( 'Select a visual style for your knowledge base display. Premium styles are available in the Pro version.', 'knowledgebase' ),
				'type'    => 'select',
				'options' => self::get_kb_styles(),
				'default' => 'classic',
			),
			'columns'                => array(
				'id'      => 'columns',
				'name'    => esc_html__( 'Number of columns', 'knowledgebase' ),
				'desc'    => esc_html__( 'Set the number of columns to display the knowledge base archives. This will be overridden on smaller screens to optimize display.', 'knowledgebase' ),
				'type'    => 'number',
				'default' => '2',
				'size'    => 'small',
				'min'     => '1',
				'max'     => '5',
			),
			'custom_css'             => array(
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
			'rating_header'                  => array(
				'id'   => 'rating_header',
				'name' => '<h3>' . esc_html__( 'Article Rating', 'knowledgebase' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'rating_system'                  => array(
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
			'rating_tracking_method'         => array(
				'id'      => 'rating_tracking_method',
				'name'    => esc_html__( 'Vote Tracking Method', 'knowledgebase' ),
				/* translators: %s: URL to rating system documentation */
				'desc'    => sprintf(
					/* translators: %1$s: Opening link tag, %2$s: Closing link tag. */
					esc_html__( 'Choose how to prevent duplicate votes. Each method has different privacy implications. %1$sLearn more about tracking methods and GDPR compliance%2$s.', 'knowledgebase' ),
					'<a href="https://webberzone.com/support/knowledgebase/knowledge-base-rating-system/#tracking-methods--gdpr-compliance" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'type'    => 'select',
				'default' => 'cookie',
				'options' => array(
					'none'           => esc_html__( 'No Tracking (allows multiple votes)', 'knowledgebase' ),
					'cookie'         => esc_html__( 'Cookie Only (requires consent)', 'knowledgebase' ),
					'ip'             => esc_html__( 'IP Address Only (stores personal data)', 'knowledgebase' ),
					'cookie_ip'      => esc_html__( 'Cookie + IP Address (either blocks voting)', 'knowledgebase' ),
					'logged_in_only' => esc_html__( 'Logged-in Users Only (best for authenticated sites)', 'knowledgebase' ),
				),
				'pro'     => true,
			),
			'show_rating_stats'              => array(
				'id'      => 'show_rating_stats',
				'name'    => esc_html__( 'Show Rating Statistics', 'knowledgebase' ),
				'desc'    => esc_html__( 'Display the average rating and vote count below the rating buttons.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
				'pro'     => true,
			),
			'help_widget_header'             => array(
				'id'   => 'help_widget_header',
				'name' => '<h3>' . esc_html__( 'Help Widget', 'knowledgebase' ) . '</h3>',
				'desc' => esc_html__( 'A floating help widget that provides self-service support with search, suggested articles, and contact form.', 'knowledgebase' ),
				'type' => 'header',
			),
			'help_widget_enabled'            => array(
				'id'      => 'help_widget_enabled',
				'name'    => esc_html__( 'Enable Help Widget', 'knowledgebase' ),
				'desc'    => esc_html__( 'Display a floating help widget on your site for self-service support.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => false,
				'pro'     => true,
			),
			'help_widget_display_location'   => array(
				'id'      => 'help_widget_display_location',
				'name'    => esc_html__( 'Display Location', 'knowledgebase' ),
				'desc'    => esc_html__( 'Choose where the help widget appears on your site.', 'knowledgebase' ),
				'type'    => 'select',
				'default' => 'kb_only',
				'options' => array(
					'kb_only'  => esc_html__( 'Knowledge Base Only', 'knowledgebase' ),
					'sitewide' => esc_html__( 'Entire Site', 'knowledgebase' ),
				),
				'pro'     => true,
			),
			'help_widget_position'           => array(
				'id'      => 'help_widget_position',
				'name'    => esc_html__( 'Button Position', 'knowledgebase' ),
				'desc'    => esc_html__( 'Choose where the help widget button appears on the screen.', 'knowledgebase' ),
				'type'    => 'select',
				'default' => 'right',
				'options' => array(
					'right' => esc_html__( 'Bottom Right', 'knowledgebase' ),
					'left'  => esc_html__( 'Bottom Left', 'knowledgebase' ),
				),
				'pro'     => true,
			),
			'help_widget_button_style'       => array(
				'id'      => 'help_widget_button_style',
				'name'    => esc_html__( 'Button Style', 'knowledgebase' ),
				'desc'    => esc_html__( 'Choose how the help widget button is displayed.', 'knowledgebase' ),
				'type'    => 'select',
				'default' => 'icon',
				'options' => array(
					'icon'          => esc_html__( 'Icon Only', 'knowledgebase' ),
					'text'          => esc_html__( 'Text Only', 'knowledgebase' ),
					'icon_and_text' => esc_html__( 'Icon and Text', 'knowledgebase' ),
				),
				'pro'     => true,
			),
			'help_widget_button_text'        => array(
				'id'          => 'help_widget_button_text',
				'name'        => esc_html__( 'Button Text', 'knowledgebase' ),
				'desc'        => esc_html__( 'Text to display on the help widget button (when text style is selected).', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => __( 'Help', 'knowledgebase' ),
				'field_class' => 'regular-text',
				'pro'         => true,
			),
			'help_widget_color'              => array(
				'id'          => 'help_widget_color',
				'name'        => esc_html__( 'Help Widget Color', 'knowledgebase' ),
				'desc'        => esc_html__( 'Primary color for the help widget button and interface elements.', 'knowledgebase' ),
				'type'        => 'color',
				'default'     => '#617DEC',
				'field_class' => 'color-field',
				'pro'         => true,
			),
			'help_widget_hover_color'        => array(
				'id'          => 'help_widget_hover_color',
				'name'        => esc_html__( 'Help Widget Hover Color', 'knowledgebase' ),
				'desc'        => esc_html__( 'Hover color for buttons and interactive elements.', 'knowledgebase' ),
				'type'        => 'color',
				'default'     => '#4c63d2',
				'field_class' => 'color-field',
				'pro'         => true,
			),
			'help_widget_text_color'         => array(
				'id'          => 'help_widget_text_color',
				'name'        => esc_html__( 'Help Widget Text Color', 'knowledgebase' ),
				'desc'        => esc_html__( 'Text color for the help widget button and interface elements.', 'knowledgebase' ),
				'type'        => 'color',
				'default'     => '#ffffff',
				'field_class' => 'color-field',
				'pro'         => true,
			),
			'help_widget_hover_text_color'   => array(
				'id'          => 'help_widget_hover_text_color',
				'name'        => esc_html__( 'Help Widget Hover Text Color', 'knowledgebase' ),
				'desc'        => esc_html__( 'Text color for the help widget button on hover.', 'knowledgebase' ),
				'type'        => 'color',
				'default'     => '#ffffff',
				'field_class' => 'color-field',
				'pro'         => true,
			),
			'help_widget_panel_bg_color'     => array(
				'id'          => 'help_widget_panel_bg_color',
				'name'        => esc_html__( 'Panel Background Color', 'knowledgebase' ),
				'desc'        => esc_html__( 'Background color for the help widget panel.', 'knowledgebase' ),
				'type'        => 'color',
				'default'     => '#ffffff',
				'field_class' => 'color-field',
				'pro'         => true,
			),
			'help_widget_panel_text_color'   => array(
				'id'          => 'help_widget_panel_text_color',
				'name'        => esc_html__( 'Panel Text Color', 'knowledgebase' ),
				'desc'        => esc_html__( 'Default text color within the help widget panel.', 'knowledgebase' ),
				'type'        => 'color',
				'default'     => '#1a1a1a',
				'field_class' => 'color-field',
				'pro'         => true,
			),
			'help_widget_link_hover_color'   => array(
				'id'          => 'help_widget_link_hover_color',
				'name'        => esc_html__( 'Link Hover Background', 'knowledgebase' ),
				'desc'        => esc_html__( 'Background color when hovering over help widget links and list items.', 'knowledgebase' ),
				'type'        => 'color',
				'default'     => '#f3f4f6',
				'field_class' => 'color-field',
				'pro'         => true,
			),
			'help_widget_greeting'           => array(
				'id'          => 'help_widget_greeting',
				'name'        => esc_html__( 'Greeting Message', 'knowledgebase' ),
				'desc'        => esc_html__( 'Welcome message shown when the help widget opens.', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => __( 'Hi! How can we help you?', 'knowledgebase' ),
				'field_class' => 'large-text',
				'pro'         => true,
			),
			'help_widget_search_placeholder' => array(
				'id'          => 'help_widget_search_placeholder',
				'name'        => esc_html__( 'Search Placeholder', 'knowledgebase' ),
				'desc'        => esc_html__( 'Placeholder text for the search input field.', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => __( 'Search for answers...', 'knowledgebase' ),
				'field_class' => 'large-text',
				'pro'         => true,
			),
			'help_widget_contact_enabled'    => array(
				'id'      => 'help_widget_contact_enabled',
				'name'    => esc_html__( 'Enable Contact Form', 'knowledgebase' ),
				'desc'    => esc_html__( 'Allow visitors to send messages through the help widget.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
				'pro'     => true,
			),
			'help_widget_contact_email'      => array(
				'id'          => 'help_widget_contact_email',
				'name'        => esc_html__( 'Contact Email', 'knowledgebase' ),
				'desc'        => esc_html__( 'Email address where help widget contact form submissions will be sent.', 'knowledgebase' ),
				'type'        => 'text',
				'default'     => get_option( 'admin_email' ),
				'field_class' => 'regular-text',
				'pro'         => true,
			),
			'help_widget_show_on_mobile'     => array(
				'id'      => 'help_widget_show_on_mobile',
				'name'    => esc_html__( 'Show on Mobile', 'knowledgebase' ),
				'desc'    => esc_html__( 'Display the help widget on mobile devices.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
				'pro'     => true,
			),
			'help_widget_enable_animation'   => array(
				'id'      => 'help_widget_enable_animation',
				'name'    => esc_html__( 'Enable button pulse', 'knowledgebase' ),
				'desc'    => esc_html__( 'Enable a subtle pulsing animation on the help widget button to draw attention. Disable to keep the button static.', 'knowledgebase' ),
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
			'classic' => esc_html__( 'Classic', 'knowledgebase' ),
			'vibrant' => esc_html__( 'Vibrant', 'knowledgebase' ),
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
				'donate'     => '<a href = "https://wzn.io/donate-wz">' . esc_html__( 'Donate', 'knowledgebase' ) . '</a>',
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
		'<p>' . sprintf( __( 'Please report bugs, contribute or request features on <a href="%s">GitHub</a>.', 'knowledgebase' ), esc_url( 'https://github.com/WebberZone/knowledgebase' ) ) . '</p>' .
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
				'id'      => 'wzkb-settings-overview',
				'title'   => __( 'Knowledge Base Settings', 'knowledgebase' ),
				'content' =>
					'<p>' . __( 'Configure every part of your docs experience from this screen. Use the sections on the left to move between General, Output, Styles, and Pro options.', 'knowledgebase' ) . '</p>' .
					'<p>' . __( '<strong>General</strong> covers Multi-Product Mode, URL slugs, caching, uninstall cleanup, and feed behaviour so links stay consistent and data is removed safely when required.', 'knowledgebase' ) . '</p>' .
					'<p>' . __( '<strong>Output</strong> controls how archives render—titles, hierarchy depth, article counts, excerpts, limits per section, sidebars, and related articles.', 'knowledgebase' ) . '</p>' .
					'<p>' . __( '<strong>Styles</strong> lets you switch layouts, set columns, and add custom CSS. Disable inbuilt styles if your theme already provides the styling you need.', 'knowledgebase' ) . '</p>' .
					'<p>' . __( '<strong>Pro features</strong> unlock article ratings, the floating help widget, and advanced layouts.', 'knowledgebase' ) . '</p>' .
					'<p>' . sprintf(
						/* translators: 1: Opening link tag, 2: Closing link tag. */
						__( '%1$sRead the full settings guide%2$s', 'knowledgebase' ),
						'<a href="' . esc_url( 'https://webberzone.com/support/knowledgebase/knowledge-base-settings/' ) . '" target="_blank" rel="noopener noreferrer" class="button">',
						'</a>'
					) . '</p>',
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

	/**
	 * Add Setup Wizard button on the settings page.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function render_wizard_button() {
		printf(
			'<br /><a aria-label="%s" class="button button-secondary" href="%s" title="%s" style="margin-top: 10px;">%s</a>',
			esc_attr__( 'Start Settings Wizard', 'knowledgebase' ),
			esc_url(
				add_query_arg(
					array(
						'post_type' => 'wz_knowledgebase',
						'page'      => 'wzkb_wizard',
					),
					admin_url( 'edit.php' )
				)
			),
			esc_attr__( 'Start Settings Wizard', 'knowledgebase' ),
			esc_html__( 'Start Settings Wizard', 'knowledgebase' )
		);
	}
}
