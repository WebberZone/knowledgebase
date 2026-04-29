<?php
/**
 * AJAX live search handler.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Live search class.
 *
 * @since 3.1.0
 */
class Live_Search {

	/**
	 * Constructor.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		Hook_Registry::add_action( 'wp_ajax_wzkb_live_search', array( $this, 'live_search' ) );
		Hook_Registry::add_action( 'wp_ajax_nopriv_wzkb_live_search', array( $this, 'live_search' ) );
	}

	/**
	 * Enqueue live search scripts and styles.
	 *
	 * @since 3.1.0
	 */
	public function enqueue_scripts() {
		if ( ! wzkb_get_option( 'enable_live_search' ) ) {
			return;
		}

		$minimize = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script(
			'wzkb-live-search',
			plugins_url( 'includes/frontend/js/wzkb-live-search' . $minimize . '.js', WZKB_PLUGIN_FILE ),
			array(),
			WZKB_VERSION,
			true
		);

		wp_localize_script(
			'wzkb-live-search',
			'wzkb_live_search',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'strings'  => array(
					'no_results'         => __( 'No results found', 'knowledgebase' ),
					'searching'          => __( 'Searching…', 'knowledgebase' ),
					'min_chars'          => __( 'Please enter at least 3 characters to search', 'knowledgebase' ),
					'suggestions_closed' => __( 'Search suggestions closed', 'knowledgebase' ),
					'back_to_search'     => __( 'Back to search', 'knowledgebase' ),
					'back_to_input'      => __( 'Back to search input', 'knowledgebase' ),
					'error_loading'      => __( 'Error loading search results', 'knowledgebase' ),
					'no_suggestions'     => __( 'No search suggestions found', 'knowledgebase' ),
					/* translators: %d: Number of suggestions found. */
					'suggestions_found'  => __( '%d search suggestions found. Use up and down arrow keys to navigate.', 'knowledgebase' ),
					/* translators: %s: Destination being navigated to. */
					'navigating_to'      => __( 'Navigating to %s', 'knowledgebase' ),
					'submitting_search'  => __( 'Submitting search', 'knowledgebase' ),
					/* translators: 1: Current result position. 2: Total results. */
					'result_position'    => __( 'Result %1$d of %2$d', 'knowledgebase' ),
					/* translators: %s: Post title. */
					'view_post'          => __( 'View article: %s', 'knowledgebase' ),
					'suggestions_label'  => __( 'Search suggestions', 'knowledgebase' ),
				),
			)
		);

		wp_enqueue_style(
			'wzkb-live-search-style',
			plugins_url( 'includes/frontend/css/wzkb-live-search' . $minimize . '.css', WZKB_PLUGIN_FILE ),
			array(),
			WZKB_VERSION
		);
	}

	/**
	 * Handle the AJAX live search request.
	 *
	 * @since 3.1.0
	 */
	public function live_search() {
		$search_query = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( empty( $search_query ) ) {
			wp_send_json( array() );
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		/**
		 * Filters the number of results returned by AJAX live search.
		 *
		 * @since 3.1.0
		 *
		 * @param int $posts_per_page Number of results. Default 5.
		 */
		$posts_per_page = (int) apply_filters( 'wzkb_live_search_posts_per_page', 5 );

		$query_args = array(
			's'              => $search_query,
			'posts_per_page' => $posts_per_page,
			'post_type'      => 'wz_knowledgebase',
			'post_status'    => 'publish',
		);

		if ( $product_id > 0 ) {
			$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'wzkb_product',
					'field'    => 'term_id',
					'terms'    => $product_id,
				),
			);
		}

		/**
		 * Filters the WP_Query arguments for live search.
		 *
		 * @since 3.1.0
		 *
		 * @param array  $query_args   WP_Query arguments.
		 * @param string $search_query The search term.
		 * @param int    $product_id   Optional product term ID (0 if not set).
		 */
		$query_args = apply_filters( 'wzkb_live_search_query_args', $query_args, $search_query, $product_id );

		$query   = new \WP_Query( $query_args );
		$results = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$results[] = array(
					'title' => html_entity_decode( (string) get_the_title(), ENT_QUOTES, 'UTF-8' ),
					'link'  => get_permalink(),
				);
			}
		}
		wp_reset_postdata();

		$response = array(
			'results' => $results,
			'total'   => count( $results ),
			'query'   => $search_query,
		);

		wp_send_json( $response );
	}
}
