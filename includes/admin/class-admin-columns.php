<?php
/**
 * Admin Columns.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Admin;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns class.
 *
 * @since 2.3.0
 */
class Admin_Columns {

	/**
	 * Main constructor class.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		Hook_Registry::add_filter( 'manage_edit-wzkb_category_columns', array( $this, 'tax_columns' ) );
		Hook_Registry::add_filter( 'manage_edit-wzkb_category_sortable_columns', array( $this, 'tax_sortable_columns' ) );
		Hook_Registry::add_filter( 'manage_edit-wzkb_tag_columns', array( $this, 'tax_columns' ) );
		Hook_Registry::add_filter( 'manage_edit-wzkb_tag_sortable_columns', array( $this, 'tax_sortable_columns' ) );

		Hook_Registry::add_filter( 'manage_wzkb_category_custom_column', array( $this, 'tax_id' ), 10, 3 );
		Hook_Registry::add_filter( 'manage_wzkb_tag_custom_column', array( $this, 'tax_id' ), 10, 3 );

		// Register Product filter for Articles admin screen.
		Hook_Registry::add_action( 'restrict_manage_posts', array( $this, 'add_product_filter_dropdown' ) );
		Hook_Registry::add_action( 'pre_get_posts', array( $this, 'filter_articles_by_product' ) );

		// Register Product filter for Sections taxonomy screen.
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_sections_filter_script' ) );
		Hook_Registry::add_filter( 'terms_clauses', array( $this, 'filter_sections_by_product' ), 10, 2 );

		// Add sorting.
		Hook_Registry::add_filter( 'terms_clauses', array( $this, 'sort_terms_by_product' ), 10, 2 );
	}

	/**
	 * Customise the taxonomy columns.
	 *
	 * @since 2.3.0
	 *
	 * @param  array $columns Columns in the admin view.
	 * @return array Updated columns.
	 */
	public static function tax_columns( $columns ) {
		// Remove the description column.
		unset( $columns['description'] );

		$new_columns = array(
			'tax_id' => 'ID',
		);

		// Only add Product column for wzkb_category taxonomy.
		$screen = get_current_screen();
		if ( isset( $screen->taxonomy ) && 'wzkb_category' === $screen->taxonomy ) {
			$new_columns['product'] = __( 'Product', 'knowledgebase' );
		}

		return array_merge( $columns, $new_columns );
	}

	/**
	 * Make the Product column sortable.
	 *
	 * @since 3.0.0
	 *
	 * @param array $columns Array of sortable columns.
	 * @return array Modified array of sortable columns.
	 */
	public function tax_sortable_columns( $columns ) {
		$columns['product'] = 'product';
		return $columns;
	}

	/**
	 * Add taxonomy ID and Product to the admin column.
	 *
	 * @since 2.3.0
	 *
	 * @param  string     $value Deprecated.
	 * @param  string     $name  Name of the column.
	 * @param  int|string $id    Category ID.
	 * @return int|string
	 */
	public static function tax_id( $value, $name, $id ) {
		if ( 'tax_id' === $name ) {
			return $id;
		}
		if ( 'product' === $name ) {
			// Get linked product for this section.
			$product_id = get_term_meta( $id, 'product_id', true );
			if ( $product_id ) {
				$product = get_term( $product_id, 'wzkb_product' );
				if ( $product && ! is_wp_error( $product ) ) {
					return sprintf(
						'<a href="%s">%s</a>',
						esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase&wzkb_product=' . $product->slug ) ),
						esc_html( $product->name )
					);
				}
			}
			return '&mdash;'; // Em dash if not linked.
		}
		return $value;
	}

	/**
	 * Sort wzkb_category terms by wzkb_product name.
	 *
	 * @since 3.0.0
	 *
	 * @param array $pieces     Array of query SQL clauses.
	 * @param array $taxonomies Array of taxonomy names.
	 * @return array Modified clauses.
	 */
	public function sort_terms_by_product( $pieces, $taxonomies ) {
		global $wpdb;

		// Only run for wzkb_category in admin.
		if ( ! is_admin() || ! in_array( 'wzkb_category', $taxonomies, true ) ) {
			return $pieces;
		}

		// Check if sorting by product.
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'product' !== $orderby ) {
			return $pieces;
		}

		// Get sort order.
		$order = isset( $_GET['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : 'ASC'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'ASC';

		// Join with termmeta to get product_id.
		$pieces['join'] .= " LEFT JOIN {$wpdb->termmeta} AS tm ON t.term_id = tm.term_id AND tm.meta_key = 'product_id'";

		// Join with terms and term_taxonomy to get wzkb_product name.
		$pieces['join'] .= " LEFT JOIN {$wpdb->terms} AS pt ON tm.meta_value = pt.term_id";
		$pieces['join'] .= " LEFT JOIN {$wpdb->term_taxonomy} AS ptt ON pt.term_id = ptt.term_id AND ptt.taxonomy = 'wzkb_product'";

		// Set the ORDER BY clause with the "ORDER BY" prefix.
		$pieces['orderby'] = "ORDER BY COALESCE(pt.name, '') $order, t.name $order";

		// Prevent WordPress from appending the order.
		$pieces['order'] = '';

		return $pieces;
	}

	/**
	 * Add product filter dropdown to Knowledgebase admin screen.
	 *
	 * @since 3.0.0
	 */
	public function add_product_filter_dropdown() {
		global $pagenow;

		// Only run on the edit.php page for wz_knowledgebase post type.
		if ( 'edit.php' !== $pagenow || ! isset( $_GET['post_type'] ) || 'wz_knowledgebase' !== $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// Get all wzkb_product terms.
		$terms = get_terms(
			array(
				'taxonomy'   => 'wzkb_product',
				'hide_empty' => false,
			)
		);

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		// Get the currently selected product filter.
		$selected = isset( $_GET['wzkb_product'] ) ? sanitize_text_field( wp_unslash( $_GET['wzkb_product'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Output the dropdown.
		?>
		<select name="wzkb_product" id="wzkb_product_filter">
			<option value=""><?php esc_html_e( 'All Products', 'knowledgebase' ); ?></option>
			<?php
			foreach ( $terms as $term ) {
				printf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $term->slug ),
					selected( $selected, $term->slug, false ),
					esc_html( $term->name )
				);
			}
			?>
		</select>
		<?php
	}

	/**
	 * Filter articles by the product in the admin.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Query $query The current WP_Query instance (passed by reference).
	 */
	public function filter_articles_by_product( $query ) {
		global $pagenow;

		// Only run in admin post list, main query, and correct post type.
		if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query() ) {
			return;
		}

		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'wz_knowledgebase' !== $post_type ) {
			return;
		}

		// Get the product filter value.
		$product = isset( $_GET['wzkb_product'] ) ? sanitize_text_field( wp_unslash( $_GET['wzkb_product'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $product ) ) {
			return;
		}

		// Ensure the taxonomy exists.
		if ( ! taxonomy_exists( 'wzkb_product' ) ) {
			return;
		}

		// Add the tax query.
		$tax_query = array(
			array(
				'taxonomy' => 'wzkb_product',
				'field'    => is_numeric( $product ) ? 'term_id' : 'slug',
				'terms'    => is_numeric( $product ) ? absint( $product ) : $product,
			),
		);

		$query->set( 'tax_query', $tax_query );
	}

	/**
	 * Enqueue script for product filter on Sections taxonomy screen.
	 *
	 * @since 3.0.0
	 */
	public function enqueue_sections_filter_script() {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-tags' !== $screen->base || 'wzkb_category' !== $screen->taxonomy ) {
			return;
		}

		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}

		$products = get_terms(
			array(
				'taxonomy'   => 'wzkb_product',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if ( empty( $products ) || is_wp_error( $products ) ) {
			return;
		}

		// Format products data for JS.
		$products_data = array();
		foreach ( $products as $product ) {
			$products_data[] = array(
				'term_id' => $product->term_id,
				'name'    => $product->name,
			);
		}

		// Get current query parameters to preserve them.
		$query_params = array();
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET ) ) {
			foreach ( $_GET as $key => $value ) {
				if ( 'wzkb_product' !== $key ) {
					// Properly sanitize GET parameters based on expected types.
					$sanitized_key = sanitize_key( $key );

					// Handle common WordPress admin parameters appropriately.
					switch ( $sanitized_key ) {
						case 'taxonomy':
						case 'post_type':
						case 'orderby':
						case 'order':
							$query_params[ $sanitized_key ] = sanitize_text_field( $value );
							break;

						case 'page':
						case 'paged':
							$query_params[ $sanitized_key ] = absint( $value );
							break;

						case 's': // Search term.
							$query_params[ $sanitized_key ] = sanitize_text_field( $value );
							break;

						default:
							// For any other parameters, apply general sanitization.
							if ( is_array( $value ) ) {
								$query_params[ $sanitized_key ] = array_map( 'sanitize_text_field', $value );
							} else {
								$query_params[ $sanitized_key ] = sanitize_text_field( $value );
							}
							break;
					}
				}
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$selected = isset( $_GET['wzkb_product'] ) ? absint( $_GET['wzkb_product'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Register and enqueue script.
		$minimize = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script(
			'wzkb-sections-product-filter',
			plugins_url( 'js/sections-product-filter' . $minimize . '.js', __FILE__ ),
			array( 'jquery' ),
			WZKB_VERSION,
			true
		);

		wp_localize_script(
			'wzkb-sections-product-filter',
			'knowledgebaseProductFilter',
			array(
				'products'        => $products_data,
				'selectedProduct' => $selected,
				'queryParams'     => $query_params,
				'strings'         => array(
					'allProducts'       => __( 'All Products', 'knowledgebase' ),
					'filter'            => __( 'Filter', 'knowledgebase' ),
					'productLabel'      => __( 'Product:', 'knowledgebase' ),
					'searchPlaceholder' => __( 'Search sections...', 'knowledgebase' ),
				),
			)
		);

		wp_enqueue_script( 'wzkb-sections-product-filter' );
	}

	/**
	 * Filter Sections by selected Product.
	 *
	 * @since 3.0.0
	 *
	 * @param array $pieces     Array of query SQL clauses.
	 * @param array $taxonomies Array of taxonomy names.
	 * @return array Modified query SQL clauses.
	 */
	public function filter_sections_by_product( $pieces, $taxonomies ) {
		global $wpdb;

		// Only run for wzkb_category taxonomy queries.
		if ( ! in_array( 'wzkb_category', $taxonomies, true ) ) {
			return $pieces;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'edit-tags' !== $screen->base || 'wzkb_category' !== $screen->taxonomy ) {
			return $pieces;
		}

		if ( ! current_user_can( 'manage_categories' ) ) {
			return $pieces;
		}

		if ( empty( $_GET['wzkb_product'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $pieces;
		}

		$product_id = absint( $_GET['wzkb_product'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $product_id ) {
			$pieces['join']  .= " INNER JOIN {$wpdb->termmeta} AS tm ON t.term_id = tm.term_id ";
			$pieces['where'] .= $wpdb->prepare( " AND tm.meta_key = 'product_id' AND tm.meta_value = %d ", $product_id );
		}

		return $pieces;
	}
}