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
		'name_admin_bar'      => __( 'Knowledgebase', 'wzkb' ),
		'parent_item_colon'   => __( 'Parent Item:', 'wzkb' ),
		'all_items'           => __( 'All Articles', 'wzkb' ),
		'add_new_item'        => __( 'Add New Article', 'wzkb' ),
		'add_new'             => __( 'Add New', 'wzkb' ),
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
		'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions', 'custom-fields', ),
		'taxonomies'          => array( 'wzkb_category', 'wzkb_tag' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-book-alt',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
	    'rewrite' 			  => array( 'slug' =>'wzkb' ),
	);
	register_post_type( 'wz_knowledgebase', $ptargs );

	// Now register categories for the Knowledgebase
	$catlabels = array(
		'name'                       => _x( 'KB Categories', 'Taxonomy General Name', 'wzkb' ),
		'singular_name'              => _x( 'KB Category', 'Taxonomy Singular Name', 'wzkb' ),
		'menu_name'                  => __( 'KB Category', 'wzkb' ),
		'all_items'                  => __( 'All Categories', 'wzkb' ),
		'parent_item'                => __( 'Parent Category', 'wzkb' ),
		'parent_item_colon'          => __( 'Parent Category:', 'wzkb' ),
		'new_item_name'              => __( 'New Category Name', 'wzkb' ),
		'add_new_item'               => __( 'Add New Category', 'wzkb' ),
		'edit_item'                  => __( 'Edit Category', 'wzkb' ),
		'update_item'                => __( 'Update Category', 'wzkb' ),
		'view_item'                  => __( 'View Category', 'wzkb' ),
		'separate_items_with_commas' => __( 'Separate Categories with commas', 'wzkb' ),
		'add_or_remove_items'        => __( 'Add or remove categories', 'wzkb' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wzkb' ),
		'popular_items'              => __( 'Popular Categories', 'wzkb' ),
		'search_items'               => __( 'Search Categories', 'wzkb' ),
		'not_found'                  => __( 'Not Found', 'wzkb' ),
	);
	$catargs = array(
		'labels'                     => $catlabels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => false,
	    'rewrite' 			         => array( 'slug' =>'kbcategory' ),
	);
	register_taxonomy( 'wzkb_category', array( 'wz_knowledgebase' ), $catargs );

	// Now register tags for the Knowledgebase
	$taglabels = array(
		'name'                       => _x( 'KB Tags', 'Taxonomy General Name', 'wzkb' ),
		'singular_name'              => _x( 'KB Tag', 'Taxonomy Singular Name', 'wzkb' ),
		'menu_name'                  => __( 'KB Tag', 'wzkb' ),
		'all_items'                  => __( 'All Tags', 'wzkb' ),
		'parent_item'                => __( 'Parent Tag', 'wzkb' ),
		'parent_item_colon'          => __( 'Parent Tag:', 'wzkb' ),
		'new_item_name'              => __( 'New Tag Name', 'wzkb' ),
		'add_new_item'               => __( 'Add New Tag', 'wzkb' ),
		'edit_item'                  => __( 'Edit Tag', 'wzkb' ),
		'update_item'                => __( 'Update Tag', 'wzkb' ),
		'view_item'                  => __( 'View Tag', 'wzkb' ),
		'separate_items_with_commas' => __( 'Separate Tags with commas', 'wzkb' ),
		'add_or_remove_items'        => __( 'Add or remove tags', 'wzkb' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wzkb' ),
		'popular_items'              => __( 'Popular Tags', 'wzkb' ),
		'search_items'               => __( 'Search Tags', 'wzkb' ),
		'not_found'                  => __( 'Not Found', 'wzkb' ),
	);
	$tagargs = array(
		'labels'                     => $taglabels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	    'rewrite' 			         => array( 'slug' =>'kbtag' ),
	);
	register_taxonomy( 'wzkb_tag', array( 'wz_knowledgebase' ), $tagargs );

}
add_action( 'init', 'wzkb_register_post_type' );


