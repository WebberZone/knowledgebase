<?php
/**
 * Setup Wizard class.
 *
 * @package WebberZone\Knowledge_Base
 * @since 3.0.0
 */

namespace WebberZone\Knowledge_Base\Admin;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

// Exit if accessed directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Setup Wizard Class.
 *
 * @since 3.0.0
 */
class Setup_Wizard {

	/**
	 * Wizard steps.
	 *
	 * @since 3.0.0

	 * @var array
	 */
	private $steps = array();

	/**
	 * Current step.
	 *
	 * @since 3.0.0

	 * @var string
	 */
	private $current_step = '';

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'admin_menu', array( $this, 'admin_menus' ), PHP_INT_MAX );
		Hook_Registry::add_action( 'admin_init', array( $this, 'setup_wizard' ), PHP_INT_MAX );
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), PHP_INT_MAX );
	}

	/**
	 * Initialize wizard steps.
	 *
	 * @since 3.0.0
	 */
	private function init_wizard_steps() {
		$this->steps = array(
			'welcome'       => array(
				'name'    => esc_html__( 'Welcome', 'knowledgebase' ),
				'view'    => array( $this, 'welcome_step' ),
				'handler' => array( $this, 'welcome_save' ),
			),
			'multi_product' => array(
				'name'    => esc_html__( 'Multi-Product Mode', 'knowledgebase' ),
				'view'    => array( $this, 'mode_step' ),
				'handler' => array( $this, 'mode_save' ),
			),
			'permalinks'    => array(
				'name'    => esc_html__( 'Permalinks', 'knowledgebase' ),
				'view'    => array( $this, 'structure_step' ),
				'handler' => array( $this, 'structure_save' ),
			),
			'display'       => array(
				'name'    => esc_html__( 'Display', 'knowledgebase' ),
				'view'    => array( $this, 'display_step' ),
				'handler' => array( $this, 'display_save' ),
			),
			'complete'      => array(
				'name'    => esc_html__( 'Ready!', 'knowledgebase' ),
				'view'    => array( $this, 'complete_step' ),
				'handler' => '',
			),
		);
	}

	/**
	 * Add admin menus/screens.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function admin_menus() {
		add_submenu_page(
			'edit.php?post_type=wz_knowledgebase',
			esc_html__( 'Knowledge Base Setup', 'knowledgebase' ),
			esc_html__( 'Setup Wizard', 'knowledgebase' ),
			'manage_options',
			'wzkb-setup',
			array( $this, 'render_wizard' )
		);
	}

	/**
	 * Handle wizard logic (redirects, saves).
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function setup_wizard() {
		if ( empty( $_GET['page'] ) || 'wzkb-setup' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'knowledgebase' ) );
		}

		if ( empty( $this->steps ) ) {
			$this->init_wizard_steps();
		}

		// Remove admin_footer button from post list if present (via Hook_Registry).
		$main = \WebberZone\Knowledge_Base\wzkb();

		// Ensure the admin property exists.
		if ( isset( $main->admin ) ) {
			$admin_instance = $main->admin;
			Hook_Registry::remove_action( 'admin_footer', array( $admin_instance, 'maybe_add_button_to_post_list' ) );
		}

		// Initialize current step.
		$this->current_step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : get_option( 'wzkb_setup_current_step', current( array_keys( $this->steps ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! isset( $this->steps[ $this->current_step ] ) ) {
			$this->current_step = current( array_keys( $this->steps ) );
		}

		if ( isset( $_GET['wzkb_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wzkb_nonce'] ) ), 'wzkb-setup' ) ) {
			update_option( 'wzkb_setup_current_step', $this->current_step, false );
		}

		if ( ! empty( $_POST['wzkb_save_step'] ) && isset( $this->steps[ $this->current_step ]['handler'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			check_admin_referer( 'wzkb-setup' );
			call_user_func( $this->steps[ $this->current_step ]['handler'] );
			update_option( 'wzkb_setup_current_step', $this->get_next_step_link(), false );
		}
	}

	/**
	 * Render the wizard page.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function render_wizard() {
		if ( empty( $this->steps ) ) {
			$this->init_wizard_steps();
		}
		?>
		<div class="wrap wzkb-setup">
			<h1><?php esc_html_e( 'WebberZone Knowledge Base Setup Wizard', 'knowledgebase' ); ?></h1>
			<?php settings_errors( 'wzkb_setup' ); ?>
			<?php $this->setup_wizard_steps(); ?>
			<?php $this->setup_wizard_content(); ?>
		</div>
		<?php
	}

	/**
	 * Output the steps.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function setup_wizard_steps() {
		$output_steps = $this->steps;
		?>
		<ol class="wzkb-setup-steps" role="tablist" aria-label="<?php esc_attr_e( 'Setup Wizard Steps', 'knowledgebase' ); ?>">
			<?php
			foreach ( $output_steps as $step_key => $step ) {
				$is_completed = array_search( $this->current_step, array_keys( $this->steps ), true ) > array_search( $step_key, array_keys( $this->steps ), true );
				$aria_current = $step_key === $this->current_step ? ' aria-current="step"' : '';
				$class        = $step_key === $this->current_step ? 'active' : ( $is_completed ? 'done' : '' );
				?>
				<li class="<?php echo esc_attr( $class ); ?>"<?php echo $aria_current; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php echo esc_html( $step['name'] ); ?>
				</li>
				<?php
			}
			?>
		</ol>
		<?php
	}

	/**
	 * Output the content for the current step.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function setup_wizard_content() {
		?>
		<div class="wzkb-setup-content">
			<?php
			if ( ! empty( $this->steps[ $this->current_step ]['view'] ) ) {
				call_user_func( $this->steps[ $this->current_step ]['view'] );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Welcome step.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function welcome_step() {
		$kb_setup_completed = get_option( 'wzkb_setup_completed', false );
		?>
		<?php if ( $kb_setup_completed ) : ?>
		<div class="notice notice-info" style="margin-bottom:20px;">
			<p><?php esc_html_e( 'The Knowledge Base has already been set up. You can rerun the setup wizard to change your configuration, or skip to the dashboard.', 'knowledgebase' ); ?></p>
		</div>
		<?php endif; ?>
		<h1><?php esc_html_e( 'Welcome to Knowledge Base!', 'knowledgebase' ); ?></h1>
		<p><?php esc_html_e( 'Thank you for choosing Knowledge Base! This quick setup wizard will help you configure the basic settings. It is completely optional and should not take longer than five minutes.', 'knowledgebase' ); ?></p>
		<p><?php esc_html_e( 'No time right now? If you do not want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!', 'knowledgebase' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'wzkb-setup' ); ?>
			<p class="wzkb-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( "Let's Go!", 'knowledgebase' ); ?>" name="wzkb_save_step" />
				<button type="button" class="button button-large" id="wzkb-not-now" data-skip-setup><?php esc_html_e( 'Not right now', 'knowledgebase' ); ?></button>
			</p>
		</form>
		<?php
	}

	/**
	 * Welcome step save.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function welcome_save() {
		check_admin_referer( 'wzkb-setup' );
		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Mode step.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function mode_step() {
		// Load existing multi-product setting.
		$settings       = wzkb_get_settings();
		$multi          = $settings['multi_product'] ?? 0;
		$category_level = $settings['category_level'] ?? 2;
		?>
		<h1><?php esc_html_e( 'Knowledge Base Mode', 'knowledgebase' ); ?></h1>
		<p><?php esc_html_e( 'Enable multi-product mode to organize your knowledge base by product.', 'knowledgebase' ); ?></p>
		<form method="post">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="multi_product"><?php esc_html_e( 'Enable Multi-Product Mode', 'knowledgebase' ); ?></label></th>
					<td>
						<input type="hidden" name="multi_product" value="0" />
						<input type="checkbox" id="multi_product" name="multi_product" value="1" <?php checked( $multi, 1 ); ?> />
						<p class="description"><?php esc_html_e( 'Multi-product mode (created in version 3.0) allows you to organize your knowledge base by product using the dedicated Product taxonomy.', 'knowledgebase' ); ?></p>
						<p class="description"><?php esc_html_e( 'This is useful if you have multiple products that require their own documentation.', 'knowledgebase' ); ?></p>
						<p class="description"><?php esc_html_e( 'This is recommended to be enabled if you plan to use the knowledge base for multiple products.', 'knowledgebase' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="category_level"><?php esc_html_e( 'First section level', 'knowledgebase' ); ?></label></th>
					<td>
						<input type="number" id="category_level" name="category_level" value="<?php echo esc_attr( $category_level ); ?>" min="1" />
						<p class="description"><?php esc_html_e( 'Knowledge Base supports an unlimited hierarchy of sections. This allows you to create a single or multi-product knowledge base by only using Sections. This was the default mode before version 3.0.', 'knowledgebase' ); ?></p>
						<p class="description"><?php esc_html_e( 'If you plan to use multi-product mode, set this to 1 to use the sections as the first level of each product. This is the recommended mode for multi-product knowledge bases.', 'knowledgebase' ); ?></p>
						<p class="description"><?php esc_html_e( "If you don't plan to use multi-product mode, but want to have multiple top-level sections for products or categories, set this to 2. The Top-level sections will be used for products or categories.", 'knowledgebase' ); ?></p>
					</td>
				</tr>
			</table>
			<p class="wzkb-setup-actions step">
				<?php wp_nonce_field( 'wzkb-setup' ); ?>
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'knowledgebase' ); ?>" name="wzkb_save_step" />
				<a href="<?php echo esc_url( add_query_arg( 'wzkb_nonce', wp_create_nonce( 'wzkb-setup' ), $this->get_next_step_link() ) ); ?>" class="button button-large button-next"><?php esc_html_e( 'Skip this step', 'knowledgebase' ); ?></a>
			</p>
		</form>
		<?php
	}

	/**
	 * Mode step save (multi-product).
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function mode_save() {
		check_admin_referer( 'wzkb-setup' );
		// Robust checkbox save pattern.
		$multi = isset( $_POST['multi_product'] ) ? intval( $_POST['multi_product'] ) : 0;
		wzkb_update_option( 'multi_product', $multi );
		if ( wzkb_get_option( 'multi_product' ) === $multi ) {
			add_settings_error( 'wzkb_setup', 'mode_saved', esc_html__( 'Mode settings saved successfully.', 'knowledgebase' ), 'success' );
		} else {
			add_settings_error( 'wzkb_setup', 'mode_save_failed', esc_html__( 'Failed to save mode settings.', 'knowledgebase' ), 'error' );
		}
		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Permalinks step.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function structure_step() {
		// Load all settings at once.
		$settings      = wzkb_get_settings();
		$kb_slug       = $settings['kb_slug'] ?? 'knowledgebase';
		$product_slug  = $settings['product_slug'] ?? 'kb/product';
		$category_slug = $settings['category_slug'] ?? 'kb/section';
		$tag_slug      = $settings['tag_slug'] ?? 'kb/tags';
		?>
		<h1><?php esc_html_e( 'Permalinks', 'knowledgebase' ); ?></h1>
		<p>
		<?php
			printf(
				esc_html__( 'Customize the URL structure of your Knowledge Base. You can enter a relative path like %1$s, but ensure it does not conflict with other plugins or themes and does not end with a slash or contain any special characters.', 'knowledgebase' ),
				'<code>support/knowledgebase</code>'
			);
		?>
		</p>
		<p class="wzkb-setup-warning">
		<?php
			printf(
				/* translators: %1$s, %2$s, and %3$s are wrapped in <code> */
				esc_html__( 'All the below slugs must be unique and not nested within each other. e.g. %1$s (Knowledge Base) and %2$s (Product) are NOT valid as the product slug is nested below the knowledgebase slug. This also applies to Sections and Tags.', 'knowledgebase' ),
				'<code>kb</code>',
				'<code>kb/product</code>'
			);
		?>
		</p>
		<form method="post">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="kb_slug"><?php esc_html_e( 'Knowledge Base slug', 'knowledgebase' ); ?></label></th>
					<td>
						<div class="wzkb-url-prefix-row">
							<span class="wzkb-url-prefix-text"><?php echo esc_html( home_url( '/' ) ); ?></span>
							<input type="text" id="kb_slug" name="kb_slug" value="<?php echo esc_attr( $kb_slug ); ?>" class="regular-text wzkb-url-slug-input" />
						</div>
						<p class="description wzkb-url-desc"><?php esc_html_e( 'This sets the main URL of the knowledge base. It will also serve as the base URL for your articles.', 'knowledgebase' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="product_slug"><?php esc_html_e( 'Product slug', 'knowledgebase' ); ?></label></th>
					<td>
						<div class="wzkb-url-prefix-row">
							<span class="wzkb-url-prefix-text"><?php echo esc_html( home_url( '/' ) ); ?></span>
							<input type="text" id="product_slug" name="product_slug" value="<?php echo esc_attr( $product_slug ); ?>" class="regular-text wzkb-url-slug-input" />
						</div>
						<p class="description wzkb-url-desc"><?php esc_html_e( 'URL slug for product pages when Multi-Product Mode is enabled. If you are not using multi-product mode, this will be ignored.', 'knowledgebase' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="category_slug"><?php esc_html_e( 'Section slug', 'knowledgebase' ); ?></label></th>
					<td>
						<div class="wzkb-url-prefix-row">
							<span class="wzkb-url-prefix-text"><?php echo esc_html( home_url( '/' ) ); ?></span>
							<input type="text" id="category_slug" name="category_slug" value="<?php echo esc_attr( $category_slug ); ?>" class="regular-text wzkb-url-slug-input" />
						</div>
						<p class="description wzkb-url-desc"><?php esc_html_e( 'URL slug for section archives. Each Section contains a group of related articles.', 'knowledgebase' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="tag_slug"><?php esc_html_e( 'Tags slug', 'knowledgebase' ); ?></label></th>
					<td>
						<div class="wzkb-url-prefix-row">
							<span class="wzkb-url-prefix-text"><?php echo esc_html( home_url( '/' ) ); ?></span>
							<input type="text" id="tag_slug" name="tag_slug" value="<?php echo esc_attr( $tag_slug ); ?>" class="regular-text wzkb-url-slug-input" />
						</div>
						<p class="description wzkb-url-desc"><?php esc_html_e( 'URL slug for tag archives. Articles can have multiple tags for cross-categorization.', 'knowledgebase' ); ?></p>
					</td>
				</tr>
			</table>
			<p class="wzkb-setup-actions step">
				<?php wp_nonce_field( 'wzkb-setup' ); ?>
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'knowledgebase' ); ?>" name="wzkb_save_step" />
				<a href="<?php echo esc_url( add_query_arg( 'wzkb_nonce', wp_create_nonce( 'wzkb-setup' ), $this->get_next_step_link() ) ); ?>" class="button button-large button-next"><?php esc_html_e( 'Skip this step', 'knowledgebase' ); ?></a>
			</p>
		</form>
		<?php
	}

	/**
	 * Permalinks step save.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function structure_save() {
		check_admin_referer( 'wzkb-setup' );
		// Robust text save pattern for permalinks.
		$partial = array(
			'kb_slug'       => isset( $_POST['kb_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['kb_slug'] ) ) : 'knowledgebase',
			'product_slug'  => isset( $_POST['product_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['product_slug'] ) ) : 'kb/product',
			'category_slug' => isset( $_POST['category_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['category_slug'] ) ) : 'kb/section',
			'tag_slug'      => isset( $_POST['tag_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['tag_slug'] ) ) : 'kb/tags',
		);
		$updated = wzkb_update_settings( $partial );
		if ( $updated ) {
			add_settings_error( 'wzkb_setup', 'permalinks_saved', esc_html__( 'Permalink settings saved successfully.', 'knowledgebase' ), 'success' );
			flush_rewrite_rules();
		} else {
			add_settings_error( 'wzkb_setup', 'permalinks_save_failed', esc_html__( 'Failed to save permalink settings.', 'knowledgebase' ), 'error' );
		}
		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Display step.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function display_step() {
		// Load all settings at once.
		$settings           = \wzkb_get_settings();
		$kb_title           = $settings['kb_title'] ?? '';
		$show_article_count = $settings['show_article_count'] ?? 0;
		$show_excerpt       = $settings['show_excerpt'] ?? 0;
		$clickable_section  = $settings['clickable_section'] ?? 0;
		$limit              = $settings['limit'] ?? 5;
		$show_related       = $settings['show_related_articles'] ?? 0;
		$cache              = $settings['cache'] ?? 0;
		$include_styles     = $settings['include_styles'] ?? 0;
		$columns            = $settings['columns'] ?? 1;
		?>
		<h1><?php esc_html_e( 'Display Settings', 'knowledgebase' ); ?></h1>
		<form method="post">
			<h2><?php esc_html_e( 'Configure the Knowledge Base Output', 'knowledgebase' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="kb_title">
							<?php esc_html_e( 'Knowledge base title', 'knowledgebase' ); ?>
						</label>
					</th>
					<td>
						<input type="text" id="kb_title" name="kb_title" value="<?php echo esc_attr( $kb_title ); ?>" class="regular-text" />
						<p class="description"><?php esc_html_e( 'This will be displayed as the title of the archive title as well as on other relevant places.', 'knowledgebase' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="show_article_count">
							<?php esc_html_e( 'Show article count', 'knowledgebase' ); ?>
						</label>
					</th>
					<td>
						<input type="hidden" name="show_article_count" value="0" />
						<input type="checkbox" id="show_article_count" name="show_article_count" value="1" <?php checked( $show_article_count, 1 ); ?> />
						<p class="description"><?php esc_html_e( 'Show the number of articles within each section.', 'knowledgebase' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="show_excerpt">
							<?php esc_html_e( 'Show excerpt', 'knowledgebase' ); ?>
						</label>
					</th>
					<td>
						<input type="hidden" name="show_excerpt" value="0" />
						<input type="checkbox" id="show_excerpt" name="show_excerpt" value="1" <?php checked( $show_excerpt, 1 ); ?> />
						<p class="description"><?php esc_html_e( 'Show the excerpt below the article title.', 'knowledgebase' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="clickable_section">
							<?php esc_html_e( 'Link section title', 'knowledgebase' ); ?>
						</label>
					</th>
					<td>
						<input type="hidden" name="clickable_section" value="0" />
						<input type="checkbox" id="clickable_section" name="clickable_section" value="1" <?php checked( $clickable_section, 1 ); ?> />
						<p class="description"><?php esc_html_e( 'Make the section title a clickable link.', 'knowledgebase' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="limit">
							<?php esc_html_e( 'Max articles per section', 'knowledgebase' ); ?>
						</label>
					</th>
					<td>
						<input type="number" id="limit" name="limit" value="<?php echo esc_attr( $limit ); ?>" min="1" max="500" class="small-text" />
						<p class="description"><?php esc_html_e( 'Maximum number of articles to display per section. After this limit is reached, the footer is displayed with the more link to view the category.', 'knowledgebase' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="show_related_articles">
							<?php esc_html_e( 'Show related articles', 'knowledgebase' ); ?>
						</label>
					</th>
					<td>
						<input type="hidden" name="show_related_articles" value="0" />
						<input type="checkbox" id="show_related_articles" name="show_related_articles" value="1" <?php checked( $show_related, 1 ); ?> />
						<p class="description"><?php esc_html_e( 'Add related articles at the bottom of the knowledge base article. Only works when using the inbuilt template.', 'knowledgebase' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="cache">
							<?php esc_html_e( 'Enable cache', 'knowledgebase' ); ?>
						</label>
					</th>
					<td>
						<input type="hidden" name="cache" value="0" />
						<input type="checkbox" id="cache" name="cache" value="1" <?php checked( $cache, 1 ); ?> />
						<p class="description"><?php esc_html_e( 'Cache the output of the queries to speed up retrieval of the knowledgebase. Recommended for large knowledge bases.', 'knowledgebase' ); ?></p>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Style Settings', 'knowledgebase' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="include_styles"><?php esc_html_e( 'Include built-in styles', 'knowledgebase' ); ?></label></th>
					<td>
						<input type="hidden" name="include_styles" value="0" />
						<input type="checkbox" id="include_styles" name="include_styles" value="1" <?php checked( $include_styles, 1 ); ?> />
						<p class="description"><?php esc_html_e( 'Include the built-in styles for the knowledge base.', 'knowledgebase' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="columns"><?php esc_html_e( 'Number of columns', 'knowledgebase' ); ?></label></th>
					<td><input type="number" id="columns" name="columns" value="<?php echo esc_attr( $columns ); ?>" min="1" max="5" class="small-text" />
						<p class="description"><?php esc_html_e( 'Number of columns to display the knowledge base in.', 'knowledgebase' ); ?></p>
					</td>
				</tr>
			</table>

			<p class="wzkb-setup-actions step">
				<?php wp_nonce_field( 'wzkb-setup' ); ?>
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'knowledgebase' ); ?>" name="wzkb_save_step" />
				<a href="<?php echo esc_url( add_query_arg( 'wzkb_nonce', wp_create_nonce( 'wzkb-setup' ), $this->get_next_step_link() ) ); ?>" class="button button-large button-next"><?php esc_html_e( 'Skip this step', 'knowledgebase' ); ?></a>
			</p>
		</form>
		<?php
	}

	/**
	 * Display step save.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function display_save() {
		check_admin_referer( 'wzkb-setup' );
		// Robust pattern for all display settings (checkboxes, text, numbers).
		$partial = array(
			'kb_title'              => isset( $_POST['kb_title'] ) ? sanitize_text_field( wp_unslash( $_POST['kb_title'] ) ) : '',
			'show_article_count'    => isset( $_POST['show_article_count'] ) ? 1 : 0,
			'show_excerpt'          => isset( $_POST['show_excerpt'] ) ? 1 : 0,
			'clickable_section'     => isset( $_POST['clickable_section'] ) ? 1 : 0,
			'limit'                 => isset( $_POST['limit'] ) ? absint( wp_unslash( $_POST['limit'] ) ) : 5,
			'show_related_articles' => isset( $_POST['show_related_articles'] ) ? 1 : 0,
			'cache'                 => isset( $_POST['cache'] ) ? 1 : 0,
			'include_styles'        => isset( $_POST['include_styles'] ) ? 1 : 0,
			'columns'               => isset( $_POST['columns'] ) ? absint( wp_unslash( $_POST['columns'] ) ) : 1,
		);
		$updated = wzkb_update_settings( $partial );
		if ( $updated ) {
			add_settings_error( 'wzkb_setup', 'display_saved', esc_html__( 'Display settings saved successfully.', 'knowledgebase' ), 'success' );
		} else {
			add_settings_error( 'wzkb_setup', 'display_save_failed', esc_html__( 'Failed to save display settings.', 'knowledgebase' ), 'error' );
		}
		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Complete step.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function complete_step() {
		update_option( 'wzkb_setup_completed', true );
		update_option( 'wzkb_setup_current_step', '' );
		$multi_product = \wzkb_get_option( 'multi_product' );
		?>
		<h1><?php esc_html_e( 'Your Knowledge Base is Ready!', 'knowledgebase' ); ?></h1>
		<p><?php esc_html_e( 'Congratulations! You have completed the setup wizard.', 'knowledgebase' ); ?></p>
		<?php if ( $multi_product ) : ?>
			<p><?php esc_html_e( 'You can now start adding Products, Sections and Articles to your knowledge base.', 'knowledgebase' ); ?></p>
		<?php else : ?>
			<p><?php esc_html_e( 'You can now start adding Sections and Articles to your knowledge base.', 'knowledgebase' ); ?></p>
		<?php endif; ?>
		<div class="wzkb-setup-next-steps">
			<div class="wzkb-setup-next-steps-first">
				<h2><?php esc_html_e( 'Next Steps', 'knowledgebase' ); ?></h2>
				<ul class="wzkb-setup-next-actions-horizontal">
					<?php if ( $multi_product ) : ?>
					<li class="setup-product">
						<a class="button button-primary button-large" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=wz_knowledgebase' ) ); ?>">
							<?php esc_html_e( 'Create your first product', 'knowledgebase' ); ?>
						</a>
					</li>
					<?php endif; ?>
					<li class="setup-product">
						<a class="button button-large <?php echo $multi_product ? '' : ' button-primary'; ?>" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=wzkb_category&post_type=wz_knowledgebase' ) ); ?>">
							<?php esc_html_e( 'Add sections', 'knowledgebase' ); ?>
						</a>
					</li>
					<li class="setup-product">
						<a class="button button-large" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=wz_knowledgebase' ) ); ?>">
							<?php esc_html_e( 'Create your first article', 'knowledgebase' ); ?>
						</a>
					</li>
					<li class="setup-product">
						<a class="button button-large" href="<?php echo esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-settings' ) ); ?>">
							<?php esc_html_e( 'Configure additional settings', 'knowledgebase' ); ?>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Get the URL for the next step's screen.
	 *
	 * @since 3.0.0
	 *
	 * @param string $step Slug (default: current step).
	 * @return string URL for next step if a next step exists.
	 *                Admin URL if it's the last step.
	 *                Empty string on failure.
	 */
	public function get_next_step_link( $step = '' ) {
		if ( empty( $step ) ) {
			$step = $this->current_step;
		}

		$keys = array_keys( $this->steps );
		if ( end( $keys ) === $step ) {
			return admin_url( 'edit.php?post_type=wz_knowledgebase' );
		}

		$step_index = array_search( $step, $keys, true );
		if ( false === $step_index ) {
			return '';
		}

		return add_query_arg( 'step', $keys[ $step_index + 1 ], remove_query_arg( 'activate_error' ) );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( empty( $_GET['page'] ) || 'wzkb-setup' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		wp_register_style(
			'wzkb-wizard',
			plugins_url( 'css/wizard.css', __FILE__ ),
			array( 'dashicons' ),
			WZKB_VERSION
		);
		wp_enqueue_style( 'wzkb-wizard' );

		wp_register_script(
			'wzkb-wizard',
			plugins_url( 'js/wizard.js', __FILE__ ),
			array( 'jquery' ),
			WZKB_VERSION,
			true
		);
		wp_localize_script(
			'wzkb-wizard',
			'wzkbWizard',
			array(
				'skip_setup'    => esc_html__( 'Are you sure you want to skip the setup wizard? You can return to it later. This will only skip the setup wizard if you have not completed it yet.', 'knowledgebase' ),
				'dashboard_url' => admin_url( 'edit.php?post_type=wz_knowledgebase' ),
			)
		);
		wp_enqueue_script( 'wzkb-wizard' );
	}
}
