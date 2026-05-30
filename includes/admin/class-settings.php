<?php
/**
 * Register Settings.
 *
 * @since 2.3.0
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Admin;

use WebberZone\Knowledge_Base\Admin\Settings\Settings_API;
use WebberZone\Knowledge_Base\Pro\GitHub\API;
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
		Hook_Registry::add_filter( self::$prefix . '_after_setting_output', array( $this, 'add_github_pat_verify_button' ), 10, 2 );
		Hook_Registry::add_action( 'wp_ajax_wzkb_github_repo_search', array( $this, 'handle_github_repo_search' ) );
		Hook_Registry::add_action( 'wp_ajax_wzkb_clear_github_repos_cache', array( $this, 'handle_clear_github_repos_cache' ) );
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
			'github'  => __( 'GitHub', 'knowledgebase' ),
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
			'search_header'         => array(
				'id'   => 'search_header',
				'name' => '<h3>' . esc_html__( 'Search', 'knowledgebase' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'enable_live_search'    => array(
				'id'      => 'enable_live_search',
				'name'    => esc_html__( 'Enable live search', 'knowledgebase' ),
				'desc'    => esc_html__( 'Show real-time search suggestions as the visitor types in the knowledge base search form.', 'knowledgebase' ),
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
			'show_floating_toc'     => array(
				'id'      => 'show_floating_toc',
				'name'    => esc_html__( 'Show floating table of contents', 'knowledgebase' ),
				'desc'    => esc_html__( 'Display a sticky/floating TOC panel that follows the reader as they scroll through a KB article. Highlights the active section automatically.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => false,
				'pro'     => true,
			),
			'floating_toc_position' => array(
				'id'      => 'floating_toc_position',
				'name'    => esc_html__( 'Floating TOC position', 'knowledgebase' ),
				'desc'    => esc_html__( 'Side of the viewport where the floating TOC panel is anchored.', 'knowledgebase' ),
				'type'    => 'radio',
				'default' => 'right',
				'options' => array(
					'right' => esc_html__( 'Right', 'knowledgebase' ),
					'left'  => esc_html__( 'Left', 'knowledgebase' ),
				),
				'pro'     => true,
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
			'show_term_thumbnail'    => array(
				'id'      => 'show_term_thumbnail',
				'name'    => esc_html__( 'Show featured image on archive pages', 'knowledgebase' ),
				'desc'    => esc_html__( 'Display the term featured image in the header of product and section archive pages.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => true,
				'pro'     => true,
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
	 * Retrieve the array of GitHub integration settings.
	 *
	 * Settings are registered via the wzkb_settings_github filter in the Pro GitHub module.
	 *
	 * @since 3.1.0
	 *
	 * @return array GitHub settings array.
	 */
	public static function settings_github() {
		$product_options = array();
		$products        = get_terms(
			array(
				'taxonomy'   => 'wzkb_product',
				'hide_empty' => false,
			)
		);
		if ( ! is_wp_error( $products ) && ! empty( $products ) ) {
			foreach ( $products as $product ) {
				$product_options[ (string) $product->term_id ] = $product->name;
			}
		}

		$settings = array(
			'github_header'         => array(
				'id'   => 'github_header',
				'name' => '<h3>' . esc_html__( 'GitHub Integration', 'knowledgebase' ) . '</h3>',
				'desc' => sprintf(
					/* translators: %s: Webhook URL. */
					esc_html__( 'Configure GitHub webhooks to automatically sync markdown docs to your knowledge base. Your webhook endpoint: %s', 'knowledgebase' ),
					'<code>' . esc_url( rest_url( 'wzkb/v1/github/webhook' ) ) . '</code>'
				),
				'type' => 'header',
				'pro'  => true,
			),
			'github_webhook_secret' => array(
				'id'      => 'github_webhook_secret',
				'name'    => esc_html__( 'Webhook Secret', 'knowledgebase' ),
				'desc'    => sprintf(
					/* translators: %1$s: opening link tag, %2$s: closing link tag. */
					wp_kses_post( __( 'An arbitrary secret string you choose and enter in both this field and the "Secret" field when %1$sadding a webhook on GitHub%2$s. GitHub signs every payload with this secret via HMAC-SHA256; the plugin rejects any request whose signature does not match.', 'knowledgebase' ) ),
					'<a href="https://docs.github.com/en/webhooks/using-webhooks/creating-webhooks#creating-a-repository-webhook" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'type'    => 'sensitive',
				'size'    => 'large',
				'default' => '',
				'pro'     => true,
			),
			'github_pat'            => array(
				'id'      => 'github_pat',
				'name'    => esc_html__( 'GitHub Personal Access Token', 'knowledgebase' ),
				'desc'    => sprintf(
					/* translators: %1$s: opening strong tag, %2$s: closing strong tag, %3$s: opening link tag, %4$s: closing link tag. */
					wp_kses_post( __( 'Used to read files from GitHub. On the %3$sNew fine-grained token%4$s page: set %1$sResource owner%2$s to the account or org that owns your repositories, then under %1$sRepository permissions%2$s set %1$sContents%2$s to %1$sRead-only%2$s for imports, or %1$sRead and write%2$s if push-back is enabled. For organisation repositories, the org must also allow fine-grained tokens under Organisation Settings → Personal access tokens. Can be overridden per repository mapping below.', 'knowledgebase' ) ),
					'<strong>',
					'</strong>',
					'<a href="https://github.com/settings/personal-access-tokens/new" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'type'    => 'sensitive',
				'size'    => 'large',
				'default' => '',
				'pro'     => true,
			),
			'github_media_import'   => array(
				'id'      => 'github_media_import',
				'name'    => esc_html__( 'Import external media', 'knowledgebase' ),
				'desc'    => esc_html__( 'Download external images found in imported Markdown files into the WordPress Media Library and rewrite the URLs to point locally. Already-sideloaded images are reused automatically on re-import.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => 1,
				'pro'     => true,
			),
			'github_auto_push'      => array(
				'id'      => 'github_auto_push',
				'name'    => esc_html__( 'Auto-push on save', 'knowledgebase' ),
				'desc'    => esc_html__( 'Automatically push a linked article to GitHub whenever it is saved. Skipped during autosaves, revisions, and webhook-triggered imports to prevent loops.', 'knowledgebase' ),
				'type'    => 'checkbox',
				'default' => 0,
				'pro'     => true,
			),
			'github_repositories'   => array(
				'id'                        => 'github_repositories',
				'name'                      => esc_html__( 'Repository Mappings', 'knowledgebase' ),
				'desc'                      => esc_html__( 'Map GitHub repository folders to Knowledge Base products.', 'knowledgebase' ),
				'type'                      => 'repeater',
				'default'                   => array(),
				'new_item_text'             => esc_html__( 'Repository', 'knowledgebase' ),
				'add_button_text'           => esc_html__( 'Add Repository', 'knowledgebase' ),
				'live_update_field'         => 'product_id',
				'live_update_field_options' => $product_options,
				'pro'                       => true,
				'fields'                    => array(
					'product_id'         => array(
						'name'     => esc_html__( 'Product', 'knowledgebase' ),
						'desc'     => esc_html__( 'The Knowledge Base product to associate articles with.', 'knowledgebase' ),
						'id'       => 'product_id',
						'type'     => 'select',
						'required' => true,
						'default'  => '',
						'options'  => array( '' => esc_html__( '-- Select --', 'knowledgebase' ) ) + $product_options,
					),
					'pat'                => array(
						'name'    => esc_html__( 'Personal Access Token', 'knowledgebase' ),
						'desc'    => esc_html__( 'Overrides the global Personal Access Token for this repository only. Useful when repositories belong to different owners or organisations. Leave empty to use the global token. Needs contents: read for import; contents: write is also required when push-back is enabled.', 'knowledgebase' ),
						'id'      => 'pat',
						'type'    => 'sensitive',
						'default' => '',
					),
					'repo_name'          => array(
						'name'             => esc_html__( 'Repository', 'knowledgebase' ),
						'desc'             => esc_html__( 'Begin typing to search repositories accessible with the configured Personal Access Token.', 'knowledgebase' ),
						'id'               => 'repo_name',
						'type'             => 'text',
						'required'         => true,
						'default'          => '',
						'size'             => 'large',
						'field_class'      => 'ts_autocomplete',
						'field_attributes' => self::get_github_repo_search_attributes(),
					),
					'folder_path'        => array(
						'name'    => esc_html__( 'Folder Path', 'knowledgebase' ),
						'desc'    => esc_html__( 'Directory within the repository, e.g. docs/. Leave blank to import all Markdown files from the repository root.', 'knowledgebase' ),
						'id'      => 'folder_path',
						'type'    => 'text',
						'default' => '',
						'size'    => 'large',
					),
					'branch'             => array(
						'name'    => esc_html__( 'Branch', 'knowledgebase' ),
						'desc'    => esc_html__( 'Branch, tag, or SHA to import from. Leave blank to use the repository default branch.', 'knowledgebase' ),
						'id'      => 'branch',
						'type'    => 'text',
						'default' => '',
						'size'    => 'large',
					),
					'default_status'     => array(
						'name'    => esc_html__( 'Default Status', 'knowledgebase' ),
						'desc'    => esc_html__( 'Override the frontmatter status when set. Leave on "No override" to respect frontmatter.', 'knowledgebase' ),
						'id'      => 'default_status',
						'type'    => 'select',
						'default' => '',
						'options' => array(
							''        => esc_html__( 'No override', 'knowledgebase' ),
							'publish' => esc_html__( 'Published', 'knowledgebase' ),
							'draft'   => esc_html__( 'Draft', 'knowledgebase' ),
						),
					),
					'duplicate_handling' => array(
						'name'    => esc_html__( 'Duplicate Handling', 'knowledgebase' ),
						'desc'    => esc_html__( 'What to do when an imported slug already exists in a non-GitHub article.', 'knowledgebase' ),
						'id'      => 'duplicate_handling',
						'type'    => 'select',
						'default' => 'overwrite',
						'options' => array(
							'overwrite'  => esc_html__( 'Overwrite existing', 'knowledgebase' ),
							'skip'       => esc_html__( 'Skip', 'knowledgebase' ),
							'create_new' => esc_html__( 'Create new with suffixed slug', 'knowledgebase' ),
						),
					),
					'delete_removed'     => array(
						'name'    => esc_html__( 'Deleted File Handling', 'knowledgebase' ),
						'desc'    => esc_html__( 'What to do when a tracked file is removed from the repository.', 'knowledgebase' ),
						'id'      => 'delete_removed',
						'type'    => 'select',
						'default' => 'draft',
						'options' => array(
							'draft'  => esc_html__( 'Set to draft', 'knowledgebase' ),
							'delete' => esc_html__( 'Permanently delete', 'knowledgebase' ),
						),
					),
					'enable_push'        => array(
						'name'    => esc_html__( 'Push-back', 'knowledgebase' ),
						'desc'    => esc_html__( 'Push article changes back to GitHub when an article is saved or via the meta box button. Requires the PAT to have contents: write permission.', 'knowledgebase' ),
						'id'      => 'enable_push',
						'type'    => 'checkbox',
						'default' => false,
					),
					'push_author_name'   => array(
						'name'    => esc_html__( 'Commit author name', 'knowledgebase' ),
						'desc'    => esc_html__( 'Optional. Name to attribute the commit to. Leave blank to let GitHub use the PAT owner\'s profile name.', 'knowledgebase' ),
						'id'      => 'push_author_name',
						'type'    => 'text',
						'default' => '',
						'size'    => 'large',
					),
					'push_author_email'  => array(
						'name'    => esc_html__( 'Commit author email', 'knowledgebase' ),
						'desc'    => esc_html__( 'Optional. Email to attribute the commit to. Leave blank to let GitHub use the PAT owner\'s public (or noreply) email.', 'knowledgebase' ),
						'id'      => 'push_author_email',
						'type'    => 'text',
						'default' => '',
						'size'    => 'large',
					),
					'status'             => array(
						'name'    => esc_html__( 'Status', 'knowledgebase' ),
						'desc'    => esc_html__( 'Enable or disable this mapping. When disabled, the mapping is completely ignored — no imports, no webhook processing, and no push-back.', 'knowledgebase' ),
						'id'      => 'status',
						'type'    => 'select',
						'default' => 'enabled',
						'options' => array(
							'enabled'  => esc_html__( 'Enabled', 'knowledgebase' ),
							'disabled' => esc_html__( 'Disabled', 'knowledgebase' ),
						),
					),
				),
			),
		);

		/**
		 * Filter the GitHub settings array.
		 *
		 * @since 3.1.0
		 *
		 * @param array $settings GitHub settings array.
		 */
		return apply_filters( self::$prefix . '_settings_github', $settings );
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
			array(
				'id'      => 'wzkb-settings-github',
				'title'   => __( 'GitHub Integration', 'knowledgebase' ),
				'content' =>
					'<p>' . __( 'Automatically sync Markdown files from a GitHub repository into your Knowledge Base. Push events trigger the webhook and import or update articles in real time.', 'knowledgebase' ) . '</p>' .
					'<p>' . __( '<strong>Webhook Secret</strong> — a shared secret between GitHub and this site. Set the same value in your GitHub repository webhook settings under "Secret". All incoming payloads are verified via HMAC-SHA256.', 'knowledgebase' ) . '</p>' .
					'<p>' . __( '<strong>Personal Access Token</strong> — required for private repositories. Create a fine-grained PAT on GitHub with <em>Contents: Read-only</em> permission (add <em>Metadata: Read-only</em> for private repos). Leave empty for public repos.', 'knowledgebase' ) . '</p>' .
					'<p>' . __( '<strong>Repository Mappings</strong> — each row maps a GitHub repository (or a subfolder within one) to a Knowledge Base product. Required fields are marked with an asterisk (*). The folder path is relative to the repo root, e.g. <code>docs/</code>. Leave the branch field empty to use the repository\'s default branch.', 'knowledgebase' ) . '</p>' .
					'<p>' . __( 'Your webhook endpoint URL is shown in the GitHub section header. Enter this URL in your GitHub repository under Settings → Webhooks, set the content type to <code>application/json</code>, and choose the <em>Push</em> event.', 'knowledgebase' ) . '</p>' .
					'<p>' . sprintf(
						/* translators: 1: Opening link tag, 2: Closing link tag. */
						__( '%1$sRead the GitHub integration guide%2$s', 'knowledgebase' ),
						'<a href="' . esc_url( 'https://webberzone.com/support/knowledgebase/github-integration/' ) . '" target="_blank" rel="noopener noreferrer" class="button">',
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
	 * Add a "Verify Token" button after the GitHub PAT field.
	 *
	 * @since 3.1.0
	 *
	 * @param string $html Field output HTML.
	 * @param array  $args Field arguments.
	 * @return string
	 */
	public function add_github_pat_verify_button( string $html, array $args ): string {
		if ( ! isset( $args['id'] ) ) {
			return $html;
		}

		if ( 'github_pat' === $args['id'] ) {
			$html .= '<p>';
			$html .= '<button type="button" class="button button-secondary wzkb-verify-github-pat">' . esc_html__( 'Verify Token', 'knowledgebase' ) . '</button>';
			$html .= '&nbsp;';
			$html .= '<button type="button" class="button button-secondary wzkb-clear-github-repos-cache">' . esc_html__( 'Refresh Repository List', 'knowledgebase' ) . '</button>';
			$html .= '<span class="wzkb-github-pat-status" style="margin-left:10px;"></span>';
			$html .= '<span class="wzkb-clear-repos-cache-status" style="margin-left:10px;"></span>';
			$html .= '</p>';
		} elseif ( 'pat' === $args['id'] && ! empty( $args['_repeater_id'] ) ) {
			$html .= '<p>';
			$html .= '<button type="button" class="button button-secondary wzkb-verify-github-pat wzkb-verify-mapping-pat">' . esc_html__( 'Verify Token', 'knowledgebase' ) . '</button>';
			$html .= '&nbsp;';
			$html .= '<button type="button" class="button button-secondary wzkb-clear-github-repos-cache">' . esc_html__( 'Refresh Repository List', 'knowledgebase' ) . '</button>';
			$html .= '<span class="wzkb-github-pat-status" style="margin-left:10px;"></span>';
			$html .= '<span class="wzkb-clear-repos-cache-status" style="margin-left:10px;"></span>';
			$html .= '</p>';
		}

		return $html;
	}

	/**
	 * Get field attributes for a GitHub repository name autocomplete field.
	 *
	 * @since 3.1.0
	 *
	 * @return array Field attributes array.
	 */
	public static function get_github_repo_search_attributes(): array {
		return array(
			'data-wp-prefix'       => self::$prefix,
			'data-wp-action'       => 'wzkb_github_repo_search',
			'data-wp-nonce'        => wp_create_nonce( 'wzkb_github_repo_search' ),
			'data-wp-endpoint'     => 'repos',
			'data-ts-config'       => wp_json_encode( array( 'maxItems' => 1 ) ),
			// Pull these sibling fields from the same repeater row and send them
			// with the AJAX request so the row's own PAT (if any) is used.
			'data-wp-extra-fields' => wp_json_encode(
				array(
					'row_pat' => 'pat',
					'row_id'  => 'row_id',
				)
			),
		);
	}

	/**
	 * Resolve the effective GitHub PAT for a repository search request.
	 *
	 * Precedence:
	 *  1. The PAT typed into the current repeater row (if it looks like a real
	 *     token — i.e. doesn't contain the masking character `*`).
	 *  2. The PAT saved against the row identified by `row_id` in the
	 *     `github_repositories` setting.
	 *  3. The global `github_pat` setting.
	 *
	 * @since 3.1.0
	 *
	 * @param string $row_pat Raw value from the row's PAT input (may be masked).
	 * @param string $row_id  Persistent row identifier from the repeater.
	 * @return string Decrypted/plaintext PAT, or empty string if none.
	 */
	public static function resolve_github_pat( string $row_pat, string $row_id ): string {
		// 1. User-typed PAT in the current row (skip masked values like `****abcd`).
		if ( '' !== $row_pat && false === strpos( $row_pat, '*' ) ) {
			return $row_pat;
		}

		// 2. Saved row PAT, looked up by top-level row_id and stored under `fields`.
		if ( '' !== $row_id ) {
			$rows = wzkb_get_option( 'github_repositories' );
			if ( is_array( $rows ) ) {
				foreach ( $rows as $row ) {
					if ( ! is_array( $row ) || empty( $row['row_id'] ) || (string) $row['row_id'] !== $row_id ) {
						continue;
					}
					$fields = isset( $row['fields'] ) && is_array( $row['fields'] ) ? $row['fields'] : array();
					if ( ! empty( $fields['pat'] ) ) {
						$plain = Settings_API::decrypt_api_key( (string) $fields['pat'] );
						if ( '' !== $plain ) {
							return $plain;
						}
					}
					break;
				}
			}
		}

		// 3. Global PAT (decrypt directly so we can bail out when no PAT is configured anywhere).
		$global = Settings_API::decrypt_api_key( (string) wzkb_get_option( 'github_pat' ) );
		return '' !== $global ? $global : '';
	}

	/**
	 * AJAX handler for GitHub repository name autocomplete (TomSelect).
	 *
	 * Fetches /user/repos (repos the PAT has access to) and returns those whose
	 * name contains the query string, in the { id, name } format expected by TomSelect.
	 * Results are cached in a transient for 24 hours; use the Refresh button to bust the cache.
	 *
	 * @since 3.1.0
	 *
	 * @return void
	 */
	public function handle_github_repo_search(): void {
		if ( ! isset( $_REQUEST['nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_send_json_error(
				array(
					'message' => 'Missing nonce.',
					'items'   => array(),
				)
			);
		}

		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['nonce'] ), 'wzkb_github_repo_search' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_send_json_error(
				array(
					'message' => 'Invalid nonce.',
					'items'   => array(),
				)
			);
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => 'Insufficient permissions.',
					'items'   => array(),
				)
			);
		}

		$query = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' === $query ) {
			wp_send_json_success( array( 'items' => array() ) );
		}

		// Resolve which PAT to use: row PAT (typed or saved) > global.
		$row_pat_raw = isset( $_REQUEST['row_pat'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['row_pat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$row_id      = isset( $_REQUEST['row_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['row_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$row_pat     = self::resolve_github_pat( $row_pat_raw, $row_id );

		// Bail early when no PAT is available — the GitHub call would fail anyway and we
		// must never cache (or even attempt) an unauthenticated /user/repos request.
		if ( '' === $row_pat ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'No GitHub Personal Access Token configured. Add a token to this row or the global setting.', 'knowledgebase' ),
					'items'   => array(),
				)
			);
		}

		// Cache per effective PAT so different rows don't share each other's repo lists.
		$cache_key = 'wzkb_github_repos_cache_' . md5( $row_pat );
		$all_repos = get_transient( $cache_key );

		if ( false === $all_repos ) {
			$api  = ( new API() )->with_pat( $row_pat );
			$url  = add_query_arg(
				array(
					'affiliation' => 'owner,organization_member,collaborator',
					'per_page'    => 100,
					'sort'        => 'updated',
				),
				'https://api.github.com/user/repos'
			);
			$body = $api->request( $url );

			if ( is_wp_error( $body ) ) {
				wp_send_json_error(
					array(
						'message' => $body->get_error_message(),
						'items'   => array(),
					)
				);
			}

			$all_repos = json_decode( $body, true );
			if ( ! is_array( $all_repos ) ) {
				$all_repos = array();
			}

			set_transient( $cache_key, $all_repos, DAY_IN_SECONDS );

			// Track this cache key so the Refresh button can clear every per-PAT cache
			// regardless of the active object-cache backend.
			$tracked = get_option( 'wzkb_github_repos_cache_keys', array() );
			if ( ! is_array( $tracked ) ) {
				$tracked = array();
			}
			if ( ! in_array( $cache_key, $tracked, true ) ) {
				$tracked[] = $cache_key;
				update_option( 'wzkb_github_repos_cache_keys', $tracked, false );
			}
		}

		$query_lower = strtolower( $query );
		$items       = array();
		foreach ( $all_repos as $repo ) {
			if ( ! is_array( $repo ) || empty( $repo['name'] ) ) {
				continue;
			}
			if ( false === strpos( strtolower( (string) $repo['full_name'] ), $query_lower ) ) {
				continue;
			}
			$items[] = array(
				'id'   => (string) ( $repo['full_name'] ?? $repo['name'] ),
				'name' => (string) ( $repo['full_name'] ?? $repo['name'] ),
			);
		}

		wp_send_json_success( array( 'items' => $items ) );
	}

	/**
	 * AJAX handler to clear the GitHub repository list cache.
	 *
	 * @since 3.1.0
	 *
	 * @return void
	 */
	public function handle_clear_github_repos_cache(): void {
		check_ajax_referer( 'wzkb-admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Insufficient permissions.', 'knowledgebase' ) ) );
		}

		// Delete every tracked per-PAT cache transient.
		// Using the tracked-keys option ensures this works regardless of which cache
		// backend (DB, Redis, Memcached, etc.) is storing the transients.
		$tracked = get_option( 'wzkb_github_repos_cache_keys', array() );
		if ( is_array( $tracked ) ) {
			foreach ( $tracked as $cache_key ) {
				delete_transient( (string) $cache_key );
			}
		}
		delete_option( 'wzkb_github_repos_cache_keys' );

		wp_send_json_success( array( 'message' => esc_html__( 'Repository list refreshed.', 'knowledgebase' ) ) );
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
