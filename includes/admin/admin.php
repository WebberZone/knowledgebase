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
 * Filters Admin Notices to add a notice when the settings are not saved.
 *
 * @since 1.2.0
 * @return void
 */
function wzkb_admin_notices() {

	$kbslug  = wzkb_get_option( 'kb_slug', 'not-set-random-string' );
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
		<p>
			<?php
				/* translators: 1. Link to admin page. */
				printf( __( 'Knowledge Base settings for the slug have not been registered. Please visit the <a href="%s">admin page</a> to update and save the options.', 'knowledgebase' ), esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-settings' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</p>
	</div>

		<?php
	}
}
add_action( 'admin_notices', 'wzkb_admin_notices' );


/**
 * Add number of articles to At a Glance widget
 *
 * @since 1.5.0
 *
 * @param array $items Array of items.
 * @return array Updated array of items
 */
function wzkb_dashboard_glance_items( $items ) {
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
add_filter( 'dashboard_glance_items', 'wzkb_dashboard_glance_items', 1 );


/**
 * Add CSS to Admin head
 *
 * @since 1.5.0
 *
 * return void
 */
function wzkb_admin_head() {
	?>
	<style type="text/css" media="screen">
		#dashboard_right_now .wzkb-article-count:before {
			content: "\f331";
		}
	</style>
	<?php
}
add_filter( 'admin_head', 'wzkb_admin_head' );
