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
 * Section Product Meta class.
 *
 * @since 3.0.0
 */
class Section_Product_Meta {

	/**
	 * Main constructor class.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		// Add product selector to section add/edit forms.
		Hook_Registry::add_action( 'wzkb_category_add_form_fields', array( $this, 'add_product_field' ) );
		Hook_Registry::add_action( 'wzkb_category_edit_form_fields', array( $this, 'edit_product_field' ), 10, 2 );

		// Save product selection when adding/editing sections.
		Hook_Registry::add_action( 'created_wzkb_category', array( $this, 'save_product_meta' ), 10 );
		Hook_Registry::add_action( 'edited_wzkb_category', array( $this, 'save_product_meta' ), 10 );

		// Enhance parent dropdown for sections.
		Hook_Registry::add_filter( 'taxonomy_parent_dropdown_args', array( $this, 'filter_parent_dropdown_args' ), 10, 2 );
	}

	/**
	 * Filter dropdown args to use custom walker for parent selection.
	 *
	 * @since 3.0.0
	 * @param array  $args     Dropdown args.
	 * @param string $taxonomy Taxonomy name.
	 * @return array Modified args.
	 */
	public function filter_parent_dropdown_args( $args, $taxonomy ) {
		if ( 'wzkb_category' === $taxonomy ) {
			$args['walker'] = new Walker_Category_Dropdown();
		}
		return $args;
	}

	/**
	 * Add product dropdown field to add new section form.
	 *
	 * @since 3.0.0
	 */
	public function add_product_field() {
		// Only show in multi-product mode.
		if ( 0 === (int) wzkb_get_option( 'multi_product', 0 ) ) {
			return;
		}

		// Get all products.
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
		?>
		<div class="form-field term-product-wrap">
			<label for="term-product"><?php esc_html_e( 'Product', 'knowledgebase' ); ?></label>
			<select name="term_product" id="term-product">
				<option value=""><?php esc_html_e( '— Select Product —', 'knowledgebase' ); ?></option>
				<?php
				foreach ( $products as $product ) {
					printf(
						'<option value="%d">%s</option>',
						absint( $product->term_id ),
						esc_html( $product->name )
					);
				}
				?>
			</select>
			<p class="description"><?php esc_html_e( 'Select the product this section belongs to.', 'knowledgebase' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Add product dropdown field to edit section form.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Term $term The term being edited.
	 */
	public function edit_product_field( $term ) {
		// Only show in multi-product mode.
		if ( 0 === (int) wzkb_get_option( 'multi_product', 0 ) ) {
			return;
		}

		// Get all products.
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

		// Get current product assignment.
		$product_id = get_term_meta( $term->term_id, 'product_id', true );
		?>
		<tr class="form-field term-product-wrap">
			<th scope="row"><label for="term-product"><?php esc_html_e( 'Product', 'knowledgebase' ); ?></label></th>
			<td>
				<select name="term_product" id="term-product">
					<option value=""><?php esc_html_e( '— Select Product —', 'knowledgebase' ); ?></option>
					<?php
					foreach ( $products as $product ) {
						printf(
							'<option value="%d" %s>%s</option>',
							absint( $product->term_id ),
							selected( $product_id, $product->term_id, false ),
							esc_html( $product->name )
						);
					}
					?>
				</select>
				<p class="description"><?php esc_html_e( 'Select the product this section belongs to.', 'knowledgebase' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save product assignment when section is added or edited.
	 *
	 * @since 3.0.0
	 *
	 * @param int $term_id  Term ID.
	 */
	public function save_product_meta( $term_id ) {
		// Only process in multi-product mode.
		if ( 0 === (int) wzkb_get_option( 'multi_product', 0 ) ) {
			return;
		}

		// Check if product field was submitted.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['term_product'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$product_id = sanitize_text_field( wp_unslash( $_POST['term_product'] ) );

			// Update term meta if product is selected, otherwise delete it.
			if ( ! empty( $product_id ) ) {
				update_term_meta( $term_id, 'product_id', absint( $product_id ) );
			} else {
				delete_term_meta( $term_id, 'product_id' );
			}
		}
	}
}
