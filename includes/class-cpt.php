<?php
/**
 * Knowledge Base Custom Post Type.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle the Custom Post Type and Taxonomies.
 *
 * @since 2.3.0
 */
class CPT {

	/**
	 * Constructor.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'init', array( $this, 'register_post_type' ) );
		Hook_Registry::add_action( 'init', array( $this, 'register_taxonomies' ) );
	}

	/**
	 * Register Knowledge Base Post Type.
	 *
	 * @since 2.3.0
	 */
	public static function register_post_type() {
		$slug     = \wzkb_get_option( 'kb_slug', 'knowledgebase' );
		$archives = defined( 'WZKB_DISABLE_ARCHIVE' ) && WZKB_DISABLE_ARCHIVE ? false : $slug;
		$rewrite  = defined( 'WZKB_DISABLE_REWRITE' ) && WZKB_DISABLE_REWRITE ? false : array(
			'slug'       => $slug,
			'with_front' => false,
			'feeds'      => \wzkb_get_option( 'disable_kb_feed' ) ? false : true,
		);

		$ptlabels = array(
			'name'                  => _x( 'Knowledge Base', 'Post Type General Name', 'knowledgebase' ),
			'singular_name'         => _x( 'Knowledge Base', 'Post Type Singular Name', 'knowledgebase' ),
			'menu_name'             => __( 'Knowledge Base', 'knowledgebase' ),
			'name_admin_bar'        => __( 'KB Article', 'knowledgebase' ),
			'parent_item_colon'     => __( 'Parent Article:', 'knowledgebase' ),
			'all_items'             => __( 'All Articles', 'knowledgebase' ),
			'add_new_item'          => __( 'Add New Article', 'knowledgebase' ),
			'add_new'               => __( 'Add New Article', 'knowledgebase' ),
			'new_item'              => __( 'New Article', 'knowledgebase' ),
			'edit_item'             => __( 'Edit Article', 'knowledgebase' ),
			'update_item'           => __( 'Update Article', 'knowledgebase' ),
			'view_item'             => __( 'View Article', 'knowledgebase' ),
			'search_items'          => __( 'Search Articles', 'knowledgebase' ),
			'not_found'             => __( 'Not found', 'knowledgebase' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'knowledgebase' ),
			'featured_image'        => __( 'Article Featured Image', 'knowledgebase' ),
			'set_featured_image'    => __( 'Set Article featured image', 'knowledgebase' ),
			'remove_featured_image' => __( 'Remove Article featured image', 'knowledgebase' ),
			'use_featured_image'    => __( 'Use as Article featured image', 'knowledgebase' ),
			'insert_into_item'      => __( 'Insert into Article', 'knowledgebase' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Article', 'knowledgebase' ),
			'items_list'            => __( 'Articles list', 'knowledgebase' ),
			'items_list_navigation' => __( 'Articles list navigation', 'knowledgebase' ),
			'filter_items_list'     => __( 'Filter Articles list', 'knowledgebase' ),
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
			'label'         => __( 'wz_knowledgebase', 'knowledgebase' ),
			'description'   => __( 'Knowledge Base', 'knowledgebase' ),
			'labels'        => $ptlabels,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author', 'custom-fields', 'comments' ),
			'show_in_rest'  => true,
			'taxonomies'    => array( 'wzkb_category', 'wzkb_tag' ),
			'public'        => true,
			'hierarchical'  => false,
			'menu_position' => 5,
			'menu_icon'     => 'dashicons-book-alt',
			'map_meta_cap'  => true,
			'has_archive'   => $archives,
			'rewrite'       => $rewrite,
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

	/**
	 * Get base arguments for taxonomies.
	 *
	 * @since 2.5.0
	 *
	 * @param string $slug     Taxonomy slug.
	 * @param bool   $is_hierarchical Whether the taxonomy is hierarchical.
	 *
	 * @return array Base arguments for the taxonomy.
	 */
	private static function get_taxonomy_base_args( string $slug, bool $is_hierarchical = true ): array {
		return array(
			'hierarchical'      => $is_hierarchical,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'show_tagcloud'     => ! $is_hierarchical,
			'rewrite'           => array(
				'slug'         => $slug,
				'with_front'   => true,
				'hierarchical' => $is_hierarchical,
			),
		);
	}

	/**
	 * Get full taxonomy labels.
	 *
	 * @since 2.5.0
	 *
	 * @param string $singular Singular name.
	 * @param string $plural   Plural name.
	 *
	 * @return array Taxonomy labels.
	 */
	private static function get_taxonomy_labels( string $singular, string $plural ): array {
		$labels = array(
			/* translators: 1: Plural taxonomy name. */
			'name'                       => sprintf( _x( 'Knowledge Base %1$s', 'Taxonomy General Name', 'knowledgebase' ), $plural ),
			/* translators: 1: Singular taxonomy name. */
			'singular_name'              => sprintf( _x( 'Knowledge Base %1$s', 'Taxonomy Singular Name', 'knowledgebase' ), $singular ),
			/* translators: 1: Plural taxonomy name. */
			'menu_name'                  => $plural,
			/* translators: 1: Plural taxonomy name. */
			'all_items'                  => sprintf( __( 'All %1$s', 'knowledgebase' ), $plural ),
			/* translators: 1: Singular taxonomy name. */
			'parent_item'                => sprintf( __( 'Parent %1$s', 'knowledgebase' ), $singular ),
			/* translators: 1: Singular taxonomy name. */
			'parent_item_colon'          => sprintf( __( 'Parent %1$s:', 'knowledgebase' ), $singular ),
			/* translators: 1: Singular taxonomy name. */
			'new_item_name'              => sprintf( __( 'New %1$s Name', 'knowledgebase' ), $singular ),
			/* translators: 1: Singular taxonomy name. */
			'add_new_item'               => sprintf( __( 'Add New %1$s', 'knowledgebase' ), $singular ),
			/* translators: 1: Singular taxonomy name. */
			'edit_item'                  => sprintf( __( 'Edit %1$s', 'knowledgebase' ), $singular ),
			/* translators: 1: Singular taxonomy name. */
			'update_item'                => sprintf( __( 'Update %1$s', 'knowledgebase' ), $singular ),
			/* translators: 1: Singular taxonomy name. */
			'view_item'                  => sprintf( __( 'View %1$s', 'knowledgebase' ), $singular ),
			/* translators: 1: Plural taxonomy name. */
			'separate_items_with_commas' => sprintf( __( 'Separate %1$s with commas', 'knowledgebase' ), $plural ),
			/* translators: 1: Plural taxonomy name. */
			'add_or_remove_items'        => sprintf( __( 'Add or remove %1$s', 'knowledgebase' ), $plural ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'knowledgebase' ),
			/* translators: 1: Plural taxonomy name. */
			'popular_items'              => sprintf( __( 'Popular %1$s', 'knowledgebase' ), $plural ),
			/* translators: 1: Plural taxonomy name. */
			'search_items'               => sprintf( __( 'Search %1$s', 'knowledgebase' ), $plural ),
			'not_found'                  => __( 'Not Found', 'knowledgebase' ),
			/* translators: 1: Plural taxonomy name. */
			'no_terms'                   => sprintf( __( 'No %1$s found', 'knowledgebase' ), $plural ),
			/* translators: 1: Plural taxonomy name. */
			'items_list'                 => sprintf( __( '%1$s list', 'knowledgebase' ), $plural ),
			/* translators: 1: Plural taxonomy name. */
			'items_list_navigation'      => sprintf( __( '%1$s list navigation', 'knowledgebase' ), $plural ),
			/* translators: 1: Plural taxonomy name. */
			'back_to_items'              => sprintf( __( 'Back to %1$s', 'knowledgebase' ), $plural ),
		);

		return $labels;
	}

	/**
	 * Register Knowledgebase Custom Taxonomies.
	 *
	 * @since 2.3.0
	 */
	public static function register_taxonomies() {
		// Get taxonomy slugs from options.
		$catslug     = \wzkb_get_option( 'category_slug', 'kb/section' );
		$tagslug     = \wzkb_get_option( 'tag_slug', 'kb/tags' );
		$productslug = \wzkb_get_option( 'product_slug', 'kb/products' );

		// Register products taxonomy first.
		$product_args           = self::get_taxonomy_base_args( $productslug, false );
		$product_args['labels'] = self::get_taxonomy_labels( 'Product', 'Products' );

		/**
		 * Filter the arguments of the products taxonomy.
		 *
		 * @since 3.0.0
		 *
		 * @param array $product_args Product arguments
		 */
		$product_args = apply_filters( 'wzkb_product_args', $product_args );

		register_taxonomy( 'wzkb_product', array( 'wz_knowledgebase' ), $product_args );

		// Register categories (sections) taxonomy.
		$cat_args           = self::get_taxonomy_base_args( $catslug, true );
		$cat_args['labels'] = self::get_taxonomy_labels( 'Section', 'Sections' );

		/**
		 * Filter the arguments of the custom categories.
		 *
		 * @since 1.2.0
		 *
		 * @param array $cat_args Category arguments
		 */
		$cat_args = apply_filters( 'wzkb_cat_args', $cat_args );

		register_taxonomy( 'wzkb_category', array( 'wz_knowledgebase' ), $cat_args );

		// Register tags taxonomy.
		$tag_args           = self::get_taxonomy_base_args( $tagslug, false );
		$tag_args['labels'] = self::get_taxonomy_labels( 'Tag', 'Tags' );

		/**
		 * Filter the arguments of the custom tags.
		 *
		 * @since 1.2.0
		 *
		 * @param array $tag_args Tag arguments
		 */
		$tag_args = apply_filters( 'wzkb_tag_args', $tag_args );

		register_taxonomy( 'wzkb_tag', array( 'wz_knowledgebase' ), $tag_args );
	}
}
