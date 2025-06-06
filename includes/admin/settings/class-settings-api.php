<?php
/**
 * Settings API.
 *
 * Functions to register, read, write and update settings.
 * Portions of this code have been inspired by Easy Digital Downloads, WordPress Settings Sandbox, WordPress Settings API class, etc.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Admin\Settings;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Settings API wrapper class
 *
 * @version 2.7.1
 */
class Settings_API {

	/**
	 * Current version number
	 *
	 * @var   string
	 */
	public const VERSION = '2.7.1';

	/**
	 * Settings Key.
	 *
	 * @var string Settings Key.
	 */
	public $settings_key;

	/**
	 * Prefix which is used for creating the unique filters and actions.
	 *
	 * @var string Prefix.
	 */
	public $prefix;

	/**
	 * Translation strings.
	 *
	 * @see set_translation_strings()
	 *
	 * @var array Translation strings.
	 */
	public $translation_strings;

	/**
	 * Menus.
	 *
	 * @var array Menus.
	 */
	public $menus = array();

	/**
	 * Menu pages.
	 *
	 * @var array Menu pages.
	 */
	public $menu_pages = array();

	/**
	 * Default navigation tab.
	 *
	 * @var string Default navigation tab.
	 */
	protected $default_tab;

	/**
	 * Settings page.
	 *
	 * @var string Settings page.
	 */
	public $settings_page = '';

	/**
	 * Admin Footer Text. Displayed at the bottom of the plugin settings page.
	 *
	 * @var string Admin Footer Text.
	 */
	protected $admin_footer_text;

	/**
	 * Array containing the settings' sections.
	 *
	 * @var array Settings sections array.
	 */
	protected $settings_sections = array();

	/**
	 * Array containing the settings' fields.
	 *
	 * @var array Settings fields array.
	 */
	protected $registered_settings = array();

	/**
	 * Array containing the settings' fields that need to be upgraded to the current Settings API.
	 *
	 * @var array Settings fields array.
	 */
	protected $upgraded_settings = array();

	/**
	 * Help sidebar content.
	 *
	 * @var string Admin Footer Text.
	 */
	protected $help_sidebar;

	/**
	 * Array of help tabs.
	 *
	 * @var array Settings sections array.
	 */
	protected $help_tabs = array();

	/**
	 * Settings form.
	 *
	 * @since 2.0.0
	 *
	 * @var object Settings form.
	 */
	public $settings_form;

	/**
	 * Main constructor class.
	 *
	 * @param string $settings_key              Settings key.
	 * @param string $prefix                    Prefix. Used for actions and filters.
	 * @param mixed  $args                      {
	 *     Array or string of arguments. Default is blank array.
	 *     @type array  $translation_strings    Translation strings.
	 *     @type array  $settings_sections      Settings sections.
	 *     @type array  $props                  Properties.
	 *     @type array  $registered_settings    Registered settings.
	 *     @type array  $upgraded_settings      Upgraded settings.
	 * }
	 */
	public function __construct( $settings_key, $prefix, $args ) {

		if ( ! defined( 'WZ_SETTINGS_API_VERSION' ) ) {
			define( 'WZ_SETTINGS_API_VERSION', self::VERSION );
		}

		$this->settings_key = $settings_key;
		$this->prefix       = $prefix;

		$defaults = array(
			'translation_strings' => array(),
			'props'               => array(),
			'settings_sections'   => array(),
			'registered_settings' => array(),
			'upgraded_settings'   => array(),
		);
		$args     = wp_parse_args( $args, $defaults );

		$this->hooks();
		$this->set_translation_strings( $args['translation_strings'] );
		$this->set_props( $args['props'] );
		$this->set_sections( $args['settings_sections'] );
		$this->set_registered_settings( $args['registered_settings'] );
		$this->set_upgraded_settings( $args['upgraded_settings'] );
	}

	/**
	 * Adds the functions to the appropriate WordPress hooks.
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 11 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Sets properties.
	 *
	 * @param array|string $args {
	 *     Array or string of arguments. Default is blank array.
	 *
	 *     @type array  $menus             Array of admin menus. See add_custom_menu_page() for more info.
	 *     @type string $default_tab       Default tab.
	 *     @type string $admin_footer_text Admin footer text.
	 *     @type string $help_sidebar      Help sidebar.
	 *     @type array  $help_tabs         Help tabs.
	 * }
	 */
	public function set_props( $args ) {

		$defaults = array(
			'menus'             => array(),
			'default_tab'       => 'general',
			'admin_footer_text' => '',
			'help_sidebar'      => '',
			'help_tabs'         => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		foreach ( $args as $name => $value ) {
			$this->$name = $value;
		}
	}

	/**
	 * Sets translation strings.
	 *
	 * @param array $strings {
	 *     Array of translation strings.
	 *
	 *     @type string $page_title           Page title.
	 *     @type string $menu_title           Menu title.
	 *     @type string $page_header          Page header.
	 *     @type string $reset_message        Reset message.
	 *     @type string $success_message      Success message.
	 *     @type string $save_changes         Save changes button label.
	 *     @type string $reset_settings       Reset settings button label.
	 *     @type string $reset_button_confirm Reset button confirmation message.
	 *     @type string $checkbox_modified    Checkbox modified label.
	 * }
	 *
	 * @return void
	 */
	public function set_translation_strings( $strings ) {

		// Args prefixed with an underscore are reserved for internal use.
		$defaults = array(
			'page_header'          => '',
			'reset_message'        => 'Settings have been reset to their default values. Reload this page to view the updated settings.',
			'success_message'      => 'Settings updated.',
			'save_changes'         => 'Save Changes',
			'reset_settings'       => 'Reset all settings',
			'reset_button_confirm' => 'Do you really want to reset all these settings to their default values?',
			'checkbox_modified'    => 'Modified from default setting',
		);

		$strings = wp_parse_args( $strings, $defaults );

		$this->translation_strings = $strings;
	}

	/**
	 * Set settings sections
	 *
	 * @param array $sections Setting sections array in the format of: id => Title.
	 * @return object Class object.
	 */
	public function set_sections( $sections ) {
		$this->settings_sections = (array) $sections;

		return $this;
	}

	/**
	 * Add a single section
	 *
	 * @param array $section New Section.
	 * @return object Object of the class instance.
	 */
	public function add_section( $section ) {
		$this->settings_sections[] = $section;

		return $this;
	}

	/**
	 * Set the settings fields for registered settings.
	 *
	 * @param array $registered_settings {
	 *     Array of settings in format id => attributes.
	 *          @type string $section           Section title.
	 *          @type string $id                Field ID.
	 *          @type string $name              Field name.
	 *          @type string $desc              Field description.
	 *          @type string $type              Field type.
	 *          @type string $options           Field default option(s).
	 *          @type string $max               Field max. Applicable for numbers.
	 *          @type string $min               Field min. Applicable for numbers.
	 *          @type string $step              Field step. Applicable for numbers.
	 *          @type string $size              Field size. Applicable for text and textarea.
	 *          @type string $field_class       CSS class.
	 *          @type array  $field_attributes  HTML Attributes in the form of attribute => value.
	 *          @type string $placeholder       Placeholder. Applicable for text and textarea.
	 *          @type string $sanitize_callback Sanitize callback.
	 *    }
	 * }
	 *                                   }
	 * @return object Object of the class instance.
	 */
	public function set_registered_settings( $registered_settings ) {
		$this->registered_settings = (array) $registered_settings;

		return $this;
	}

	/**
	 * Set the settings fields for settings to upgrade.
	 *
	 * @param array $upgraded_settings Settings array.
	 * @return object Object of the class instance.
	 */
	public function set_upgraded_settings( $upgraded_settings = array() ) {
		$this->upgraded_settings = (array) $upgraded_settings;

		return $this;
	}

	/**
	 * Add a menu page to the WordPress admin area.
	 *
	 * @param array $menu Array of settings for the menu page.
	 *
	 * @return string|false The resulting page’s hook_suffix, or false if the user does not have the capability required.
	 */
	public function add_custom_menu_page( $menu ) {
		$defaults = array(

			// Modes: submenu, management, options, theme, plugins, users, dashboard, posts, media, links, pages, comments.
			'type'        => 'submenu',

			// Submenu default settings.
			'parent_slug' => 'options-general.php',
			'page_title'  => '',
			'menu_title'  => '',
			'capability'  => $this->get_capability_for_menu(),
			'menu_slug'   => '',
			'function'    => array( $this, 'plugin_settings' ),

			// Menu default settings.
			'icon_url'    => 'dashicons-admin-generic',
			'position'    => null,

		);
		$menu = wp_parse_args( $menu, $defaults );

		$menu_page = false;

		switch ( $menu['type'] ) {
			case 'submenu':
				$menu_page = add_submenu_page(
					$menu['parent_slug'],
					$menu['page_title'],
					$menu['menu_title'],
					$menu['capability'],
					$menu['menu_slug'],
					$menu['function'],
					$menu['position']
				);
				break;
			case 'management':
			case 'options':
			case 'theme':
			case 'plugins':
			case 'users':
			case 'dashboard':
			case 'posts':
			case 'media':
			case 'links':
			case 'pages':
			case 'comments':
				$f = 'add_' . $menu['type'] . '_page';
				if ( function_exists( $f ) ) {
					$menu_page = $f(
						$menu['page_title'],
						$menu['menu_title'],
						$menu['capability'],
						$menu['menu_slug'],
						$menu['function'],
						$menu['position']
					);
				}
				break;
			default:
				$menu_page = add_menu_page(
					$menu['page_title'],
					$menu['menu_title'],
					$menu['capability'],
					$menu['menu_slug'],
					$menu['function'],
					$menu['icon_url'],
					$menu['position']
				);
				break;
		}

		return $menu_page;
	}


	/**
	 * Add admin menu.
	 */
	public function admin_menu() {
		global ${$this->prefix . '_menu_pages'};

		foreach ( $this->menus as $menu ) {
			$menu_page = $this->add_custom_menu_page( $menu );

			$this->menu_pages[ $menu['menu_slug'] ] = $menu_page;
			if ( isset( $menu['settings_page'] ) && $menu['settings_page'] ) {
				$this->settings_page = $menu_page;
			}
		}
		${$this->prefix . '_menu_pages'} = $this->menu_pages;

		// Load the settings contextual help.
		add_action( 'load-' . $this->settings_page, array( $this, 'settings_help' ) );
	}

	/**
	 * Get the appropriate capability for the menu based on the user's roles and settings.
	 *
	 * @param array    $roles Array of roles to check.
	 * @param string   $base_capability The default capability.
	 * @param \WP_User $current_user The current user object.
	 * @param array    $role_capabilities Array of role capabilities.
	 * @return string The capability to use for the menu.
	 */
	public static function get_capability_for_menu( $roles = array(), $base_capability = 'manage_options', $current_user = null, $role_capabilities = array() ) {
		if ( ! $current_user ) {
			$current_user = wp_get_current_user();
		}

		if ( empty( $roles ) || in_array( 'administrator', $current_user->roles, true ) ) {
			return $base_capability;
		}

		if ( empty( $role_capabilities ) ) {
			$role_capabilities = array(
				'editor'      => 'edit_others_posts',
				'author'      => 'publish_posts',
				'contributor' => 'edit_posts',
				'subscriber'  => 'read',
			);
		}

		foreach ( $current_user->roles as $role ) {
			if ( in_array( $role, $roles, true ) && isset( $role_capabilities[ $role ] ) ) {
				return $role_capabilities[ $role ];
			}
		}

		return $base_capability;
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {

		$minimize = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// Settings API scripts.
		wp_register_script(
			'wz-' . $this->prefix . '-admin',
			plugins_url( 'js/settings-admin-scripts' . $minimize . '.js', __FILE__ ),
			array( 'jquery' ),
			self::VERSION,
			true
		);
		wp_register_script(
			'wz-' . $this->prefix . '-codemirror',
			plugins_url( 'js/apply-cm' . $minimize . '.js', __FILE__ ),
			array( 'jquery' ),
			self::VERSION,
			true
		);
		wp_register_script(
			'wz-' . $this->prefix . '-taxonomy-suggest',
			plugins_url( 'js/taxonomy-suggest' . $minimize . '.js', __FILE__ ),
			array( 'jquery' ),
			self::VERSION,
			true
		);
		wp_register_script(
			'wz-' . $this->prefix . '-media-selector',
			plugins_url( 'js/media-selector' . $minimize . '.js', __FILE__ ),
			array( 'jquery' ),
			self::VERSION,
			true
		);
		wp_register_style(
			'wz-' . $this->prefix . '-admin',
			plugins_url( 'css/admin-style' . $minimize . '.css', __FILE__ ),
			array(),
			self::VERSION
		);

		// Top Select scripts and styles.
		wp_register_style(
			'wz-' . $this->prefix . '-tom-select',
			'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css',
			array(),
			'2.3.1'
		);
		wp_register_script(
			'wz-' . $this->prefix . '-tom-select',
			'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js',
			array( 'jquery' ),
			'2.3.1',
			true
		);
		wp_register_script(
			'wz-' . $this->prefix . '-tom-select-init',
			plugin_dir_url( __FILE__ ) . 'js/tom-select-init' . $minimize . '.js',
			array( 'jquery', 'wz-' . $this->prefix . '-tom-select' ),
			self::VERSION,
			true
		);
		wp_localize_script(
			"wz-{$this->prefix}-admin",
			'WZSettingsAdmin',
			array(
				'prefix'       => $this->prefix,
				'settings_key' => $this->settings_key,
			)
		);

		if ( $hook === $this->settings_page ) {
			$this->enqueue_scripts_styles();
		}
	}

	/**
	 * Enqueues all scripts, styles, settings, and templates necessary to use the Settings API.
	 */
	public function enqueue_scripts_styles() {

		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_media();
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'jquery-ui-tabs' );

		wp_enqueue_code_editor(
			array(
				'type'       => 'text/html',
				'codemirror' => array(
					'indentUnit' => 2,
					'tabSize'    => 2,
				),
			)
		);

		wp_enqueue_script( 'wz-' . $this->prefix . '-admin' );
		wp_enqueue_script( 'wz-' . $this->prefix . '-codemirror' );
		wp_enqueue_script( 'wz-' . $this->prefix . '-taxonomy-suggest' );
		wp_enqueue_script( 'wz-' . $this->prefix . '-media-selector' );

		// Enqueue Tom Select.
		wp_enqueue_style( 'wz-' . $this->prefix . '-tom-select' );
		wp_enqueue_script( 'wz-' . $this->prefix . '-tom-select' );

		// Localize Tom Select settings.
		wp_localize_script(
			'wz-' . $this->prefix . '-tom-select-init',
			'WZTomSelectSettings',
			array(
				'action'   => $this->prefix . '_kit_search',
				'nonce'    => wp_create_nonce( $this->prefix . '_kit_search' ),
				'endpoint' => 'forms',
			)
		);
		wp_enqueue_script( 'wz-' . $this->prefix . '-tom-select-init' );

		wp_enqueue_style( 'wz-' . $this->prefix . '-admin' );
	}

	/**
	 * Initialize and registers the settings sections and fields to WordPress
	 *
	 * Usually this should be called at `admin_init` hook.
	 *
	 * This public function gets the initiated settings sections and fields. Then
	 * registers them to WordPress and ready for use.
	 */
	public function admin_init() {

		$settings_key = $this->settings_key;

		if ( false === get_option( $settings_key ) ) {
			add_option( $settings_key, $this->settings_defaults() );
		}

		$this->settings_form = new Settings_Form(
			array(
				'settings_key'           => $settings_key,
				'prefix'                 => $this->prefix,
				'checkbox_modified_text' => $this->translation_strings['checkbox_modified'],
			)
		);

		foreach ( $this->registered_settings as $section => $settings ) {

			add_settings_section(
				"{$settings_key}_{$section}", // ID used to identify this section and with which to register options.
				'', // No title, we will handle this via a separate function.
				'__return_false', // No callback function needed. We'll process this separately.
				"{$settings_key}_{$section}"  // Page on which these options will be added.
			);

			foreach ( $settings as $setting ) {

				$args = self::parse_field_args( $setting, $section );

				$id       = $args['id'];
				$name     = $args['name'];
				$type     = isset( $args['type'] ) ? $args['type'] : 'text';
				$callback = method_exists( $this->settings_form, "callback_{$type}" ) ? array( $this->settings_form, "callback_{$type}" ) : array( $this->settings_form, 'callback_missing' );

				add_settings_field(
					"{$settings_key}[{$id}]",     // ID of the settings field. We save it within the settings array.
					$name,                        // Label of the setting.
					$callback,                    // Function to handle the setting.
					"{$settings_key}_{$section}", // Page to display the setting. In our case it is the section as defined above.
					"{$settings_key}_{$section}", // Name of the section.
					$args
				);
			}
		}

		// Register the settings into the options table.
		register_setting(
			$settings_key,
			$settings_key,
			array(
				'sanitize_callback' => array( $this, 'settings_sanitize' ),
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Flattens $this->registered_settings into $setting[id] => $setting[type] format.
	 *
	 * @return array Default settings
	 */
	public function get_registered_settings_types() {

		$options = array();

		// Populate some default values.
		foreach ( $this->registered_settings as $tab => $settings ) {
			foreach ( $settings as $option ) {
				$options[ $option['id'] ] = $option['type'];
			}
		}

		/**
		 * Filters the settings array.
		 *
		 * @param array   $options Default settings.
		 */
		return apply_filters( $this->prefix . '_get_settings_types', $options );
	}


	/**
	 * Default settings.
	 *
	 * @return array Default settings
	 */
	public function settings_defaults() {

		$options = array();

		// Populate some default values.
		foreach ( $this->registered_settings as $tab => $settings ) {
			foreach ( $settings as $option ) {
				// When checkbox is set to true, set this to 1.
				if ( 'checkbox' === $option['type'] && ! empty( $option['options'] ) ) {
					$options[ $option['id'] ] = 1;
				} else {
					$options[ $option['id'] ] = 0;
				}
				// If an option is set.
				if ( in_array( $option['type'], array( 'textarea', 'css', 'html', 'text', 'url', 'csv', 'color', 'numbercsv', 'postids', 'posttypes', 'number', 'wysiwyg', 'file', 'password' ), true ) ) {
					if ( isset( $option['default'] ) ) {
						$options[ $option['id'] ] = $option['default'];
					} elseif ( isset( $option['options'] ) ) {
						$options[ $option['id'] ] = $option['options'];
					}
				}
				if ( in_array( $option['type'], array( 'multicheck', 'radio', 'select', 'radiodesc', 'thumbsizes' ), true ) && isset( $option['default'] ) ) {
					$options[ $option['id'] ] = $option['default'];
				}
			}
		}

		$upgraded_settings = $this->upgraded_settings;

		if ( false !== $upgraded_settings ) {
			$options = array_merge( $options, $upgraded_settings );
		}

		/**
		 * Filters the default settings array.
		 *
		 * @param array $options Default settings.
		 */
		return apply_filters( $this->prefix . '_settings_defaults', $options );
	}


	/**
	 * Get the default option for a specific key
	 *
	 * @param string $key Key of the option to fetch.
	 * @return mixed
	 */
	public function get_default_option( $key = '' ) {

		$default_settings = $this->settings_defaults();

		if ( array_key_exists( $key, $default_settings ) ) {
			return $default_settings[ $key ];
		} else {
			return false;
		}
	}


	/**
	 * Reset settings.
	 *
	 * @return void
	 */
	public function settings_reset() {
		delete_option( $this->settings_key );
	}

	/**
	 * Get sanitization callback for given Settings key.
	 *
	 * @param string $key Settings key.
	 *
	 * @return mixed Callback function or false if callback isn't found.
	 */
	public function get_sanitize_callback( $key = '' ) {
		if ( empty( $key ) ) {
			return false;
		}

		$settings_sanitize = new Settings_Sanitize(
			array(
				'settings_key' => $this->settings_key,
				'prefix'       => $this->prefix,
			)
		);

		// Iterate over registered fields and see if we can find proper callback.
		foreach ( $this->registered_settings as $section => $settings ) {
			foreach ( $settings as $setting ) {
				if ( $setting['id'] !== $key ) {
					continue;
				}

				// Return the callback name.
				$sanitize_callback = false;

				if ( isset( $setting['sanitize_callback'] ) && is_callable( $setting['sanitize_callback'] ) ) {
					$sanitize_callback = $setting['sanitize_callback'];
					return $sanitize_callback;
				}

				if ( is_callable( array( $settings_sanitize, 'sanitize_' . $setting['type'] . '_field' ) ) ) {
					// For repeater fields, create a closure to pass the field configuration.
					if ( 'repeater' === $setting['type'] ) {
						return function ( $value ) use ( $settings_sanitize, $setting ) {
							return $settings_sanitize->sanitize_repeater_field( $value, $setting );
						};
					}
					$sanitize_callback = array( $settings_sanitize, 'sanitize_' . $setting['type'] . '_field' );
					return $sanitize_callback;
				}

				return $sanitize_callback;
			}
		}

		return false;
	}

	/**
	 * Sanitize the form data being submitted.
	 *
	 * @param  array $input Input unclean array.
	 * @return array Sanitized array
	 */
	public function settings_sanitize( $input ) {
		// This should be set if a form is submitted, so let's save it in the $referrer variable.
		if ( empty( $_POST['_wp_http_referer'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return $input;
		}

		parse_str( sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ), $referrer ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		// Check if we need to set to defaults.
		$reset = isset( $_POST['settings_reset'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( $reset ) {
			$this->settings_reset();
			$settings = get_option( $this->settings_key );

			add_settings_error( $this->prefix . '-notices', '', $this->translation_strings['reset_message'], 'error' );

			return $settings;
		}

		// Get the various settings we've registered.
		$settings       = get_option( $this->settings_key );
		$settings_types = $this->get_registered_settings_types();

		// Get the tab. This is also our settings' section.
		$tab = isset( $referrer['tab'] ) ? $referrer['tab'] : $this->default_tab;

		$input = $input ? $input : array();

		/**
		 * Filter the settings for the tab. e.g. prefix_settings_general_sanitize.
		 *
		 * @param  array $input Input unclean array
		 */
		$input = apply_filters( $this->prefix . '_settings_' . $tab . '_sanitize', $input );

		// Create an output array by merging the existing settings with the ones submitted.
		$output = array_merge( $settings, $input );

		// Loop through each setting being saved and pass it through a sanitization filter.
		foreach ( $settings_types as $key => $type ) {
			/**
			 * Skip settings that are not really settings.
			 *
			 * @param  array $non_setting_types Array of types which are not settings.
			 */
			$non_setting_types = apply_filters( $this->prefix . '_non_setting_types', array( 'header', 'descriptive_text' ) );

			if ( in_array( $type, $non_setting_types, true ) ) {
				continue;
			}

			if ( array_key_exists( $key, $output ) ) {
				$sanitize_callback = $this->get_sanitize_callback( $key );

				// If callback is set, call it.
				if ( $sanitize_callback ) {
					// Pass the field configuration for repeater fields.
					if ( 'repeater' === $type && isset( $this->registered_settings[ $key ] ) ) {
						$output[ $key ] = call_user_func( $sanitize_callback, $output[ $key ], $this->registered_settings[ $key ] );
					} elseif ( 'sensitive' === $type ) {
						$output[ $key ] = call_user_func( $sanitize_callback, $output[ $key ], $key );
					} else {
						$output[ $key ] = call_user_func( $sanitize_callback, $output[ $key ] );
					}
					continue;
				}
			}

			// Delete any key that is not present when we submit the input array.
			if ( ! isset( $input[ $key ] ) ) {
				unset( $output[ $key ] );
			}

			// Delete any settings that are no longer part of our registered settings.
			if ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $settings_types ) ) {
				unset( $output[ $key ] );
			}
		}

		add_settings_error( $this->prefix . '-notices', '', $this->translation_strings['success_message'], 'updated' );

		/**
		 * Filter the settings array before it is returned.
		 *
		 * @param array $output Settings array.
		 * @param array $input  Input settings array.
		 */
		return apply_filters( $this->prefix . '_settings_sanitize', $output, $input );
	}

	/**
	 * Render the settings page.
	 */
	public function plugin_settings() {
		ob_start();
		?>
			<div class="wrap">
				<h1><?php echo esc_html( $this->translation_strings['page_header'] ); ?></h1>
				<?php do_action( $this->prefix . '_settings_page_header' ); ?>

				<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">

				<?php $this->show_navigation(); ?>
				<?php $this->show_form(); ?>

				</div><!-- /#post-body-content -->

				<div id="postbox-container-1" class="postbox-container">

					<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php
					$sidebar_file = dirname( __DIR__ ) . '/sidebar.php';
					if ( file_exists( $sidebar_file ) ) {
						include_once $sidebar_file;
					}
					?>
					</div><!-- /#side-sortables -->

				</div><!-- /#postbox-container-1 -->
				</div><!-- /#post-body -->
				<br class="clear" />
				</div><!-- /#poststuff -->

			</div><!-- /.wrap -->

			<?php
			echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Show navigations as tab
	 *
	 * Shows all the settings section labels as tab
	 */
	public function show_navigation() {
		$active_tab = isset( $_GET['tab'] ) && array_key_exists( sanitize_key( wp_unslash( $_GET['tab'] ) ), $this->settings_sections ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended

		$html = '<ul class="nav-tab-wrapper" style="padding:0">';

		$count = count( $this->settings_sections );

		// Don't show the navigation if only one section exists.
		if ( 1 === $count ) {
			return;
		}

		foreach ( $this->settings_sections as $tab_id => $tab_name ) {

			$active = $active_tab === $tab_id ? ' ' : '';

			$html .= '<li style="padding:0; border:0; margin:0;"><a href="#' . esc_attr( $tab_id ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab ' . sanitize_html_class( $active ) . '">';
			$html .= esc_html( $tab_name );
			$html .= '</a></li>';

		}

		$html .= '</ul>';

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Show the section settings forms
	 *
	 * This public function displays every sections in a different form
	 */
	public function show_form() {
		ob_start();
		?>

			<form method="post" action="options.php" id="<?php echo esc_attr( "{$this->prefix}-settings-form" ); ?>">

			<?php settings_fields( $this->settings_key ); ?>

			<?php foreach ( $this->settings_sections as $tab_id => $tab_name ) : ?>

				<div id="<?php echo esc_attr( $tab_id ); ?>">
					<table class="form-table">
					<?php
						do_settings_fields( $this->prefix . '_settings_' . $tab_id, $this->prefix . '_settings_' . $tab_id );
					?>
					</table>
					<p>
					<?php
						// Default submit button.
						submit_button(
							$this->translation_strings['save_changes'],
							'primary',
							'submit',
							false
						);

					echo '&nbsp;&nbsp;';

					// Reset button.
					$confirm = esc_js( $this->translation_strings['reset_button_confirm'] );
					submit_button(
						$this->translation_strings['reset_settings'],
						'secondary',
						'settings_reset',
						false,
						array(
							'onclick' => "return confirm('{$confirm}');",
						)
					);

					echo '&nbsp;&nbsp;';

					/**
					 * Action to add more buttons in each tab.
					 *
					 * @param string $tab_id            Tab ID.
					 * @param string $tab_name          Tab name.
					 * @param array  $settings_sections Settings sections.
					 */
					do_action( $this->prefix . '_settings_form_buttons', $tab_id, $tab_name, $this->settings_sections );
					?>
					</p>
				</div><!-- /#tab_id-->

				<?php endforeach; ?>

			</form>

			<?php
			echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Add rating links to the admin dashboard
	 *
	 * @param string $footer_text The existing footer text.
	 * @return string Updated Footer text
	 */
	public function admin_footer_text( $footer_text ) {

		if ( ! empty( $this->admin_footer_text ) && get_current_screen()->id === $this->settings_page ) {

			$text = $this->admin_footer_text;

			return str_replace( '</span>', '', $footer_text ) . ' | ' . $text . '</span>';
		} else {
			return $footer_text;
		}
	}

	/**
	 * Function to add the contextual help in the settings page.
	 */
	public function settings_help() {
		$screen = get_current_screen();

		if ( $screen->id !== $this->settings_page ) {
			return;
		}

		$screen->set_help_sidebar( $this->help_sidebar );

		foreach ( $this->help_tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
	}

	/**
	 * Parse field arguments with defaults.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $field   Field arguments.
	 * @param string $section Section name.
	 *
	 * @return array Parsed field arguments.
	 */
	public static function parse_field_args( $field, $section = '' ) {
		$defaults = array(
			'id'               => null,
			'name'             => '',
			'desc'             => '',
			'type'             => 'text',
			'size'             => null,
			'options'          => '',
			'default'          => '',
			'min'              => 0,
			'max'              => 999999,
			'step'             => 1,
			'field_class'      => '',
			'field_attributes' => array(),
			'placeholder'      => '',
			'readonly'         => false,
			'required'         => false,
			'disabled'         => false,
			'pro'              => false,
			'section'          => $section,
		);

		$field = wp_parse_args( $field, $defaults );

		// Add required indicator to field name if the field is required.
		if ( ! empty( $field['required'] ) && true === $field['required'] ) {
			$field['name'] = sprintf( '%s <span class="required" title="%s">*</span>', $field['name'], esc_attr__( 'Required', 'glue-link' ) );
		}

		return $field;
	}

	/**
	 * Get the encryption key for API key encryption/decryption.
	 *
	 * @return string The encryption key.
	 */
	private static function get_encryption_key() {
		return defined( 'AUTH_SALT' ) ? AUTH_SALT : ( defined( 'SECURE_AUTH_SALT' ) ? SECURE_AUTH_SALT : hash( 'sha256', __NAMESPACE__ . 'knowledgebase_encryption_fallback' ) );
	}

	/**
	 * Encrypts an API key using either OpenSSL or Sodium, if available.
	 *
	 * @param string $key The API key to encrypt.
	 * @return string The encrypted API key, or the plain text key if no secure method is available.
	 */
	public static function encrypt_api_key( $key ) {
		if ( empty( $key ) ) {
			return '';
		}

		// Use OpenSSL if available.
		if ( extension_loaded( 'openssl' ) ) {
			$iv_length = openssl_cipher_iv_length( 'aes-256-cbc' );
			$iv        = openssl_random_pseudo_bytes( $iv_length );
			$encrypted = openssl_encrypt( $key, 'aes-256-cbc', self::get_encryption_key(), 0, $iv );

			// Store IV + ciphertext in hex format.
			return 'enc:' . bin2hex( $iv . $encrypted );
		}

		// Use Sodium (libsodium) if OpenSSL is unavailable.
		if ( extension_loaded( 'sodium' ) ) {
			$sodium_key = substr( hash( 'sha256', self::get_encryption_key(), true ), 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES );
			$nonce      = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
			$encrypted  = sodium_crypto_secretbox( $key, $nonce, $sodium_key );

			return 'enc:' . sodium_bin2hex( $nonce . $encrypted );
		}

		return $key;
	}

	/**
	 * Decrypts an API key using either OpenSSL or Sodium, if available.
	 *
	 * @param string $encrypted_key The encrypted API key to decrypt.
	 * @return string The decrypted API key, or the encrypted key if no secure method is available.
	 */
	public static function decrypt_api_key( $encrypted_key ) {
		if ( empty( $encrypted_key ) ) {
			return '';
		}

		// If the key doesn't start with 'enc:', it's not encrypted.
		if ( strpos( $encrypted_key, 'enc:' ) !== 0 ) {
			return $encrypted_key;
		}

		// Remove the 'enc:' prefix.
		$encrypted_key = substr( $encrypted_key, 4 );

		// Try OpenSSL decryption.
		if ( extension_loaded( 'openssl' ) ) {
			$data = hex2bin( $encrypted_key );
			if ( false === $data ) {
				return '';
			}

			$iv_length  = openssl_cipher_iv_length( 'aes-256-cbc' );
			$iv         = mb_substr( $data, 0, $iv_length, '8bit' );
			$ciphertext = mb_substr( $data, $iv_length, null, '8bit' );

			$decrypted = openssl_decrypt( $ciphertext, 'aes-256-cbc', self::get_encryption_key(), 0, $iv );
			return false === $decrypted ? '' : $decrypted;
		}

		// Try Sodium (libsodium) decryption.
		if ( extension_loaded( 'sodium' ) ) {
			$sodium_key = substr( hash( 'sha256', self::get_encryption_key(), true ), 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES );
			$decoded    = sodium_hex2bin( $encrypted_key );

			if ( ! $decoded ) {
				return '';
			}

			$nonce      = mb_substr( $decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit' );
			$ciphertext = mb_substr( $decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit' );
			$decrypted  = sodium_crypto_secretbox_open( $ciphertext, $nonce, $sodium_key );

			return false === $decrypted ? '' : $decrypted;
		}

		return $encrypted_key;
	}
}
