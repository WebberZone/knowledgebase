<?php
/**
 * Registers Knowledge Base abilities.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base;

use WebberZone\Knowledge_Base\REST\REST_Controller;
use WebberZone\Knowledge_Base\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Registers the shared Knowledge Base abilities.
 *
 * @since 3.2.0
 */
class Abilities {

	/**
	 * Ability category slug.
	 *
	 * @var string
	 */
	const CATEGORY = 'webberzone';

	/**
	 * REST controller.
	 *
	 * @var REST_Controller
	 */
	protected REST_Controller $rest_controller;

	/**
	 * Constructor.
	 *
	 * @since 3.2.0
	 *
	 * @param REST_Controller $rest_controller REST controller.
	 */
	public function __construct( REST_Controller $rest_controller ) {
		$this->rest_controller = $rest_controller;

		if ( ! function_exists( 'wp_register_ability' ) || ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		Hook_Registry::add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
		Hook_Registry::add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Register the shared WebberZone ability category if it is not already present.
	 *
	 * @since 3.2.0
	 */
	public function register_category(): void {
		if ( ! function_exists( 'wp_has_ability_category' ) || wp_has_ability_category( self::CATEGORY ) ) {
			return;
		}

		wp_register_ability_category(
			self::CATEGORY,
			array(
				'label'       => __( 'WebberZone', 'knowledgebase' ),
				'description' => __( 'Abilities provided by WebberZone plugins.', 'knowledgebase' ),
			)
		);
	}

	/**
	 * Register read-only Knowledge Base abilities.
	 *
	 * @since 3.2.0
	 */
	public function register_abilities(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'knowledgebase/search-articles',
			array(
				'label'               => __( 'Search Knowledge Base Articles', 'knowledgebase' ),
				'description'         => __( 'Search published Knowledge Base articles using a few keywords. Optionally limit results to a section ID or slug. Returns each matching article ID, plain-text title and excerpt, URL, and section terms.', 'knowledgebase' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'required'             => array( 'query' ),
					'additionalProperties' => false,
					'properties'           => array(
						'query'   => array(
							'type'        => 'string',
							'minLength'   => 2,
							'maxLength'   => 500,
							'description' => __( 'Keywords or a short phrase to search for.', 'knowledgebase' ),
						),
						'section' => array(
							'anyOf'       => array(
								array(
									'type'      => 'string',
									'minLength' => 1,
									'maxLength' => 200,
								),
								array(
									'type'    => 'integer',
									'minimum' => 1,
								),
							),
							'description' => __( 'Section term ID or slug to limit the search.', 'knowledgebase' ),
						),
						'limit'   => array(
							'type'        => 'integer',
							'default'     => 10,
							'minimum'     => 1,
							'maximum'     => 50,
							'description' => __( 'Maximum number of articles to return.', 'knowledgebase' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'array',
					'description' => __( 'Matching Knowledge Base articles.', 'knowledgebase' ),
					'items'       => array(
						'type'                 => 'object',
						'required'             => array( 'id', 'title', 'url', 'excerpt', 'section' ),
						'additionalProperties' => false,
						'properties'           => array(
							'id'      => array( 'type' => 'integer' ),
							'title'   => array( 'type' => 'string' ),
							'url'     => array(
								'type'   => 'string',
								'format' => 'uri',
							),
							'excerpt' => array( 'type' => 'string' ),
							'section' => array(
								'type'  => 'array',
								'items' => array(
									'type'                 => 'object',
									'required'             => array( 'id', 'name', 'slug' ),
									'additionalProperties' => false,
									'properties'           => array(
										'id'   => array( 'type' => 'integer' ),
										'name' => array( 'type' => 'string' ),
										'slug' => array( 'type' => 'string' ),
									),
								),
							),
						),
					),
				),
				'execute_callback'    => array( $this, 'search_articles' ),
				'permission_callback' => array( $this, 'can_read_search' ),
				'meta'                => array(
					'public'       => true,
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					),
				),
			)
		);

		wp_register_ability(
			'knowledgebase/get-sections',
			array(
				'label'               => __( 'Get Knowledge Base Sections', 'knowledgebase' ),
				'description'         => __( 'Get the hierarchical Knowledge Base section tree. Provide a parent section ID to return its children and descendants; omit it to return top-level sections. Each section includes its ID, name, slug, URL, and children.', 'knowledgebase' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'default'              => array(),
					'additionalProperties' => false,
					'properties'           => array(
						'parent' => array(
							'type'        => 'integer',
							'default'     => 0,
							'minimum'     => 0,
							'description' => __( 'Parent section ID. Omit this to return top-level sections.', 'knowledgebase' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'array',
					'description' => __( 'Hierarchical Knowledge Base sections.', 'knowledgebase' ),
					'items'       => array(
						'type'                 => 'object',
						'required'             => array( 'id', 'name', 'slug', 'url', 'children' ),
						'additionalProperties' => false,
						'properties'           => array(
							'id'       => array( 'type' => 'integer' ),
							'name'     => array( 'type' => 'string' ),
							'slug'     => array( 'type' => 'string' ),
							'url'      => array(
								'type'   => 'string',
								'format' => 'uri',
							),
							'children' => array(
								'type'  => 'array',
								'items' => array(
									'type'                 => 'object',
									'required'             => array( 'id', 'name', 'slug', 'url', 'children' ),
									'additionalProperties' => false,
									'properties'           => array(
										'id'       => array( 'type' => 'integer' ),
										'name'     => array( 'type' => 'string' ),
										'slug'     => array( 'type' => 'string' ),
										'url'      => array(
											'type'   => 'string',
											'format' => 'uri',
										),
										'children' => array(
											'type'  => 'array',
											'items' => array( 'type' => 'object' ),
										),
									),
								),
							),
						),
					),
				),
				'execute_callback'    => array( $this, 'get_sections' ),
				'permission_callback' => array( $this, 'can_read_sections' ),
				'meta'                => array(
					'public'       => true,
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					),
				),
			)
		);
	}

	/**
	 * Check whether the current user can read Knowledge Base content.
	 *
	 * @since 3.2.0
	 *
	 * @param mixed $input Ability input.
	 * @return bool Whether the current user can read.
	 */
	public function can_read_search( $input = array() ): bool {
		unset( $input );

		return current_user_can( 'read' ) && $this->rest_controller->can_read_ability_route( 'search' );
	}

	/**
	 * Check whether the current user can read Knowledge Base sections.
	 *
	 * @since 3.2.0
	 *
	 * @param mixed $input Ability input.
	 * @return bool Whether the current user can read sections.
	 */
	public function can_read_sections( $input = array() ): bool {
		unset( $input );

		return current_user_can( 'read' ) && $this->rest_controller->can_read_ability_route( 'sections' );
	}

	/**
	 * Search published Knowledge Base articles.
	 *
	 * @since 3.2.0
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error Search results or an error.
	 */
	public function search_articles( $input ) {
		return $this->rest_controller->search_articles_for_ability( $input );
	}

	/**
	 * Get sections through the shared REST controller.
	 *
	 * @since 3.2.0
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error Section tree or an error.
	 */
	public function get_sections( $input ) {
		return $this->rest_controller->get_sections_for_ability( $input );
	}
}
