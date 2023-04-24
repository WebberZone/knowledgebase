<?php
/**
 * Settings API.
 *
 * Functions to register, read, write and update settings.
 * Portions of this code have been inspired by Easy Digital Downloads, WordPress Settings Sandbox, WordPress Settings API class, etc.
 *
 * @link  https://webberzone.com
 *
 * @package Knowledgebase
 * @subpackage Admin
 */

namespace Knowledgebase_Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Settings_API' ) ) :
	/**
	 * Settings API wrapper class
	 *
	 * @version 2.2.0
	 */
	class Settings_API {

		/**
		 * Current version number
		 *
		 * @var   string
		 */
		const VERSION = '2.2.0';

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
		 * @var array Translation strings.
		 */
		public $translation_strings;

		/**
		 * Menu type.
		 *
		 * @see add_custom_menu_page()
		 *
		 * @var string Menu slug.
		 */
		public $menu_type;

		/**
		 * The slug name of the parent of the menu.
		 *
		 * @var string Menu slug of the parent.
		 */
		public $parent_slug;

		/**
		 * The slug name to refer to this menu by (should be unique for this menu).
		 *
		 * @var string Menu slug.
		 */
		public $menu_slug;

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
		public $settings_page;

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
			add_action( 'admin_footer_text', array( $this, 'admin_footer_text' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}

		/**
		 * Sets properties.
		 *
		 * @param array|string $args {
		 *     Array or string of arguments. Default is blank array.
		 *
		 *     @type string $menu_type         Admin menu type. See add_custom_menu_page() for options.
		 *     @type string $menu_parent       Parent menu slug.
		 *     @type string $menu_slug         Admin menu slug.
		 *     @type string $default_tab       Default tab.
		 *     @type string $admin_footer_text Admin footer text.
		 *     @type string $help_sidebar      Help sidebar.
		 *     @type array  $help_tabs         Help tabs.
		 * }
		 */
		public function set_props( $args ) {

			$defaults = array(
				'menu_type'         => 'options',
				'parent_slug'       => 'options-general.php',
				'menu_slug'         => '',
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
				'page_title'           => '',
				'menu_title'           => '',
				'page_header'          => '',
				'reset_message'        => __( 'Settings have been reset to their default values. Reload this page to view the updated settings.' ),
				'success_message'      => __( 'Settings updated.' ),
				'save_changes'         => __( 'Save Changes' ),
				'reset_settings'       => __( 'Reset all settings' ),
				'reset_button_confirm' => __( 'Do you really want to reset all these settings to their default values?' ),
				'checkbox_modified'    => __( 'Modified from default setting' ),
			);

			$strings = wp_parse_args( $strings, $defaults );

			$this->translation_strings = $strings;
		}

		/**
		 * Set settings sections
		 *
		 * @param array $sections Setting sections array.
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
		 * @param array $registered_settings Registered settings array.
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
		 */
		public function add_custom_menu_page( $menu ) {
			$defaults = array(

				// Modes: submenu, management, options, theme, plugins, users, dashboard, posts, media, links, pages, comments.
				'type'        => 'submenu',

				// Submenu default settings.
				'parent_slug' => 'options-general.php',
				'page_title'  => '',
				'menu_title'  => '',
				'capability'  => 'manage_options',
				'menu_slug'   => '',
				'function'    => array( $this, 'plugin_settings' ),

				// Menu default settings.
				'icon_url'    => 'dashicons-admin-generic',
				'position'    => null,

			);
			$menu = wp_parse_args( $menu, $defaults );

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
			$menu = array(
				'type'        => $this->menu_type,
				'parent_slug' => $this->parent_slug,
				'page_title'  => $this->translation_strings['page_title'],
				'menu_title'  => $this->translation_strings['menu_title'],
				'capability'  => 'manage_options',
				'menu_slug'   => $this->menu_slug,
				'function'    => array( $this, 'plugin_settings' ),
			);

			$this->settings_page = $this->add_custom_menu_page( $menu );

			// Load the settings contextual help.
			add_action( 'load-' . $this->settings_page, array( $this, 'settings_help' ) );
		}

		/**
		 * Enqueue scripts and styles.
		 *
		 * @param string $hook The current admin page.
		 */
		public function admin_enqueue_scripts( $hook ) {

			if ( $hook === $this->settings_page ) {
				$this->enqueue_scripts_styles();
			}
		}

		/**
		 * Enqueues all scripts, styles, settings, and templates necessary to use the Settings API.
		 */
		public function enqueue_scripts_styles() {

			$minimize = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

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

			wp_enqueue_script(
				'wz-admin-js',
				plugins_url( 'js/admin-scripts' . $minimize . '.js', __FILE__ ),
				array( 'jquery' ),
				self::VERSION,
				true
			);
			wp_enqueue_script(
				'wz-codemirror-js',
				plugins_url( 'js/apply-codemirror' . $minimize . '.js', __FILE__ ),
				array( 'jquery' ),
				self::VERSION,
				true
			);
			wp_enqueue_script(
				'wz-taxonomy-suggest-js',
				plugins_url( 'js/taxonomy-suggest' . $minimize . '.js', __FILE__ ),
				array( 'jquery' ),
				self::VERSION,
				true
			);
		}

		/**
		 * Initialize and registers the settings sections and fileds to WordPress
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

			foreach ( $this->registered_settings as $section => $settings ) {

				add_settings_section(
					"{$settings_key}_{$section}", // ID used to identify this section and with which to register options.
					__return_null(), // No title, we will handle this via a separate function.
					'__return_false', // No callback function needed. We'll process this separately.
					"{$settings_key}_{$section}"  // Page on which these options will be added.
				);

				foreach ( $settings as $setting ) {

					$args = wp_parse_args(
						$setting,
						array(
							'section'          => $section,
							'id'               => null,
							'name'             => '',
							'desc'             => '',
							'type'             => null,
							'options'          => '',
							'max'              => null,
							'min'              => null,
							'step'             => null,
							'size'             => null,
							'field_class'      => '',
							'field_attributes' => '',
							'placeholder'      => '',
						)
					);

					$id       = $args['id'];
					$name     = $args['name'];
					$type     = isset( $args['type'] ) ? $args['type'] : 'text';
					$callback = method_exists( $this, "callback_{$type}" ) ? array( $this, "callback_{$type}" ) : array( $this, 'callback_missing' );

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
			register_setting( $settings_key, $settings_key, array( $this, 'settings_sanitize' ) );
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
					if ( in_array( $option['type'], array( 'textarea', 'css', 'html', 'text', 'url', 'csv', 'color', 'numbercsv', 'postids', 'posttypes', 'number', 'wysiwyg', 'file', 'password' ), true ) && isset( $option['options'] ) ) {
						$options[ $option['id'] ] = $option['options'];
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
		 * Get field description for display.
		 *
		 * @param array $args settings Arguments array.
		 */
		public function get_field_description( $args ) {
			if ( ! empty( $args['desc'] ) ) {
				$desc = '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';
			} else {
				$desc = '';
			}

			/**
			 * After Settings Output filter
			 *
			 * @param string $desc Description of the field.
			 * @param array Arguments array.
			 */
			$desc = apply_filters( $this->prefix . '_setting_field_description', $desc, $args );
			return $desc;
		}

		/**
		 * Get the value of a settings field.
		 *
		 * @param string $option  Settings field name.
		 * @param string $default_value Default text if it's not found.
		 * @return string
		 */
		public function get_option( $option, $default_value = '' ) {

			$options = get_option( $this->settings_key );

			if ( isset( $options[ $option ] ) ) {
				return $options[ $option ];
			}

			return $default_value;
		}

		/**
		 * Miscellaneous callback funcion
		 *
		 * @param array $args Arguments array.
		 * @return void
		 */
		public function callback_missing( $args ) {
			/* translators: 1: Code. */
			printf( esc_html__( 'The callback function used for the %1$s setting is missing.' ), '<strong>' . esc_attr( $args['id'] ) . '</strong>' );
		}

		/**
		 * Header Callback
		 *
		 * Renders the header.
		 *
		 * @param array $args Arguments passed by the setting.
		 * @return void
		 */
		public function callback_header( $args ) {
			$html = $this->get_field_description( $args );

			/**
			 * After Settings Output filter
			 *
			 * @param string $html HTML string.
			 * @param array Arguments array.
			 */
			echo apply_filters( $this->prefix . '_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Descriptive text callback.
		 *
		 * Renders descriptive text onto the settings field.
		 *
		 * @param array $args Array of arguments.
		 * @return void
		 */
		public function callback_descriptive_text( $args ) {
			$this->callback_header( $args );
		}

		/**
		 * Display text fields.
		 *
		 * @param array $args Array of arguments.
		 */
		public function callback_text( $args ) {

			$value       = $this->get_option( $args['id'], $args['options'] );
			$size        = sanitize_html_class( ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular' );
			$class       = sanitize_html_class( $args['field_class'] );
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$disabled    = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
			$readonly    = ( isset( $args['readonly'] ) && true === $args['readonly'] ) ? ' readonly="readonly"' : '';
			$attributes  = $disabled . $readonly;

			foreach ( (array) $args['field_attributes'] as $attribute => $val ) {
				$attributes .= sprintf( ' %1$s="%2$s"', $attribute, esc_attr( $val ) );
			}

			$html  = sprintf(
				'<input type="text" id="%1$s[%2$s]" name="%1$s[%2$s]" class="%3$s" value="%4$s" %5$s %6$s />',
				$this->settings_key,
				sanitize_key( $args['id'] ),
				$class . ' ' . $size . '-text',
				esc_attr( stripslashes( $value ) ),
				$attributes,
				$placeholder
			);
			$html .= $this->get_field_description( $args );

			/** This filter has been defined in class-settings-api.php */
			echo apply_filters( $this->prefix . '_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Display url fields.
		 *
		 * @param array $args Array of arguments.
		 */
		public function callback_url( $args ) {
			$this->callback_text( $args );
		}

		/**
		 * Display csv fields.
		 *
		 * @param array $args Array of arguments.
		 */
		public function callback_csv( $args ) {
			$this->callback_text( $args );
		}

		/**
		 * Display color fields.
		 *
		 * @param array $args Array of arguments.
		 */
		public function callback_color( $args ) {
			$this->callback_text( $args );
		}

		/**
		 * Display numbercsv fields.
		 *
		 * @param array $args Array of arguments.
		 */
		public function callback_numbercsv( $args ) {
			$this->callback_text( $args );
		}

		/**
		 * Display postids fields.
		 *
		 * @param array $args Array of arguments.
		 */
		public function callback_postids( $args ) {
			$this->callback_text( $args );
		}

		/**
		 * Display textarea.
		 *
		 * @param array $args Array of arguments.
		 * @return void
		 */
		public function callback_textarea( $args ) {

			$value = $this->get_option( $args['id'], $args['options'] );
			$class = sanitize_html_class( $args['field_class'] );

			$html  = sprintf(
				'<textarea class="%4$s" cols="50" rows="5" id="%1$s[%2$s]" name="%1$s[%2$s]">%3$s</textarea>',
				$this->settings_key,
				sanitize_key( $args['id'] ),
				esc_textarea( stripslashes( $value ) ),
				'large-text ' . $class
			);
			$html .= $this->get_field_description( $args );

			/** This filter has been defined in class-settings-api.php */
			echo apply_filters( $this->prefix . '_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Display CSS fields.
		 *
		 * @param array $args Array of arguments.
		 * @return void
		 */
		public function callback_css( $args ) {
			$this->callback_textarea( $args );
		}

		/**
		 * Display HTML fields.
		 *
		 * @param array $args Array of arguments.
		 * @return void
		 */
		public function callback_html( $args ) {
			$this->callback_textarea( $args );
		}

		/**
		 * Display checkboxes.
		 *
		 * @param array $args Array of arguments.
		 * @return void
		 */
		public function callback_checkbox( $args ) {

			$value   = $this->get_option( $args['id'], $args['options'] );
			$checked = ! empty( $value ) ? checked( 1, $value, false ) : '';
			$default = isset( $args['options'] ) ? (int) $args['options'] : '';

			$html  = sprintf( '<input type="hidden" name="%1$s[%2$s]" value="-1" />', $this->settings_key, sanitize_key( $args['id'] ) );
			$html .= sprintf( '<input type="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="1" %3$s />', $this->settings_key, sanitize_key( $args['id'] ), $checked );
			$html .= ( (bool) $value !== (bool) $default ) ? '<em style="color:orange">' . $this->translation_strings['checkbox_modified'] . '</em>' : ''; // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$html .= $this->get_field_description( $args );

			/** This filter has been defined in class-settings-api.php */
			echo apply_filters( $this->prefix . '_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Multicheck Callback
		 *
		 * Renders multiple checkboxes.
		 *
		 * @param array $args Array of arguments.
		 * @return void
		 */
		public function callback_multicheck( $args ) {
			$html = '';

			$value = $this->get_option( $args['id'], $args['options'] );

			if ( ! empty( $args['options'] ) ) {
				$html .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="-1" />', $this->settings_key, $args['id'] );

				foreach ( $args['options'] as $key => $option ) {
					if ( isset( $value[ $key ] ) ) {
						$enabled = $key;
					} else {
						$enabled = null;
					}

					$html .= sprintf(
						'<input name="%1$s[%2$s][%3$s]" id="%1$s[%2$s][%3$s]" type="checkbox" value="%4$s" %5$s /> ',
						$this->settings_key,
						sanitize_key( $args['id'] ),
						sanitize_key( $key ),
						esc_attr( $key ),
						checked( $key, $enabled, false )
					);
					$html .= sprintf(
						'<label for="%1$s[%2$s][%3$s]">%4$s</label> <br />',
						$this->settings_key,
						sanitize_key( $args['id'] ),
						sanitize_key( $key ),
						$option
					);
				}

				$html .= $this->get_field_description( $args );
			}

			/** This filter has been defined in class-settings-api.php */
			echo apply_filters( $this->prefix . '_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Radio Callback
		 *
		 * Renders radio boxes.
		 *
		 * @param array $args Array of arguments.
		 * @return void
		 */
		public function callback_radio( $args ) {
			$html = '';

			$value = $this->get_option( $args['id'], $args['default'] );

			foreach ( $args['options'] as $key => $option ) {
				$html .= sprintf(
					'<input name="%1$s[%2$s]" id="%1$s[%2$s][%3$s]" type="radio" value="%3$s" %4$s /> ',
					$this->settings_key,
					sanitize_key( $args['id'] ),
					$key,
					checked( $value, $key, false )
				);
				$html .= sprintf(
					'<label for="%1$s[%2$s][%3$s]">%4$s</label> <br />',
					$this->settings_key,
					sanitize_key( $args['id'] ),
					$key,
					$option
				);
			}

			$html .= $this->get_field_description( $args );

			/** This filter has been defined in class-settings-api.php */
			echo apply_filters( $this->prefix . '_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Radio callback with description.
		 *
		 * Renders radio boxes with each item having it separate description.
		 *
		 * @param array $args Array of arguments.
		 * @return void
		 */
		public function callback_radiodesc( $args ) {
			$html = '';

			$value = $this->get_option( $args['id'], $args['default'] );

			foreach ( $args['options'] as $option ) {
				$html .= sprintf(
					'<input name="%1$s[%2$s]" id="%1$s[%2$s][%3$s]" type="radio" value="%3$s" %4$s /> ',
					$this->settings_key,
					sanitize_key( $args['id'] ),
					$option['id'],
					checked( $value, $option['id'], false )
				);
				$html .= sprintf(
					'<label for="%1$s[%2$s][%3$s]">%4$s</label>',
					$this->settings_key,
					sanitize_key( $args['id'] ),
					$option['id'],
					$option['name']
				);

				$html .= ': <em>' . wp_kses_post( $option['description'] ) . '</em> <br />';
			}

			$html .= $this->get_field_description( $args );

			/** This filter has been defined in class-settings-api.php */
			echo apply_filters( $this->prefix . '_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Radio callback with description.
		 *
		 * Renders radio boxes with each item having it separate description.
		 *
		 * @param array $args Array of arguments.
		 * @return void
		 */
		public function callback_thumbsizes( $args ) {
			$html = '';

			$value = $this->get_option( $args['id'], $args['default'] );

			foreach ( $args['options'] as $name => $option ) {
				$html .= sprintf(
					'<input name="%1$s[%2$s]" id="%1$s[%2$s][%3$s]" type="radio" value="%3$s" %4$s /> ',
					$this->settings_key,
					sanitize_key( $args['id'] ),
					$name,
					checked( $value, $name, false )
				);
				$html .= sprintf(
					'<label for="%1$s[%2$s][%3$s]">%3$s (%4$sx%5$s%6$s)</label> <br />',
					$this->settings_key,
					sanitize_key( $args['id'] ),
					$name,
					(int) $option['width'],
					(int) $option['height'],
					(bool) $option['crop'] ? ' ' . __( 'cropped' ) : ''
				);
			}

			$html .= $this->get_field_description( $args );

			/** This filter has been defined in class-settings-api.php */
			echo apply_filters( $this->prefix . '_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Number Callback
		 *
		 * Renders number fields.
		 *
		 * @param array $args Array of arguments.
		 * @return void
		 */
		public function callback_number( $args ) {
			$value       = $this->get_option( $args['id'], $args['options'] );
			$max         = isset( $args['max'] ) ? intval( $args['max'] ) : 999999;
			$min         = isset( $args['min'] ) ? intval( $args['min'] ) : 0;
			$step        = isset( $args['step'] ) ? intval( $args['step'] ) : 1;
			$size        = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . esc_attr( $args['placeholder'] ) . '"';

			$html  = sprintf(
				'<input type="number" step="%1$s" max="%2$s" min="%3$s" class="%4$s" id="%8$s[%5$s]" name="%8$s[%5$s]" value="%6$s" %7$s />',
				esc_attr( $step ),
				esc_attr( $max ),
				esc_attr( $min ),
				sanitize_html_class( $size ) . '-text',
				sanitize_key( $args['id'] ),
				esc_attr( stripslashes( $value ) ),
				$placeholder,
				$this->settings_key
			);
			$html .= $this->get_field_description( $args );

			/** This filter has been defined in class-settings-api.php */
			echo apply_filters( $this->prefix . '_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Select Callback
		 *
		 * Renders select fields.
		 *
		 * @param array $args Array of arguments.
		 * @return void
		 */
		public function callback_select( $args ) {
			$value = $this->get_option( $args['id'], $args['options'] );

			if ( isset( $args['chosen'] ) ) {
				$chosen = 'class="chosen"';
			} else {
				$chosen = '';
			}

			$html = sprintf( '<select id="%1$s[%2$s]" name="%1$s[%2$s]" %2$s />', $this->settings_key, sanitize_key( $args['id'] ), $chosen );

			foreach ( $args['options'] as $option => $name ) {
				$html .= sprintf( '<option value="%1$s" %2$s>%3$s</option>', sanitize_key( $option ), selected( $option, $value, false ), $name );
			}

			$html .= '</select>';
			$html .= $this->get_field_description( $args );

			/** This filter has been defined in class-settings-api.php */
			echo apply_filters( $this->prefix . '_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Display posttypes fields.
		 *
		 * @param array $args Array of arguments.
		 * @return void
		 */
		public function callback_posttypes( $args ) {
			$html = '';

			$options = $this->get_option( $args['id'], $args['options'] );

			// If post_types contains a query string then parse it with wp_parse_args.
			if ( is_string( $options ) && strpos( $options, '=' ) ) {
				$post_types = wp_parse_args( $options );
			} else {
				$post_types = wp_parse_list( $options );
			}

			$wp_post_types   = get_post_types(
				array(
					'public' => true,
				)
			);
			$posts_types_inc = array_intersect( $wp_post_types, $post_types );

			foreach ( $wp_post_types as $wp_post_type ) {

				$html .= sprintf(
					'<input name="%4$s[%1$s][%2$s]" id="%4$s[%1$s][%2$s]" type="checkbox" value="%2$s" %3$s /> ',
					sanitize_key( $args['id'] ),
					esc_attr( $wp_post_type ),
					checked( true, in_array( $wp_post_type, $posts_types_inc, true ), false ),
					$this->settings_key
				);
				$html .= sprintf( '<label for="%3$s[%1$s][%2$s]">%2$s</label> <br />', sanitize_key( $args['id'] ), $wp_post_type, $this->settings_key );

			}

			$html .= $this->get_field_description( $args );

			/** This filter has been defined in class-settings-api.php */
			echo apply_filters( $this->prefix . '_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}


		/**
		 * Display taxonomies fields.
		 *
		 * @param array $args Array of arguments.
		 * @return void
		 */
		public function callback_taxonomies( $args ) {
			$html = '';

			$options = $this->get_option( $args['id'], $args['options'] );

			// If taxonomies contains a query string then parse it with wp_parse_args.
			if ( is_string( $options ) && strpos( $options, '=' ) ) {
				$taxonomies = wp_parse_args( $options );
			} else {
				$taxonomies = wp_parse_list( $options );
			}

			/* Fetch taxonomies */
			$argsc         = array(
				'public' => true,
			);
			$output        = 'objects';
			$operator      = 'and';
			$wp_taxonomies = get_taxonomies( $argsc, $output, $operator );

			$taxonomies_inc = array_intersect( wp_list_pluck( (array) $wp_taxonomies, 'name' ), $taxonomies );

			foreach ( $wp_taxonomies as $wp_taxonomy ) {

				$html .= sprintf(
					'<input name="%4$s[%1$s][%2$s]" id="%4$s[%1$s][%2$s]" type="checkbox" value="%2$s" %3$s /> ',
					sanitize_key( $args['id'] ),
					esc_attr( $wp_taxonomy->name ),
					checked( true, in_array( $wp_taxonomy->name, $taxonomies_inc, true ), false ),
					$this->settings_key
				);
				$html .= sprintf(
					'<label for="%4$s[%1$s][%2$s]">%3$s (%2$s)</label> <br />',
					sanitize_key( $args['id'] ),
					esc_attr( $wp_taxonomy->name ),
					$wp_taxonomy->labels->name,
					$this->settings_key
				);

			}

			$html .= $this->get_field_description( $args );

			/** This filter has been defined in class-settings-api.php */
			echo apply_filters( $this->prefix . '_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}


		/**
		 * Displays a rich text textarea for a settings field.
		 *
		 * @param array $args Array of arguments.
		 */
		public function callback_wysiwyg( $args ) {

			$value = $this->get_option( $args['id'], $args['options'] );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : '500px';

			echo '<div style="max-width: ' . esc_attr( $size ) . ';">';

			$editor_settings = array(
				'teeny'         => true,
				'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
				'textarea_rows' => 10,
			);

			if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
				$editor_settings = array_merge( $editor_settings, $args['options'] );
			}

			wp_editor( $value, $args['section'] . '-' . $args['id'], $editor_settings );

			echo '</div>';

			echo $this->get_field_description( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Displays a file upload field for a settings field.
		 *
		 * @param array $args Array of arguments.
		 */
		public function callback_file( $args ) {

			$value = $this->get_option( $args['id'], $args['options'] );
			$size  = sanitize_html_class( ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular' );
			$class = sanitize_html_class( $args['field_class'] );
			$label = isset( $args['options']['button_label'] ) ? $args['options']['button_label'] : __( 'Choose File' );

			$html  = sprintf(
				'<input type="text" class="%1$s" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>',
				$class . ' ' . $size . '-text file-url',
				$this->settings_key,
				sanitize_key( $args['id'] ),
				esc_attr( $value )
			);
			$html .= '<input type="button" class="button button-secondary file-browser" value="' . $label . '" />';
			$html .= $this->get_field_description( $args );

			/** This filter has been defined in class-settings-api.php */
			echo apply_filters( $this->prefix . '_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Displays a password field for a settings field.
		 *
		 * @param array $args Array of arguments.
		 */
		public function callback_password( $args ) {

			$value = $this->get_option( $args['id'], $args['options'] );
			$size  = sanitize_html_class( ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular' );
			$class = sanitize_html_class( $args['field_class'] );

			$html  = sprintf(
				'<input type="password" class="%1$s" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>',
				$class . ' ' . $size . '-text',
				$this->settings_key,
				sanitize_key( $args['id'] ),
				esc_attr( $value )
			);
			$html .= $this->get_field_description( $args );

			/** This filter has been defined in class-settings-api.php */
			echo apply_filters( $this->prefix . '_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
						$output[ $key ] = call_user_func( $sanitize_callback, $output[ $key ] );
						continue;
					}
				}

				// Delete any key that is not present when we submit the input array.
				if ( ! isset( $input[ $key ] ) ) {
					unset( $output[ $key ] );
				}
			}

			// Delete any settings that are no longer part of our registered settings.
			if ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $settings_types ) ) {
				unset( $output[ $key ] );
			}

			add_settings_error( $this->prefix . '-notices', '', $this->translation_strings['success_message'], 'updated' );

			/**
			 * Filter the settings array before it is returned.
			 *
			 * @param array $output Settings array.
			 * @param array $input Input settings array.
			 */
			return apply_filters( $this->prefix . '_settings_sanitize', $output, $input );
		}

		/**
		 * Get sanitization callback for given Settings key.
		 *
		 * @param string $key Settings key.
		 *
		 * @return string|bool Callback function or false if callback isn't found.
		 */
		public function get_sanitize_callback( $key = '' ) {
			if ( empty( $key ) ) {
				return false;
			}

			// Iterate over registered fields and see if we can find proper callback.
			foreach ( $this->registered_settings as $section => $settings ) {
				foreach ( $settings as $option ) {
					if ( $option['id'] !== $key ) {
						continue;
					}

					// Return the callback name.
					$sanitize_callback = false;

					if ( isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ) {
						$sanitize_callback = $option['sanitize_callback'];
						return $sanitize_callback;
					}

					if ( is_callable( array( $this, 'sanitize_' . $option['type'] . '_field' ) ) ) {
						$sanitize_callback = array( $this, 'sanitize_' . $option['type'] . '_field' );
						return $sanitize_callback;
					}

					return $sanitize_callback;
				}
			}

			return false;
		}

		/**
		 * Sanitize text fields
		 *
		 * @param string $value The field value.
		 * @return string Sanitizied value
		 */
		public function sanitize_text_field( $value ) {
			return $this->sanitize_textarea_field( $value );
		}

		/**
		 * Sanitize number fields
		 *
		 * @param  array $value The field value.
		 * @return string  $value  Sanitized value
		 */
		public function sanitize_number_field( $value ) {
			return filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
		}

		/**
		 * Sanitize CSV fields
		 *
		 * @param string $value The field value.
		 * @return string Sanitizied value
		 */
		public function sanitize_csv_field( $value ) {
			return implode( ',', array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $value ) ) ) ) );
		}

		/**
		 * Sanitize CSV fields which hold numbers
		 *
		 * @param string $value The field value.
		 * @return string Sanitized value
		 */
		public function sanitize_numbercsv_field( $value ) {
			return implode( ',', array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $value ) ) ) ) ) );
		}

		/**
		 * Sanitize CSV fields which hold post IDs
		 *
		 * @param string $value The field value.
		 * @return string Sanitized value
		 */
		public function sanitize_postids_field( $value ) {
			$ids = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $value ) ) ) ) );

			foreach ( $ids as $key => $value ) {
				if ( false === get_post_status( $value ) ) {
					unset( $ids[ $key ] );
				}
			}

			return implode( ',', $ids );
		}

		/**
		 * Sanitize textarea fields
		 *
		 * @param string $value The field value.
		 * @return string Sanitized value
		 */
		public function sanitize_textarea_field( $value ) {

			global $allowedposttags;

			// We need more tags to allow for script and style.
			$moretags = array(
				'script' => array(
					'type'    => true,
					'src'     => true,
					'async'   => true,
					'defer'   => true,
					'charset' => true,
				),
				'style'  => array(
					'type'   => true,
					'media'  => true,
					'scoped' => true,
				),
				'link'   => array(
					'rel'      => true,
					'type'     => true,
					'href'     => true,
					'media'    => true,
					'sizes'    => true,
					'hreflang' => true,
				),
			);

			$allowedtags = array_merge( $allowedposttags, $moretags );

			/**
			 * Filter allowed tags allowed when sanitizing text and textarea fields.
			 *
			 * @param array $allowedtags Allowed tags array.
			 */
			$allowedtags = apply_filters( $this->prefix . '_sanitize_allowed_tags', $allowedtags );

			return wp_kses( wp_unslash( $value ), $allowedtags );
		}

		/**
		 * Sanitize checkbox fields
		 *
		 * @param mixed $value The field value.
		 * @return int  Sanitized value
		 */
		public function sanitize_checkbox_field( $value ) {
			$value = ( -1 === (int) $value ) ? 0 : 1;

			return $value;
		}

		/**
		 * Sanitize post_types fields
		 *
		 * @param  array $value The field value.
		 * @return string  $value  Sanitized value
		 */
		public function sanitize_posttypes_field( $value ) {
			$post_types = is_array( $value ) ? array_map( 'sanitize_text_field', wp_unslash( $value ) ) : array( 'post', 'page' );

			return implode( ',', $post_types );
		}

		/**
		 * Sanitize post_types fields
		 *
		 * @param  array $value The field value.
		 * @return string  $value  Sanitized value
		 */
		public function sanitize_taxonomies_field( $value ) {
			$taxonomies = is_array( $value ) ? array_map( 'sanitize_text_field', wp_unslash( $value ) ) : array();

			return implode( ',', $taxonomies );
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
						<?php include_once 'sidebar.php'; ?>
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

				$html .= '<li><a href="#' . esc_attr( $tab_id ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab ' . sanitize_html_class( $active ) . '">';
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

			<form method="post" action="options.php">

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

	}

endif;
