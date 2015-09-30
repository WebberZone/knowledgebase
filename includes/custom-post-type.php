<?php
/**
 * Knowledgebase Custom Post Type.
 *
 * @link       	https://webberzone.com
 * @since      	1.0.0
 *
 * @package    	WZKB
 * @subpackage 	WZKB/CPT
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Register Knowledgebase Post Type.
 *
 * @since	1.0.0
 */
function wzkb_register_post_type() {

	$ptlabels = array(
		'name'                => _x( 'Knowledgebase', 'Post Type General Name', 'wzkb' ),
		'singular_name'       => _x( 'Knowledgebase', 'Post Type Singular Name', 'wzkb' ),
		'menu_name'           => __( 'Knowledgebase', 'wzkb' ),
		'name_admin_bar'      => __( 'Knowledgebase Article', 'wzkb' ),
		'parent_item_colon'   => __( 'Parent Article', 'wzkb' ),
		'all_items'           => __( 'All Articles', 'wzkb' ),
		'add_new_item'        => __( 'Add New Article', 'wzkb' ),
		'add_new'             => __( 'Add New Article', 'wzkb' ),
		'new_item'            => __( 'New Article', 'wzkb' ),
		'edit_item'           => __( 'Edit Article', 'wzkb' ),
		'update_item'         => __( 'Update Article', 'wzkb' ),
		'view_item'           => __( 'View Article', 'wzkb' ),
		'search_items'        => __( 'Search Article', 'wzkb' ),
		'not_found'           => __( 'Not found', 'wzkb' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'wzkb' ),
	);
	$ptargs = array(
		'label'               => __( 'wz_knowledgebase', 'wzkb' ),
		'description'         => __( 'Knowledgebase', 'wzkb' ),
		'labels'              => $ptlabels,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions', 'custom-fields' ),
		'taxonomies'          => array( 'wzkb_category', 'wzkb_tag' ),
		'public'              => true,
		'hierarchical'        => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-book-alt',
		'has_archive'         => 'kb-articles',
	    'rewrite' 			  => array( 'slug' => 'knowledgebase', 'with_front' => false ),
	);
	register_post_type( 'wz_knowledgebase', $ptargs );

}
add_action( 'init', 'wzkb_register_post_type' );


function wzkb_register_taxonomies() {

	$args = array(
		'hierarchical'               => true,
		'show_admin_column'          => true,
		'show_tagcloud'              => false,
	    'rewrite' 			         => array( 'slug' => 'kb-articles', 'with_front' => true, 'hierarchical' => true ),
	);

	// Now register categories for the Knowledgebase
	$catlabels = array(
		'name'                       => _x( 'Knowledgebase Categories', 'Taxonomy General Name', 'wzkb' ),
		'singular_name'              => _x( 'Knowledgebase Category', 'Taxonomy Singular Name', 'wzkb' ),
		'menu_name'                  => __( 'KB Category', 'wzkb' ),
	);
	$args['labels'] = $catlabels;

	register_taxonomy( 'wzkb_category', array( 'wz_knowledgebase' ), $args );

	// Now register tags for the Knowledgebase
	$taglabels = array(
		'name'                       => _x( 'Knowledgebase Tags', 'Taxonomy General Name', 'wzkb' ),
		'singular_name'              => _x( 'Knowledgebase Tag', 'Taxonomy Singular Name', 'wzkb' ),
		'menu_name'                  => __( 'KB Tag', 'wzkb' ),
	);
	$args['labels'] = $taglabels;

	$args['hierarchical'] 		= false;
	$args['show_tagcloud'] 		= true;
	$args['rewrite']['slug']	= 'kb-tags';

	register_taxonomy( 'wzkb_tag', array( 'wz_knowledgebase' ), $args );

}
add_action( 'init', 'wzkb_register_taxonomies' );

