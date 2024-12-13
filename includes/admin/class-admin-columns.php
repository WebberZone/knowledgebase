<?php
/**
 * Admin columns class
 *
 * @since 2.3.0
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to register the Better Search Admin Area.
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
		add_filter( 'manage_edit-wzkb_category_columns', array( $this, 'tax_columns' ) );
		add_filter( 'manage_edit-wzkb_category_sortable_columns', array( $this, 'tax_columns' ) );
		add_filter( 'manage_edit-wzkb_tag_columns', array( $this, 'tax_columns' ) );
		add_filter( 'manage_edit-wzkb_tag_sortable_columns', array( $this, 'tax_columns' ) );

		add_filter( 'manage_wzkb_category_custom_column', array( $this, 'tax_id' ), 10, 3 );
		add_filter( 'manage_wzkb_tag_custom_column', array( $this, 'tax_id' ), 10, 3 );
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

		return array_merge( $columns, $new_columns );
	}

	/**
	 * Add taxonomy ID to the admin column.
	 *
	 * @since 2.3.0
	 *
	 * @param  string     $value Deprecated.
	 * @param  string     $name  Name of the column.
	 * @param  int|string $id    Category ID.
	 * @return int|string
	 */
	public static function tax_id( $value, $name, $id ) {
		return 'tax_id' === $name ? $id : $value;
	}
}
