<?php
/**
 * The template handler class.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

/**
 * Class Template_Handler
 *
 * Handles template-related functionalities for the Knowledge Base plugin.
 * Delegates block template management to Block_Template_Manager.
 */
class Template_Handler {
	/**
	 * Template directory path.
	 */
	const TEMPLATE_DIR = __DIR__ . '/templates';

	/**
	 * Post type for knowledge base.
	 */
	const POST_TYPE = 'wz_knowledgebase';

	/**
	 * Block template manager instance.
	 *
	 * @var Block_Template_Manager
	 */
	public Block_Template_Manager $block_template_manager;

	/**
	 * Constructor for the Template_Handler class.
	 */
	public function __construct() {
		// Initialize block template manager.
		$this->block_template_manager = new Block_Template_Manager();

		// Classic theme support.
		Hook_Registry::add_filter( 'template_include', array( $this, 'archive_template' ), 99 );

		// Query modifications and sidebar registration.
		Hook_Registry::add_filter( 'pre_get_posts', array( $this, 'posts_per_search_page' ) );
		Hook_Registry::add_filter( 'document_title_parts', array( $this, 'update_title' ), 99999 );
		Hook_Registry::add_action( 'widgets_init', array( $this, 'register_sidebars' ), 11 );
	}


	/**
	 * Replace the archive template for the knowledge base (classic themes only).
	 *
	 * Filters template_include to load custom PHP templates for classic themes.
	 * Block themes are handled by Block_Template_Manager.
	 *
	 * To customize these archive views, create a new template file in your theme's folder:
	 * archive-wz_knowledgebase.php (Main KB archives), wzkb-category.php (Category/Section archives),
	 * wzkb-search.php (Search results page) or taxonomy-wzkb_tag.php (Tag archives)
	 *
	 * @since 2.3.0
	 *
	 * @param  string $template Default Archive Template location.
	 * @return string Modified Archive Template location
	 */
	public function archive_template( $template ) {
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			return $template;
		}

		$template_name = null;

		if ( is_singular( self::POST_TYPE ) ) {
			$template_name = 'single-wz_knowledgebase.php';
		} elseif ( is_post_type_archive( self::POST_TYPE ) ) {
			$template_name = is_search() ? 'wzkb-search.php' : 'archive-wz_knowledgebase.php';
		} elseif ( is_tax( 'wzkb_category' ) && ! is_search() ) {
			$template_name = 'taxonomy-wzkb_category.php';
		} elseif ( is_tax( 'wzkb_product' ) && ! is_search() ) {
			$template_name = 'taxonomy-wzkb_product.php';
		}

		if ( $template_name ) {
			$new_template = locate_template( array( $template_name ) );
			if ( $new_template ) {
				return $new_template;
			}

			$new_template = WP_CONTENT_DIR . '/knowledgebase/templates/' . $template_name;
			if ( file_exists( $new_template ) ) {
				return $new_template;
			}
			$new_template = self::TEMPLATE_DIR . '/' . str_replace( '.html', '.php', $template_name );
			if ( file_exists( $new_template ) ) {
				return $new_template;
			}
		}

		return $template;
	}

	/**
	 * Add custom template for the wz_knowledgebase custom post type and wzkb_category taxonomy.
	 *
	 * @param array  $templates Array of found templates.
	 * @param string $type Type of template (archive, single, taxonomy).
	 * @param string $post_type Post type or taxonomy name.
	 * @param string $template_name Template name to add.
	 * @return array Updated array of found templates.
	 */
	private function add_custom_template( $templates, $type, $post_type, $template_name ) {
		if ( ( in_array( $type, array( 'archive', 'index', 'search' ), true ) ) ||
			( 'single' === $type && is_singular( $post_type ) ) ) {
			array_unshift( $templates, $template_name );
		}
		return $templates;
	}

	/**
	 * Add custom archive template for the wz_knowledgebase custom post type.
	 *
	 * @param array $templates Array of found templates.
	 * @return array Updated array of found templates.
	 */
	public function add_custom_archive_template( $templates ) {
		if ( is_tax( 'wzkb_category' ) ) {
			return $this->add_custom_template( $templates, 'archive', 'wzkb_category', 'taxonomy-wzkb_category' );
		}
		if ( is_tax( 'wzkb_product' ) ) {
			return $this->add_custom_template( $templates, 'archive', 'wzkb_product', 'taxonomy-wzkb_product' );
		}
		if ( is_singular( self::POST_TYPE ) ) {
			return $this->add_custom_template( $templates, 'single', self::POST_TYPE, 'single-wz_knowledgebase' );
		}
		if ( is_search() ) {
			return $this->add_custom_template( $templates, 'search', self::POST_TYPE, 'wzkb-search' );
		}
		return $this->add_custom_template( $templates, 'archive', self::POST_TYPE, 'archive-wz_knowledgebase' );
	}

	/**
	 * Add custom archive template for the wz_knowledgebase custom post type.
	 *
	 * @param array $templates Array of found templates.
	 * @return array Updated array of found templates.
	 */
	public function add_custom_index_template( $templates ) {
		return $this->add_custom_archive_template( $templates );
	}

	/**
	 * Add custom single template for the wz_knowledgebase custom post type.
	 *
	 * @param array $templates Array of found templates.
	 * @return array Updated array of found templates.
	 */
	public function add_custom_single_template( $templates ) {
		return $this->add_custom_template( $templates, 'single', self::POST_TYPE, 'single-wz_knowledgebase' );
	}

	/**
	 * Add custom taxonomy template for the wzkb_category taxonomy.
	 *
	 * @param array $templates Array of found templates.
	 * @return array Updated array of found templates.
	 */
	public function add_custom_taxonomy_template( $templates ) {
		return $this->add_custom_template( $templates, 'archive', 'wzkb_category', 'taxonomy-wzkb_category' );
	}

	/**
	 * Add custom search template for the wz_knowledgebase custom post type.
	 *
	 * @param array $templates Array of found templates.
	 * @return array Updated array of found templates.
	 */
	public function add_custom_search_template( $templates ) {
		return $this->add_custom_template( $templates, 'search', self::POST_TYPE, 'wzkb-search' );
	}


	/**
	 * For knowledge base search results, set posts_per_page 10.
	 *
	 * @since 2.3.0
	 *
	 * @param  \WP_Query $query The search query object.
	 * @return \WP_Query $query Updated search query object
	 */
	public function posts_per_search_page( $query ) {

		if ( ! is_admin() && $query->is_search() && isset( $query->query_vars['post_type'] ) && self::POST_TYPE === $query->query_vars['post_type'] ) {
			$query->set( 'posts_per_page', 12 );
			$query->set( 'post_type', self::POST_TYPE );
		}

		return $query;
	}


	/**
	 * Update the title on WZKB archive.
	 *
	 * @since 2.3.0
	 *
	 * @param array $title Title of the page.
	 * @return array Updated title
	 */
	public function update_title( $title ) {

		if ( is_post_type_archive( self::POST_TYPE ) && ! is_search() ) {
			$title['title'] = wzkb_get_option( 'kb_title' );
		}

		return $title;
	}

	/**
	 * Register the WZ Knowledge Base sidebars.
	 *
	 * @since 2.3.0
	 */
	public function register_sidebars() {
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
	}
}
