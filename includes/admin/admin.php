<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://webberzone.com
 * @since 1.0.0
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
 * @since 1.2.0
 *
 * @global $wzkb_settings_page
 * @return void
 */
function wzkb_add_admin_pages_links() {
	global $wzkb_settings_page;

	$wzkb_settings_page = add_submenu_page( 'edit.php?post_type=wz_knowledgebase', __( 'Settings', 'knowledgebase' ), __( 'Settings', 'knowledgebase' ), 'manage_options', 'wzkb-settings', 'wzkb_options_page' );

}
add_action( 'admin_menu', 'wzkb_add_admin_pages_links' );


/**
 * Customise the taxonomy columns.
 *
 * @since  1.0.0
 * @param  array $columns Columns in the admin view.
 * @return array Updated columns.
 */
function wzkb_tax_columns( $columns ) {

	// Remove the description column.
	unset( $columns['description'] );

	$new_columns = array(
		'tax_id' => 'ID',
	);

	return array_merge( $columns, $new_columns );
}
add_filter( 'manage_edit-wzkb_category_columns', 'wzkb_tax_columns' );
add_filter( 'manage_edit-wzkb_category_sortable_columns', 'wzkb_tax_columns' );
add_filter( 'manage_edit-wzkb_tag_columns', 'wzkb_tax_columns' );
add_filter( 'manage_edit-wzkb_tag_sortable_columns', 'wzkb_tax_columns' );


/**
 * Add taxonomy ID to the admin column.
 *
 * @since 1.0.0
 *
 * @param  string     $value Deprecated.
 * @param  string     $name  Name of the column.
 * @param  int|string $id    Category ID.
 * @return int|string
 */
function wzkb_tax_id( $value, $name, $id ) {
	return 'tax_id' === $name ? $id : $value;
}
add_filter( 'manage_wzkb_category_custom_column', 'wzkb_tax_id', 10, 3 );
add_filter( 'manage_wzkb_tag_custom_column', 'wzkb_tax_id', 10, 3 );


/**
 * Add rating links to the admin dashboard
 *
 * @since 1.2.0
 *
 * @param string $footer_text The existing footer text
 * @return string Updated Footer text
 */
function wzkb_admin_footer( $footer_text ) {

	if ( get_current_screen()->post_type == 'wz_knowledgebase' ) {

		$text = sprintf( __( 'Thank you for using <a href="%1$s" target="_blank">Knowledgebase</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">WordPress.org</a>', 'knowledgebase' ),
			'https://webberzone.com/knowledgebase',
			'https://wordpress.org/support/view/plugin-reviews/knowledgebase?filter=5#postform'
		);

		return str_replace( '</span>', '', $footer_text ) . ' | ' . $text . '</span>';

	} else {

		return $footer_text;

	}
}
add_filter( 'admin_footer_text', 'wzkb_admin_footer' );


/**
 * Filters Admin Notices to add a notice when the settings are not saved.
 *
 * @since 1.2.0
 * @return void
 */
function wzkb_admin_notices() {

	$kbslug = wzkb_get_option( 'kb_slug', 'not-set-random-string' );
	$catslug = wzkb_get_option( 'category_slug', 'not-set-random-string' );
	$tagslug = wzkb_get_option( 'tag_slug', 'not-set-random-string' );

	// Only add the notice if the user is an admin.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Only add the notice if the settings cannot be found.
	if ( 'not-set-random-string' === $kbslug || 'not-set-random-string' === $catslug || 'not-set-random-string' === $tagslug ) {
	?>

	<div class="updated">
		<p><?php printf( __( 'Knowledgebase settings for the slug have not been registered. Please visit the <a href="%s">admin page</a> update options.', 'knowledgebase' ), admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-settings' ) ); ?></p>
	</div>

	<?php
	}
}
add_action( 'admin_notices', 'wzkb_admin_notices' );

