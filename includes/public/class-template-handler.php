<?php
/**
 * The template handler class.
 *
 * @package WebberZone\KnowledgeBase
 */

namespace WebberZone\KnowledgeBase\Public;

/**
 * Class Template_Handler
 *
 * Handles template-related functionalities for the Knowledge Base plugin.
 *
 * @package WebberZone\KnowledgeBase
 */
class Template_Handler {
	/**
	 * Constructor for the Template_Handler class.
	 */
	public function __construct() {
		add_filter( 'get_block_templates', array( $this, 'manage_block_templates' ), 10, 3 );

		$template_types = array(
			'archive'  => 'add_custom_archive_template',
			'index'    => 'add_custom_index_template',
			'single'   => 'add_custom_single_template',
			'taxonomy' => 'add_custom_taxonomy_template',
			'search'   => 'add_custom_search_template',
		);

		foreach ( $template_types as $type => $callback ) {
			add_filter( "{$type}_template_hierarchy", array( $this, $callback ) );
		}
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
		}

		if ( $template_name ) {
			$template_file_path = $theme->get_template_directory() . '/templates/' . $template_name . '.html';
			if ( file_exists( $template_file_path ) ) {
				$block_source = 'theme';
			} else {
				$template_file_path = __DIR__ . '/templates/' . $template_name . '.html';
			}

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

			$query_result[] = $new_block;
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
}

new Template_Handler();
