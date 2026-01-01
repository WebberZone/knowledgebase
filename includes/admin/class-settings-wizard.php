<?php
/**
 * Settings Wizard for Knowledge Base.
 *
 * Provides a guided setup experience for new users.
 *
 * @since 3.0.0
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Admin;

use WebberZone\Knowledge_Base\Util\Hook_Registry;
use WebberZone\Knowledge_Base\Admin\Settings\Settings_Wizard_API;
use WebberZone\Knowledge_Base\Admin\Settings;
use function WebberZone\Knowledge_Base\wzkb;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Settings Wizard class for Knowledge Base.
 *
 * @since 3.0.0
 */
class Settings_Wizard extends Settings_Wizard_API {

	/**
	 * Settings page URL.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	protected $settings_page_url;

	/**
	 * Main constructor class.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$settings_key = 'wzkb_settings';
		$prefix       = 'wzkb';

		$this->settings_page_url = admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-settings' );

		$args = array(
			'steps'               => $this->get_wizard_steps(),
			'translation_strings' => $this->get_translation_strings(),
			'page_slug'           => 'wzkb_wizard',
			'menu_args'           => array(
				'parent'     => 'edit.php?post_type=wz_knowledgebase',
				'capability' => 'manage_options',
			),
		);

		parent::__construct( $settings_key, $prefix, $args );

		$this->additional_hooks();
	}

	/**
	 * Additional hooks specific to Knowledge Base.
	 *
	 * @since 3.0.0
	 */
	protected function additional_hooks() {
		Hook_Registry::add_action( 'wzkb_activate', array( $this, 'trigger_wizard_on_activation' ) );
		Hook_Registry::add_action( 'admin_init', array( $this, 'register_wizard_notice' ) );
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_custom_scripts' ) );
		Hook_Registry::add_action( 'wp_ajax_wzkb_flush_permalinks', array( $this, 'flush_permalinks' ) );

		// Register Tom Select AJAX handlers for wizard taxonomy fields.
		Hook_Registry::add_action( 'wp_ajax_nopriv_' . $this->prefix . '_taxonomy_search_tom_select', array( Settings::class, 'taxonomy_search_tom_select' ) );
		Hook_Registry::add_action( 'wp_ajax_' . $this->prefix . '_taxonomy_search_tom_select', array( Settings::class, 'taxonomy_search_tom_select' ) );
	}

	/**
	 * Get the skip wizard link URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string Skip wizard link URL.
	 */
	protected function get_skip_link_url() {
		return $this->settings_page_url;
	}

	/**
	 * Get wizard steps configuration.
	 *
	 * @since 3.0.0
	 *
	 * @return array Wizard steps.
	 */
	public function get_wizard_steps() {
		$all_settings_grouped = Settings::get_registered_settings();
		$all_settings         = array();
		foreach ( $all_settings_grouped as $section_settings ) {
			$all_settings = array_merge( $all_settings, $section_settings );
		}

		$mode_keys             = array(
			'multi_product',
			'category_level',
		);
		$permalink_keys        = array(
			'kb_slug',
			'product_slug',
			'category_slug',
			'tag_slug',
			'article_permalink',
		);
		$performance_keys      = array(
			'cache',
			'cache_expiry',
		);
		$display_settings_keys = array(
			'kb_title',
			'show_article_count',
			'show_excerpt',
			'clickable_section',
			'show_empty_sections',
			'limit',
			'show_related_articles',
			'show_sidebar',
		);
		$style_settings_keys   = array(
			'include_styles',
			'product_archive_layout',
			'kb_style',
			'columns',
			'custom_css',
		);
		$pro_features_keys     = array(
			'rating_system',
			'rating_tracking_method',
			'show_rating_stats',
			'help_widget_enabled',
			'help_widget_display_location',
			'help_widget_position',
			'help_widget_color',
			'help_widget_greeting',
			'help_widget_contact_enabled',
		);

		$steps = array(
			'welcome'               => array(
				'title'       => __( 'Knowledge Base Setup', 'knowledgebase' ),
				'description' => __( 'Thank you for installing Knowledge Base! This wizard will help you configure the essential settings to get your knowledge base working perfectly.', 'knowledgebase' ),
				'settings'    => array(),
			),
			'mode_settings'         => array(
				'title'       => __( 'Mode Settings', 'knowledgebase' ),
				'description' => __( 'Enable multi-product mode and determine which section level is displayed first.', 'knowledgebase' ),
				'settings'    => $this->build_step_settings( $mode_keys, $all_settings ),
			),
			'permalink_performance' => array(
				'title'       => __( 'Permalinks & Performance', 'knowledgebase' ),
				'description' => __( 'Define slugs, permalink structure, and caching settings for the knowledge base.', 'knowledgebase' ),
				'settings'    => $this->build_step_settings( array_merge( $permalink_keys, $performance_keys ), $all_settings ),
			),
			'display_options'       => array(
				'title'       => __( 'Display Options', 'knowledgebase' ),
				'description' => __( 'Customize how the knowledge base archive looks and which metadata is visible.', 'knowledgebase' ),
				'settings'    => $this->build_step_settings( array_merge( $display_settings_keys, $style_settings_keys ), $all_settings ),
			),
			'pro_features'          => array(
				'title'       => __( 'Pro Features', 'knowledgebase' ),
				'description' => __( 'Unlock premium features like ratings and the help widget. Configure the essentials here before diving deeper.', 'knowledgebase' ),
				'settings'    => $this->build_step_settings( $pro_features_keys, $all_settings ),
			),
		);

		/**
		 * Filter wizard steps.
		 *
		 * @param array $steps Wizard steps.
		 */
		return apply_filters( 'wzkb_wizard_steps', $steps );
	}

	/**
	 * Build settings array for a wizard step from keys.
	 *
	 * @since 3.0.0
	 *
	 * @param array $keys Setting keys for this step.
	 * @param array $all_settings All settings array.
	 * @return array
	 */
	protected function build_step_settings( $keys, $all_settings ) {
		$step_settings = array();

		foreach ( $keys as $key ) {
			if ( isset( $all_settings[ $key ] ) ) {
				$step_settings[ $key ] = $all_settings[ $key ];
			}
		}

		return $step_settings;
	}

	/**
	 * Get translation strings for the wizard.
	 *
	 * @since 3.0.0
	 *
	 * @return array Translation strings.
	 */
	public function get_translation_strings() {
		return array(
			'page_title'      => __( 'Knowledge Base Setup Wizard', 'knowledgebase' ),
			'menu_title'      => __( 'Setup Wizard', 'knowledgebase' ),
			'next_step'       => __( 'Next Step', 'knowledgebase' ),
			'previous_step'   => __( 'Previous Step', 'knowledgebase' ),
			'finish_setup'    => __( 'Finish Setup', 'knowledgebase' ),
			'skip_wizard'     => __( 'Skip Wizard', 'knowledgebase' ),
			/* translators: %1$d: Current step number, %2$d: Total number of steps */
			'step_of'         => __( 'Step %1$d of %2$d', 'knowledgebase' ),
			'wizard_complete' => __( 'Setup Complete!', 'knowledgebase' ),
			'setup_complete'  => __( 'Your Knowledge Base has been configured successfully. You can now start organizing your documentation.', 'knowledgebase' ),
			'go_to_settings'  => __( 'Go to Settings', 'knowledgebase' ),
		);
	}

	/**
	 * Trigger wizard on plugin activation.
	 *
	 * @since 3.0.0
	 */
	public function trigger_wizard_on_activation() {
		// Set a transient that will trigger the wizard on first admin page visit.
		// This works better than an option because it's temporary and won't persist
		// if the wizard is never accessed.
		set_transient( 'wzkb_show_wizard_activation_redirect', true, HOUR_IN_SECONDS );

		// Also set an option for more persistent storage in multisite environments.
		update_option( 'wzkb_show_wizard', true );
	}

	/**
	 * Register the wizard notice with the Admin_Notices_API.
	 *
	 * @since 3.0.0
	 */
	public function register_wizard_notice() {
		// Get the Admin_Notices_API instance.
		$admin_notices_api = wzkb()->admin->admin_notices_api;
		if ( ! $admin_notices_api ) {
			return;
		}

		$admin_notices_api->register_notice(
			array(
				'id'          => 'wzkb_wizard_notice',
				'message'     => sprintf(
					'<p>%s</p><p><a href="%s" class="button button-primary">%s</a></p>',
					esc_html__( 'Welcome to Knowledge Base! Would you like to run the setup wizard to configure the plugin?', 'knowledgebase' ),
					esc_url( admin_url( 'admin.php?page=wzkb_wizard' ) ),
					esc_html__( 'Run Setup Wizard', 'knowledgebase' )
				),
				'type'        => 'info',
				'dismissible' => true,
				'capability'  => 'manage_options',
				'conditions'  => array(
					function () {
						$page = sanitize_key( (string) filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );

						// Only show if wizard is not completed, not dismissed, and activation flag is set.
						// Check both transient and option to ensure it works in multisite environments.
						return ! $this->is_wizard_completed() &&
							! get_option( 'wzkb_wizard_notice_dismissed', false ) &&
							( get_transient( 'wzkb_show_wizard_activation_redirect' ) || get_option( 'wzkb_show_wizard', false ) ) &&
							'wzkb_wizard' !== $page;
					},
				),
			)
		);
	}

	/**
	 * Get the URL to redirect to after wizard completion.
	 *
	 * @since 3.0.0
	 *
	 * @return string Redirect URL.
	 */
	protected function get_completion_redirect_url() {
		return $this->settings_page_url;
	}

	/**
	 * Enqueue custom scripts for the wizard.
	 *
	 * @since 3.0.0
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_custom_scripts( $hook ) {
		if ( false === strpos( $hook, $this->page_slug ) ) {
			return;
		}

		// Check if we're on the custom tables indexing step.
		$step_config = $this->get_current_step_config();
		if ( ! empty( $step_config['custom_step'] ) ) {
			// Enqueue the reindex script from custom tables admin.
			wp_enqueue_script(
				'wzkb-reindex',
				WZKB_PLUGIN_URL . 'includes/pro/custom-tables/admin/js/reindex.js',
				array( 'jquery' ),
				WZKB_VERSION,
				true
			);

			// Localize script with necessary data.
			wp_localize_script(
				'wzkb-reindex',
				'wzkbReindexSettings',
				array(
					'ajaxurl'        => admin_url( 'admin-ajax.php' ),
					'nonce'          => wp_create_nonce( 'wzkb_reindex_nonce' ),
					'strings'        => array(
						'starting'    => __( 'Starting reindex process...', 'knowledgebase' ),
						'completed'   => __( 'Reindexing complete!', 'knowledgebase' ),
						'error'       => __( 'An error occurred during reindexing. Please try again.', 'knowledgebase' ),
						'buttonText'  => __( 'Reindex Custom Tables', 'knowledgebase' ),
						'clickToStop' => __( 'Reindexing... Click to Stop', 'knowledgebase' ),
					),
					'isNetworkAdmin' => is_multisite() && is_network_admin(),
				)
			);
		}
	}

	/**
	 * Override render_wizard_page to handle custom steps.
	 *
	 * @since 3.0.0
	 */
	public function render_wizard_page() {
		$this->current_step = $this->get_current_step();
		$step_config        = $this->get_current_step_config();

		if ( empty( $step_config ) ) {
			$this->render_completion_page();
			return;
		}

		// Use parent method for regular steps.
		parent::render_wizard_page();
	}

	/**
	 * Handle AJAX request to flush permalinks.
	 *
	 * @since 3.0.0
	 */
	public function flush_permalinks() {
		check_ajax_referer( 'wzkb_flush_permalinks', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Insufficient permissions.', 'knowledgebase' ) ) );
		}

		flush_rewrite_rules();

		wp_send_json_success( array( 'message' => esc_html__( 'Permalinks flushed successfully.', 'knowledgebase' ) ) );
	}

	/**
	 * Override the render completion page to show CRP specific content.
	 *
	 * @since 3.0.0
	 */
	protected function render_completion_page() {
		$multi_product = \wzkb_get_option( 'multi_product' );
		$pro_active    = ! empty( wzkb()->pro );
		?>
		<div class="wrap wizard-wrap wizard-complete">
			<div class="wizard-completion-header">
				<h1><?php echo esc_html( $this->translation_strings['wizard_complete'] ); ?></h1>
				<p class="wizard-completion-message">
					<?php echo esc_html( $this->translation_strings['setup_complete'] ); ?>
				</p>
			</div>

			<div class="wizard-completion-content">
				<div class="wizard-setup-top-actions">
					<button type="button" class="button button-secondary button-large" onclick="jQuery.post(ajaxurl, {action:'wzkb_flush_permalinks', nonce:'<?php echo esc_js( wp_create_nonce( 'wzkb_flush_permalinks' ) ); ?>'}, function(response){ if(response.success) { alert(response.data.message); } else { alert(response.data.message || 'Error flushing permalinks.'); } }).fail(function(){ alert('Error flushing permalinks.'); });">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Flush Permalinks', 'knowledgebase' ); ?>
					</button>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'wz_knowledgebase' ) ); ?>" class="button button-secondary button-large" target="_blank">
						<span class="dashicons dashicons-visibility"></span>
						<?php esc_html_e( 'Visit Knowledge Base', 'knowledgebase' ); ?>
					</a>
				</div>

				<div class="wizard-completion-features">
					<div class="wizard-setup-guidance">
						<p><strong><?php esc_html_e( 'Next Steps:', 'knowledgebase' ); ?></strong> <?php echo $multi_product ? esc_html__( 'Start by creating products to organize your knowledge base content.', 'knowledgebase' ) : esc_html__( 'Create sections to categorize your articles and improve navigation.', 'knowledgebase' ); ?></p>
					</div>

					<ul class="wizard-setup-next-actions-horizontal">
						<?php if ( $multi_product ) : ?>
						<li class="setup-product">
							<a class="button button-primary button-large" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=wzkb_product&post_type=wz_knowledgebase' ) ); ?>">
								<span class="dashicons dashicons-plus-alt"></span>
								<?php esc_html_e( 'Create your first product', 'knowledgebase' ); ?>
							</a>
							<span class="wizard-action-description"><?php esc_html_e( 'Organize content by products', 'knowledgebase' ); ?></span>
						</li>
						<?php endif; ?>
						<li class="setup-sections">
							<a class="button button-large<?php echo ! $multi_product ? ' button-primary' : ''; ?>" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=wzkb_category&post_type=wz_knowledgebase' ) ); ?>">
								<span class="dashicons dashicons-category"></span>
								<?php esc_html_e( 'Add sections', 'knowledgebase' ); ?>
							</a>
							<span class="wizard-action-description"><?php esc_html_e( 'Create categories for articles', 'knowledgebase' ); ?></span>
						</li>
						<li class="setup-article">
							<a class="button button-large" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=wz_knowledgebase' ) ); ?>">
								<span class="dashicons dashicons-edit-page"></span>
								<?php esc_html_e( 'Create your first article', 'knowledgebase' ); ?>
							</a>
							<span class="wizard-action-description"><?php esc_html_e( 'Add helpful content', 'knowledgebase' ); ?></span>
						</li>
						<li class="setup-settings">
							<a class="button button-large" href="<?php echo esc_url( $this->settings_page_url ); ?>">
								<span class="dashicons dashicons-admin-settings"></span>
								<?php esc_html_e( 'Configure settings', 'knowledgebase' ); ?>
							</a>
							<span class="wizard-action-description"><?php esc_html_e( 'Customize display and behavior', 'knowledgebase' ); ?></span>
						</li>
					</ul>

					<?php if ( $pro_active ) : ?>
						<div class="wizard-setup-pro-features">
							<h3><?php esc_html_e( 'Pro Features Available', 'knowledgebase' ); ?></h3>
							<ul class="wizard-setup-pro-actions">
								<li class="setup-permalinks">
									<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-settings#general' ) ); ?>">
										<span class="dashicons dashicons-admin-links"></span>
										<?php esc_html_e( 'Customize Permalinks', 'knowledgebase' ); ?>
									</a>
									<span class="wizard-action-description"><?php esc_html_e( 'Optimize URL structure', 'knowledgebase' ); ?></span>
								</li>
								<?php
								$rating_system = \wzkb_get_option( 'rating_system' );
								if ( 'disabled' !== $rating_system ) :
									?>
								<li class="setup-rating">
									<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-settings#pro' ) ); ?>">
										<span class="dashicons dashicons-star-filled"></span>
										<?php esc_html_e( 'Configure Rating System and Help Widget', 'knowledgebase' ); ?>
									</a>
									<span class="wizard-action-description"><?php esc_html_e( 'Set up article feedback and help widget', 'knowledgebase' ); ?></span>
								</li>
								<?php endif; ?>
							</ul>
						</div>
					<?php endif; ?>

					<div class="wizard-setup-resources">
						<h3><?php esc_html_e( 'Helpful Resources', 'knowledgebase' ); ?></h3>
						<ul class="wizard-setup-resource-links">
							<li>
								<a href="https://webberzone.com/support/product/knowledgebase/" class="button button-secondary" target="_blank">
									<span class="dashicons dashicons-book"></span>
									<?php esc_html_e( 'Documentation', 'knowledgebase' ); ?>
								</a>
								<span class="wizard-action-description"><?php esc_html_e( 'Read the plugin manual', 'knowledgebase' ); ?></span>
							</li>
							<li>
								<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb_tools_page' ) ); ?>" class="button button-secondary">
									<span class="dashicons dashicons-admin-tools"></span>
									<?php esc_html_e( 'Tools Page', 'knowledgebase' ); ?>
								</a>
								<span class="wizard-action-description"><?php esc_html_e( 'Maintenance and debugging', 'knowledgebase' ); ?></span>
							</li>
							<li>
								<a href="https://wordpress.org/support/plugin/knowledgebase/" class="button button-secondary" target="_blank">
									<span class="dashicons dashicons-sos"></span>
									<?php esc_html_e( 'Support Forum', 'knowledgebase' ); ?>
								</a>
								<span class="wizard-action-description"><?php esc_html_e( 'Get help from the community', 'knowledgebase' ); ?></span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
