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
	 * Constructor.
	 */
	public function __construct() {
		if ( 0 === (int) wzkb_get_option( 'multi_product', 0 ) ) {
			return;
		}

		Hook_Registry::add_action( 'init', array( $this, 'register_meta' ) );
		Hook_Registry::add_action( 'add_meta_boxes', array( $this, 'remove_classic_section_metabox' ), 99 );
		Hook_Registry::add_filter( 'get_user_option_meta-box-order_wz_knowledgebase', array( $this, 'filter_meta_box_order' ) );
		Hook_Registry::add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		Hook_Registry::add_action( 'rest_after_insert_wz_knowledgebase', array( $this, 'sync_sections_after_rest_save' ), 10, 3 );
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
	 * Remove classic metabox so only the custom panel remains.
	 */
	public function remove_classic_section_metabox() {
		remove_meta_box( 'wzkb_categorydiv', 'wz_knowledgebase', 'side' );
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

		foreach ( $order as $context => $boxes ) {
			if ( empty( $boxes ) ) {
				continue;
			}

			$order[ $context ] = implode(
				',',
				array_filter(
					array_map( 'trim', explode( ',', $boxes ) ),
					static function ( $box_id ) {
						return 'wzkb_categorydiv' !== $box_id;
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

		$products = get_terms(
			array(
				'taxonomy'   => 'wzkb_product',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		$product_map = array();
		if ( ! empty( $products ) && ! is_wp_error( $products ) ) {
			foreach ( $products as $product ) {
				$product_map[ (int) $product->term_id ] = $product->name;
			}
		}

		wp_localize_script(
			'wzkb-editor-sections-panel',
			'WZKBEditorSections',
			array(
				'endpoint' => esc_url_raw( rest_url( 'wzkb/v1/sections' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'products' => $product_map,
				'strings'  => array(
					'panelTitle'     => esc_html__( 'Knowledge Base Sections', 'knowledgebase' ),
					'selectProducts' => esc_html__( 'Select a product to load its sections.', 'knowledgebase' ),
					'noSections'     => esc_html__( 'No sections match the selected products.', 'knowledgebase' ),
					'loading'        => esc_html__( 'Loading sections…', 'knowledgebase' ),
					'unassigned'     => esc_html__( 'Sections without a product', 'knowledgebase' ),
					/* translators: %s: Product name. */
					'productHeading' => esc_html__( '%s sections', 'knowledgebase' ),
				),
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

		$this->sync_product_meta( $post->ID );
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

		$section_ids = get_post_meta( $post_id, '_wzkb_section_ids', true );

		if ( empty( $section_ids ) ) {
			$current_terms = get_the_terms( $post_id, 'wzkb_category' );
			if ( ! empty( $current_terms ) && ! is_wp_error( $current_terms ) ) {
				$section_ids = array_map( 'absint', wp_list_pluck( $current_terms, 'term_id' ) );
				update_post_meta( $post_id, '_wzkb_section_ids', $section_ids );
			}
		}

		try {
			$this->assign_section_terms( $post_id, $section_ids );
			$this->sync_product_meta( $post_id );
		} finally {
			$this->is_syncing = false;
		}
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
}
