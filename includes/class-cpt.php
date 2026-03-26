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
		// Sanitize KB slug (remove placeholders, preserve slashes).
		$slug              = self::sanitize_slug( \wzkb_get_option( 'kb_slug', 'knowledgebase' ) );
		$article_structure = \wzkb_get_option( 'article_permalink', '' );

		// If article permalink is set to just %postname%, don't use a slug prefix.
		// This allows KB articles to use the same permalink structure as regular posts.
		$use_slug_prefix = ! ( '%postname%' === $article_structure );

		$archives = defined( 'WZKB_DISABLE_ARCHIVE' ) && WZKB_DISABLE_ARCHIVE ? false : $slug;

		$rewrite = defined( 'WZKB_DISABLE_REWRITE' ) && WZKB_DISABLE_REWRITE ? false : array(
			'slug'       => $use_slug_prefix ? $slug : 'wz_knowledgebase',
			'with_front' => false,
			'feeds'      => \wzkb_get_option( 'disable_kb_feed' ) ? false : true,
		);

		// If not using slug prefix, we'll add custom rewrite rules and filter permalinks.
		if ( ! $use_slug_prefix ) {
			add_action( 'init', array( __CLASS__, 'add_root_level_rewrite_rules' ), 99 );
			add_filter( 'post_type_link', array( __CLASS__, 'filter_root_level_permalink' ), 10, 2 );
		}

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
			'name'          => 'wz_knowledgebase',
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
			'query_var'     => true,
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
	 * Sanitize slug while preserving slashes and optionally removing placeholders.
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug                The slug to sanitize.
	 * @param bool   $remove_placeholders Whether to remove placeholders. Default true.
	 * @return string Sanitized slug.
	 */
	public static function sanitize_slug( string $slug, bool $remove_placeholders = true ): string {
		// Remove any placeholders (e.g., %product_name%, %section_name%) if requested.
		if ( $remove_placeholders ) {
			$slug = preg_replace( '/%[^%]+%/', '', $slug );
		}

		// Split by slash, sanitize each part, then rejoin.
		$parts = explode( '/', $slug );
		$parts = array_map( 'sanitize_title', $parts );
		$parts = array_filter( $parts ); // Remove empty parts.

		return implode( '/', $parts );
	}

	/**
	 * Get base arguments for taxonomies.
	 *
	 * @since 2.5.0
	 *
	 * @param string $slug     Taxonomy slug.
	 * @param bool   $is_hierarchical Whether the taxonomy is hierarchical.
	 * @param bool   $enable_rewrite Whether to enable rewrite rules. Default true.
	 *
	 * @return array Base arguments for the taxonomy.
	 */
	private static function get_taxonomy_base_args( string $slug, bool $is_hierarchical = true, bool $enable_rewrite = true ): array {
		$args = array(
			'hierarchical'      => $is_hierarchical,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'show_tagcloud'     => ! $is_hierarchical,
		);

		// Only add rewrite rules if enabled. When custom article structures are used,
		// we disable permastruct rewrite and rely on custom rewrite rules instead.
		if ( $enable_rewrite ) {
			$args['rewrite'] = array(
				'slug'         => $slug,
				'with_front'   => false,
				'hierarchical' => $is_hierarchical,
			);
		} else {
			$args['rewrite'] = false;
		}

		return $args;
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
		// Use custom sanitization that preserves slashes and removes placeholders.
		// When custom article structure is set, use the base slug without placeholders.
		$article_structure            = \wzkb_get_option( 'article_permalink', '' );
		$has_custom_article_structure = ! empty( $article_structure ) && '%postname%' !== trim( $article_structure );

		// Only disable permastruct if Pro is enabled to handle custom structures.
		// If Pro isn't enabled, keep permastruct so URLs work with standard rewrite rules.
		$is_pro_enabled      = wzkb()->is_pro_enabled;
		$disable_permastruct = $has_custom_article_structure && $is_pro_enabled;

		$catslug     = self::sanitize_slug( \wzkb_get_option( 'category_slug', 'kb/section' ) );
		$tagslug     = self::sanitize_slug( \wzkb_get_option( 'tag_slug', 'kb/tags' ) );
		$productslug = self::sanitize_slug( \wzkb_get_option( 'product_slug', 'kb/product' ) );

		// Register products taxonomy first.
		// Disable permastruct rewrite only if Pro is enabled and custom article structure is used, to avoid conflicts.
		$product_args           = self::get_taxonomy_base_args( $productslug, false, ! $disable_permastruct );
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
		// Disable permastruct rewrite only if Pro is enabled and custom article structure is used, to avoid conflicts.
		$cat_args           = self::get_taxonomy_base_args( $catslug, true, ! $disable_permastruct );
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
		// Disable permastruct rewrite only if Pro is enabled and custom article structure is used, to avoid conflicts.
		$tag_args           = self::get_taxonomy_base_args( $tagslug, false, ! $disable_permastruct );
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

		// Add taxonomy rewrite rules with 'top' priority to ensure they match before post type rules.
		// Only add if Pro is enabled and custom article structure is used (otherwise WordPress handles it via permastruct).
		if ( $disable_permastruct ) {
			add_action( 'init', array( __CLASS__, 'add_taxonomy_rewrite_rules' ), 20 );
		}
	}

	/**
	 * Filter KB article permalinks to remove the CPT slug when using %postname% structure.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $permalink The post's permalink.
	 * @param \WP_Post $post      The post object.
	 * @return string Filtered permalink.
	 */
	public static function filter_root_level_permalink( $permalink, $post ) {
		// Only process KB articles.
		if ( 'wz_knowledgebase' !== $post->post_type ) {
			return $permalink;
		}

		// Remove the 'wz_knowledgebase/' prefix from the permalink.
		$permalink = str_replace( '/wz_knowledgebase/', '/', $permalink );

		return $permalink;
	}

	/**
	 * Maybe query KB article if regular post isn't found.
	 *
	 * When using %postname% structure, WordPress will try to find a regular post first.
	 * If not found, we check if there's a KB article with that slug.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Query $query The WP_Query instance.
	 */
	public static function maybe_query_kb_article( $query ) {
		// Only on main query, not admin.
		if ( ! $query->is_main_query() || is_admin() ) {
			return;
		}

		// Get the post slug from query vars.
		$post_name = $query->get( 'postname' );
		if ( empty( $post_name ) ) {
			$post_name = $query->get( 'name' );
		}

		// If no post name, nothing to check.
		if ( empty( $post_name ) ) {
			return;
		}

		// If it's already querying a specific post type, don't interfere.
		$post_type = $query->get( 'post_type' );
		if ( ! empty( $post_type ) && 'post' !== $post_type ) {
			return;
		}

		// Check if there's a regular post with this slug first.
		$regular_post = get_posts(
			array(
				'name'           => $post_name,
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
			)
		);

		// If a regular post exists, let WordPress handle it.
		if ( ! empty( $regular_post ) ) {
			return;
		}

		// No regular post found, check if there's a KB article with this slug.
		$kb_post = get_posts(
			array(
				'name'           => $post_name,
				'post_type'      => 'wz_knowledgebase',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
			)
		);

		// If a KB article exists, modify the query to fetch it instead.
		if ( ! empty( $kb_post ) ) {
			$query->set( 'post_type', 'wz_knowledgebase' );
			$query->set( 'name', $post_name );
		}
	}

	/**
	 * Add root-level rewrite rules for KB articles when using %postname% structure.
	 *
	 * This allows KB articles to use the same permalink structure as regular posts.
	 * Note: These rules are added but WordPress will try regular posts first, then fall back to KB articles.
	 *
	 * @since 3.0.0
	 */
	public static function add_root_level_rewrite_rules() {
		// We don't actually need custom rewrite rules because WordPress's default post rules
		// will handle the URL matching. We just need to hook into the query to check for KB articles
		// when a regular post isn't found. Use parse_query which runs before WordPress sets p=0.
		add_action( 'parse_query', array( __CLASS__, 'maybe_query_kb_article' ), 1 );
	}

	/**
	 * Add taxonomy rewrite rules with top priority.
	 *
	 * This ensures taxonomy archive URLs are matched before post type attachment rules.
	 *
	 * @since 3.0.0
	 */
	public static function add_taxonomy_rewrite_rules() {
		$productslug = self::sanitize_slug( \wzkb_get_option( 'product_slug', 'kb/product' ) );
		$catslug     = self::sanitize_slug( \wzkb_get_option( 'category_slug', 'kb/section' ) );
		$tagslug     = self::sanitize_slug( \wzkb_get_option( 'tag_slug', 'kb/tags' ) );

		// Add product taxonomy rules.
		add_rewrite_rule(
			'^' . preg_quote( $productslug, '/' ) . '/([^/]+)/?$',
			'index.php?wzkb_product=$matches[1]',
			'top'
		);

		// Add section taxonomy rules (hierarchical).
		add_rewrite_rule(
			'^' . preg_quote( $catslug, '/' ) . '/(.+?)/?$',
			'index.php?wzkb_category=$matches[1]',
			'top'
		);

		// Add tag taxonomy rules.
		add_rewrite_rule(
			'^' . preg_quote( $tagslug, '/' ) . '/([^/]+)/?$',
			'index.php?wzkb_tag=$matches[1]',
			'top'
		);
	}
}
