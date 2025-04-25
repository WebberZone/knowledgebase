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
 */
class Template_Handler {
	/**
	 * Constructor for the Template_Handler class.
	 */
	public function __construct() {
		Hook_Registry::add_filter( 'template_include', array( $this, 'archive_template' ) );

		Hook_Registry::add_filter( 'get_block_templates', array( $this, 'manage_block_templates' ), 10, 3 );

		$template_types = array(
			'archive'  => 'add_custom_archive_template',
			'index'    => 'add_custom_index_template',
			'single'   => 'add_custom_single_template',
			'taxonomy' => 'add_custom_taxonomy_template',
			'search'   => 'add_custom_search_template',
		);

		foreach ( $template_types as $type => $callback ) {
			Hook_Registry::add_filter( "{$type}_template_hierarchy", array( $this, $callback ) );
		}

		Hook_Registry::add_filter( 'pre_get_posts', array( $this, 'posts_per_search_page' ) );
		Hook_Registry::add_filter( 'document_title_parts', array( $this, 'update_title' ), 99999 );
		Hook_Registry::add_action( 'widgets_init', array( $this, 'register_sidebars' ), 11 );
	}

	/**
	 * Replace the archive temlate for the knowledge base. Filters template_include.
	 *
	 * To further customize these archive views, you may create a
	 * new template file for each one in your theme's folder:
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

		if ( is_singular( 'wz_knowledgebase' ) ) {
			$template_name = 'single-wz_knowledgebase.php';
		} elseif ( is_post_type_archive( 'wz_knowledgebase' ) ) {
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
			$new_template = __DIR__ . '/templates/' . $template_name;
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
		if ( is_singular( 'wz_knowledgebase' ) ) {
			return $this->add_custom_template( $templates, 'single', 'wz_knowledgebase', 'single-wz_knowledgebase' );
		}
		if ( is_search() ) {
			return $this->add_custom_template( $templates, 'search', 'wz_knowledgebase', 'wzkb-search' );
		}
		return $this->add_custom_template( $templates, 'archive', 'wz_knowledgebase', 'archive-wz_knowledgebase' );
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
		return $this->add_custom_template( $templates, 'single', 'wz_knowledgebase', 'single-wz_knowledgebase' );
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
		return $this->add_custom_template( $templates, 'search', 'wz_knowledgebase', 'wzkb-search' );
	}

	/**
	 * Manage block templates for the wz_knowledgebase custom post type.
	 *
	 * @param array  $query_result   Array of found block templates.
	 * @param array  $query          Arguments to retrieve templates.
	 * @param string $template_type  $template_type wp_template or wp_template_part.
	 * @return array Updated array of found block templates.
	 */
	public function manage_block_templates( $query_result, $query, $template_type ) {
		if ( 'wp_template' !== $template_type ) {
			return $query_result;
		}

		global $post;
		if ( ( empty( $post ) && ! is_admin() ) || ( ! empty( $post ) && 'wz_knowledgebase' !== $post->post_type ) ) {
			return $query_result;
		}

		$theme        = wp_get_theme();
		$block_source = 'plugin';

		$template_name = null;

		if ( is_singular( 'wz_knowledgebase' ) ) {
			$template_name = 'single-wz_knowledgebase';
		} elseif ( is_post_type_archive( 'wz_knowledgebase' ) ) {
			$template_name = is_search() ? 'wzkb-search' : 'archive-wz_knowledgebase';
		} elseif ( is_tax( 'wzkb_category' ) && ! is_search() ) {
			$template_name = 'taxonomy-wzkb_category';
		} elseif ( is_tax( 'wzkb_product' ) && ! is_search() ) {
			$template_name = 'taxonomy-wzkb_product';
		}

		if ( $template_name ) {
			$template_file_path = $theme->get_template_directory() . '/templates/' . $template_name . '.html';
			if ( file_exists( $template_file_path ) ) {
				$block_source = 'theme';
			} else {
				$template_file_path = __DIR__ . '/templates/' . $template_name . '.html';
			}

			if ( file_exists( $template_file_path ) ) {
				$template_contents = self::get_template_content( $template_file_path );
				$template_contents = self::replace_placeholders_with_shortcodes( $template_contents );

				$new_block                 = new \WP_Block_Template();
				$new_block->type           = 'wp_template';
				$new_block->theme          = $theme->stylesheet;
				$new_block->slug           = $template_name;
				$new_block->id             = 'wzkb//' . $template_name;
				$new_block->title          = 'Knowledge Base Template - ' . $template_name;
				$new_block->description    = '';
				$new_block->source         = $block_source;
				$new_block->status         = 'publish';
				$new_block->has_theme_file = true;
				$new_block->is_custom      = true;
				$new_block->content        = $template_contents;
				$new_block->post_types     = array( 'wz_knowledgebase' );

				// Add taxonomy support for block template.
				if ( 'taxonomy-wzkb_category' === $template_name ) {
					$new_block->description = 'Knowledge Base Section (taxonomy) block template.';
				}
				if ( 'taxonomy-wzkb_product' === $template_name ) {
					$new_block->description = 'Knowledge Base Product (taxonomy) block template.';
				}

				$query_result[] = $new_block;
			}
		}

		return $query_result;
	}

	/**
	 * Replaces placeholders with corresponding shortcode output.
	 *
	 * @param string $template_contents The template content containing placeholders.
	 * @return string The updated template with shortcodes replaced by their output.
	 */
	public static function replace_placeholders_with_shortcodes( $template_contents ) {
		// Regular expression to match placeholders like {{shortcode param="value"}}.
		$pattern = '/\{\{([a-zA-Z_]+)(.*?)\}\}/';

		// Callback function to process each match.
		$callback = function ( $matches ) {
			$shortcode = trim( $matches[1] ); // Extract the shortcode name.
			$params    = trim( $matches[2] ); // Extract any parameters.

			// Construct the shortcode with the parameters.
			if ( ! empty( $params ) ) {
				$shortcode_output = '[' . $shortcode . ' ' . $params . ']';
			} else {
				$shortcode_output = '[' . $shortcode . ']';
			}

			// Run the shortcode and return the output.
			return do_shortcode( $shortcode_output );
		};

		// Run the preg_replace_callback to find and replace all placeholders.
		return preg_replace_callback( $pattern, $callback, $template_contents );
	}

	/**
	 * Get the content of a template file.
	 *
	 * @param string $template The template file to include.
	 * @return string The content of the template file.
	 */
	public static function get_template_content( $template ) {
		ob_start();
		include $template;
		return ob_get_clean();
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

		if ( ! is_admin() && $query->is_search() && isset( $query->query_vars['post_type'] ) && 'wz_knowledgebase' === $query->query_vars['post_type'] ) {
			$query->set( 'posts_per_page', 12 );
			$query->set( 'post_type', 'wz_knowledgebase' );
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

		if ( is_post_type_archive( 'wz_knowledgebase' ) && ! is_search() ) {
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
