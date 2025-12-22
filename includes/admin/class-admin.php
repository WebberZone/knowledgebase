<?php
/**
 * Admin class.
 *
 * @since 2.3.0
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Admin;

use WebberZone\Knowledge_Base\Util\Cache;
use WebberZone\Knowledge_Base\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to register the Knowledge Base Admin Area.
 *
 * @since 2.3.0
 */
class Admin {

	/**
	 * Settings API.
	 *
	 * @since 2.3.0
	 *
	 * @var object Settings API.
	 */
	public $settings;

	/**
	 * Activator class.
	 *
	 * @since 2.3.0
	 *
	 * @var object Activator class.
	 */
	public $activator;

	/**
	 * Setup wizard.
	 *
	 * @since 3.0.0
	 *
	 * @var Setup_Wizard|null Setup wizard instance.
	 */
	public ?Setup_Wizard $setup_wizard = null;

	/**
	 * Cache.
	 *
	 * @since 2.3.0
	 *
	 * @var object Cache.
	 */
	public $cache;

	/**
	 * Admin columns.
	 *
	 * @since 2.3.0
	 *
	 * @var object Admin columns.
	 */
	public $admin_columns;

	/**
	 * Product Migrator class.
	 *
	 * @since 3.0.0
	 *
	 * @var object Product Migrator class.
	 */
	public $product_migrator;

	/**
	 * Section Product Meta class.
	 *
	 * @since 3.0.0
	 *
	 * @var object Section Product Meta class.
	 */
	public $section_product_meta;

	/**
	 * Tools Page class.
	 *
	 * @since 3.0.0
	 *
	 * @var object Tools Page class.
	 */
	public $tools_page;

	/**
	 * Main constructor class.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		$this->hooks();

		// Initialise admin classes.
		$this->settings             = new Settings();
		$this->activator            = new Activator();
		$this->cache                = new Cache();
		$this->admin_columns        = new Admin_Columns();
		$this->section_product_meta = new Section_Product_Meta();
		$this->product_migrator     = new Product_Migrator();
		$this->setup_wizard         = new Setup_Wizard();
		$this->tools_page           = new Tools_Page();
	}

	/**
	 * Render the Knowledge Base admin banner.
	 *
	 * @since 3.0.0
	 */
	public function render_admin_banner() {
		$screen = get_current_screen();

		if ( ! $screen || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		if ( ! $this->is_knowledge_base_screen( $screen ) ) {
			return;
		}

		$kb_url          = wzkb_get_kb_url();
		$products_url    = admin_url( 'edit-tags.php?taxonomy=wzkb_product&post_type=wz_knowledgebase' );
		$sections_url    = admin_url( 'edit-tags.php?taxonomy=wzkb_category&post_type=wz_knowledgebase' );
		$tags_url        = admin_url( 'edit-tags.php?taxonomy=wzkb_tag&post_type=wz_knowledgebase' );
		$tools_url       = admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb_tools_page' );
		$plugin_url      = 'https://webberzone.com/plugins/';
		$current_section = '';
		$page_param      = $this->get_request_page_param();

		if ( ! empty( $screen->taxonomy ) ) {
			if ( 'wzkb_product' === $screen->taxonomy ) {
				$current_section = 'products';
			} elseif ( 'wzkb_category' === $screen->taxonomy ) {
				$current_section = 'sections';
			} elseif ( 'wzkb_tag' === $screen->taxonomy ) {
				$current_section = 'tags';
			}
		} elseif ( $this->is_tools_screen( $screen, $page_param ) ) {
			$current_section = 'tools';
		}

		$products_classes = 'wzkb-admin-banner__link wzkb-admin-banner__link--secondary' . ( 'products' === $current_section ? ' wzkb-admin-banner__link--current' : '' );
		$sections_classes = 'wzkb-admin-banner__link wzkb-admin-banner__link--secondary' . ( 'sections' === $current_section ? ' wzkb-admin-banner__link--current' : '' );
		$tags_classes     = 'wzkb-admin-banner__link wzkb-admin-banner__link--secondary' . ( 'tags' === $current_section ? ' wzkb-admin-banner__link--current' : '' );
		$tools_classes    = 'wzkb-admin-banner__link wzkb-admin-banner__link--secondary' . ( 'tools' === $current_section ? ' wzkb-admin-banner__link--current' : '' );

		?>
		<div class="wzkb-admin-banner" role="region" aria-label="<?php echo esc_attr__( 'Knowledge Base quick links', 'knowledgebase' ); ?>">
			<div class="wzkb-admin-banner__intro">
				<span class="wzkb-admin-banner__eyebrow"><?php esc_html_e( 'WebberZone Knowledge Base', 'knowledgebase' ); ?></span>
				<p class="wzkb-admin-banner__title"><?php esc_html_e( 'Shape a helpful support hub your users will love.', 'knowledgebase' ); ?></p>
				<p class="wzkb-admin-banner__text"><?php esc_html_e( 'Jump to your most-used Knowledge Base tools, manage content faster, and explore more WebberZone plugins.', 'knowledgebase' ); ?></p>
			</div>
			<nav class="wzkb-admin-banner__links" aria-label="<?php echo esc_attr__( 'Knowledge Base admin shortcuts', 'knowledgebase' ); ?>">
				<a class="wzkb-admin-banner__link wzkb-admin-banner__link--primary wzkb-admin-banner__link--kb-archive" href="<?php echo esc_url( $kb_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'View Knowledge Base', 'knowledgebase' ); ?>
				</a>
				<a class="<?php echo esc_attr( $products_classes . ' wzkb-admin-banner__link--products' ); ?>" href="<?php echo esc_url( $products_url ); ?>">
					<?php esc_html_e( 'Products', 'knowledgebase' ); ?>
				</a>
				<a class="<?php echo esc_attr( $sections_classes . ' wzkb-admin-banner__link--sections' ); ?>" href="<?php echo esc_url( $sections_url ); ?>">
					<?php esc_html_e( 'Sections', 'knowledgebase' ); ?>
				</a>
				<a class="<?php echo esc_attr( $tags_classes . ' wzkb-admin-banner__link--tags' ); ?>" href="<?php echo esc_url( $tags_url ); ?>">
					<?php esc_html_e( 'Tags', 'knowledgebase' ); ?>
				</a>
				<a class="<?php echo esc_attr( $tools_classes . ' wzkb-admin-banner__link--tools' ); ?>" href="<?php echo esc_url( $tools_url ); ?>">
					<?php esc_html_e( 'Tools', 'knowledgebase' ); ?>
				</a>
				<a class="wzkb-admin-banner__link wzkb-admin-banner__link--accent wzkb-admin-banner__link--plugins" href="<?php echo esc_url( $plugin_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'More WebberZone Plugins', 'knowledgebase' ); ?>
				</a>
			</nav>
		</div>
		<?php
	}

	/**
	 * Determine if the banner should be rendered on the current screen.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Screen $screen Current screen.
	 *
	 * @return bool
	 */
	private function is_knowledge_base_screen( \WP_Screen $screen ): bool {
		$page_param = $this->get_request_page_param();

		if ( isset( $screen->post_type ) && 'wz_knowledgebase' === $screen->post_type ) {
			return true;
		}

		if ( isset( $screen->taxonomy ) && in_array( $screen->taxonomy, array( 'wzkb_category', 'wzkb_product', 'wzkb_tag' ), true ) ) {
			return true;
		}

		if ( isset( $screen->id ) && in_array( $screen->id, array( 'wz_knowledgebase_page_wzkb-settings', 'knowledgebase_page_wzkb-settings' ), true ) ) {
			return true;
		}

		if ( $this->is_tools_screen( $screen, $page_param ) ) {
			return true;
		}

		if ( '' !== $page_param && in_array( $page_param, array( 'wzkb-settings', 'wzkb_tools_page' ), true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve the current admin page query parameter in a sanitised form.
	 *
	 * @since 3.0.0
	 *
	 * @return string Sanitised page identifier.
	 */
	private function get_request_page_param(): string {
		$page_param_raw = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( is_string( $page_param_raw ) && '' !== $page_param_raw ) {
			return sanitize_key( $page_param_raw );
		}

		if ( isset( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return sanitize_key( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return '';
	}

	/**
	 * Determine whether the current screen represents the Tools page.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Screen $screen      Current screen instance.
	 * @param string     $page_param  Sanitised page query parameter.
	 *
	 * @return bool
	 */
	private function is_tools_screen( \WP_Screen $screen, string $page_param ): bool {
		$candidates = array();

		if ( isset( $screen->id ) ) {
			$candidates[] = (string) $screen->id;
		}

		if ( isset( $screen->base ) ) {
			$candidates[] = (string) $screen->base;
		}

		if ( isset( $screen->parent_base ) ) {
			$candidates[] = (string) $screen->parent_base;
		}

		if ( isset( $screen->parent_file ) ) {
			$candidates[] = (string) $screen->parent_file;
		}

		if ( '' !== $page_param ) {
			$candidates[] = $page_param;
		}

		foreach ( $candidates as $candidate ) {
			if ( '' === $candidate ) {
				continue;
			}

			if ( false !== strpos( $candidate, 'wzkb_tools_page' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Run the hooks.
	 *
	 * @since 2.3.0
	 */
	public function hooks() {
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		Hook_Registry::add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		Hook_Registry::add_filter( 'dashboard_glance_items', array( $this, 'dashboard_glance_items' ), 10, 1 );
		Hook_Registry::add_filter( 'admin_head', array( $this, 'admin_head' ) );
		Hook_Registry::add_action( 'in_admin_header', array( $this, 'render_admin_banner' ) );
	}

	/**
	 * Enqueue scripts in admin area.
	 *
	 * @since 2.3.0
	 */
	public function admin_enqueue_scripts() {

		$minimize = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_script(
			'wzkb-admin',
			plugins_url( "js/admin-scripts{$minimize}.js", __FILE__ ),
			array( 'jquery', 'jquery-ui-tabs' ),
			WZKB_VERSION,
			true
		);
		wp_localize_script(
			'wzkb-admin',
			'WZKBAdminData',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'wzkb-admin' ),
				'strings'  => array(
					'confirm_message'      => esc_html__( 'Are you sure you want to clear the cache?', 'knowledgebase' ),
					'success_message'      => esc_html__( 'Cache cleared successfully!', 'knowledgebase' ),
					'fail_message'         => esc_html__( 'Failed to clear cache. Please try again.', 'knowledgebase' ),
					'request_fail_message' => esc_html__( 'Request failed: ', 'knowledgebase' ),
				),
			)
		);

		wp_register_style(
			'wzkb-admin-ui',
			plugins_url( "css/admin{$minimize}.css", __FILE__ ),
			array(),
			WZKB_VERSION
		);

		$screen         = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$should_enqueue = false;

		if ( $screen && $this->is_knowledge_base_screen( $screen ) ) {
			$should_enqueue = true;
		} else {
			$post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING );
			$taxonomy  = filter_input( INPUT_GET, 'taxonomy', FILTER_SANITIZE_STRING );
			$page      = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

			if ( 'wz_knowledgebase' === $post_type || in_array( $taxonomy, array( 'wzkb_category', 'wzkb_product', 'wzkb_tag' ), true ) || in_array( $page, array( 'wzkb-settings', 'wzkb_tools_page' ), true ) ) {
				$should_enqueue = true;
			}
		}

		if ( $should_enqueue ) {
			wp_enqueue_style( 'wzkb-admin-ui' );
		}
	}

	/**
	 * Display admin notices.
	 *
	 * @since 2.3.0
	 */
	public function admin_notices() {
		// Only add the notice if the user is an admin.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$kb_slug      = \wzkb_get_option( 'kb_slug', 'not-set-random-string' );
		$product_slug = \wzkb_get_option( 'product_slug', 'not-set-random-string' );
		$cat_slug     = \wzkb_get_option( 'category_slug', 'not-set-random-string' );
		$tag_slug     = \wzkb_get_option( 'tag_slug', 'not-set-random-string' );

		// Only add the notice if the settings cannot be found. Skip if on the setup wizard page.
		if ( ! ( isset( $_GET['page'] ) && 'wzkb-setup' === $_GET['page'] ) && ( 'not-set-random-string' === $kb_slug || 'not-set-random-string' === $product_slug || 'not-set-random-string' === $cat_slug || 'not-set-random-string' === $tag_slug ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
						<div class="updated">
				<p>
					<?php
						printf(
							/* translators: 1. Link to admin page. */
							esc_html__( 'Knowledge Base settings for the slug have not been registered. Please visit the %s to update and save the options.', 'knowledgebase' ),
							'<a href="' . esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-settings' ) ) . '">' . esc_html__( 'admin page', 'knowledgebase' ) . '</a>'
						);
					?>
				</p>
			</div>
			<?php
		}

		// Show notice if on Products taxonomy screen and multi-product mode is not enabled.
		global $current_screen;
		if ( isset( $current_screen ) && 'edit-wzkb_product' === $current_screen->id && 'wzkb_product' === $current_screen->taxonomy ) { // Check for Products taxonomy admin screen.
			$multi_product = (int) wzkb_get_option( 'multi_product', 0 );
			if ( ! $multi_product ) { // Yoda condition: Only show if not enabled.
				// translators: %s: Link to plugin settings page.
				$settings_link = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-settings' ) ), esc_html__( 'plugin settings', 'knowledgebase' ) );
				$message       = sprintf(
					/* translators: %s: HTML link to the plugin settings page. */
					esc_html__( 'The Products taxonomy is only available in multi-product mode. Please enable multi-product mode in the %s.', 'knowledgebase' ),
					$settings_link
				);
				printf(
					'<div class="notice notice-warning"><p>%s</p></div>',
					wp_kses_post( $message )
				);
			}
		}
	}

	/**
	 * Add number of articles to At a Glance widget
	 *
	 * @since 2.3.0
	 *
	 * @param array $items Array of items.
	 * @return array Updated array of items
	 */
	public function dashboard_glance_items( $items ) {
		$num_posts = wp_count_posts( 'wz_knowledgebase' );

		if ( ! empty( $num_posts->publish ) ) {
			/* translators: 1. Number of articles */
			$text = _n( '%s KB article', '%s KB articles', $num_posts->publish, 'knowledgebase' );

			$text = sprintf( $text, number_format_i18n( $num_posts->publish ) );

			if ( current_user_can( 'edit_posts' ) ) {
				$text = sprintf( '<a class="wzkb-article-count" href="edit.php?post_type=wz_knowledgebase">%1$s</a>', $text );
			} else {
				$text = sprintf( '<span class="wzkb-article-count">%1$s</span>', $text );
			}

			$items[] = $text;
		}

		return $items;
	}

	/**
	 * Add CSS to Admin head
	 *
	 * @since 2.3.0
	 *
	 * return void
	 */
	public function admin_head() {
		if ( 'index.php' === $GLOBALS['pagenow'] ) {
			?>
			<style type="text/css" media="screen">
				#dashboard_right_now .wzkb-article-count:before {
					content: "\f331";
				}
			</style>
			<?php
		}
	}


	/**
	 * Display admin sidebar.
	 *
	 * @since 2.3.0
	 */
	public static function display_admin_sidebar() {
		require_once __DIR__ . '/settings/sidebar.php';
	}

	/**
	 * Display Pro upgrade banner.
	 *
	 * @since 3.0.0
	 *
	 * @param bool   $donate      Whether to show the donate banner.
	 * @param string $custom_text Custom text to show in the banner.
	 */
	public static function pro_upgrade_banner( $donate = true, $custom_text = '' ) {
		?>
			<div id="pro-upgrade-banner">
				<div class="inside">
					<?php if ( ! empty( $custom_text ) ) : ?>
						<p><?php echo wp_kses_post( $custom_text ); ?></p>
					<?php endif; ?>

					<?php if ( $donate ) : ?>
						<p><a href="https://wzn.io/donate-kb" target="_blank"><img src="<?php echo esc_url( plugins_url( 'images/support.webp', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Support the development - Send us a donation today.', 'knowledgebase' ); ?>" width="300" height="169" style="max-width: 100%;" /></a></p>
					<?php endif; ?>
				</div>
			</div>
		<?php
	}
}
