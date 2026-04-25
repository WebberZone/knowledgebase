<?php
/**
 * Gutenberg Sections panel + REST plumbing.
 *
 * @package WebberZone\Knowledge_Base\Admin
 */

namespace WebberZone\Knowledge_Base\Admin;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Handles product-aware section selection inside the block editor.
 *
 * Editor meta is the source of truth. Taxonomy assignments are derived during save
 * operations (REST or classic). Direct term edits performed elsewhere are reconciled
 * the next time the post is saved; implement a load-time reconciliation only if that
 * invariant stops being acceptable.
 *
 * @since 3.0.0
 */
class Product_Section_Selector {

	/**
	 * Internal guard to avoid recursive sync loops.
	 *
	 * @var bool
	 */
	private bool $is_syncing = false;

	/**
	 * Cached product map.
	 *
	 * @var array|null
	 */
	private ?array $product_map = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		Hook_Registry::add_filter( 'wp_terms_checklist_args', array( $this, 'use_hierarchical_section_walker' ), 10, 2 );

		if ( 0 === (int) wzkb_get_option( 'multi_product', 0 ) ) {
			return;
		}

		Hook_Registry::add_action( 'init', array( $this, 'register_meta' ) );
		Hook_Registry::add_action( 'add_meta_boxes', array( $this, 'remove_core_taxonomy_metaboxes' ), 99 );
		Hook_Registry::add_action( 'add_meta_boxes', array( $this, 'register_classic_sections_metabox' ), 100 );
		Hook_Registry::add_filter( 'get_user_option_meta-box-order_wz_knowledgebase', array( $this, 'filter_meta_box_order' ) );
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_classic_assets' ) );
		Hook_Registry::add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		Hook_Registry::add_action( 'rest_after_insert_wz_knowledgebase', array( $this, 'sync_sections_after_rest_save' ), 10, 3 );
		Hook_Registry::add_action( 'save_post_wz_knowledgebase', array( $this, 'maybe_capture_classic_submission' ), 5, 2 );
		Hook_Registry::add_action( 'save_post_wz_knowledgebase', array( $this, 'maybe_sync_sections_during_save' ), 20, 2 );
	}

	/**
	 * Register Gutenberg-safe post meta for storing section selections.
	 */
	public function register_meta() {
		register_post_meta(
			'wz_knowledgebase',
			'_wzkb_product_ids',
			array(
				'type'              => 'array',
				'single'            => true,
				'sanitize_callback' => array( $this, 'sanitize_id_array' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'integer',
						),
					),
				),
				'auth_callback'     => static function () {
					return current_user_can( 'edit_posts' );
				},
				'default'           => array(),
			)
		);

		register_post_meta(
			'wz_knowledgebase',
			'_wzkb_section_ids',
			array(
				'type'              => 'array',
				'single'            => true,
				'sanitize_callback' => array( $this, 'sanitize_id_array' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'integer',
						),
					),
				),
				'auth_callback'     => static function () {
					return current_user_can( 'edit_posts' );
				},
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanitize array meta values.
	 *
	 * @param mixed $value Raw value.
	 * @return array
	 */
	public function sanitize_id_array( $value ) {
		if ( empty( $value ) ) {
			return array();
		}

		if ( ! is_array( $value ) ) {
			$value = array( $value );
		}

		return array_values(
			array_filter(
				array_map( 'absint', $value ),
				static function ( $maybe_id ) {
					return $maybe_id > 0;
				}
			)
		);
	}

	/**
	 * Remove classic taxonomy metaboxes replaced by custom UI.
	 */
	public function remove_core_taxonomy_metaboxes() {
		remove_meta_box( 'wzkb_categorydiv', 'wz_knowledgebase', 'side' );

		if ( ! $this->is_block_editor_enabled() ) {
			remove_meta_box( 'wzkb_productdiv', 'wz_knowledgebase', 'side' );
			remove_meta_box( 'tagsdiv-wzkb_product', 'wz_knowledgebase', 'side' );
		}
	}

	/**
	 * Register replacement metabox for the classic editor.
	 */
	public function register_classic_sections_metabox() {
		if ( $this->is_block_editor_enabled() ) {
			return;
		}

		add_meta_box(
			'wzkb-classic-sections',
			esc_html__( 'Product-aware Sections', 'knowledgebase' ),
			array( $this, 'render_classic_sections_metabox' ),
			'wz_knowledgebase',
			'side',
			'high'
		);
	}

	/**
	 * Render the custom metabox contents.
	 *
	 * @param \WP_Post $post Current post.
	 */
	public function render_classic_sections_metabox( \WP_Post $post ) {
		wp_nonce_field( 'wzkb_classic_sections', 'wzkb_classic_sections_nonce' );

		$product_ids = $this->sanitize_id_array( get_post_meta( $post->ID, '_wzkb_product_ids', true ) );
		$section_ids = $this->sanitize_id_array( get_post_meta( $post->ID, '_wzkb_section_ids', true ) );
		?>
		<input type="hidden" name="_wzkb_product_ids" id="wzkb_classic_product_ids" value="<?php echo esc_attr( implode( ',', $product_ids ) ); ?>" />
		<input type="hidden" name="_wzkb_section_ids" id="wzkb_classic_section_ids" value="<?php echo esc_attr( implode( ',', $section_ids ) ); ?>" />

		<div class="wzkb-classic-sections" data-role="root">
			<div class="wzkb-classic-sections__product-search">
				<label class="screen-reader-text" for="wzkb_classic_product_search">
					<?php esc_html_e( 'Search products', 'knowledgebase' ); ?>
				</label>
				<input
					type="search"
					id="wzkb_classic_product_search"
					class="wzkb-classic-sections__product-search-input"
					data-role="product-search"
					placeholder="<?php echo esc_attr__( 'Search products…', 'knowledgebase' ); ?>"
				/>
			</div>
			<div class="wzkb-classic-sections__products" data-role="products">
				<p class="wzkb-classic-sections__message">
					<?php esc_html_e( 'Loading products…', 'knowledgebase' ); ?>
				</p>
			</div>
			<div class="wzkb-classic-sections__sections" data-role="sections">
				<p class="wzkb-classic-sections__message">
					<?php esc_html_e( 'Select one or more products to load their sections.', 'knowledgebase' ); ?>
				</p>
			</div>
		</div>
		<noscript>
			<p class="wzkb-classic-sections__note">
				<?php esc_html_e( 'JavaScript is required to manage product-aware sections in the classic editor.', 'knowledgebase' ); ?>
			</p>
		</noscript>
		<?php
	}

	/**
	 * Enqueue assets for the classic editor metabox.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_classic_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'wz_knowledgebase' !== $screen->post_type || $this->is_block_editor_enabled() ) {
			return;
		}

		$asset_url = plugin_dir_url( __FILE__ );
		$minimize  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script(
			'wzkb-classic-sections-metabox',
			$asset_url . 'js/classic-sections-metabox' . $minimize . '.js',
			array( 'jquery', 'wp-api-fetch' ),
			WZKB_VERSION,
			true
		);

		$post_id      = $this->get_current_post_id();
		$products     = $this->sanitize_id_array( $post_id ? get_post_meta( $post_id, '_wzkb_product_ids', true ) : array() );
		$sections     = $this->sanitize_id_array( $post_id ? get_post_meta( $post_id, '_wzkb_section_ids', true ) : array() );
		$strings      = $this->get_ui_strings();
		$product_map  = $this->get_product_map();
		$localization = array(
			'endpoint' => esc_url_raw( rest_url( 'wzkb/v1/sections' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'products' => $product_map,
			'meta'     => array(
				'products' => $products,
				'sections' => $sections,
			),
			'strings'  => $strings,
		);

		wp_localize_script( 'wzkb-classic-sections-metabox', 'WZKBClassicSections', $localization );

		wp_enqueue_style(
			'wzkb-classic-sections-metabox',
			$asset_url . 'css/classic-sections-metabox' . $minimize . '.css',
			array(),
			WZKB_VERSION
		);
	}

	/**
	 * Strip the section metabox from stored meta-box order.
	 *
	 * @param array|string $order Saved order.
	 * @return array|string
	 */
	public function filter_meta_box_order( $order ) {
		if ( empty( $order ) || ! is_array( $order ) ) {
			return $order;
		}

		$hidden_boxes = array( 'wzkb_categorydiv' );
		if ( ! $this->is_block_editor_enabled() ) {
			$hidden_boxes[] = 'wzkb_productdiv';
			$hidden_boxes[] = 'tagsdiv-wzkb_product';
		}

		foreach ( $order as $context => $boxes ) {
			if ( empty( $boxes ) ) {
				continue;
			}

			$order[ $context ] = implode(
				',',
				array_filter(
					array_map( 'trim', explode( ',', $boxes ) ),
					static function ( $box_id ) use ( $hidden_boxes ) {
						return ! in_array( $box_id, $hidden_boxes, true );
					}
				)
			);
		}

		return $order;
	}

	/**
	 * Enqueue the Gutenberg panel assets.
	 */
	public function enqueue_editor_assets() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'wz_knowledgebase' !== $screen->post_type ) {
			return;
		}

		$asset_url = plugin_dir_url( __FILE__ );
		$version   = defined( 'WZKB_VERSION' ) ? WZKB_VERSION : '1.0.0';
		$minimize  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script(
			'wzkb-editor-sections-panel',
			$asset_url . 'js/editor-sections-panel' . $minimize . '.js',
			array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-data', 'wp-components', 'wp-api-fetch' ),
			$version,
			true
		);

		$product_map = $this->get_product_map();
		$strings     = $this->get_ui_strings();

		wp_localize_script(
			'wzkb-editor-sections-panel',
			'WZKBEditorSections',
			array(
				'endpoint' => esc_url_raw( rest_url( 'wzkb/v1/sections' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'products' => $product_map,
				'strings'  => $strings,
			)
		);

		wp_enqueue_style(
			'wzkb-editor-sections-panel',
			$asset_url . 'css/editor-sections-panel' . $minimize . '.css',
			array( 'wp-components' ),
			$version
		);
	}

	/**
	 * Sync taxonomy assignments after a REST save.
	 *
	 * @param \WP_Post         $post     Post object.
	 * @param \WP_REST_Request $request  Request.
	 * @param bool             $creating Whether this is a create operation.
	 */
	public function sync_sections_after_rest_save( \WP_Post $post, \WP_REST_Request $request, $creating ) {
		unset( $creating );

		if ( 'wz_knowledgebase' !== $post->post_type ) {
			return;
		}

		if ( isset( $request['meta']['_wzkb_section_ids'] ) && is_array( $request['meta']['_wzkb_section_ids'] ) ) {
			$this->assign_section_terms( $post->ID, $request['meta']['_wzkb_section_ids'] );
		}

		$this->sync_products_from_sections( $post->ID );
		$this->sync_product_meta( $post->ID );
		$this->sync_section_product_meta( $post->ID );
	}

	/**
	 * Ensure taxonomy stays in sync when posts are saved outside REST.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function maybe_sync_sections_during_save( $post_id, $post ) {
		if ( 'wz_knowledgebase' !== $post->post_type || wp_is_post_revision( $post ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( true === $this->is_syncing ) {
			return;
		}

		$this->is_syncing = true;

		// Quick Edit saves taxonomy terms directly without updating post meta.
		// Read back what WordPress just saved so meta stays in sync, then let
		// sync_products_from_sections derive product assignments from sections.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- action detection only, no data used.
		$is_quick_edit = isset( $_POST['action'] ) && 'inline-save' === $_POST['action']; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( $is_quick_edit ) {
			$current_terms = get_the_terms( $post_id, 'wzkb_category' );
			$section_ids   = ( ! empty( $current_terms ) && ! is_wp_error( $current_terms ) )
				? array_map( 'absint', wp_list_pluck( $current_terms, 'term_id' ) )
				: array();
			update_post_meta( $post_id, '_wzkb_section_ids', $section_ids );
		} else {
			$section_ids = get_post_meta( $post_id, '_wzkb_section_ids', true );

			if ( empty( $section_ids ) ) {
				$current_terms = get_the_terms( $post_id, 'wzkb_category' );
				if ( ! empty( $current_terms ) && ! is_wp_error( $current_terms ) ) {
					$section_ids = array_map( 'absint', wp_list_pluck( $current_terms, 'term_id' ) );
					update_post_meta( $post_id, '_wzkb_section_ids', $section_ids );
				}
			}
		}

		try {
			$this->assign_section_terms( $post_id, $section_ids );
			$this->sync_products_from_sections( $post_id );
			$this->sync_product_meta( $post_id );
			$this->sync_section_product_meta( $post_id );
		} finally {
			$this->is_syncing = false;
		}
	}

	/**
	 * Capture classic editor submissions and persist meta.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function maybe_capture_classic_submission( $post_id, $post ) {
		if ( 'wz_knowledgebase' !== $post->post_type || $this->is_block_editor_enabled() ) {
			return;
		}

		if ( ! isset( $_POST['wzkb_classic_sections_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wzkb_classic_sections_nonce'] ) ), 'wzkb_classic_sections' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$product_ids = $this->get_sanitized_ids_from_input( '_wzkb_product_ids' );
		$section_ids = $this->get_sanitized_ids_from_input( '_wzkb_section_ids' );

		update_post_meta( $post_id, '_wzkb_product_ids', $product_ids );
		update_post_meta( $post_id, '_wzkb_section_ids', $section_ids );

		wp_set_post_terms( $post_id, $product_ids, 'wzkb_product', false );
	}

	/**
	 * Assign taxonomy terms based on meta values.
	 *
	 * @param int   $post_id     Post ID.
	 * @param mixed $section_ids Raw section IDs.
	 */
	private function assign_section_terms( $post_id, $section_ids ) {
		if ( empty( $section_ids ) ) {
			wp_set_post_terms( $post_id, array(), 'wzkb_category', false );
			return;
		}

		if ( ! is_array( $section_ids ) ) {
			$section_ids = array( $section_ids );
		}

		$section_ids = array_values(
			array_filter(
				array_map( 'absint', $section_ids ),
				static function ( $maybe_id ) {
					return $maybe_id > 0;
				}
			)
		);

		wp_set_post_terms( $post_id, $section_ids, 'wzkb_category', false );
	}

	/**
	 * Keep `_wzkb_product_ids` meta aligned with taxonomy selections.
	 *
	 * @param int $post_id Post ID.
	 */
	private function sync_product_meta( $post_id ) {
		$product_terms = get_the_terms( $post_id, 'wzkb_product' );
		if ( empty( $product_terms ) || is_wp_error( $product_terms ) ) {
			delete_post_meta( $post_id, '_wzkb_product_ids' );
			return;
		}

		$product_ids = array_map( 'absint', wp_list_pluck( $product_terms, 'term_id' ) );
		update_post_meta( $post_id, '_wzkb_product_ids', array_values( $product_ids ) );
	}

	/**
	 * Merge product assignments derived from assigned sections.
	 *
	 * Each section can carry a `product_id` term meta. Any product referenced
	 * this way is added to the post's wzkb_product taxonomy so the article is
	 * always discoverable from its product. Existing product assignments are
	 * preserved — this only ever adds, never removes.
	 *
	 * @param int $post_id Post ID.
	 */
	private function sync_products_from_sections( int $post_id ): void {
		$section_terms = get_the_terms( $post_id, 'wzkb_category' );
		if ( empty( $section_terms ) || is_wp_error( $section_terms ) ) {
			return;
		}

		$derived_ids = array();
		foreach ( $section_terms as $section ) {
			$product_id = (int) get_term_meta( $section->term_id, 'product_id', true );
			if ( $product_id > 0 ) {
				$derived_ids[] = $product_id;
			}
		}

		if ( empty( $derived_ids ) ) {
			return;
		}

		$current_terms = get_the_terms( $post_id, 'wzkb_product' );
		$current_ids   = ( ! empty( $current_terms ) && ! is_wp_error( $current_terms ) )
			? array_map( 'absint', wp_list_pluck( $current_terms, 'term_id' ) )
			: array();

		$to_add = array_diff( $derived_ids, $current_ids );
		if ( empty( $to_add ) ) {
			return;
		}

		wp_set_post_terms( $post_id, array_values( array_unique( array_merge( $current_ids, $derived_ids ) ) ), 'wzkb_product', false );
	}

	/**
	 * Backfill product_id term meta on sections that have none.
	 *
	 * When exactly one product is assigned to the article, any section that
	 * lacks a product_id term meta is updated to match. This ensures sections
	 * linked via the article editor are discoverable by the frontend, which
	 * queries sections by product_id term meta.
	 *
	 * Skips sections that already carry a product_id so that explicit
	 * assignments made via the Sections admin are never overwritten.
	 *
	 * @param int $post_id Post ID.
	 */
	private function sync_section_product_meta( int $post_id ): void {
		$product_terms = get_the_terms( $post_id, 'wzkb_product' );
		if ( empty( $product_terms ) || is_wp_error( $product_terms ) || 1 !== count( $product_terms ) ) {
			return;
		}

		$section_terms = get_the_terms( $post_id, 'wzkb_category' );
		if ( empty( $section_terms ) || is_wp_error( $section_terms ) ) {
			return;
		}

		$product_id = (int) $product_terms[0]->term_id;

		foreach ( $section_terms as $section ) {
			if ( 0 === (int) get_term_meta( $section->term_id, 'product_id', true ) ) {
				update_term_meta( $section->term_id, 'product_id', $product_id );
			}
		}
	}

	/**
	 * Retrieve sanitized IDs from POST input.
	 *
	 * @param string $key Field key.
	 * @return array
	 */
	private function get_sanitized_ids_from_input( string $key ): array {
		$raw_value = filter_input( INPUT_POST, $key, FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY );

		if ( null === $raw_value ) {
			$string_value = filter_input( INPUT_POST, $key, FILTER_UNSAFE_RAW );

			if ( null === $string_value || '' === $string_value ) {
				return array();
			}

			$raw_value = explode( ',', $string_value );
		}

		$raw_value = (array) wp_unslash( $raw_value );
		$raw_value = array_filter(
			array_map(
				static function ( $item ) {
					return sanitize_text_field( $item );
				},
				$raw_value
			),
			static function ( $item ) {
				return '' !== $item;
			}
		);

		return $this->sanitize_id_array( $raw_value );
	}

	/**
	 * Determine if the block editor is active for Knowledge Base posts.
	 *
	 * @return bool
	 */
	private function is_block_editor_enabled(): bool {
		return (bool) use_block_editor_for_post_type( 'wz_knowledgebase' );
	}

	/**
	 * Get the current editing post ID.
	 *
	 * @return int
	 */
	private function get_current_post_id(): int {
		global $post;

		if ( $post instanceof \WP_Post && 'wz_knowledgebase' === $post->post_type ) {
			return (int) $post->ID;
		}

		$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		if ( $post_id ) {
			return absint( $post_id );
		}

		$post_id = filter_input( INPUT_POST, 'post_ID', FILTER_SANITIZE_NUMBER_INT );
		return $post_id ? absint( $post_id ) : 0;
	}

	/**
	 * Retrieve a cached map of product IDs to names.
	 *
	 * @return array
	 */
	private function get_product_map(): array {
		if ( null !== $this->product_map ) {
			return $this->product_map;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'wzkb_product',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		$this->product_map = array();

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$this->product_map[ (int) $term->term_id ] = $term->name;
			}
		}

		return $this->product_map;
	}

	/**
	 * Shared UI strings for selectors.
	 *
	 * @return array
	 */
	private function get_ui_strings(): array {
		return array(
			'panelTitle'        => esc_html__( 'Knowledge Base Sections', 'knowledgebase' ),
			'selectProducts'    => esc_html__( 'Select a product to load its sections.', 'knowledgebase' ),
			'noSections'        => esc_html__( 'No sections match the selected products.', 'knowledgebase' ),
			'loading'           => esc_html__( 'Loading sections…', 'knowledgebase' ),
			'unassigned'        => esc_html__( 'Sections without a product', 'knowledgebase' ),
			/* translators: %s: Product name. */
			'productHeading'    => esc_html__( '%s sections', 'knowledgebase' ),
			'searchPlaceholder' => esc_html__( 'Search products…', 'knowledgebase' ),
			'noProductMatches'  => esc_html__( 'No products match your search.', 'knowledgebase' ),
			/* translators: 1: shown count. 2: total count. */
			'productOverflow'   => esc_html__( 'Showing first %1$s products out of %2$s. Refine your search.', 'knowledgebase' ),
		);
	}

	/**
	 * Inject Walker_Section_Checklist for the wzkb_category checklist (Quick Edit and post editor).
	 *
	 * @param array $args    wp_terms_checklist() arguments.
	 * @param int   $post_id Post ID (unused).
	 * @return array
	 */
	public function use_hierarchical_section_walker( array $args, $post_id ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( ! isset( $args['taxonomy'] ) || 'wzkb_category' !== $args['taxonomy'] ) {
			return $args;
		}

		$args['walker'] = new Walker_Section_Checklist();

		return $args;
	}
}
