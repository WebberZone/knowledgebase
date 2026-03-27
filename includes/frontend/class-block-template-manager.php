<?php
/**
 * Block Template Manager for Knowledge Base.
 *
 * Handles registration and management of block templates with WordPress 6.7+ support
 * and graceful fallback for earlier versions.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

/**
 * Class Block_Template_Manager
 *
 * Manages block template registration using native WordPress 6.7+ API
 * with fallback support for pre-6.7 versions.
 */
class Block_Template_Manager {
	/**
	 * Plugin slug for template registration.
	 */
	const PLUGIN_SLUG = 'knowledgebase-pro';

	/**
	 * Block template directory path.
	 */
	const TEMPLATE_DIR = __DIR__ . '/templates/block-templates';

	/**
	 * Post type for knowledge base.
	 */
	const POST_TYPE = 'wz_knowledgebase';

	/**
	 * Whether to use native registration (WP 6.7+).
	 *
	 * @var bool
	 */
	private bool $use_native_registration;

	/**
	 * Constructor for Block_Template_Manager.
	 */
	public function __construct() {
		$this->use_native_registration = function_exists( 'register_block_template' );
		$this->init_hooks();
	}

	/**
	 * Initialize hooks based on WordPress version.
	 */
	private function init_hooks() {
		Hook_Registry::add_filter( 'search_template_hierarchy', array( $this, 'add_search_template_to_hierarchy' ) );

		if ( $this->use_native_registration ) {
			Hook_Registry::add_action( 'init', array( $this, 'register_block_templates' ) );
		} else {
			Hook_Registry::add_filter( 'get_block_templates', array( $this, 'inject_legacy_templates' ), 10, 3 );
		}
	}

	/**
	 * Add wzkb-search to the block template hierarchy for KB search pages.
	 *
	 * @param string[] $templates Ordered list of template slugs to look for.
	 * @return string[] Modified list of template slugs.
	 */
	public function add_search_template_to_hierarchy( array $templates ): array {
		$post_types = array_filter( (array) get_query_var( 'post_type' ) );
		if ( array( self::POST_TYPE ) === $post_types ) {
			array_unshift( $templates, 'wzkb-search.php' );
		}
		return $templates;
	}

	/**
	 * Register block templates using native WordPress 6.7+ API.
	 */
	public function register_block_templates() {
		$definitions = $this->get_template_definitions();

		foreach ( $definitions as $slug => $meta ) {
			$this->register_single_template( $slug, $meta );
		}
	}

	/**
	 * Inject templates for pre-6.7 WordPress versions.
	 *
	 * @param array  $query_result  Array of found block templates.
	 * @param array  $query         Arguments to retrieve templates.
	 * @param string $template_type Template type (wp_template or wp_template_part).
	 * @return array Updated array of found block templates.
	 */
	public function inject_legacy_templates( $query_result, $query, $template_type ) {
		if ( 'wp_template' !== $template_type ) {
			return $query_result;
		}

		$template_slug = $this->determine_current_template();
		if ( ! $template_slug ) {
			return $query_result;
		}

		$definitions = $this->get_template_definitions();
		if ( ! isset( $definitions[ $template_slug ] ) ) {
			return $query_result;
		}

		$template_object = $this->build_legacy_template_object( $template_slug, $definitions[ $template_slug ] );
		if ( $template_object ) {
			$query_result[] = $template_object;
		}

		return $query_result;
	}

	/**
	 * Get template definitions with metadata.
	 *
	 * @return array Template definitions keyed by slug.
	 */
	private function get_template_definitions(): array {
		return array(
			'single-wz_knowledgebase'  => array(
				'title'       => __( 'Knowledge Base Single Article', 'knowledgebase' ),
				'description' => __( 'Displays a single knowledge base article.', 'knowledgebase' ),
				'post_types'  => array( self::POST_TYPE ),
			),
			'archive-wz_knowledgebase' => array(
				'title'       => __( 'Knowledge Base Archive', 'knowledgebase' ),
				'description' => __( 'Displays the knowledge base archive.', 'knowledgebase' ),
			),
			'wzkb-search'              => array(
				'title'       => __( 'Knowledge Base Search', 'knowledgebase' ),
				'description' => __( 'Displays knowledge base search results.', 'knowledgebase' ),
			),
			'taxonomy-wzkb_category'   => array(
				'title'       => __( 'Knowledge Base Section', 'knowledgebase' ),
				'description' => __( 'Knowledge Base Section (taxonomy) block template.', 'knowledgebase' ),
			),
			'taxonomy-wzkb_product'    => array(
				'title'       => __( 'Knowledge Base Product', 'knowledgebase' ),
				'description' => __( 'Knowledge Base Product (taxonomy) block template.', 'knowledgebase' ),
			),
		);
	}

	/**
	 * Register a single block template using WordPress 6.7+ API.
	 *
	 * @param string $slug Template slug.
	 * @param array  $meta Template metadata.
	 */
	private function register_single_template( string $slug, array $meta ) {
		$content = $this->load_template_content( $slug );
		if ( ! $content ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				wp_trigger_error( __CLASS__, "Template file not found for: {$slug}" );
			}
			return;
		}

		$registration_args = array_merge(
			$meta,
			array(
				'content' => $content,
				'plugin'  => self::PLUGIN_SLUG,
			)
		);

		$result = register_block_template( self::PLUGIN_SLUG . '//' . $slug, $registration_args );

		if ( is_wp_error( $result ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			wp_trigger_error( __CLASS__, "Failed to register template {$slug}: " . $result->get_error_message() );
		}
	}

	/**
	 * Build a WP_Block_Template object for pre-6.7 versions.
	 *
	 * @param string $slug Template slug.
	 * @param array  $meta Template metadata.
	 * @return \WP_Block_Template|null Template object or null on failure.
	 */
	private function build_legacy_template_object( string $slug, array $meta ): ?\WP_Block_Template {
		$content = $this->load_template_content( $slug );
		if ( ! $content ) {
			return null;
		}

		$theme  = wp_get_theme();
		$source = $this->get_template_source( $slug );

		$template                 = new \WP_Block_Template();
		$template->type           = 'wp_template';
		$template->theme          = $theme->stylesheet;
		$template->slug           = $slug;
		$template->id             = self::PLUGIN_SLUG . '//' . $slug;
		$template->title          = $meta['title'] ?? '';
		$template->description    = $meta['description'] ?? '';
		$template->source         = $source;
		$template->status         = 'publish';
		$template->has_theme_file = true;
		$template->is_custom      = true;
		$template->content        = $content;
		$template->post_types     = $meta['post_types'] ?? array();

		return $template;
	}

	/**
	 * Load template content from file.
	 *
	 * @param string $slug Template slug.
	 * @return string|null Template content or null if file not found.
	 */
	private function load_template_content( string $slug ): ?string {
		$template_file = $this->locate_template_file( $slug );
		if ( ! $template_file ) {
			return null;
		}

		ob_start();
		include $template_file;
		$content = ob_get_clean();

		if ( false === $content ) {
			return null;
		}

		return $content;
	}

	/**
	 * Locate template file, checking theme override first.
	 *
	 * @param string $slug Template slug.
	 * @return string|null Full path to template file or null if not found.
	 */
	private function locate_template_file( string $slug ): ?string {
		// Check theme override (block themes use /templates/ directory).
		$theme_file = get_stylesheet_directory() . '/templates/' . $slug . '.html';
		if ( file_exists( $theme_file ) ) {
			return $theme_file;
		}

		// Check plugin block-templates directory.
		$plugin_file = self::TEMPLATE_DIR . '/' . $slug . '.html';
		if ( file_exists( $plugin_file ) ) {
			return $plugin_file;
		}

		return null;
	}

	/**
	 * Get template source (theme or plugin).
	 *
	 * @param string $slug Template slug.
	 * @return string Template source.
	 */
	private function get_template_source( string $slug ): string {
		$theme_file = get_stylesheet_directory() . '/templates/' . $slug . '.html';
		return file_exists( $theme_file ) ? 'theme' : 'plugin';
	}

	/**
	 * Determine current template slug based on request context.
	 *
	 * @return string|null Template slug or null if not a KB template.
	 */
	private function determine_current_template(): ?string {
		if ( is_singular( self::POST_TYPE ) ) {
			return 'single-wz_knowledgebase';
		}

		if ( is_search() && array( self::POST_TYPE ) === array_filter( (array) get_query_var( 'post_type' ) ) ) {
			return 'wzkb-search';
		}

		if ( is_post_type_archive( self::POST_TYPE ) ) {
			return 'archive-wz_knowledgebase';
		}

		if ( is_tax( 'wzkb_category' ) ) {
			return 'taxonomy-wzkb_category';
		}

		if ( is_tax( 'wzkb_product' ) ) {
			return 'taxonomy-wzkb_product';
		}

		return null;
	}
}
