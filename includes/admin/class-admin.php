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
 * Class to register the Better Search Admin Area.
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
	 * Main constructor class.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		$this->hooks();

		// Initialise admin classes.
		$this->settings         = new Settings();
		$this->activator        = new Activator();
		$this->cache            = new Cache();
		$this->admin_columns    = new Admin_Columns();
		$this->product_migrator = new Product_Migrator();
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
		Hook_Registry::add_action( 'admin_footer', array( $this, 'maybe_add_button_to_post_list' ) );
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

		if ( isset( $_GET['post_type'] ) && 'wz_knowledgebase' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
			wp_enqueue_style( 'wzkb-admin-ui' );
		}
	}

	/**
	 * Display admin notices.
	 *
	 * @since 2.3.0
	 */
	public function admin_notices() {
		$kb_slug      = \wzkb_get_option( 'kb_slug', 'not-set-random-string' );
		$product_slug = \wzkb_get_option( 'product_slug', 'not-set-random-string' );
		$cat_slug     = \wzkb_get_option( 'category_slug', 'not-set-random-string' );
		$tag_slug     = \wzkb_get_option( 'tag_slug', 'not-set-random-string' );

		// Only add the notice if the user is an admin.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Only add the notice if the settings cannot be found.
		if ( 'not-set-random-string' === $kb_slug || 'not-set-random-string' === $product_slug || 'not-set-random-string' === $cat_slug || 'not-set-random-string' === $tag_slug ) {
			?>		
			<div class="updated">
				<p>
					<?php
						printf(
							/* translators: 1. Link to admin page. */
							esc_html__( 'Knowledge Base settings for the slug have not been registered. Please visit the <a href="%s">admin page</a> to update and save the options.', 'knowledgebase' ),
							esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-settings' ) )
						);
					?>
				</p>
			</div>
			<?php
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
	 * Add button to knowledgebase post type list screen
	 */
	public function maybe_add_button_to_post_list() {
		$screen = get_current_screen();
		if ( ! $screen ||
			( 'wz_knowledgebase' !== $screen->post_type && 'wzkb_category' !== $screen->taxonomy ) ||
			( 'edit' !== $screen->base && 'wz_knowledgebase' !== $screen->post_type ) ) {
			return;
		}

		$this->render_custom_button( 'Visit Knowledge Base' );
	}

	/**
	 * Render the custom button
	 *
	 * @since 2.3.0
	 *
	 * @param string $button_text Text to display on the button.
	 */
	private function render_custom_button( $button_text = 'Custom Action' ) {
		?>
		<script>
		jQuery(document).ready(function($) {
			// Find the H1 and insert our button right after it
			var $h1 = $('.wrap h1');
			
			// Create the button
			var $kbUrlButton = $('<a>', {
				href: '<?php wzkb_the_kb_url(); ?>',
				class: 'page-title-action wzkb_button wzkb_button_blue',
				text: '<?php echo esc_js( $button_text ); ?>',
				target: '_blank'
			});

			// Insert the button right after the H1
			$h1.after($kbUrlButton);
		});
		</script>
		<?php
	}
}
