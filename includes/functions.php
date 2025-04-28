<?php
/**
 * This class handles the output of the knowledge base.
 *
 * @package WebberZone\Knowledge_Base
 */

use WebberZone\Knowledge_Base\Frontend\Media_Handler;
use WebberZone\Knowledge_Base\Frontend\Related;
use WebberZone\Knowledge_Base\Util\Helpers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * The main function to generate the output.
 *
 * @since 1.0.0
 * @since 2.1.0 Added additional parameters that can be passed.
 *
 * @param string|array $args Knowledge base arguments. See Display::get_knowledge_base() for list of accepted args.
 * @return string Knowledge Base output.
 */
function wzkb_knowledge( $args = array() ) {
	return \WebberZone\Knowledge_Base\Frontend\Display::get_knowledge_base( $args );
}

/**
 * Get a hierarchical list of WZ Knowledge Base sections.
 *
 * @param  int   $term_id Term ID.
 * @param  int   $level   Level of the loop.
 * @param  array $args    Array or arguments.
 * @return string HTML output with the categories.
 */
function wzkb_categories_list( $term_id, $level = 0, $args = array() ) {
	return \WebberZone\Knowledge_Base\Frontend\Display::get_categories_list( $term_id, $level, $args );
}


/**
 * Creates the breadcrumb.
 *
 * @since 1.6.0
 *
 * @param  array $args Parameters array.
 * @return string|bool Formatted shortcode output. False if not a WZKB post type archive or post.
 */
function wzkb_get_breadcrumb( $args = array() ) {
	return \WebberZone\Knowledge_Base\Frontend\Breadcrumbs::get_breadcrumb( $args );
}

/**
 * Echo the breadcrumb output.
 *
 * @since 1.6.0
 *
 * @param  array $args Parameters array.
 */
function wzkb_breadcrumb( $args = array() ) {
	echo wzkb_get_breadcrumb( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Display custom search form for WZKB.
 *
 * @since 1.1.0
 *
 * @return string   String when retrieving, null when displaying or if searchform.php exists.
 */
function wzkb_get_search_form() {
	return \WebberZone\Knowledge_Base\Frontend\Search::get_search_form();
}

/**
 * Echo the custom search form for WZKB.
 *
 * @since 2.3.0
 */
function wzkb_search_form() {
	echo wzkb_get_search_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Get the HTML for alert messages
 *
 * @since 2.7.0
 *
 * @param  array  $args Arguments array.
 * @param  string $content Content to wrap in the alert divs.
 * @return string HTML output.
 */
function wzkb_get_alert( $args = array(), $content = '' ) {

	$defaults = array(
		'type'  => 'primary',
		'class' => 'alert',
		'text'  => '',
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );
	$args = Helpers::sanitize_args( $args );

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
 * Get the post thumbnail.
 *
 * @since 2.1.0
 *
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type WP_Post $post          Post ID or WP_Post object. Default current post.
 *     @type string  $thumb_default Default thumbnail.
 *     @type string  $class         Thumbnail class.
 *     @type string  $thumb_size    Thumbnail size.
 * }
 * @return string Image tag.
 */
function wzkb_get_the_post_thumbnail( $args = array() ) {

	$defaults = array(
		'post'          => get_post(),
		'thumb_default' => plugins_url( 'frontend/images/default-thumb.png', __FILE__ ),
		'class'         => 'wzkb-relatd-article-thumb',
		'size'          => 'thumbnail',
		'scan_images'   => true,
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	return Media_Handler::get_the_post_thumbnail( $args );
}

/**
 * Get related knowledge base articles.
 *
 * @since 2.1.0
 *
 * @param array $args Optional array of parameters. See Related::get_related_articles() for list of accepted args.
 * @return string|void Void if 'echo' argument is true, the post excerpt if 'echo' is false.
 */
function wzkb_related_articles( $args = array() ) {

	$defaults = array(
		'echo' => true,
	);

	$args = wp_parse_args( $args, $defaults );

	$related = Related::get_related_articles( $args );

	if ( $args['echo'] ) {
		echo $related; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $related;
	}
}

/**
 * Retrieves the URL to the knowledge base archive page.
 *
 * @since 2.3.0
 *
 * @return string The URL to the knowledge base archive page.
 */
function wzkb_get_kb_url() {
	return get_post_type_archive_link( 'wz_knowledgebase' );
}

/**
 * Outputs the URL to the knowledge base archive page.
 *
 * @since 2.3.0
 */
function wzkb_the_kb_url() {
	echo wzkb_get_kb_url(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
