<?php
/**
 * Admin class.
 *
 * @since 2.3.0
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Admin;

use WebberZone\Knowledge_Base\Admin\Settings\Settings_API;
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
	 * @var Settings Settings API.
	 */
	public Settings $settings;

	/**
	 * Activator class.
	 *
	 * @since 2.3.0
	 *
	 * @var Activator Activator class.
	 */
	public Activator $activator;

	/**
	 * Settings wizard.
	 *
	 * @since 3.0.0
	 *
	 * @var Settings_Wizard|null Settings wizard instance.
	 */
	public ?Settings_Wizard $settings_wizard = null;

	/**
	 * Cache.
	 *
	 * @since 2.3.0
	 *
	 * @var Cache Cache.
	 */
	public Cache $cache;

	/**
	 * Admin columns.
	 *
	 * @since 2.3.0
	 *
	 * @var Admin_Columns Admin columns.
	 */
	public Admin_Columns $admin_columns;

	/**
	 * Product Migrator class.
	 *
	 * @since 3.0.0
	 *
	 * @var Product_Migrator Product Migrator class.
	 */
	public Product_Migrator $product_migrator;

	/**
	 * Admin Notices API.
	 *
	 * @since 4.1.0
	 *
	 * @var Admin_Notices_API Admin notices API.
	 */
	public Admin_Notices_API $admin_notices_api;

	/**
	 * Section Product Meta class.
	 *
	 * @since 3.0.0
	 *
	 * @var Section_Product_Meta Section Product Meta class.
	 */
	public Section_Product_Meta $section_product_meta;

	/**
	 * Tools Page class.
	 *
	 * @since 3.0.0
	 *
	 * @var Tools_Page Tools Page class.
	 */
	public Tools_Page $tools_page;

	/**
	 * Admin banner helper instance.
	 *
	 * @since 3.0.0
	 *
	 * @var Admin_Banner
	 */
	public Admin_Banner $admin_banner;

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
		$this->admin_notices_api    = new Admin_Notices_API();
		$this->settings_wizard      = new Settings_Wizard();
		$this->tools_page           = new Tools_Page();
		$this->admin_banner         = new Admin_Banner( $this->get_admin_banner_config() );
	}

	/**
	 * Retrieve the configuration array for the admin banner.
	 *
	 * @since 3.0.0
	 *
	 * @return array<string, mixed>
	 */
	private function get_admin_banner_config(): array {
		$kb_url       = wzkb_get_kb_url();
		$settings_url = admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-settings' );
		$products_url = admin_url( 'edit-tags.php?taxonomy=wzkb_product&post_type=wz_knowledgebase' );
		$sections_url = admin_url( 'edit-tags.php?taxonomy=wzkb_category&post_type=wz_knowledgebase' );
		$tags_url     = admin_url( 'edit-tags.php?taxonomy=wzkb_tag&post_type=wz_knowledgebase' );
		$tools_url    = admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb_tools_page' );

		return array(
			'capability' => 'edit_posts',
			'prefix'     => 'wzkb',
			'screen_ids' => array(
				'edit-wz_knowledgebase',
				'wz_knowledgebase',
				'wz_knowledgebase_page_wzkb-settings',
				'knowledgebase_page_wzkb-settings',
				'wz_knowledgebase_page_wzkb_tools_page',
				'knowledgebase_page_wzkb_tools_page',
				'wz_knowledgebase_page_wzkb-github',
				'knowledgebase_page_wzkb-github',
				'wz_knowledgebase_page_wzkb-ratings',
				'knowledgebase_page_wzkb-ratings',
				'wz_knowledgebase_page_wzkb-product-migration',
				'knowledgebase_page_wzkb-product-migration',
				'wz_knowledgebase_page_wzkb_wizard',
				'knowledgebase_page_wzkb_wizard',
				'edit-wzkb_category',
				'term-wzkb_category',
				'edit-wzkb_product',
				'term-wzkb_product',
				'edit-wzkb_tag',
				'term-wzkb_tag',
			),
			'page_slugs' => array(
				'wzkb-settings',
				'wzkb_tools_page',
				'wzkb-github',
				'wzkb-ratings',
				'wzkb-product-migration',
				'wzkb_wizard',
			),
			'strings'    => array(
				'region_label' => esc_html__( 'Knowledge Base quick links', 'knowledgebase' ),
				'nav_label'    => esc_html__( 'Knowledge Base admin shortcuts', 'knowledgebase' ),
				'eyebrow'      => esc_html__( 'WebberZone Knowledge Base', 'knowledgebase' ),
				'title'        => esc_html__( 'Shape a helpful support hub your users will love.', 'knowledgebase' ),
				'text'         => esc_html__( 'Jump to your most-used Knowledge Base tools, manage content faster, and explore more WebberZone plugins.', 'knowledgebase' ),
			),
			'sections'   => array(
				'archive'  => array(
					'label'  => esc_html__( 'View Knowledge Base', 'knowledgebase' ),
					'url'    => $kb_url,
					'type'   => 'primary',
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
				),
				'settings' => array(
					'label'      => esc_html__( 'Settings', 'knowledgebase' ),
					'url'        => $settings_url,
					'screen_ids' => array( 'wz_knowledgebase_page_wzkb-settings', 'knowledgebase_page_wzkb-settings' ),
					'page_slugs' => array( 'wzkb-settings' ),
				),
				'products' => array(
					'label'      => esc_html__( 'Products', 'knowledgebase' ),
					'url'        => $products_url,
					'screen_ids' => array( 'edit-wzkb_product', 'term-wzkb_product' ),
					'page_slugs' => array( 'edit-tags.php?taxonomy=wzkb_product' ),
				),
				'sections' => array(
					'label'      => esc_html__( 'Sections', 'knowledgebase' ),
					'url'        => $sections_url,
					'screen_ids' => array( 'edit-wzkb_category', 'term-wzkb_category' ),
				),
				'tags'     => array(
					'label'      => esc_html__( 'Tags', 'knowledgebase' ),
					'url'        => $tags_url,
					'screen_ids' => array( 'edit-wzkb_tag', 'term-wzkb_tag' ),
				),
				'tools'    => array(
					'label'      => esc_html__( 'Tools', 'knowledgebase' ),
					'url'        => $tools_url,
					'screen_ids' => array( 'wz_knowledgebase_page_wzkb_tools_page', 'knowledgebase_page_wzkb_tools_page' ),
					'page_slugs' => array( 'wzkb_tools_page' ),
				),
				'plugins'  => array(
					'label'  => esc_html__( 'WebberZone Plugins', 'knowledgebase' ),
					'url'    => 'https://webberzone.com/plugins/',
					'type'   => 'secondary',
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
				),
			),
		);
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

		if ( 'wz_knowledgebase' === (string) $screen->post_type ) {
			return true;
		}

		$screen_taxonomy = (string) $screen->taxonomy;
		if ( '' !== $screen_taxonomy && in_array( $screen_taxonomy, array( 'wzkb_category', 'wzkb_product', 'wzkb_tag' ), true ) ) {
			return true;
		}

		$screen_id = (string) $screen->id;
		if ( '' !== $screen_id && in_array( $screen_id, array( 'wz_knowledgebase_page_wzkb-settings', 'knowledgebase_page_wzkb-settings' ), true ) ) {
			return true;
		}

		if ( $this->is_tools_screen( $screen, $page_param ) ) {
			return true;
		}

		if ( '' !== $page_param && in_array(
			$page_param,
			array( 'wzkb-settings', 'wzkb_tools_page', 'wzkb-github', 'wzkb-ratings', 'wzkb-product-migration', 'wzkb_wizard' ),
			true
		) ) {
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
	 * Retrieve a sanitized request variable intended for use as a key/slug.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key Request key to fetch.
	 *
	 * @return string Sanitised key value.
	 */
	private function get_request_key_param( string $key ): string {
		$value_raw = filter_input( INPUT_GET, $key, FILTER_UNSAFE_RAW );

		if ( is_string( $value_raw ) && '' !== $value_raw ) {
			return sanitize_key( $value_raw );
		}

		if ( isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return sanitize_key( wp_unslash( (string) $_GET[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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
		$candidates = array_filter(
			array(
				(string) $screen->id,
				(string) $screen->base,
				(string) $screen->parent_base,
				(string) $screen->parent_file,
				$page_param,
			)
		);

		foreach ( $candidates as $candidate ) {
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
		Hook_Registry::add_action( 'admin_init', array( $this, 'register_notices' ) );
		Hook_Registry::add_filter( 'dashboard_glance_items', array( $this, 'dashboard_glance_items' ), 10, 1 );
		Hook_Registry::add_filter( 'admin_head', array( $this, 'admin_head' ) );
		Hook_Registry::add_action( 'wp_ajax_wzkb_verify_github_pat', array( $this, 'ajax_verify_github_pat' ) );
	}

	/**
	 * Register admin notices.
	 *
	 * @since 3.0.0
	 */
	public function register_notices() {
		$kb_slug      = \wzkb_get_option( 'kb_slug', 'not-set-random-string' );
		$product_slug = \wzkb_get_option( 'product_slug', 'not-set-random-string' );
		$cat_slug     = \wzkb_get_option( 'category_slug', 'not-set-random-string' );
		$tag_slug     = \wzkb_get_option( 'tag_slug', 'not-set-random-string' );

		// Notice for missing settings.
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$is_setup_page = 'wzkb_wizard' === $page;
		$slugs_not_set = 'not-set-random-string' === $kb_slug
			|| 'not-set-random-string' === $product_slug
			|| 'not-set-random-string' === $cat_slug
			|| 'not-set-random-string' === $tag_slug;

		if ( ! $is_setup_page && $slugs_not_set ) {
			$this->admin_notices_api->register_notice(
				array(
					'id'          => 'wzkb_settings_not_registered',
					'type'        => 'error',
					'dismissible' => false,
					'message'     => sprintf(
						/* translators: 1. Link to admin page. */
						esc_html__( 'Knowledge Base settings for the slug have not been registered. Please visit the %s to update and save the options.', 'knowledgebase' ),
						'<a href="' . esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-settings' ) ) . '">' . esc_html__( 'admin page', 'knowledgebase' ) . '</a>'
					),
				)
			);
		}

		// Notice for Products taxonomy when multi-product mode is disabled.
		$this->admin_notices_api->register_notice(
			array(
				'id'          => 'wzkb_multi_product_disabled',
				'type'        => 'warning',
				'dismissible' => false,
				'screens'     => array( 'edit-wzkb_product' ),
				'conditions'  => array(
					function () {
						return ! (int) \wzkb_get_option( 'multi_product', 0 );
					},
				),
				'message'     => sprintf(
					/* translators: %s: HTML link to the plugin settings page. */
					esc_html__( 'The Products taxonomy is only available in multi-product mode. Please enable multi-product mode in the %s.', 'knowledgebase' ),
					sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-settings' ) ), esc_html__( 'plugin settings', 'knowledgebase' ) )
				),
			)
		);
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
					'confirm_message'       => esc_html__( 'Are you sure you want to clear the cache?', 'knowledgebase' ),
					'flush_confirm_message' => esc_html__( 'Are you sure you want to flush the permalinks?', 'knowledgebase' ),
					'success_message'       => esc_html__( 'Cache cleared successfully!', 'knowledgebase' ),
					'fail_message'          => esc_html__( 'Failed to clear cache. Please try again.', 'knowledgebase' ),
					'request_fail_message'  => esc_html__( 'Request failed: ', 'knowledgebase' ),
					'pat_verify_error'      => esc_html__( 'Unable to verify token.', 'knowledgebase' ),
					'flush_permalinks_text' => esc_html__( 'Flush Permalinks', 'knowledgebase' ),
					'pat_required'          => esc_html__( 'Please enter a Personal Access Token first.', 'knowledgebase' ),
					'repos_refreshed'       => esc_html__( 'Repository list refreshed.', 'knowledgebase' ),
					'repos_refresh_failed'  => esc_html__( 'Unable to refresh.', 'knowledgebase' ),
					'token_valid'           => esc_html__( 'Token valid.', 'knowledgebase' ),
					'contents_read_label'   => esc_html__( 'contents:read', 'knowledgebase' ),
					'contents_write_label'  => esc_html__( 'contents:write', 'knowledgebase' ),
					'no_repo_to_test'       => esc_html__( 'no accessible repository found to test', 'knowledgebase' ),
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
			$post_type = $this->get_request_key_param( 'post_type' );
			$taxonomy  = $this->get_request_key_param( 'taxonomy' );
			$page      = $this->get_request_page_param();

			if ( 'wz_knowledgebase' === $post_type || in_array( $taxonomy, array( 'wzkb_category', 'wzkb_product', 'wzkb_tag' ), true ) || in_array( $page, array( 'wzkb-settings', 'wzkb_tools_page' ), true ) ) {
				$should_enqueue = true;
			}
		}

		if ( $should_enqueue ) {
			wp_enqueue_script( 'wzkb-admin' );
			wp_enqueue_style( 'wzkb-admin-ui' );
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
	 * AJAX handler to verify a GitHub Personal Access Token.
	 *
	 * Accepts the current field value (fresh or masked) and an optional
	 * mapping row ID. Mirrors freemkit's connection-validate pattern:
	 * fresh token → use directly; masked → decrypt from saved settings.
	 *
	 * @since 3.1.0
	 *
	 * @return void
	 */
	public function ajax_verify_github_pat(): void {
		check_ajax_referer( 'wzkb-admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to perform this action.', 'knowledgebase' ) ) );
		}

		$pat_param   = isset( $_POST['pat'] ) ? trim( (string) wp_unslash( $_POST['pat'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$mapping_row = isset( $_POST['mapping_row'] ) ? sanitize_text_field( wp_unslash( $_POST['mapping_row'] ) ) : '';

		// Resolve PAT: fresh value > saved per-mapping value > saved global value.
		if ( '' !== $pat_param && false === strpos( $pat_param, '**' ) ) {
			// Unmasked token passed directly from the field.
			$pat = $pat_param;
		} elseif ( '' !== $mapping_row ) {
			// Masked or empty per-mapping token — decrypt from saved settings row.
			$pat = $this->resolve_mapping_pat( $mapping_row );
		} else {
			// Global PAT — decrypt from settings.
			$pat = Settings_API::decrypt_api_key( (string) wzkb_get_option( 'github_pat' ) );
		}

		if ( empty( $pat ) ) {
			wp_send_json_error(
				array( 'message' => esc_html__( 'No GitHub Personal Access Token configured. Save a token first.', 'knowledgebase' ) )
			);
		}

		$api          = new \WebberZone\Knowledge_Base\Pro\GitHub\API();
		$api_with_pat = $api->with_pat( $pat );
		$result       = $api_with_pat->validate_token();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		$user_data = $result['user'] ?? array();
		$scopes    = $result['scopes'] ?? '';
		$login     = isset( $user_data['login'] ) ? (string) $user_data['login'] : '';
		$message   = $login
			? sprintf(
				/* translators: %s: GitHub username. */
				esc_html__( 'Token valid — connected as %s', 'knowledgebase' ),
				$login
			)
			: esc_html__( 'Token valid.', 'knowledgebase' );

		// Determine contents permissions.
		// Classic tokens expose scopes via X-OAuth-Scopes; fine-grained tokens do not,
		// so we probe an accessible repo and read its `permissions` block (pull/push)
		// which GitHub returns for authenticated requests on GET /repos/{owner}/{repo}.
		if ( '' === $scopes ) {
			$probed      = $this->probe_fine_grained_contents_perms( $api_with_pat );
			$permissions = array(
				'token_type'     => 'fine_grained',
				'contents_read'  => $probed['read'],
				'contents_write' => $probed['write'],
			);
		} else {
			$scope_list      = array_map( 'trim', explode( ',', $scopes ) );
			$has_repo        = in_array( 'repo', $scope_list, true );
			$has_public_repo = in_array( 'public_repo', $scope_list, true );
			$permissions     = array(
				'token_type'     => 'classic',
				'scopes'         => $scopes,
				'contents_read'  => $has_repo || $has_public_repo,
				'contents_write' => $has_repo || $has_public_repo,
			);
		}

		wp_send_json_success(
			array(
				'message'     => $message,
				'user'        => $login,
				'permissions' => $permissions,
			)
		);
	}

	/**
	 * Probe contents:read / contents:write for a fine-grained PAT.
	 *
	 * GitHub does not expose a fine-grained token's permission set directly, and
	 * the `permissions` object on `GET /repos/{owner}/{repo}` reflects the
	 * authenticated *user's* role on the repo, not the token's effective scopes
	 * — so a repo owner with a read-only PAT still sees `push: true` there.
	 *
	 * Reliable approach:
	 *  - contents:read  → call the Contents API and check it succeeds.
	 *  - contents:write → make a PUT to a non-existent path with no body. GitHub
	 *    enforces token permissions before validating the body, so the response
	 *    is 403 when the token lacks contents:write, or 4xx (validation) when
	 *    it has it. No write occurs because the body is empty.
	 *
	 * Returns array with keys 'read' and 'write', each true|false|null
	 * (null = could not be determined — no accessible repository found).
	 *
	 * @since 3.1.0
	 *
	 * @param \WebberZone\Knowledge_Base\Pro\GitHub\API $api Configured API instance.
	 * @return array{read: bool|null, write: bool|null}
	 */
	private function probe_fine_grained_contents_perms( \WebberZone\Knowledge_Base\Pro\GitHub\API $api ): array {
		$result = array(
			'read'  => null,
			'write' => null,
		);

		$repos_body = $api->request(
			add_query_arg(
				array(
					'per_page'    => 1,
					'affiliation' => 'owner,organization_member,collaborator',
					'sort'        => 'updated',
				),
				'https://api.github.com/user/repos'
			)
		);
		if ( is_wp_error( $repos_body ) ) {
			return $result;
		}
		$repos = json_decode( $repos_body, true );
		if ( ! is_array( $repos ) || empty( $repos ) ) {
			return $result;
		}
		$parts = explode( '/', (string) ( $repos[0]['full_name'] ?? '' ), 2 );
		if ( count( $parts ) < 2 || '' === $parts[1] ) {
			return $result;
		}
		$owner = $parts[0];
		$repo  = $parts[1];

		// contents:read — listing the repo root via Contents API requires
		// contents:read. A successful response (any 2xx) means read is granted.
		$contents_body  = $api->request(
			sprintf(
				'https://api.github.com/repos/%s/%s/contents/',
				rawurlencode( $owner ),
				rawurlencode( $repo )
			)
		);
		$result['read'] = ! is_wp_error( $contents_body );

		// contents:write — non-destructive probe: PUT to Contents with empty body.
		// 403 = no write; any other 4xx = write granted (failed validation only).
		$result['write'] = $this->probe_contents_write( $api, $owner, $repo );

		return $result;
	}

	/**
	 * Non-destructive probe to determine if a PAT has contents:write on a repo.
	 *
	 * Issues `PUT /repos/{owner}/{repo}/contents/{probe-path}` with an empty body.
	 * GitHub validates token permissions before the body, so:
	 *  - 403 → token lacks contents:write
	 *  - 4xx other (typically 422) → token has contents:write; only the body
	 *    failed validation. No write occurs.
	 *
	 * @since 3.1.0
	 *
	 * @param \WebberZone\Knowledge_Base\Pro\GitHub\API $api   Configured API instance.
	 * @param string                                    $owner Repository owner.
	 * @param string                                    $repo  Repository name.
	 * @return bool|null True/false on conclusive result, null on transport error.
	 */
	private function probe_contents_write( \WebberZone\Knowledge_Base\Pro\GitHub\API $api, string $owner, string $repo ) {
		$url = sprintf(
			'https://api.github.com/repos/%s/%s/contents/.wzkb-permission-probe',
			rawurlencode( $owner ),
			rawurlencode( $repo )
		);

		$response = $api->request_raw(
			$url,
			'PUT',
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => '{}',
			)
		);
		if ( is_wp_error( $response ) ) {
			return null;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( 403 === $code ) {
			return false;
		}
		if ( $code >= 400 && $code < 500 ) {
			// 422/404/etc. — body rejected, but the permission check passed.
			return true;
		}
		// 2xx (shouldn't happen with empty body) or 5xx — treat as inconclusive.
		return null;
	}

	/**
	 * Resolve the decrypted PAT for a saved repository mapping by its row ID.
	 *
	 * @since 3.1.0
	 *
	 * @param string $row_id Repeater row identifier.
	 * @return string Decrypted PAT, or empty string if not found.
	 */
	private function resolve_mapping_pat( string $row_id ): string {
		$repositories = wzkb_get_option( 'github_repositories', array() );

		if ( ! is_array( $repositories ) ) {
			return '';
		}

		foreach ( $repositories as $repo ) {
			if ( ! is_array( $repo ) ) {
				continue;
			}
			if ( (string) ( $repo['row_id'] ?? '' ) !== $row_id ) {
				continue;
			}
			$flat = \WebberZone\Knowledge_Base\Pro\GitHub\GitHub::normalize_mapping( $repo );
			return Settings_API::decrypt_api_key( (string) ( $flat['pat'] ?? '' ) );
		}

		return '';
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
		if ( function_exists( '\WebberZone\Knowledge_Base\wzkb_freemius' ) && ! \WebberZone\Knowledge_Base\wzkb_freemius()->is_paying() ) {
			?>
				<div id="pro-upgrade-banner">
					<div class="inside">
						<?php if ( ! empty( $custom_text ) ) : ?>
							<p><?php echo wp_kses_post( $custom_text ); ?></p>
						<?php endif; ?>

						<?php if ( $donate ) : ?>
							<p><a href="https://wzn.io/donate-wz" target="_blank"><img src="<?php echo esc_url( plugins_url( 'images/support.webp', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Support the development - Send us a donation today.', 'knowledgebase' ); ?>" width="300" height="169" style="max-width: 100%;" /></a></p>
						<?php endif; ?>
					</div>
				</div>
			<?php
		}
	}
}
