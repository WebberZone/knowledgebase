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

		$multi_product = (int) \wzkb_get_option( 'multi_product', 0 );

		$mode_keys             = array(
			'multi_product',
			'category_level',
		);
		$permalink_keys        = array(
			'permalink_header',
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
			'products_setup'        => array(
				'title'       => __( 'Create Products', 'knowledgebase' ),
				'description' => __( 'Add one or more products to organize your knowledge base content.', 'knowledgebase' ),
				'settings'    => array(),
				'custom_step' => 'products',
			),
			'sections_setup'        => array(
				'title'       => __( 'Create Sections', 'knowledgebase' ),
				'description' => __( 'Add sections to organize your articles. You can add more later.', 'knowledgebase' ),
				'settings'    => array(),
				'custom_step' => 'sections',
			),
			'subsections_setup'     => array(
				'title'       => __( 'Create Subsections', 'knowledgebase' ),
				'description' => __( 'Add subsections and assign them to a parent section. You can add more later.', 'knowledgebase' ),
				'settings'    => array(),
				'custom_step' => 'subsections',
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
			'page_title'            => __( 'Knowledge Base Setup Wizard', 'knowledgebase' ),
			'menu_title'            => __( 'Setup Wizard', 'knowledgebase' ),
			'next_step'             => __( 'Next Step', 'knowledgebase' ),
			'previous_step'         => __( 'Previous Step', 'knowledgebase' ),
			'finish_setup'          => __( 'Finish Setup', 'knowledgebase' ),
			'skip_wizard'           => __( 'Skip Wizard', 'knowledgebase' ),
			/* translators: %s: Search query. */
			'tom_select_no_results' => __( 'No results found for "%s"', 'knowledgebase' ),
			'steps_nav_aria_label'  => __( 'Setup Wizard Steps', 'knowledgebase' ),
			/* translators: %1$d: Current step number, %2$d: Total number of steps */
			'step_of'               => __( 'Step %1$d of %2$d', 'knowledgebase' ),
			'wizard_complete'       => __( 'Setup Complete!', 'knowledgebase' ),
			'setup_complete'        => __( 'Your Knowledge Base has been configured successfully. You can now start organizing your documentation.', 'knowledgebase' ),
			'go_to_settings'        => __( 'Go to Settings', 'knowledgebase' ),
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

		$step_config = $this->get_current_step_config();
		$custom_step = $step_config['custom_step'] ?? '';
		$minimize    = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		if ( in_array( $custom_step, array( 'products', 'sections', 'subsections' ), true ) ) {
			wp_enqueue_style(
				'wzkb-wizard-content',
				plugins_url( 'css/wizard-content' . $minimize . '.css', __FILE__ ),
				array(),
				WZKB_VERSION
			);
			wp_enqueue_script(
				'wzkb-wizard-content',
				plugins_url( 'js/wizard-content' . $minimize . '.js', __FILE__ ),
				array( 'jquery' ),
				WZKB_VERSION,
				true
			);
		}
	}

	/**
	 * Process the current step's form data.
	 *
	 * Custom taxonomy setup steps don't use the settings table, so we need to
	 * handle them explicitly and still fire the step processed action.
	 */
	protected function process_current_step() {
		$current_step_config = $this->get_current_step_config();
		$custom_step         = $current_step_config['custom_step'] ?? '';

		if ( in_array( $custom_step, array( 'products', 'sections', 'subsections' ), true ) ) {
			switch ( $custom_step ) {
				case 'products':
					$this->process_products_submission();
					break;
				case 'sections':
					$this->process_sections_submission();
					break;
				case 'subsections':
					$this->process_subsections_submission();
					break;
			}

			do_action( $this->prefix . '_wizard_step_processed', $this->current_step, array() );
			return;
		}

		parent::process_current_step();
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

		$custom_step = $step_config['custom_step'] ?? '';
		if ( in_array( $custom_step, array( 'products', 'sections', 'subsections' ), true ) ) {
			$this->render_taxonomy_setup_step( $custom_step, $step_config );
			return;
		}

		parent::render_wizard_page();
	}

	/**
	 * Render custom taxonomy setup steps inside the wizard.
	 *
	 * @since 3.0.0
	 *
	 * @param string $custom_step Custom step identifier.
	 * @param array  $step_config Current step configuration.
	 * @return void
	 */
	protected function render_taxonomy_setup_step( string $custom_step, array $step_config ) {
		$this->maybe_clamp_current_step();
		$multi_product = (int) \wzkb_get_option( 'multi_product', 0 );
		?>
		<div class="wrap wizard-wrap">
			<h1><?php echo esc_html( $this->translation_strings['wizard_title'] ); ?></h1>

			<?php $this->render_wizard_steps_navigation(); ?>

			<div class="wizard-progress">
				<div class="wizard-progress-bar">
					<div class="wizard-progress-fill" style="width: <?php echo esc_attr( (string) ( ( $this->current_step / $this->total_steps ) * 100 ) ); ?>%;"></div>
				</div>
				<p class="wizard-step-counter">
					<?php
					$current_step_name = $step_config['title'] ?? '';
					$step_pattern      = ! empty( $current_step_name ) ? '%1$s - Step %2$d of %3$d' : $this->translation_strings['step_of'];
					printf(
						esc_html( $step_pattern ),
						esc_html( $current_step_name ),
						esc_html( (string) $this->current_step ),
						esc_html( (string) $this->total_steps )
					);
					?>
				</p>
			</div>

			<div class="wizard-content">
				<div class="wizard-step">
					<h2><?php echo esc_html( $step_config['title'] ?? '' ); ?></h2>
					<?php if ( ! empty( $step_config['description'] ) ) : ?>
						<p class="wizard-step-description"><?php echo wp_kses_post( $step_config['description'] ); ?></p>
					<?php endif; ?>

					<form method="post" action="">
						<?php wp_nonce_field( "{$this->prefix}_wizard_nonce", "{$this->prefix}_wizard_nonce" ); ?>
						<div class="wizard-fields">
							<?php
							switch ( $custom_step ) {
								case 'products':
									$this->render_products_fields();
									break;
								case 'sections':
									$this->render_sections_fields( $multi_product );
									break;
								case 'subsections':
									$this->render_subsections_fields();
									break;
							}
							?>
						</div>

						<?php
						do_action( "{$this->prefix}_wizard_before_actions", $this->current_step, $this->total_steps );
						?>
						<div class="wizard-actions">
							<?php $this->render_wizard_buttons(); ?>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the products repeater fields.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	protected function render_products_fields() {
		$existing_products = get_terms(
			array(
				'taxonomy'   => 'wzkb_product',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);
		if ( is_wp_error( $existing_products ) ) {
			$existing_products = array();
		}
		?>
		<div class="wzkb-wizard-repeater" data-repeater-type="products">
			<table class="widefat striped wzkb-wizard-repeater-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Existing', 'knowledgebase' ); ?></th>
						<th><?php esc_html_e( 'Name', 'knowledgebase' ); ?></th>
						<th><?php esc_html_e( 'Slug', 'knowledgebase' ); ?></th>
						<th><?php esc_html_e( 'Description', 'knowledgebase' ); ?></th>
						<th class="wzkb-wizard-col-actions"><?php esc_html_e( 'Actions', 'knowledgebase' ); ?></th>
					</tr>
				</thead>
				<tbody class="wzkb-wizard-repeater-rows">
					<?php $this->render_empty_repeater_row( 'wzkb_wizard_products', $existing_products ); ?>
				</tbody>
			</table>
			<p>
				<button type="button" class="button wzkb-wizard-add-row" data-target="wzkb_wizard_products"><?php esc_html_e( 'Add another product', 'knowledgebase' ); ?></button>
			</p>
		</div>
		<?php
	}

	/**
	 * Render the sections repeater fields.
	 *
	 * @since 3.0.0
	 *
	 * @param int $multi_product Whether multi-product mode is enabled.
	 * @return void
	 */
	protected function render_sections_fields( int $multi_product ) {
		$products = array();
		if ( 1 === $multi_product ) {
			$products = get_terms(
				array(
					'taxonomy'   => 'wzkb_product',
					'hide_empty' => false,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);
			if ( is_wp_error( $products ) ) {
				$products = array();
			}
		}
		$existing_sections = get_terms(
			array(
				'taxonomy'   => 'wzkb_category',
				'hide_empty' => false,
				'parent'     => 0,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);
		if ( is_wp_error( $existing_sections ) ) {
			$existing_sections = array();
		}
		?>
		<div class="wzkb-wizard-repeater" data-repeater-type="sections" data-multi-product="<?php echo esc_attr( (string) $multi_product ); ?>">
			<table class="widefat striped wzkb-wizard-repeater-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Existing', 'knowledgebase' ); ?></th>
						<th><?php esc_html_e( 'Name', 'knowledgebase' ); ?></th>
						<th><?php esc_html_e( 'Slug', 'knowledgebase' ); ?></th>
						<th><?php esc_html_e( 'Description', 'knowledgebase' ); ?></th>
						<?php if ( 1 === $multi_product ) : ?>
							<th><?php esc_html_e( 'Product', 'knowledgebase' ); ?></th>
						<?php endif; ?>
						<th class="wzkb-wizard-col-actions"><?php esc_html_e( 'Actions', 'knowledgebase' ); ?></th>
					</tr>
				</thead>
				<tbody class="wzkb-wizard-repeater-rows">
					<?php $this->render_empty_repeater_row( 'wzkb_wizard_sections', $existing_sections, $products, ( 1 === $multi_product ) ); ?>
				</tbody>
			</table>
			<p>
				<button type="button" class="button wzkb-wizard-add-row" data-target="wzkb_wizard_sections"><?php esc_html_e( 'Add another section', 'knowledgebase' ); ?></button>
			</p>
		</div>
		<?php
	}

	/**
	 * Render the subsections repeater fields.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	protected function render_subsections_fields() {
		$sections = get_terms(
			array(
				'taxonomy'   => 'wzkb_category',
				'hide_empty' => false,
				'parent'     => 0,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);
		if ( is_wp_error( $sections ) ) {
			$sections = array();
		}
		$all_sections = get_terms(
			array(
				'taxonomy'   => 'wzkb_category',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);
		if ( is_wp_error( $all_sections ) ) {
			$all_sections = array();
		}
		$existing_subsections = array_values(
			array_filter(
				(array) $all_sections,
				static function ( $term ) {
					return is_numeric( $term->parent ) && (int) $term->parent > 0;
				}
			)
		);
		?>
		<div class="wzkb-wizard-repeater" data-repeater-type="subsections">
			<table class="widefat striped wzkb-wizard-repeater-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Existing', 'knowledgebase' ); ?></th>
						<th><?php esc_html_e( 'Name', 'knowledgebase' ); ?></th>
						<th><?php esc_html_e( 'Slug', 'knowledgebase' ); ?></th>
						<th><?php esc_html_e( 'Description', 'knowledgebase' ); ?></th>
						<th><?php esc_html_e( 'Parent section', 'knowledgebase' ); ?></th>
						<th class="wzkb-wizard-col-actions"><?php esc_html_e( 'Actions', 'knowledgebase' ); ?></th>
					</tr>
				</thead>
				<tbody class="wzkb-wizard-repeater-rows">
					<?php $this->render_empty_repeater_row( 'wzkb_wizard_subsections', $existing_subsections, $sections, true, true ); ?>
				</tbody>
			</table>
			<p>
				<button type="button" class="button wzkb-wizard-add-row" data-target="wzkb_wizard_subsections"><?php esc_html_e( 'Add another subsection', 'knowledgebase' ); ?></button>
			</p>
		</div>
		<?php
	}

	/**
	 * Get the full section hierarchy path for a term.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Term $term Term object.
	 * @return string Hierarchy path.
	 */
	protected function get_term_hierarchy_path( $term ) {
		return wzkb_get_term_hierarchy_path( $term );
	}

	/**
	 * Render a single empty repeater row template.
	 *
	 * @since 3.0.0
	 *
	 * @param string $field_name        Field name base.
	 * @param array  $existing_terms    Existing terms for the "Existing" selector.
	 * @param array  $terms             Optional terms to populate select options.
	 * @param bool   $show_term_select  Whether to show a term select field.
	 * @param bool   $is_parent_section Whether the select is for parent section.
	 * @return void
	 */
	protected function render_empty_repeater_row( string $field_name, array $existing_terms = array(), array $terms = array(), bool $show_term_select = false, bool $is_parent_section = false ) {
		?>
		<tr class="wzkb-wizard-repeater-row">
			<td>
				<select class="wzkb-wizard-existing-select" name="<?php echo esc_attr( $field_name ); ?>[0][existing_id]">
					<option value="0"><?php esc_html_e( '— New —', 'knowledgebase' ); ?></option>
					<?php foreach ( $existing_terms as $term ) : ?>
						<?php
						$related_id = 0;
						if ( 'wzkb_wizard_sections' === $field_name ) {
							$related_id = (int) get_term_meta( (int) $term->term_id, 'product_id', true );
						}
						?>
						<option
							value="<?php echo esc_attr( (string) (int) $term->term_id ); ?>"
							data-name="<?php echo esc_attr( $term->name ); ?>"
							data-slug="<?php echo esc_attr( $term->slug ); ?>"
							data-description="<?php echo esc_attr( (string) $term->description ); ?>"
							data-parent="<?php echo esc_attr( (string) (int) $term->parent ); ?>"
							data-related-id="<?php echo esc_attr( (string) $related_id ); ?>"
						>
							<?php echo esc_html( $this->get_term_hierarchy_path( $term ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
			<td>
				<input type="text" class="regular-text wzkb-wizard-name" name="<?php echo esc_attr( $field_name ); ?>[0][name]" value="" />
			</td>
			<td>
				<input type="text" class="regular-text wzkb-wizard-slug" name="<?php echo esc_attr( $field_name ); ?>[0][slug]" value="" />
			</td>
			<td>
				<textarea class="large-text wzkb-wizard-description" rows="2" name="<?php echo esc_attr( $field_name ); ?>[0][description]"></textarea>
			</td>
			<?php if ( $show_term_select ) : ?>
				<td>
					<select class="wzkb-wizard-term-select" name="<?php echo esc_attr( $field_name ); ?>[0][<?php echo $is_parent_section ? 'parent_section_id' : 'product_id'; ?>]">
						<option value="0"><?php echo esc_html( $is_parent_section ? __( '— Select Section —', 'knowledgebase' ) : __( '— Select Product —', 'knowledgebase' ) ); ?></option>
						<?php foreach ( $terms as $term ) : ?>
							<option value="<?php echo esc_attr( (string) (int) $term->term_id ); ?>">
								<?php echo esc_html( $this->get_term_hierarchy_path( $term ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			<?php endif; ?>
			<td class="wzkb-wizard-col-actions">
				<button type="button" class="button-link-delete wzkb-wizard-remove-row"><?php esc_html_e( 'Remove', 'knowledgebase' ); ?></button>
			</td>
		</tr>
		<?php
	}

	/**
	 * Ensure the stored/current step stays within valid bounds.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	protected function maybe_clamp_current_step() {
		if ( $this->current_step < 1 ) {
			$this->current_step = 1;
		}
		if ( $this->current_step > $this->total_steps ) {
			$this->current_step = $this->total_steps;
			update_option( "{$this->prefix}_wizard_current_step", $this->current_step );
		}
	}

	/**
	 * Create products submitted from the products wizard step.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	protected function process_products_submission() {
		$rows = filter_input( INPUT_POST, 'wzkb_wizard_products', FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY );
		if ( empty( $rows ) || ! is_array( $rows ) ) {
			return;
		}
		foreach ( $rows as $row ) {
			$this->insert_or_update_term_id_from_row( $row, 'wzkb_product' );
		}
	}

	/**
	 * Create sections submitted from the sections wizard step.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	protected function process_sections_submission() {
		$rows = filter_input( INPUT_POST, 'wzkb_wizard_sections', FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY );
		if ( empty( $rows ) || ! is_array( $rows ) ) {
			return;
		}
		$multi_product = (int) \wzkb_get_option( 'multi_product', 0 );
		foreach ( $rows as $row ) {
			$term_id = $this->insert_or_update_term_id_from_row( $row, 'wzkb_category' );
			if ( $term_id <= 0 ) {
				continue;
			}
			if ( 1 === $multi_product ) {
				$product_id = isset( $row['product_id'] ) ? absint( $row['product_id'] ) : 0;
				if ( $product_id > 0 ) {
					update_term_meta( $term_id, 'product_id', $product_id );
				}
			}
		}
	}

	/**
	 * Create subsections submitted from the subsections wizard step.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	protected function process_subsections_submission() {
		$rows = filter_input( INPUT_POST, 'wzkb_wizard_subsections', FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY );
		if ( empty( $rows ) || ! is_array( $rows ) ) {
			return;
		}
		$multi_product = (int) \wzkb_get_option( 'multi_product', 0 );
		foreach ( $rows as $row ) {
			$parent_id   = isset( $row['parent_section_id'] ) ? absint( $row['parent_section_id'] ) : 0;
			$existing_id = isset( $row['existing_id'] ) ? absint( $row['existing_id'] ) : 0;
			if ( $parent_id <= 0 && $existing_id <= 0 ) {
				continue;
			}
			if ( $existing_id > 0 && $parent_id <= 0 ) {
				$existing_term = get_term( $existing_id, 'wzkb_category' );
				if ( $existing_term && ! is_wp_error( $existing_term ) ) {
					$parent_id = (int) $existing_term->parent;
				}
			}

			$args = array();
			if ( $parent_id > 0 ) {
				$args['parent'] = $parent_id;
			}
			$term_id = $this->insert_or_update_term_id_from_row( $row, 'wzkb_category', $args );
			if ( $term_id <= 0 ) {
				continue;
			}
			if ( 1 === $multi_product ) {
				$inherited_product_id = $parent_id > 0 ? (int) get_term_meta( $parent_id, 'product_id', true ) : 0;
				if ( $inherited_product_id > 0 ) {
					update_term_meta( $term_id, 'product_id', $inherited_product_id );
				}
			}
		}
	}

	/**
	 * Insert a term from a repeater row, or return existing term ID when it exists.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed  $row        Raw row data.
	 * @param string $taxonomy   Taxonomy name.
	 * @param array  $extra_args Extra arguments passed to wp_insert_term.
	 * @return int Term ID on success, 0 on failure.
	 */
	protected function insert_or_update_term_id_from_row( $row, string $taxonomy, array $extra_args = array() ): int {
		if ( ! is_array( $row ) ) {
			return 0;
		}
		$existing_id = isset( $row['existing_id'] ) ? absint( $row['existing_id'] ) : 0;
		$name        = isset( $row['name'] ) ? sanitize_text_field( wp_unslash( $row['name'] ) ) : '';
		$slug        = isset( $row['slug'] ) ? sanitize_title( wp_unslash( $row['slug'] ) ) : '';
		$description = isset( $row['description'] ) ? wp_kses_post( wp_unslash( $row['description'] ) ) : '';

		if ( $existing_id > 0 ) {
			$args = array();
			if ( '' !== $name ) {
				$args['name'] = $name;
			}
			if ( '' !== $slug ) {
				$args['slug'] = $slug;
			}
			if ( '' !== $description ) {
				$args['description'] = $description;
			}
			$args = array_merge( $args, $extra_args );

			if ( empty( $args ) ) {
				return $existing_id;
			}
			$updated = wp_update_term( $existing_id, $taxonomy, $args );
			if ( is_wp_error( $updated ) ) {
				return 0;
			}
			return (int) ( $updated['term_id'] ?? $existing_id );
		}

		if ( '' === $name ) {
			return 0;
		}
		if ( '' === $slug ) {
			$slug = sanitize_title( $name );
		}

		$existing = get_term_by( 'slug', $slug, $taxonomy );
		if ( $existing instanceof \WP_Term ) {
			return (int) $existing->term_id;
		}

		$args = array_merge(
			array(
				'slug'        => $slug,
				'description' => $description,
			),
			$extra_args
		);

		$inserted = wp_insert_term( $name, $taxonomy, $args );
		if ( is_wp_error( $inserted ) ) {
			if ( 'term_exists' === $inserted->get_error_code() ) {
				$term_id = (int) $inserted->get_error_data();
				return $term_id;
			}
			return 0;
		}
		return isset( $inserted['term_id'] ) ? (int) $inserted['term_id'] : 0;
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
