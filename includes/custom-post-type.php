<?php
/**
 * Knowledgebase Custom Post Type.
 *
 * @link  https://webberzone.com
 * @since 1.0.0
 *
 * @package    WZKB
 * @subpackage WZKB/CPT
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Register Knowledgebase Post Type.
 *
 * @since 1.0.0
 */
function wzkb_register_post_type() {
	global $wzkb_options;

	$slug = wzkb_get_option( 'kb_slug', 'knowledgebase' );
	$archives = defined( 'WZKB_DISABLE_ARCHIVE' ) && WZKB_DISABLE_ARCHIVE ? false : $slug;
	$rewrite  = defined( 'WZKB_DISABLE_REWRITE' ) && WZKB_DISABLE_REWRITE ? false : array( 'slug' => $slug, 'with_front' => false );

	$ptlabels = array(
		'name'               => _x( 'Knowledgebase', 'Post Type General Name', 'wzkb' ),
		'singular_name'      => _x( 'Knowledgebase', 'Post Type Singular Name', 'wzkb' ),
		'menu_name'          => __( 'Knowledgebase', 'wzkb' ),
		'name_admin_bar'     => __( 'Knowledgebase Article', 'wzkb' ),
		'parent_item_colon'  => __( 'Parent Article', 'wzkb' ),
		'all_items'          => __( 'All Articles', 'wzkb' ),
		'add_new_item'       => __( 'Add New Article', 'wzkb' ),
		'add_new'            => __( 'Add New Article', 'wzkb' ),
		'new_item'           => __( 'New Article', 'wzkb' ),
		'edit_item'          => __( 'Edit Article', 'wzkb' ),
		'update_item'        => __( 'Update Article', 'wzkb' ),
		'view_item'          => __( 'View Article', 'wzkb' ),
		'search_items'       => __( 'Search Article', 'wzkb' ),
		'not_found'          => __( 'Not found', 'wzkb' ),
		'not_found_in_trash' => __( 'Not found in Trash', 'wzkb' ),
	);

	/**
	 * Filter the labels of the post type.
	 *
	 * @since 1.2.0
	 *
	 * @param array $ptlabels Post type lables
	 */
	$ptlabels = apply_filters( 'wzkb_post_type_labels', $ptlabels );

	$ptargs = array(
		'label'              => __( 'wz_knowledgebase', 'wzkb' ),
		'description'        => __( 'Knowledgebase', 'wzkb' ),
		'labels'             => $ptlabels,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author', 'custom-fields' ),
		'taxonomies'         => array( 'wzkb_category', 'wzkb_tag' ),
		'public'             => true,
		'hierarchical'       => true,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-book-alt',
		'capability_type'    => 'knowledgebase',
		'map_meta_cap'       => true,
		'has_archive'        => $archives,
		'rewrite'            => $rewrite,
	);

	/**
	 * Filter the arguments passed to register the post type.
	 *
	 * @since 1.2.0
	 *
	 * @param array $ptargs Post type arguments
	 */
	$ptargs = apply_filters( 'wzkb_post_type_args', $ptargs );

	register_post_type( 'wz_knowledgebase', $ptargs );

}
add_action( 'init', 'wzkb_register_post_type' );


function wzkb_register_taxonomies() {
	global $wzkb_options;

	$catslug = wzkb_get_option( 'category_slug', 'section' );
	$tagslug = wzkb_get_option( 'tag_slug', 'kb-tags' );

	$args = array(
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_tagcloud'     => false,
		'rewrite'           => array( 'slug' => $catslug, 'with_front' => true, 'hierarchical' => true ),
	);

	// Now register categories for the Knowledgebase
	$catlabels = array(
		'name'              => _x( 'Knowledgebase Categories', 'Taxonomy General Name', 'wzkb' ),
		'singular_name'     => _x( 'Knowledgebase Category', 'Taxonomy Singular Name', 'wzkb' ),
		'menu_name'         => __( 'KB Category', 'wzkb' ),
	);

	/**
	 * Filter the labels of the custom categories.
	 *
	 * @since 1.2.0
	 *
	 * @param array $catlabels Category labels
	 */
	$args['labels'] = apply_filters( 'wzkb_cat_labels', $catlabels );

	register_taxonomy(
		'wzkb_category',
		array( 'wz_knowledgebase' ),
		/**
		 * Filter the arguments of the custom categories.
		 *
		 * @since 1.2.0
		 *
		 * @param array $catlabels Category labels
		 */
		apply_filters( 'wzkb_cat_args', $args )
	);

	// Now register tags for the Knowledgebase
	$taglabels = array(
		'name'          => _x( 'Knowledgebase Tags', 'Taxonomy General Name', 'wzkb' ),
		'singular_name' => _x( 'Knowledgebase Tag', 'Taxonomy Singular Name', 'wzkb' ),
		'menu_name'     => __( 'KB Tag', 'wzkb' ),
	);

	/**
	 * Filter the labels of the custom tags.
	 *
	 * @since 1.2.0
	 *
	 * @param array $taglabels Tags labels
	 */
	$args['labels'] = apply_filters( 'wzkb_tag_labels', $taglabels );

	$args['hierarchical']    = false;
	$args['show_tagcloud']   = true;
	$args['rewrite']['slug'] = $tagslug;

	register_taxonomy(
		'wzkb_tag',
		array( 'wz_knowledgebase' ),
		/**
		 * Filter the arguments of the custom tags.
		 *
		 * @since 1.2.0
		 *
		 * @param array $args Tag arguments
		 */
		apply_filters( 'wzkb_tag_args', $args )
	);

}
add_action( 'init', 'wzkb_register_taxonomies' );

