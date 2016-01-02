<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://webberzone.com
 * @since      1.0.0
 *
 * @package    WZKB
 * @subpackage Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since	1.2.0
 *
 * @global	$wzkb_settings_page
 * @return	void
 */
function wzkb_add_admin_pages_links() {
	global $wzkb_settings_page;

	$wzkb_settings_page = add_submenu_page( 'edit.php?post_type=wz_knowledgebase', __( 'Settings', 'knowledgebase' ), __( 'Settings', 'knowledgebase' ), 'edit_posts', 'wzkb-settings', 'wzkb_options_page' );

}
add_action( 'admin_menu', 'wzkb_add_admin_pages_links' );


/**
 * Customise the taxonomy columns.
 *
 * @since	1.0.0
 *
 * @param	array	Admin Columns
 * @return	array	Updated Admin Columns Array
 */
function wzkb_tax_columns( $new_columns ) {
	$new_columns = array(
		'cb' => '<input type="checkbox" />',
		'name'   => __( 'Name' ),
		'slug'   => __( 'Slug' ),
		'posts'  => __( 'Posts' ),
		'tax_id' => 'ID',
	);

	return $new_columns;
}
add_filter( 'manage_edit-wzkb_category_columns', 'wzkb_tax_columns' );
add_filter( 'manage_edit-wzkb_category_sortable_columns', 'wzkb_tax_columns' );
add_filter( 'manage_edit-wzkb_tag_columns', 'wzkb_tax_columns' );
add_filter( 'manage_edit-wzkb_tag_sortable_columns', 'wzkb_tax_columns' );


/**
 * Add taxonomy ID to the admin column.
 *
 * @since	1.0.0
 *
 * @param	string     $value  Deprecated
 * @param	string     $name   Name of the column
 * @param	int|string $id     Category ID
 * @return void
 */
function wzkb_tax_id( $value, $name, $id ) {
	return 'tax_id' === $name ? $id : $value;
}
add_filter( 'manage_wzkb_category_custom_column', 'wzkb_tax_id', 10, 3 );
add_filter( 'manage_wzkb_tag_custom_column', 'wzkb_tax_id', 10, 3 );

