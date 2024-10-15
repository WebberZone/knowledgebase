<?php
/**
 * Register Knowledgebase Settings.
 *
 * @link  https://webberzone.com
 * @since 2.2.0
 *
 * @package Knowledgebase
 * @subpackage Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Knowledgebase_Settings' ) ) :
	/**
	 * Knowledgebase Settings class to register the settings.
	 *
	 * @version 1.0
	 * @since   2.2.0
	 */
	class Knowledgebase_Settings {

		/**
		 * Class instance.
		 *
		 * @var Knowledgebase_Settings Class instance.
		 */
		public static $instance;

		/**
		 * Settings API.
		 *
		 * @since 2.2.0
		 *
		 * @var object Settings API.
		 */
		public $settings_api;

		/**
		 * Tools Page in Admin area.
		 *
		 * @since 2.2.0
		 *
		 * @var string Tools Page.
		 */
		public $tools_page;

		/**
		 * Prefix which is used for creating the unique filters and actions.
		 *
		 * @since 2.2.0
		 *
		 * @var string Prefix.
		 */
		public static $prefix;

		/**
		 * Settings Key.
		 *
		 * @since 2.2.0
		 *
		 * @var string Settings Key.
		 */
		public $settings_key;

		/**
		 * The slug name to refer to this menu by (should be unique for this menu).
		 *
		 * @since 2.2.0
		 *
		 * @var string Menu slug.
		 */
		public $menu_slug;

		/**
		 * Main constructor class.
		 *
		 * @since 2.2.0
		 */
		protected function __construct() {
			$this->settings_key = 'wzkb_settings';
			self::$prefix       = 'wzkb';
			$this->menu_slug    = 'wzkb-settings';

			$props = array(
				'menu_type'         => 'submenu',
				'parent_slug'       => 'edit.php?post_type=wz_knowledgebase',
				'menu_slug'         => $this->menu_slug,
				'default_tab'       => 'general',
				'help_sidebar'      => $this->get_help_sidebar(),
				'help_tabs'         => $this->get_help_tabs(),
				'admin_footer_text' => $this->get_admin_footer_text(),
			);

			$args = array(
				'translation_strings' => $this->get_translation_strings(),
				'props'               => $props,
				'settings_sections'   => $this->get_settings_sections(),
				'registered_settings' => $this->get_registered_settings(),
				'upgraded_settings'   => array(),
			);

			$this->settings_api = new Knowledgebase_Admin\Settings_API( $this->settings_key, self::$prefix, $args );

			add_action( 'admin_head', array( $this, 'admin_head' ), 11 );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
			add_filter( 'plugin_action_links_' . plugin_basename( WZKB_PLUGIN_FILE ), array( $this, 'plugin_actions_links' ) );
			add_action( 'wzkb_settings_page_header', array( $this, 'settings_page_header' ), 11 );
			add_filter( 'wzkb_settings_sanitize', array( $this, 'change_settings_on_save' ), 99 );
		}

		/**
		 * Singleton instance
		 *
		 * @since 2.2.0
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Array containing the various strings used by the Settings_API.
		 *
		 * @since 2.2.0
		 *
		 * @return array Settings array
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
		 * Array containing the settings' sections.
		 *
		 * @since 2.2.0
		 *
		 * @return array Array of settings' sections.
		 */
		public function get_settings_sections() {
			$sections = array(
				'general' => __( 'General', 'knowledgebase' ),
				'output'  => __( 'Output', 'knowledgebase' ),
				'styles'  => __( 'Styles', 'knowledgebase' ),
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
		 * @since 2.2.0
		 *
		 * @return array Settings array
		 */
		public static function get_registered_settings() {

			$settings = array(
				'general' => self::settings_general(),
				'output'  => self::settings_output(),
				'styles'  => self::settings_styles(),
			);

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
		 * Returns the Header settings.
		 *
		 * @since 2.2.0
		 *
		 * @return array Header settings.
		 */
		public static function settings_general() {

			$settings = array(
				'slug_header'       => array(
					'id'   => 'slug_header',
					'name' => '<h3>' . esc_html__( 'Slug options', 'knowledgebase' ) . '</h3>',
					'desc' => '',
					'type' => 'header',
				),
				'kb_slug'           => array(
					'id'      => 'kb_slug',
					'name'    => esc_html__( 'Knowledge Base slug', 'knowledgebase' ),
					'desc'    => esc_html__( 'This will set the opening path of the URL of the knowledge base and is set when registering the custom post type', 'knowledgebase' ),
					'type'    => 'text',
					'options' => 'knowledgebase',
				),
				'category_slug'     => array(
					'id'      => 'category_slug',
					'name'    => esc_html__( 'Category slug', 'knowledgebase' ),
					'desc'    => esc_html__( 'Each category is a section of the knowledge base. This setting is used when registering the custom category and forms a part of the URL when browsing category archives', 'knowledgebase' ),
					'type'    => 'text',
					'options' => 'section',
				),
				'tag_slug'          => array(
					'id'      => 'tag_slug',
					'name'    => esc_html__( 'Tag slug', 'knowledgebase' ),
					'desc'    => esc_html__( 'Each article can have multiple tags. This setting is used when registering the custom tag and forms a part of the URL when browsing tag archives', 'knowledgebase' ),
					'type'    => 'text',
					'options' => 'kb-tags',
				),
				'cache'             => array(
					'id'      => 'cache',
					'name'    => esc_html__( 'Enable cache', 'knowledgebase' ),
					'desc'    => esc_html__( 'Cache the output of the WP_Query lookups to speed up retrieval of the knowledgebase. Recommended for large knowledge bases', 'knowledgebase' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'uninstall_header'  => array(
					'id'      => 'uninstall_header',
					'name'    => '<h3>' . esc_html__( 'Uninstall options', 'knowledgebase' ) . '</h3>',
					'desc'    => '',
					'type'    => 'header',
					'options' => '',
				),
				'uninstall_options' => array(
					'id'      => 'uninstall_options',
					'name'    => esc_html__( 'Delete options on uninstall', 'knowledgebase' ),
					'desc'    => esc_html__( 'Check this box to delete the settings on this page when the plugin is deleted via the Plugins page in your WordPress Admin', 'knowledgebase' ),
					'type'    => 'checkbox',
					'options' => true,
				),
				'uninstall_data'    => array(
					'id'      => 'uninstall_data',
					'name'    => esc_html__( 'Delete all knowledge base posts on uninstall', 'knowledgebase' ),
					'desc'    => esc_html__( 'Check this box to delete all the posts, categories and tags created by the plugin. There is no way to restore the data if you choose this option', 'knowledgebase' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'feed_header'       => array(
					'id'      => 'feed_header',
					'name'    => '<h3>' . esc_html__( 'Feed options', 'knowledgebase' ) . '</h3>',
					'desc'    => '',
					'type'    => 'header',
					'options' => '',
				),
				'include_in_feed'   => array(
					'id'      => 'include_in_feed',
					'name'    => esc_html__( 'Include in feed', 'knowledgebase' ),
					'desc'    => esc_html__( 'Adds the knowledge base articles to the main RSS feed for your site', 'knowledgebase' ),
					'type'    => 'checkbox',
					'options' => true,
				),
				'disable_kb_feed'   => array(
					'id'      => 'disable_kb_feed',
					'name'    => esc_html__( 'Disable KB feed', 'knowledgebase' ),
					/* translators: 1: Opening link tag, 2: Closing link tag. */
					'desc'    => sprintf( esc_html__( 'The knowledge base articles have a default feed. This option will disable the feed. You might need to %1$srefresh your permalinks%2$s when changing this option.', 'knowledgebase' ), '<a href="' . admin_url( 'options-permalink.php' ) . '" target="_blank">', '</a>' ),
					'type'    => 'checkbox',
					'options' => false,
				),
			);

			/**
			 * Filters the Header settings array
			 *
			 * @since 2.2.0
			 *
			 * @param array $settings Header Settings array
			 */
			return apply_filters( self::$prefix . '_settings_general', $settings );
		}

		/**
		 * Returns the Pingbacks/Trackbacks settings.
		 *
		 * @since 2.2.0
		 *
		 * @return array Pingbacks/Trackbacks settings.
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
					'options' => '2',
					'size'    => 'small',
					'min'     => '1',
					'max'     => '5',
				),
				'show_article_count'    => array(
					'id'      => 'show_article_count',
					'name'    => esc_html__( 'Show article count', 'knowledgebase' ),
					'desc'    => esc_html__( 'If selected, the number of articles will be displayed in an orange circle next to the header. You can override the color by styling wzkb_section_count', 'knowledgebase' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'show_excerpt'          => array(
					'id'      => 'show_excerpt',
					'name'    => esc_html__( 'Show excerpt', 'knowledgebase' ),
					'desc'    => esc_html__( 'Select to include the post excerpt after the article link', 'knowledgebase' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'clickable_section'     => array(
					'id'      => 'clickable_section',
					'name'    => esc_html__( 'Link section title', 'knowledgebase' ),
					'desc'    => esc_html__( 'If selected, the title of each section of the knowledgebase will be linked to its own page', 'knowledgebase' ),
					'type'    => 'checkbox',
					'options' => true,
				),
				'show_empty_sections'   => array(
					'id'      => 'show_empty_sections',
					'name'    => esc_html__( 'Show empty sections', 'knowledgebase' ),
					'desc'    => esc_html__( 'If selected, sections with no articles will also be displayed', 'knowledgebase' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'limit'                 => array(
					'id'      => 'limit',
					'name'    => esc_html__( 'Max articles per section', 'knowledgebase' ),
					'desc'    => esc_html__( 'Enter the number of articles that should be displayed in each section when viewing the knowledge base. After this limit is reached, the footer is displayed with the more link to view the category.', 'knowledgebase' ),
					'type'    => 'number',
					'options' => '5',
					'size'    => 'small',
					'min'     => '1',
					'max'     => '500',
				),
				'show_sidebar'          => array(
					'id'      => 'show_sidebar',
					'name'    => esc_html__( 'Show sidebar', 'knowledgebase' ),
					'desc'    => esc_html__( 'Add the sidebar of your theme into the inbuilt templates for archive, sections and search. Activate this option if your theme does not already include this.', 'knowledgebase' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'show_related_articles' => array(
					'id'      => 'show_related_articles',
					'name'    => esc_html__( 'Show related articles', 'knowledgebase' ),
					'desc'    => esc_html__( 'Add related articles at the bottom of the knowledge base article. Only works when using the inbuilt template.', 'knowledgebase' ),
					'type'    => 'checkbox',
					'options' => true,
				),
			);

			/**
			 * Filters the Pingbacks/Trackbacks settings array
			 *
			 * @since 2.2.0
			 *
			 * @param array $settings Pingbacks/Trackbacks Settings array
			 */
			return apply_filters( self::$prefix . '_settings_output', $settings );
		}

		/**
		 * Returns the Comments settings.
		 *
		 * @since 2.2.0
		 *
		 * @return array Comments settings.
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
				'columns'        => array(
					'id'      => 'columns',
					'name'    => esc_html__( 'Number of columns', 'knowledgebase' ),
					'desc'    => esc_html__( 'Set number of columns to display the knowledge base archives. This is only works if the above option is selected.', 'knowledgebase' ),
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
			 * Filters the Comments settings array
			 *
			 * @since 2.2.0
			 *
			 * @param array $settings Comments Settings array
			 */
			return apply_filters( self::$prefix . '_settings_styles', $settings );
		}

		/**
		 * Adding WordPress plugin action links.
		 *
		 * @since 2.2.0
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
		 * @since 2.2.0
		 *
		 * @param array  $links Array of Links.
		 * @param string $file Current file.
		 * @return array
		 */
		public function plugin_row_meta( $links, $file ) {

			if ( false !== strpos( $file, 'knowledgebase.php' ) ) {
				$new_links = array(
					'support' => '<a href = "https://wordpress.org/support/plugin/knowledgebase">' . esc_html__( 'Support', 'knowledgebase' ) . '</a>',
				);

				$links = array_merge( $links, $new_links );
			}
			return $links;
		}

		/**
		 * Get the help sidebar content to display on the plugin settings page.
		 *
		 * @since 2.2.0
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
					esc_url( 'https://github.com/ajaydsouza/knowledgebase/issues' ),
					esc_url( 'https://github.com/ajaydsouza/knowledgebase' )
				) . '</p>';

			/**
			 * Filter to modify the help sidebar content.
			 *
			 * @since 2.2.0
			 *
			 * @param string $help_sidebar Help sidebar content.
			 */
			return apply_filters( self::$prefix . '_settings_help_sidebar', $help_sidebar );
		}

		/**
		 * Get the help tabs to display on the plugin settings page.
		 *
		 * @since 2.2.0
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
		 * Function to add contextual help in the Tools page.
		 *
		 * @since 2.2.0
		 */
		public function settings_help() {
			$screen = get_current_screen();

			if ( $screen->id === $this->tools_page ) {

				$screen->set_help_sidebar( $this->get_help_sidebar() );

				$screen->add_help_tab(
					array(
						'id'      => 'wzkb-tools-general',
						'title'   => __( 'Tools', 'knowledgebase' ),
						'content' =>
						'<p>' . __( 'This screen gives you a few tools namely one click buttons to run the closing algorithm or open comments, pingbacks/trackbacks.', 'knowledgebase' ) . '</p>' .
							'<p>' . __( 'You can also delete the old settings from prior to v2.0.0', 'knowledgebase' ) . '</p>',
					)
				);
			}
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
		 * Function to add a link below the page header of the Settings page.
		 *
		 * @since 2.2.0
		 */
		public function settings_page_header() {
			?>
			<p>
				<a class="wzkb_button" href="<?php echo get_post_type_archive_link( 'wz_knowledgebase' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" target="_blank">
					<?php esc_html_e( 'Visit the Knowledge Base', 'plugin-name' ); ?>
				</a>
			<p>
			<?php
		}

		/**
		 * Add footer text on the plugin page.
		 *
		 * @since 2.2.0
		 */
		public function get_admin_footer_text() {
			return sprintf(
				/* translators: 1: Opening achor tag with Plugin page link, 2: Closing anchor tag, 3: Opening anchor tag with review link. */
				__( 'Thank you for using %1$sKnowledgebase%2$s! Please %3$srate us%2$s on %3$sWordPress.org%2$s', 'knowledgebase' ),
				'<a href="https://webberzone.com/plugins/knowledgebase/" target="_blank">',
				'</a>',
				'<a href="https://wordpress.org/support/plugin/knowledgebase/reviews/#new-post" target="_blank">'
			);
		}

		/**
		 * Modify settings when they are being saved.
		 *
		 * @since 2.2.0
		 *
		 * @param  array $settings Settings array.
		 * @return array $settings  Sanitized settings array.
		 */
		public function change_settings_on_save( $settings ) {

			// Delete the cache.
			wzkb_cache_delete();

			return $settings;
		}
	}

	/** Initiate the class */
	add_action(
		'init',
		function () {
			Knowledgebase_Settings::get_instance();
		},
		999
	);

endif;
