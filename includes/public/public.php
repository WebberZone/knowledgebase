<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link  https://webberzone.com
 * @since 1.0.0
 *
 * @package    WZKB
 * @subpackage WZKB/public
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Initialises text domain for l10n.
 *
 * @since 1.0.0
 */
function wzkb_lang_init() {
	load_plugin_textdomain( 'wzkb', false, dirname( plugin_basename( WZKB_PLUGIN_FILE ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wzkb_lang_init' );


/**
 * Register Styles and scripts.
 *
 * @since 1.0.0
 */
function wpkb_enqueue_styles() {

	if ( wzkb_get_option( 'include_styles' ) ) {
		wp_register_style( 'wzkb_styles', WZKB_PLUGIN_URL . 'includes/public/css/styles.min.css', false, '1.0' );
	}

	wp_add_inline_style( 'wzkb_styles', esc_html( wzkb_get_option( 'custom_css' ) ) );

}
add_action( 'wp_enqueue_scripts', 'wpkb_enqueue_styles' );


/**
 * Replace the archive temlate for the knowledge base. Filters template_include.
 *
 * To further customize these archive views, you may create a
 * new template file for each one in your theme's folder:
 * wzkb-archive.php (Main KB archives), wzkb-category.php (Category/Section archives),
 * wzkb-search.php (Search results page) or taxonomy-wzkb_tag.php (Tag archives)
 *
 * @since 1.0.0
 *
 * @param  string $template Default Archive Template location.
 * @return string Modified Archive Template location
 */
function wzkb_archive_template( $template ) {

	$template_name = '';

	if ( is_post_type_archive( 'wz_knowledgebase' ) ) {

		if ( is_search() ) {
			$template_name = 'wzkb-search.php';
		} else {
			$template_name = 'wzkb-archive.php';
		}
	}

	if ( is_tax( 'wzkb_category' ) && ! is_search() ) {
		$template_name = 'wzkb-category.php';
	}

	if ( '' !== $template_name && '' === locate_template( array( $template_name ) ) ) {
		$template = WZKB_PLUGIN_DIR . 'includes/public/templates/' . $template_name;
	}

	return $template;
}
add_filter( 'template_include', 'wzkb_archive_template' );


/**
 * For knowledge base search results, set posts_per_page 10.
 *
 * @since 1.1.0
 *
 * @param  object $query The search query object.
 * @return object $query Updated search query object
 */
function wzkb_posts_per_search_page( $query ) {

	if ( ! is_admin() && is_search() && isset( $query->query_vars['post_type'] ) && 'wz_knowledgebase' === $query->query_vars['post_type'] ) {
		$query->query_vars['posts_per_page'] = 10;
	}

	return $query;
}
add_filter( 'pre_get_posts', 'wzkb_posts_per_search_page' );

/**
 * Update the title on WZKB archive.
 *
 * @since 1.6.0
 *
 * @param array $title Title of the page.
 * @return array Updated title
 */
function wzkb_update_title( $title ) {

	if ( is_post_type_archive( 'wz_knowledgebase' ) && ! is_search() ) {

		$title['title'] = wzkb_get_option( 'kb_title' );
	}

	return $title;
}
add_filter( 'document_title_parts', 'wzkb_update_title', 99999 );
