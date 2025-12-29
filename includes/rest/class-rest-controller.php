<?php
/**
 * Knowledge Base REST controller.
 *
 * @package WebberZone\Knowledge_Base\REST
 */

namespace WebberZone\Knowledge_Base\REST;

use WP_Error;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WebberZone\Knowledge_Base\Frontend\Related;
use WebberZone\Knowledge_Base\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Registers Knowledge Base specific REST API endpoints.
 *
 * @since 3.0.0
 */
class REST_Controller {

	/**
	 * REST namespace.
	 */
	private const NAMESPACE = 'wzkb/v1';

	/**
	 * Object cache group name.
	 */
	private const CACHE_GROUP = 'wzkb_rest';

	/**
	 * Default cache lifetime (in seconds).
	 */
	private const DEFAULT_TTL = 300;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		Hook_Registry::add_action( 'save_post_wz_knowledgebase', array( $this, 'bump_cache_version' ) );
		Hook_Registry::add_action( 'transition_post_status', array( $this, 'maybe_bump_on_status_change' ), 10, 3 );
		Hook_Registry::add_action( 'deleted_post', array( $this, 'maybe_bump_on_post_delete' ) );
		Hook_Registry::add_action( 'created_term', array( $this, 'maybe_bump_on_term_change' ), 10, 3 );
		Hook_Registry::add_action( 'edited_term', array( $this, 'maybe_bump_on_term_change' ), 10, 3 );
		Hook_Registry::add_action( 'delete_term', array( $this, 'maybe_bump_on_term_change' ), 10, 5 );
		Hook_Registry::add_action( 'updated_term_meta', array( $this, 'maybe_bump_on_term_meta_update' ), 10, 3 );
	}

	/**
	 * Register plugin endpoints.
	 */
	public function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/sections',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_sections_for_products' ),
				'permission_callback' => $this->get_permission_callback( 'sections', true ),
				'args'                => array(
					'products' => array(
						'description' => __( 'Comma-separated list of product term IDs.', 'knowledgebase' ),
						'required'    => true,
						'type'        => 'string',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/knowledgebase',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_knowledgebase_posts' ),
				'permission_callback' => $this->get_permission_callback( 'knowledgebase_list', true ),
				'args'                => $this->get_post_list_args(),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/knowledgebase/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_single_knowledgebase_post' ),
				'permission_callback' => $this->get_permission_callback( 'knowledgebase_single', true ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Knowledge Base post ID.', 'knowledgebase' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/products',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_products' ),
				'permission_callback' => $this->get_permission_callback( 'products', true ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/search',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_search_results' ),
				'permission_callback' => $this->get_permission_callback( 'search', true ),
				'args'                => array(
					'query'   => array(
						'description' => __( 'Search keywords.', 'knowledgebase' ),
						'type'        => 'string',
						'required'    => true,
						'minLength'   => 2,
					),
					'product' => array(
						'description' => __( 'Filter by product term ID.', 'knowledgebase' ),
						'type'        => 'integer',
					),
					'section' => array(
						'description' => __( 'Filter by section term ID.', 'knowledgebase' ),
						'type'        => 'integer',
					),
					'limit'   => array(
						'description' => __( 'Maximum number of results to return.', 'knowledgebase' ),
						'type'        => 'integer',
						'default'     => 10,
						'minimum'     => 1,
						'maximum'     => 50,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/related',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_related_articles' ),
				'permission_callback' => $this->get_permission_callback( 'related', true ),
				'args'                => array(
					'post_id' => array(
						'description' => __( 'Knowledge Base post ID used to fetch related articles.', 'knowledgebase' ),
						'type'        => 'integer',
						'required'    => true,
						'minimum'     => 1,
					),
					'limit'   => array(
						'description' => __( 'Maximum number of related articles to return.', 'knowledgebase' ),
						'type'        => 'integer',
						'default'     => 5,
						'minimum'     => 1,
						'maximum'     => 20,
					),
				),
			)
		);
	}

	/**
	 * Build a permission callback that supports user overrides.
	 *
	 * @param string $route_slug     Unique slug for the route.
	 * @param bool   $default_public Should the route be public by default.
	 *
	 * @return callable
	 */
	private function get_permission_callback( string $route_slug, bool $default_public = true ): callable {
		return function () use ( $route_slug, $default_public ) {
			return $this->check_route_permission( $route_slug, $default_public );
		};
	}

	/**
	 * Resolve permission based on filters or fallbacks.
	 *
	 * @param string $route_slug     Route identifier.
	 * @param bool   $default_public Default visibility.
	 *
	 * @return bool
	 */
	private function check_route_permission( string $route_slug, bool $default_public ): bool {
		/**
		 * Filters the permission logic for a REST route.
		 *
		 * Returning a boolean short-circuits the check.
		 * Returning a capability string triggers current_user_can().
		 * Returning a callable allows custom evaluation and should return a boolean.
		 *
		 * @since 3.0.0
		 *
		 * @param bool|string|callable|null $permission     Filtered permission directive.
		 * @param string                    $route_slug     Route identifier.
		 * @param bool                      $default_public Whether the route is public by default.
		 */
		$permission = apply_filters( 'wzkb_rest_route_permission', null, $route_slug, $default_public );

		if ( is_bool( $permission ) ) {
			return $permission;
		}

		if ( is_string( $permission ) && '' !== $permission ) {
			return current_user_can( $permission );
		}

		if ( is_callable( $permission ) ) {
			return (bool) call_user_func( $permission, $route_slug, $default_public );
		}

		return $default_public ? true : current_user_can( 'edit_posts' );
	}

	/**
	 * REST callback: return hierarchical sections filtered by product IDs.
	 *
	 * @param WP_REST_Request $request Request instance.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_sections_for_products( WP_REST_Request $request ) {
		if ( 0 === (int) wzkb_get_option( 'multi_product', 0 ) ) {
			return new WP_Error(
				'wzkb_rest_sections_disabled',
				__( 'Section filtering is only available when multi-product mode is enabled.', 'knowledgebase' ),
				array( 'status' => 400 )
			);
		}

		$product_ids = $this->parse_product_ids( $request->get_param( 'products' ) );

		if ( empty( $product_ids ) ) {
			return rest_ensure_response( array() );
		}

		$cache_key = $this->get_cache_key(
			'sections',
			array(
				'products' => $product_ids,
			)
		);

		$cached = $this->cache_get( $cache_key );
		if ( false !== $cached ) {
			return rest_ensure_response( $cached );
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'wzkb_category',
				'hide_empty' => false,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array(
						'key'     => 'product_id',
						'value'   => $product_ids,
						'compare' => 'IN',
					),
					array(
						'key'     => 'product_id',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'product_id',
						'value'   => 0,
						'compare' => '=',
					),
				),
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		$response = array_map(
			static function ( $term ) {
				return array(
					'id'      => (int) $term->term_id,
					'name'    => $term->name,
					'parent'  => (int) $term->parent,
					'product' => (int) get_term_meta( $term->term_id, 'product_id', true ),
				);
			},
			$terms
		);

		$this->cache_set( $cache_key, $response );

		return rest_ensure_response( $response );
	}

	/**
	 * Fetch paginated knowledgebase posts.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_knowledgebase_posts( WP_REST_Request $request ) {
		$per_page = $this->sanitize_page_size( $request->get_param( 'per_page' ) );
		$page     = max( 1, absint( $request->get_param( 'page' ) ) );
		$search   = sanitize_text_field( $request->get_param( 'search' ) );

		$query_args = array(
			'post_type'           => 'wz_knowledgebase',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => false,
			'posts_per_page'      => $per_page,
			'paged'               => $page,
			's'                   => $search,
		);

		$tax_query = array();

		if ( $request->get_param( 'product' ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wzkb_product',
				'field'    => 'term_id',
				'terms'    => absint( $request['product'] ),
			);
		}

		if ( $request->get_param( 'section' ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wzkb_category',
				'field'    => 'term_id',
				'terms'    => absint( $request['section'] ),
			);
		}

		if ( ! empty( $tax_query ) ) {
			$query_args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}

		$cache_key = $this->get_cache_key(
			'kb_list',
			array(
				'per_page' => $per_page,
				'page'     => $page,
				'search'   => $search,
				'product'  => (int) $request->get_param( 'product' ),
				'section'  => (int) $request->get_param( 'section' ),
			)
		);

		$cached = $this->cache_get( $cache_key );
		if ( false !== $cached ) {
			$response = rest_ensure_response( $cached['body'] );
			$response->header( 'X-WP-Total', (string) $cached['total'] );
			$response->header( 'X-WP-TotalPages', (string) $cached['pages'] );

			return $response;
		}

		$query = new WP_Query( $query_args );

		$posts = array();
		foreach ( $query->posts as $post ) {
			$posts[] = $this->prepare_post_for_response( $post );
		}

		$response = rest_ensure_response( $posts );
		$response->header( 'X-WP-Total', (string) (int) $query->found_posts );
		$response->header( 'X-WP-TotalPages', (string) (int) $query->max_num_pages );

		$this->cache_set(
			$cache_key,
			array(
				'body'  => $posts,
				'total' => (int) $query->found_posts,
				'pages' => (int) $query->max_num_pages,
			)
		);

		return $response;
	}

	/**
	 * Retrieve single Knowledge Base post.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_single_knowledgebase_post( WP_REST_Request $request ) {
		$post_id = absint( $request['id'] );
		$post    = get_post( $post_id );

		if ( ! $post || 'wz_knowledgebase' !== $post->post_type ) {
			return new WP_Error(
				'wzkb_rest_post_not_found',
				__( 'Knowledge Base post not found.', 'knowledgebase' ),
				array( 'status' => 404 )
			);
		}

		if ( 'publish' !== $post->post_status ) {
			return new WP_Error(
				'wzkb_rest_post_unpublished',
				__( 'Knowledge Base post is not published.', 'knowledgebase' ),
				array( 'status' => 403 )
			);
		}

		$cache_key = $this->get_cache_key(
			'kb_single',
			array(
				'id' => $post_id,
			)
		);

		$cached = $this->cache_get( $cache_key );
		if ( false !== $cached ) {
			return rest_ensure_response( $cached );
		}

		$data = $this->prepare_post_for_response( $post, true );
		$this->cache_set( $cache_key, $data );

		return rest_ensure_response( $data );
	}

	/**
	 * Fetch all knowledge base products.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_products() {
		$cache_key = $this->get_cache_key( 'products' );

		$cached = $this->cache_get( $cache_key );
		if ( false !== $cached ) {
			return rest_ensure_response( $cached );
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'wzkb_product',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		$response = array_map(
			static function ( $term ) {
				return array(
					'id'          => (int) $term->term_id,
					'name'        => $term->name,
					'slug'        => $term->slug,
					'description' => $term->description,
					'count'       => (int) $term->count,
				);
			},
			$terms
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Fetch related Knowledge Base articles.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_related_articles( WP_REST_Request $request ) {
		$post_id = absint( $request['post_id'] );
		$limit   = absint( $request['limit'] );

		if ( $limit < 1 ) {
			$limit = 5;
		}

		$post = get_post( $post_id );
		if ( ! $post || 'wz_knowledgebase' !== $post->post_type ) {
			return new WP_Error(
				'wzkb_rest_post_not_found',
				__( 'Knowledge Base post not found.', 'knowledgebase' ),
				array( 'status' => 404 )
			);
		}

		$cache_key = $this->get_cache_key(
			'related',
			array(
				'post_id' => $post_id,
				'limit'   => $limit,
			)
		);

		$cached = $this->cache_get( $cache_key );
		if ( false !== $cached ) {
			return rest_ensure_response( $cached );
		}

		$related = Related::get_related_articles(
			array(
				'post_id' => $post_id,
				'limit'   => $limit,
				'echo'    => false,
			)
		);

		$this->cache_set( $cache_key, $related );

		return rest_ensure_response( $related );
	}

	/**
	 * Lightweight Knowledge Base search.
	 *
	 * @param WP_REST_Request $request Request instance.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_search_results( WP_REST_Request $request ) {
		$query   = sanitize_text_field( $request['query'] );
		$product = absint( $request->get_param( 'product' ) );
		$section = absint( $request->get_param( 'section' ) );
		$limit   = $this->sanitize_page_size( $request->get_param( 'limit' ) );

		if ( strlen( $query ) < 2 ) {
			return new WP_Error(
				'wzkb_rest_search_short',
				__( 'Search query must be at least two characters long.', 'knowledgebase' ),
				array( 'status' => 400 )
			);
		}

		$cache_key = $this->get_cache_key(
			'search',
			array(
				'q'       => $query,
				'product' => $product,
				'section' => $section,
				'limit'   => $limit,
			)
		);

		$cached = $this->cache_get( $cache_key );
		if ( false !== $cached ) {
			return rest_ensure_response( $cached );
		}

		$query_args = array(
			'post_type'           => 'wz_knowledgebase',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'posts_per_page'      => $limit,
			'no_found_rows'       => true,
			's'                   => $query,
		);

		$tax_query = array();

		if ( $product ) {
			$tax_query[] = array(
				'taxonomy' => 'wzkb_product',
				'field'    => 'term_id',
				'terms'    => $product,
			);
		}

		if ( $section ) {
			$tax_query[] = array(
				'taxonomy' => 'wzkb_category',
				'field'    => 'term_id',
				'terms'    => $section,
			);
		}

		if ( ! empty( $tax_query ) ) {
			$query_args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}

		$wp_query = new WP_Query( $query_args );

		$results = array();
		foreach ( $wp_query->posts as $post ) {
			$results[] = $this->prepare_post_for_response( $post );
		}

		$this->cache_set( $cache_key, $results );

		return rest_ensure_response( $results );
	}

	/**
	 * Convert incoming product string to sanitized array of IDs.
	 *
	 * @param string $raw_products Raw products parameter.
	 * @return int[]
	 */
	private function parse_product_ids( $raw_products ) {
		if ( empty( $raw_products ) ) {
			return array();
		}

		$product_ids = array_filter(
			array_map(
				'absint',
				explode( ',', (string) $raw_products )
			),
			static function ( $value ) {
				return $value > 0;
			}
		);

		return array_values( array_unique( $product_ids ) );
	}

	/**
	 * Sanitize pagination size.
	 *
	 * @param int $value Raw value.
	 * @return int
	 */
	private function sanitize_page_size( $value ) {
		$value = absint( $value );
		if ( $value < 1 ) {
			$value = 10;
		}

		return min( 50, $value );
	}

	/**
	 * Arguments for knowledgebase list endpoint.
	 *
	 * @return array
	 */
	private function get_post_list_args() {
		return array(
			'per_page' => array(
				'description' => __( 'Number of posts to return per page.', 'knowledgebase' ),
				'type'        => 'integer',
				'default'     => 10,
				'minimum'     => 1,
				'maximum'     => 50,
			),
			'page'     => array(
				'description' => __( 'Current page of the collection.', 'knowledgebase' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
			'search'   => array(
				'description' => __( 'Search term.', 'knowledgebase' ),
				'type'        => 'string',
			),
			'product'  => array(
				'description' => __( 'Filter by product term ID.', 'knowledgebase' ),
				'type'        => 'integer',
			),
			'section'  => array(
				'description' => __( 'Filter by section term ID.', 'knowledgebase' ),
				'type'        => 'integer',
			),
		);
	}

	/**
	 * Prepare KB post data.
	 *
	 * @param \WP_Post $post             Post object.
	 * @param bool     $include_content  Include full content flag.
	 * @return array
	 */
	private function prepare_post_for_response( $post, $include_content = false ) {
		$post = get_post( $post );
		if ( ! ( $post instanceof \WP_Post ) ) {
			return array();
		}

		$data = array(
			'id'        => (int) $post->ID,
			'title'     => get_the_title( $post ),
			'slug'      => $post->post_name,
			'excerpt'   => wp_trim_words( wp_strip_all_tags( $post->post_content ), 55 ),
			'permalink' => get_permalink( $post ),
			'products'  => $this->format_terms( get_the_terms( $post, 'wzkb_product' ) ),
			'sections'  => $this->format_terms( get_the_terms( $post, 'wzkb_category' ) ),
			'date'      => mysql2date( DATE_RFC3339, $post->post_date_gmt, false ),
			'modified'  => mysql2date( DATE_RFC3339, $post->post_modified_gmt, false ),
		);

		if ( $include_content ) {
			$data['content'] = apply_filters( 'the_content', $post->post_content );
		}

		return $data;
	}

	/**
	 * Format terms list.
	 *
	 * @param array|\WP_Error $terms Terms.
	 * @return array
	 */
	private function format_terms( $terms ) {
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return array();
		}

		return array_map(
			static function ( $term ) {
				return array(
					'id'   => (int) $term->term_id,
					'name' => $term->name,
					'slug' => $term->slug,
				);
			},
			$terms
		);
	}

	/**
	 * Retrieve cached value.
	 *
	 * @param string $cache_key Cache key.
	 * @return mixed Cached data or false.
	 */
	private function cache_get( $cache_key ) {
		return wp_cache_get( $cache_key, self::CACHE_GROUP );
	}

	/**
	 * Store cached value.
	 *
	 * @param string $cache_key Cache key.
	 * @param mixed  $value     Data.
	 * @param int    $ttl       Time to live.
	 * @return void
	 */
	private function cache_set( $cache_key, $value, $ttl = self::DEFAULT_TTL ) {
		wp_cache_set( $cache_key, $value, self::CACHE_GROUP, $ttl );
	}

	/**
	 * Build deterministic cache key.
	 *
	 * @param string $prefix  Prefix.
	 * @param array  $context Context array.
	 * @return string
	 */
	private function get_cache_key( $prefix, $context = array() ) {
		if ( ! empty( $context ) ) {
			ksort( $context );
		}

		return sprintf(
			'%s_%d_%s',
			$prefix,
			$this->get_cache_version(),
			empty( $context ) ? 'static' : md5( wp_json_encode( $context ) )
		);
	}

	/**
	 * Cache version getter.
	 *
	 * @return int
	 */
	private function get_cache_version() {
		return (int) get_option( 'wzkb_rest_cache_version', 1 );
	}

	/**
	 * Increment cache version (global invalidation).
	 *
	 * @return void
	 */
	public function bump_cache_version() {
		$version = $this->get_cache_version();
		update_option( 'wzkb_rest_cache_version', $version + 1, false );
	}

	/**
	 * Maybe bump cache on status change.
	 *
	 * @param string   $new_status New status.
	 * @param string   $old_status Old status.
	 * @param \WP_Post $post       Post object.
	 * @return void
	 */
	public function maybe_bump_on_status_change( $new_status, $old_status, $post ) {
		if ( $post instanceof \WP_Post && 'wz_knowledgebase' === $post->post_type && $new_status !== $old_status ) {
			$this->bump_cache_version();
		}
	}

	/**
	 * Maybe bump cache when KB post deleted.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function maybe_bump_on_post_delete( $post_id ) {
		$post = get_post( $post_id );
		if ( $post && 'wz_knowledgebase' === $post->post_type ) {
			$this->bump_cache_version();
		}
	}

	/**
	 * Maybe bump cache when KB terms change.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Taxonomy term ID.
	 * @param string $taxonomy Taxonomy.
	 * @return void
	 */
	public function maybe_bump_on_term_change( $term_id, $tt_id = 0, $taxonomy = '' ) {
		if ( in_array( $taxonomy, array( 'wzkb_category', 'wzkb_product' ), true ) ) {
			$this->bump_cache_version();
		}
	}

	/**
	 * Maybe bump cache when section meta updated.
	 *
	 * @param int    $meta_id  Meta ID.
	 * @param int    $term_id  Term ID.
	 * @param string $meta_key Meta key.
	 * @return void
	 */
	public function maybe_bump_on_term_meta_update( $meta_id, $term_id, $meta_key ) {
		if ( 'product_id' !== $meta_key ) {
			return;
		}

		$term = get_term( $term_id );
		if ( $term && 'wzkb_category' === $term->taxonomy ) {
			$this->bump_cache_version();
		}
	}
}
