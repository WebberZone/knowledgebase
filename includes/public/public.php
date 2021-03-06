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
		wp_register_style( 'wzkb_styles', WZKB_PLUGIN_URL . 'includes/public/css/wzkb-styles.min.css', false, '1.0' );
	}

	wp_add_inline_style( 'wzkb_styles', esc_html( wzkb_get_option( 'custom_css' ) ) );

	if ( wzkb_get_option( 'show_sidebar' ) ) {
		$extra_styles = '#wzkb-sidebar-primary{width:25%;}#wzkb-content-primary{width:75%;float:left;}';
		wp_add_inline_style( 'wzkb_styles', $extra_styles );
	}

	if ( is_singular() ) {
		$id = get_the_ID();
		if ( has_block( 'knowledgebase/knowledgebase', $id ) ) {
			wp_enqueue_style( 'wzkb_styles' );
		}
	}

}
add_action( 'wp_enqueue_scripts', 'wpkb_enqueue_styles' );


/**
 * Replace the archive temlate for the knowledge base. Filters template_include.
 *
 * To further customize these archive views, you may create a
 * new template file for each one in your theme's folder:
 * archive-wz_knowledgebase.php (Main KB archives), wzkb-category.php (Category/Section archives),
 * wzkb-search.php (Search results page) or taxonomy-wzkb_tag.php (Tag archives)
 *
 * @since 1.0.0
 *
 * @param  string $template Default Archive Template location.
 * @return string Modified Archive Template location
 */
function wzkb_archive_template( $template ) {

	$template_name = '';

	if ( is_singular( 'wz_knowledgebase' ) ) {
		$template_name = 'single-wz_knowledgebase.php';
	}

	if ( is_post_type_archive( 'wz_knowledgebase' ) ) {

		if ( is_search() ) {
			$template_name = 'wzkb-search.php';
		} else {
			$template_name = 'archive-wz_knowledgebase.php';
		}
	}

	if ( is_tax( 'wzkb_category' ) && ! is_search() ) {
		$template_name = 'taxonomy-wzkb_category.php';
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


/**
 * Get the HTML for alert messages
 *
 * @since 2.7.0
 *
 * @param  array  $args Arguments array.
 * @param  string $content Content to wrap in the alert divs.
 * @return string HTML output.
 */
function wzkb_get_alert( $args = array(), $content ) {

	$defaults = array(
		'type'  => 'primary',
		'class' => 'alert',
		'text'  => '',
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	$type = 'wzkb-alert-' . $args['type'];

	$class = implode( ' ', explode( ',', $args['class'] ) );
	$class = $type . ' ' . $class;

	ob_start();
	?>

	<div class="wzkb-alert <?php echo $class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" role="alert">
	<?php
		echo $args['text']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo do_shortcode( $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
	</div>

	<?php

	$html = ob_get_clean();

	/**
	 * Filter the HTML for alert messages
	 *
	 * @since 2.7.0
	 *
	 * @param  string $html HTML for alert messages.
	 * @param  array  $args Arguments array.
	 * @param  string $content Content to wrap in the alert divs.
	 */
	return apply_filters( 'wzkb_get_alert', $html, $args, $content );
}


/**
 * Register the WZ Knowledge Base sidebars.
 *
 * @since 1.9.0
 */
function wzkb_register_sidebars() {
	/* Register the 'wzkb-primary' sidebar. */
	register_sidebar(
		array(
			'id'            => 'wzkb-primary',
			'name'          => __( 'WZ Knowledge Base Sidebar', 'knowledgebase' ),
			'description'   => __( 'Displays on WZ Knowledge Base templates displayed by the plugin', 'knowledgebase' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
	/* Repeat register_sidebar() code for additional sidebars. */
}
add_action( 'widgets_init', 'wzkb_register_sidebars' );
