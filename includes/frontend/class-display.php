<?php
/**
 * Display module
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

use WebberZone\Knowledge_Base\Util\Cache;
use WebberZone\Knowledge_Base\Util\Helpers;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Display Class.
 *
 * @since 2.3.0
 */
class Display {

	/**
	 * Cache for frequently accessed options.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	private static $options_cache = array();

	/**
	 * Default cache time in seconds.
	 *
	 * @since 3.0.0
	 * @var int
	 */
	const DEFAULT_CACHE_TIME = HOUR_IN_SECONDS;

	/**
	 * Default post limit.
	 *
	 * @since 3.0.0
	 * @var int
	 */
	const DEFAULT_POST_LIMIT = 5;

	/**
	 * Constructor class.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		new Breadcrumbs();
	}

	/**
	 * Get a cached option value.
	 *
	 * @since 3.0.0
	 *
	 * @param string $option        Option name.
	 * @param mixed  $default_value Default value to return if option doesn't exist.
	 * @return mixed Option value.
	 */
	private static function get_cached_option( $option, $default_value = null ) {
		if ( ! isset( self::$options_cache[ $option ] ) ) {
			self::$options_cache[ $option ] = \wzkb_get_option( $option, $default_value );
		}
		return self::$options_cache[ $option ];
	}

	/**
	 * The main function to generate the output.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args {
	 *     Optional. Array of parameters.
	 *
	 *     @type int    $category            Create a knowledge base for this category.
	 *     @type int    $product             Create a knowledge base for this product.
	 *     @type bool   $is_shortcode        Is this created using the shortcode?
	 *     @type bool   $is_block            Is this created using the block?
	 *     @type string $extra_class         Space separated list of classes for the wrapping `div`.
	 *     @type bool   $show_article_count  Show article count?
	 *     @type bool   $show_excerpt        Show excerpt?
	 *     @type bool   $show_empty_sections Show empty sections?
	 *     @type int    $limit               Number of articles to display in each group.
	 *     @type int    $columns             Number of columns to display the knowledge base.
	 * }
	 * @return string Knowledge Base output.
	 */
	public static function get_knowledge_base( $args = array() ) {
		$defaults = array(
			'category'               => 0,
			'product'                => 0,
			'is_shortcode'           => false,
			'is_block'               => false,
			'extra_class'            => '',
			'show_article_count'     => self::get_cached_option( 'show_article_count' ),
			'show_excerpt'           => self::get_cached_option( 'show_excerpt' ),
			'clickable_section'      => self::get_cached_option( 'clickable_section' ),
			'show_empty_sections'    => self::get_cached_option( 'show_empty_sections' ),
			'limit'                  => self::get_cached_option( 'limit' ),
			'columns'                => self::get_cached_option( 'columns' ),
			'product_archive_layout' => self::get_cached_option( 'product_archive_layout', 'sections' ),
		);

		$args = wp_parse_args( $args, $defaults );
		$args = Helpers::sanitize_args( $args );

		// Normalize layout rules for consistency.
		$args = self::normalize_arguments( $args );

		// Set defaults if variables are empty.
		$args['limit']   = isset( $args['limit'] ) ? \intval( $args['limit'] ) : self::get_cached_option( 'limit' );
		$args['columns'] = isset( $args['columns'] ) ? \absint( $args['columns'] ) : self::get_cached_option( 'columns' );

		$args['product_archive_layout'] = in_array( $args['product_archive_layout'], array( 'sections', 'grid' ), true ) ? $args['product_archive_layout'] : 'sections';

		// Set default classes.
		$div_classes = self::build_wrapper_classes( $args );

		$output = '<div class="wzkb ' . esc_attr( $div_classes ) . '">';

		$product          = \intval( $args['product'] );
		$category         = \intval( $args['category'] );
		$is_multi_product = self::get_cached_option( 'multi_product' );

		// Harmonize: If product = -1, auto-detect current product term (like category does).
		if ( -1 === $product ) {
			$queried_object = get_queried_object();
			if ( isset( $queried_object->term_id ) && isset( $queried_object->taxonomy ) && 'wzkb_product' === $queried_object->taxonomy ) {
				$product = \intval( $queried_object->term_id );
			}
		}

		// Resolve view and render accordingly.
		switch ( self::resolve_view( $product, $category, $is_multi_product ) ) {
			case 'product':
				$output .= self::render_product_sections( $product, $args );
				break;

			case 'product_archive':
				$products = self::fetch_terms( 'wzkb_product' );
				if ( ! empty( $products ) && ! is_wp_error( $products ) ) {
					$output .= self::render_product_archive( $products, $args );
				} else {
					$output .= '<div class="wzkb-no-products">' . esc_html__( 'No products found.', 'knowledgebase' ) . '</div>';
				}
				break;

			default:
				$output .= self::render_category_view( $category, $args );
				break;
		}

		$output .= '</div>';
		$output .= '<div class="wzkb-clear"></div>';

		/**
		 * Filter the formatted output.
		 *
		 * @since 1.0.0
		 *
		 * @param string $output Formatted HTML output.
		 * @param array  $args   Parameters array.
		 */
		return apply_filters( 'wzkb_knowledge', $output, $args );
	}

	/**
	 * Resolve which view to render based on product and category context.
	 *
	 * @since 3.0.0
	 *
	 * @param int  $product         Product ID.
	 * @param int  $category        Category ID.
	 * @param bool $is_multi_product Whether multi-product mode is enabled.
	 * @return string View type: 'product', 'product_archive', or 'category'.
	 */
	private static function resolve_view( int $product, int $category, bool $is_multi_product ): string {
		// Early return if not in multi-product mode.
		if ( ! $is_multi_product ) {
			return 'category';
		}

		// Early return if we have a specific product.
		if ( $product > 0 ) {
			return 'product';
		}

		// Early return if no product or category specified - show archive.
		if ( 0 === $product && 0 === $category ) {
			return 'product_archive';
		}

		// Default to category view.
		return 'category';
	}

	/**
	 * Generic method to fetch terms with optional meta query filters.
	 *
	 * @since 2.3.0
	 *
	 * @param string $taxonomy     Taxonomy to query.
	 * @param array  $query_args   Base arguments for get_terms().
	 * @param array  $meta_query   Optional meta query array.
	 * @return array|\WP_Error    Array of terms or WP_Error on failure.
	 */
	public static function fetch_terms( $taxonomy, $query_args = array(), $meta_query = array() ) {
		$default_args = array(
			'taxonomy'   => $taxonomy,
			'orderby'    => 'slug',
			'order'      => 'ASC',
			'hide_empty' => true,
		);
		$args         = wp_parse_args( $query_args, $default_args );

		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}

		// Only cache if caching is enabled.
		$cache_enabled = ! empty( self::get_cached_option( 'cache' ) );

		if ( $cache_enabled ) {
			// Create a transient-based cache key for non-term-specific queries.
			$transient_key = 'wzkb_fetch_terms_' . md5( wp_json_encode( $args ) );

			// Try to get from transient cache first.
			$cached_terms = get_transient( $transient_key );
			if ( false !== $cached_terms ) {
				return $cached_terms;
			}
		}

		// Fetch terms.
		$terms = get_terms( $args );

		// Cache the results if caching is enabled.
		if ( $cache_enabled && ! is_wp_error( $terms ) ) {
			$cache_expiry = (int) self::get_cached_option( 'cache_expiry', self::DEFAULT_CACHE_TIME );
			set_transient( $transient_key, $terms, $cache_expiry );
		}

		return $terms;
	}

	/**
	 * Render all top-level sections for a given product term ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $product_id Product term ID.
	 * @param array $args       Arguments for display.
	 * @return string           Rendered HTML for sections.
	 */
	public static function render_product_sections( int $product_id, array $args ): string {
		$product = get_term( $product_id, 'wzkb_product' );
		if ( is_wp_error( $product ) || ! $product ) {
			return '<p>' . esc_html__( 'Invalid product ID.', 'knowledgebase' ) . '</p>';
		}

		$result = self::render_product_sections_only( $product_id, $args );

		if ( '' === $result['output'] ) {
			// Fallback to product articles if no sections found.
			$product_articles_output = self::render_product_articles_fallback( $product, $product_id, $args, $result['section_ids'] );

			if ( '' === $product_articles_output ) {
				return '<p>' . esc_html__( 'No sections found for this product.', 'knowledgebase' ) . '</p>';
			}

			return $product_articles_output;
		}

		return $result['output'];
	}

	/**
	 * Render only the sections for a product, without fallback logic.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $product_id Product term ID.
	 * @param array $args       Arguments for display.
	 * @return array           Array with 'output' and 'section_ids' keys.
	 */
	private static function render_product_sections_only( int $product_id, array $args ): array {
		$defaults = array(
			'show_empty_sections' => (int) self::get_cached_option( 'show_empty_sections' ),
			'show_article_count'  => (int) self::get_cached_option( 'show_article_count' ),
			'show_excerpt'        => (int) self::get_cached_option( 'show_excerpt' ),
			'clickable_section'   => (int) self::get_cached_option( 'clickable_section' ),
			'columns'             => (int) self::get_cached_option( 'columns', 1 ),
			'limit'               => (int) self::get_cached_option( 'limit', 5 ),
			'depth'               => -1,
			'is_block'            => 1,
		);
		$args     = wp_parse_args( $args, $defaults );
		$args     = Helpers::sanitize_args( $args );

		// Cache category level to avoid repeated option calls.
		$category_level = (int) self::get_cached_option( 'category_level' );

		$sections = self::fetch_terms(
			'wzkb_category',
			array(
				'parent'     => 0,
				'hide_empty' => empty( $args['show_empty_sections'] ),
			),
			array(
				array(
					'key'     => 'product_id',
					'value'   => $product_id,
					'compare' => '=',
				),
			)
		);

		$section_output   = '';
		$section_term_ids = array();
		if ( ! empty( $sections ) && ! is_wp_error( $sections ) ) {
			if ( 1 === $category_level ) {
				$section_output .= '<div class="section group">';
			}

			foreach ( $sections as $section ) {
				$section_output    .= self::get_knowledge_base_loop( $section->term_id, 1, true, $args );
				$section_term_ids[] = (int) $section->term_id;

				$child_ids = get_term_children( $section->term_id, 'wzkb_category' );
				if ( ! is_wp_error( $child_ids ) && ! empty( $child_ids ) ) {
					$section_term_ids = array_merge( $section_term_ids, array_map( 'intval', $child_ids ) );
				}
			}

			if ( 1 === $category_level ) {
				$section_output .= '</div>';
			}
		}

		return array(
			'output'      => $section_output,
			'section_ids' => array_unique( $section_term_ids ),
		);
	}

	/**
	 * Render articles directly tagged to a product as a fallback when no sections exist.
	 *
	 * @param \WP_Term $product    Product term object.
	 * @param int      $product_id Product term ID.
	 * @param array    $args       Display arguments.
	 * @param array    $section_ids Section term IDs associated with the product.
	 * @return string             HTML output.
	 */
	private static function render_product_articles_fallback( $product, $product_id, $args, $section_ids = array() ) {
		$tax_query = array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'wzkb_product',
				'field'    => 'term_id',
				'terms'    => $product_id,
			),
		);

		if ( ! empty( $section_ids ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wzkb_category',
				'field'    => 'term_id',
				'terms'    => $section_ids,
				'operator' => 'NOT IN',
			);
		}

		$product_articles = new \WP_Query(
			array(
				'post_type'           => 'wz_knowledgebase',
				'posts_per_page'      => -1,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'tax_query'           => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			)
		);

		if ( ! $product_articles->have_posts() ) {
			wp_reset_postdata();
			return '';
		}

		$output  = '<div class="wzkb-product-articles">';
		$output .= self::get_articles_loop( $product, 1, $product_articles, $args );
		$output .= self::get_article_footer( $product, 1, $product_articles, $args );
		$output .= '</div>';

		wp_reset_postdata();

		return $output;
	}

	/**
	 * Creates the knowledge base loop.
	 *
	 * @since 2.3.0
	 *
	 * @param int   $term_id Term ID.
	 * @param int   $level   Level of the loop.
	 * @param bool  $nested  Run recursive loops before closing HTML wrappers.
	 * @param array $args    Parameters array.
	 * @return string Formatted output.
	 */
	public static function get_knowledge_base_loop( int $term_id, int $level, bool $nested = true, array $args = array() ): string {
		// Recursion guard: skip if this term_id has already been rendered.
		if ( isset( $args['visited_term_ids'] ) && in_array( $term_id, $args['visited_term_ids'], true ) ) {
			return '';
		}

		// Create a local copy of args for recursion safety.
		$local_args                     = $args;
		$visited                        = $local_args['visited_term_ids'] ?? array();
		$visited[]                      = $term_id;
		$local_args['visited_term_ids'] = $visited;

		// Special handling for root level (term_id = 0) in single product mode.
		if ( 0 === $term_id && 0 === $level ) {
			$output = '';

			$sections = self::fetch_terms(
				'wzkb_category',
				array(
					'parent'     => 0,
					'hide_empty' => empty( $local_args['show_empty_sections'] ),
				)
			);

			if ( ! empty( $sections ) && ! is_wp_error( $sections ) ) {
				// Add section wrapper if category_level is 2.
				$category_level = (int) self::get_cached_option( 'category_level' );
				if ( 2 === $category_level ) {
					$output .= '<div class="section group">';
				}

				foreach ( $sections as $section ) {
					$output .= self::get_knowledge_base_loop( $section->term_id, 1, true, $local_args );
				}

				if ( 2 === $category_level ) {
					$output .= '</div>';
				}
			}

			return $output;
		}

		$term = get_term( $term_id, 'wzkb_category' );
		if ( is_wp_error( $term ) || ! $term ) {
			/* translators: %s: Term ID */
			return sprintf( __( '%s is not a valid section ID', 'knowledgebase' ), $term_id );
		}

		$output = self::open_section_wrapper( $level, $local_args );

		if ( ! ( 0 === $level && isset( $local_args['skip_top_header'] ) && $local_args['skip_top_header'] ) ) {
			$output .= self::get_article_header( $term, $level, $local_args );
		}
		$output .= self::get_posts_by_term( $term, $level, $local_args );

		$output .= '<div class="wzkb-section-wrapper">';

		// Get Knowledge Base Sections.
		$sections = self::fetch_terms(
			'wzkb_category',
			array(
				'orderby'    => 'slug',
				'parent'     => $term_id,
				'hide_empty' => empty( $local_args['show_empty_sections'] ),
			)
		);

		if ( ! $nested ) {
			$output .= self::close_section_inner();
			$output .= '</div>'; // End wzkb-section.
		}

		if ( ! empty( $sections ) && ! is_wp_error( $sections ) ) {
			foreach ( $sections as $section ) {
				// Check if we've reached the maximum depth limit for nested sections.
				// If depth is set and not -1 (unlimited), and current level exceeds it, skip this section.
				if ( isset( $local_args['depth'] ) && -1 !== (int) $local_args['depth'] && $level >= (int) $local_args['depth'] ) {
					continue;
				}
				$output .= self::get_knowledge_base_loop( $section->term_id, $level + 1, $nested, $local_args );
			}
		}

		if ( $nested ) {
			$output .= self::close_section_inner();
			$output .= '</div>'; // End wzkb-section.
		}

		return $output;
	}

	/**
	 * Open section wrapper HTML.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $level Section level.
	 * @param array $args  Display arguments.
	 * @return string Opening wrapper HTML.
	 */
	private static function open_section_wrapper( $level, $args ) {
		$category_level = (int) self::get_cached_option( 'category_level' );
		$divclasses     = array( 'wzkb-section', 'wzkb-section-level-' . $level );

		if ( 1 === $category_level && 1 === $level ) {
			$divclasses[] = 'col span_1_of_' . $args['columns'];
		} elseif ( 2 === $category_level && 2 === $level ) {
			$divclasses[] = 'col span_1_of_' . $args['columns'];
		}

		return '<div class="' . esc_attr( implode( ' ', $divclasses ) ) . '">';
	}

	/**
	 * Close section inner wrapper HTML.
	 *
	 * @since 3.0.0
	 *
	 * @return string Closing wrapper HTML.
	 */
	private static function close_section_inner() {
		return '</div>'; // End wzkb-section-wrapper.
	}

	/**
	 * Returns query results for a specific term.
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Term $term The Term.
	 * @return \WP_Query Query results for the given term.
	 */
	public static function get_term_posts( $term ): \WP_Query {
		// Get the term children for the current term.
		$termchildren = get_term_children( $term->term_id, 'wzkb_category' );

		// Get all the posts for the current term excluding posts located in its child terms.
		$args = array(
			'posts_per_page'      => -1,
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'tax_query'           => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				'relation' => 'AND',
				array(
					'taxonomy' => 'wzkb_category',
					'field'    => 'id',
					'terms'    => $term->term_id,
				),
				array(
					'taxonomy' => 'wzkb_category',
					'field'    => 'id',
					'terms'    => $termchildren,
					'operator' => 'NOT IN',
				),
			),
		);

		$query         = null;
		$cache_enabled = ! empty( self::get_cached_option( 'cache' ) );
		$meta_key      = null;

		// Support caching to speed up retrieval.
		if ( $cache_enabled ) {
			$meta_key = Cache::get_key( $args );
			$query    = Cache::get( $term->term_id, $meta_key );
		}

		if ( empty( $query ) ) {
			$query = new \WP_Query( $args );

			// Support caching to speed up retrieval.
			if ( $cache_enabled ) {
				Cache::set( $term->term_id, $meta_key, $query );
			}
		}

		return $query;
	}

	/**
	 * Formatted output of posts for a given term.
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Term $term  Current term.
	 * @param int      $level Current level in the recursive loop.
	 * @param array    $args  Parameters array.
	 * @return string HTML string of posts.
	 */
	public static function get_posts_by_term( $term, int $level, array $args ): string {
		$output = '';
		$query  = self::get_term_posts( $term );

		if ( $query->have_posts() ) {
			$output .= self::get_articles_loop( $term, $level, $query, $args );
			$output .= self::get_article_footer( $term, $level, $query, $args );

			wp_reset_postdata();
		}

		return $output;
	}

	/**
	 * Get the article header.
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Term $term  Current term.
	 * @param int      $level Current level in the recursive loop.
	 * @param array    $args  Parameters array.
	 * @return string Formatted header output.
	 */
	public static function get_article_header( $term, int $level, array $args = array() ): string {
		$heading_level = min( 2 + $level, 6 ); // Start at h3, max h6.
		$output        = '<h' . $heading_level . ' class="wzkb-section-name wzkb-section-name-level-' . $level . '">';

		if ( ! empty( $args['clickable_section'] ) ) {
			$output .= '<a href="' . esc_url( get_term_link( $term ) ) . '" title="' . esc_attr( $term->name ) . '">' . esc_html( $term->name ) . '</a>';
		} else {
			$output .= esc_html( $term->name );
		}

		if ( ! empty( $args['show_article_count'] ) ) {
			/* translators: %d: Number of articles within a section. */
			$count_text = sprintf( _n( '%d article', '%d articles', $term->count, 'knowledgebase' ), $term->count );
			$output    .= '<span class="wzkb-section-count" aria-label="' . esc_attr( $count_text ) . '">' . $term->count . '</span>';
		}

		$output .= '</h' . $heading_level . '> ';

		return $output;
	}

	/**
	 * Creates the list of articles for a particular query results object.
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Term  $term  Current term.
	 * @param int       $level Current level in the recursive loop.
	 * @param \WP_Query $query Query results object.
	 * @param array     $args  Parameters array.
	 * @return string Formatted articles list.
	 */
	public static function get_articles_loop( $term, int $level, \WP_Query $query, array $args ): string {
		$limit  = 0;
		$output = '<ul class="wzkb-articles-list term-' . $term->term_id . '">';

		while ( $query->have_posts() ) :
			$query->the_post();

			$output .= '<li class="wzkb-article-name post-' . absint( get_the_ID() ) . '">';
			$output .= '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark" title="' . the_title_attribute( array( 'echo' => false ) ) . '">' . get_the_title() . '</a>';

			if ( ! empty( $args['show_excerpt'] ) ) {
				$output .= '<div class="wzkb-article-excerpt post-' . absint( get_the_ID() ) . '">' . wp_kses_post( get_the_excerpt() ) . '</div>';
			}

			$output .= '</li>';

			++$limit;

			if ( $args['limit'] > 0 && $limit >= $args['limit'] && ! is_tax( 'wzkb_category', $term->term_id ) ) {
				break;
			}
		endwhile;

		$output .= '</ul>';

		return $output;
	}

	/**
	 * Footer of the articles list.
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Term  $term  Current term.
	 * @param int       $level Current level in the recursive loop.
	 * @param \WP_Query $query Query results object.
	 * @param array     $args  Parameters array.
	 * @return string Formatted footer output.
	 */
	public static function get_article_footer( $term, int $level, \WP_Query $query, array $args ): string {
		$output = '';

		if ( $args['limit'] > 0 && $query->found_posts > $args['limit'] && ! is_tax( 'wzkb_category', $term->term_id ) ) {
			$output .= sprintf(
				'<p class="wzkb-article-footer">%1$s<a href="%2$s" title="%3$s">%3$s</a> &raquo;</p>',
				esc_html__( 'Read more articles in ', 'knowledgebase' ),
				esc_url( get_term_link( $term ) ),
				esc_html( $term->name )
			);
		}

		return $output;
	}

	/**
	 * Get a hierarchical list of WZ Knowledge Base sections.
	 *
	 * @since 2.3.0
	 *
	 * @param int   $term_id Term ID.
	 * @param int   $level   Level of the loop.
	 * @param array $args    Array of arguments.
	 * @return string HTML output with the categories.
	 */
	public static function get_categories_list( $term_id, $level = 0, $args = array() ) {
		$defaults = array(
			'depth'               => 0,  // Depth of nesting.
			'before_li_item'      => '', // Before list item - just after <li>.
			'after_li_item'       => '', // Before list item - just before </li>.
			'show_empty_sections' => (int) self::get_cached_option( 'show_empty_sections' ),
		);

		$args = wp_parse_args( $args, $defaults );
		$args = Helpers::sanitize_args( $args );

		// Get Knowledge Base Sections.
		$sections = self::fetch_terms(
			'wzkb_category',
			array(
				'orderby'    => 'slug',
				'parent'     => $term_id,
				'hide_empty' => empty( $args['show_empty_sections'] ),
			)
		);

		$output = '';

		if ( ! empty( $sections ) && ! is_wp_error( $sections ) ) {
			$output .= '<ul class="wzkb_terms_widget wzkb_term_' . $term_id . ' wzkb_ul_level_' . $level . '">';
			++$level;

			foreach ( $sections as $term ) {
				$term_link = get_term_link( $term );

				// If there was an error, continue to the next term.
				if ( is_wp_error( $term_link ) ) {
					continue;
				}

				$output .= '<li class="wzkb_cat_' . $term->term_id . '">' . $args['before_li_item'];
				$output .= '<a href="' . esc_url( $term_link ) . '" title="' . esc_attr( $term->name ) . '">' . esc_html( $term->name ) . '</a>';
				if ( -1 === (int) $args['depth'] || $level < (int) $args['depth'] ) {
					$output .= self::get_categories_list( $term->term_id, $level, $args );
				}
				$output .= $args['after_li_item'] . '</li>';
			}

			$output .= '</ul>';
		}

		return $output;
	}

	/**
	 * Get top-level sections for a given product.
	 *
	 * @since 2.3.0
	 *
	 * @param int $product_id Product term ID.
	 * @return array Array of section term objects.
	 */
	public static function get_sections_by_product( $product_id ) {
		global $wpdb;

		if ( empty( $product_id ) ) {
			return array();
		}

		$section_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT term_id FROM $wpdb->termmeta WHERE meta_key = %s AND meta_value = %d",
				'product_id',
				$product_id
			)
		);

		if ( empty( $section_ids ) ) {
			return array();
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'wzkb_category',
				'hide_empty' => false,
				'parent'     => 0,
				'include'    => $section_ids,
			)
		);

		return is_array( $terms ) ? $terms : array();
	}

	/**
	 * Get a hierarchical list of sections for a given product.
	 *
	 * @since 2.3.0
	 *
	 * @param int   $product_id Product term ID.
	 * @param array $args       Arguments for display.
	 * @param int   $level      Level of the loop (for indentation/nesting).
	 * @return string           HTML output.
	 */
	public static function get_product_sections_list( $product_id, $args = array(), $level = 0 ) {
		$defaults = array(
			'depth'               => 0,  // Depth of nesting.
			'before_li_item'      => '', // Before list item - just after <li>.
			'after_li_item'       => '', // Before list item - just before </li>.
			'show_empty_sections' => (int) self::get_cached_option( 'show_empty_sections' ),
		);

		$args = wp_parse_args( $args, $defaults );
		$args = Helpers::sanitize_args( $args );

		$sections = self::get_sections_by_product( $product_id );
		$output   = '';

		if ( ! empty( $sections ) ) {
			$output .= '<ul class="wzkb_product_sections wzkb_ul_level_' . (int) $level . '">';
			++$level;
			foreach ( $sections as $section ) {
				$output .= '<li class="wzkb_cat_' . $section->term_id . '">' . $args['before_li_item'];
				$output .= '<a href="' . esc_url( get_term_link( $section ) ) . '">' . esc_html( $section->name ) . '</a>';
				if ( -1 === (int) $args['depth'] || $level < (int) $args['depth'] ) {
					$output .= self::get_categories_list( $section->term_id, $level, $args );
				}
				$output .= $args['after_li_item'] . '</li>';
			}
			$output .= '</ul>';
		}

		return $output;
	}

	/**
	 * Get a hierarchical list of sections, either for a specific term or a full products/sections tree.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $term_id Term ID (0 for full tree).
	 * @param array $args    Display arguments.
	 * @return string        HTML output.
	 */
	public static function get_sections_tree( int $term_id, array $args = array() ): string {
		if ( $term_id > 0 ) {
			return wzkb_categories_list( $term_id, 0, $args );
		}

		$is_multi_product = self::get_cached_option( 'multi_product' );

		if ( $is_multi_product ) {
			$products = self::fetch_terms( 'wzkb_product' );
			if ( ! empty( $products ) && ! is_wp_error( $products ) ) {
				$output                      = '';
				$args['show_empty_sections'] = $args['show_empty_sections'] ?? (int) self::get_cached_option( 'show_empty_sections' );
				foreach ( $products as $product ) {
					$output .= '<ul class="wzkb-products-list"><li class="wzkb-product-item"><a href="' . esc_url( get_term_link( $product ) ) . '" class="wzkb-product-link">' . esc_html( $product->name ) . '</a>';
					$output .= self::get_product_sections_list( $product->term_id, $args );
					$output .= '</li></ul>';
				}
				return $output;
			}
		}

		return wzkb_categories_list( 0, 0, $args );
	}

	/**
	 * Render the category-based view of the knowledge base.
	 *
	 * @param int   $category Category ID to display, -1 for current, 0 for all.
	 * @param array $args     Display arguments.
	 * @return string HTML output.
	 */
	private static function render_category_view( $category, $args ) {
		if ( -1 === $category ) {
			$term = get_queried_object();
			if ( isset( $term->term_id ) ) {
				$category = $term->term_id;
			}
		}

		$level          = $category > 0 ? 1 : 0;
		$term_id        = $category > 0 ? $category : 0;
		$nested_wrapper = isset( $args['nested_wrapper'] ) ? $args['nested_wrapper'] : true;

		// Create a local copy of args for recursion safety.
		$local_args = $args;
		return self::get_knowledge_base_loop( $term_id, $level, $nested_wrapper, $local_args );
	}

	/**
	 * Normalize arguments for layout context.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Arguments to normalize.
	 * @return array Normalized arguments.
	 */
	public static function normalize_arguments( $args ) {
		// Ensure all defaults are present.
		$args = wp_parse_args(
			$args,
			array(
				'category'    => 0,
				'product'     => 0,
				'columns'     => self::get_cached_option( 'columns' ),
				'extra_class' => '',
			)
		);

		// Type casting for safety.
		$args['category']    = (int) $args['category'];
		$args['product']     = (int) $args['product'];
		$args['columns']     = (int) $args['columns'];
		$args['extra_class'] = (string) $args['extra_class'];

		// Force single column for single sections via class and ensure extra_class is set.
		if ( 0 !== $args['product'] ) {
			$args['extra_class'] = trim( $args['extra_class'] . ' wzkb-product-archive' );
		} elseif ( 0 !== $args['category'] ) {
			$args['extra_class'] = trim( $args['extra_class'] . ' wzkb-category-archive' );
			$args['columns']     = 1;
		}

		return $args;
	}

	/**
	 * Build CSS classes for a wrapper based on arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Arguments containing class-related flags.
	 * @return string     Space-separated string of CSS classes.
	 */
	private static function build_wrapper_classes( $args ) {
		$classes   = array();
		$classes[] = $args['extra_class'];
		$classes[] = $args['is_shortcode'] ? 'wzkb-shortcode' : '';
		$classes[] = $args['is_block'] ? 'wzkb-block' : '';
		if ( isset( $args['product_archive_layout'] ) && 'grid' === $args['product_archive_layout'] ) {
			$classes[] = 'wzkb-product-grid-layout';
		}

		// Filter out empty values to prevent double spaces.
		$classes = array_filter( $classes );

		/**
		 * Filter the classes added to the div wrapper of the Knowledge Base.
		 *
		 * @since 2.0.0
		 *
		 * @param string $div_classes String with the classes of the div wrapper.
		 */
		return apply_filters( 'wzkb_div_class', implode( ' ', $classes ) );
	}

	/**
	 * Render product archive view.
	 *
	 * @since 3.0.0
	 *
	 * @param array $products Array of product terms.
	 * @param array $args     Display arguments.
	 * @return string Rendered HTML.
	 */
	private static function render_product_archive( $products, $args ) {
		$output = '';

		if ( 'grid' === $args['product_archive_layout'] ) {
			$output .= self::render_product_archive_grid( $products );
		} else {
			foreach ( $products as $product_term ) {
				$output .= '<div class="wzkb-product wzkb-product-' . esc_attr( $product_term->term_id ) . '">';
				$output .= '<div class="wzkb-product-header">';

				// Display product title as clickable if clickable_section is enabled.
				$output .= '<h2 class="wzkb-product-title">';
				if ( ! empty( $args['clickable_section'] ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_term_link is properly escaped below.
					$output .= '<a href="' . esc_url( get_term_link( $product_term ) ) . '" title="' . esc_attr( $product_term->name ) . '">' . esc_html( $product_term->name ) . '</a>';
				} else {
					$output .= esc_html( $product_term->name );
				}
				$output .= '</h2>';

				if ( $product_term->description ) {
					$output .= '<div class="wzkb-product-description">' . esc_html( $product_term->description ) . '</div>';
				}

				$output .= '</div>'; // .wzkb-product-header

				$output .= '<div class="wzkb-product-grid">';
				$output .= self::render_product_sections( $product_term->term_id, $args );
				$output .= '</div>'; // .wzkb-product-grid
				$output .= '</div>';
			}
		}

		return $output;
	}

	/**
	 * Render products as a grid of cards.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Term[] $products Array of product terms.
	 * @return string
	 */
	private static function render_product_archive_grid( $products ) {
		if ( empty( $products ) ) {
			return '';
		}

		$output = '<div class="wzkb-product-grid-wrapper"><div class="wzkb-product-grid" role="list">';

		foreach ( $products as $product_term ) {
			$term_link = get_term_link( $product_term );

			if ( is_wp_error( $term_link ) ) {
				continue;
			}

			$description = ! empty( $product_term->description ) ? wp_trim_words( $product_term->description, 35, '&hellip;' ) : '';

			$term_class = 'wzkb-product-' . sanitize_html_class( (string) $product_term->term_id );

			$output .= '<article class="wzkb-product-card ' . esc_attr( $term_class ) . '" role="listitem">';
			$output .= '<a class="wzkb-product-link" href="' . esc_url( $term_link ) . '">';
			$output .= '<h2 class="wzkb-product-title">' . esc_html( $product_term->name ) . '</h2>';

			if ( $description ) {
				$output .= '<p class="wzkb-product-description">' . esc_html( $description ) . '</p>';
			}

			$output .= '<span class="wzkb-product-card-cta">' . esc_html__( 'View articles', 'knowledgebase' ) . '</span>';
			$output .= '</a>';
			$output .= '</article>';
		}

		$output .= '</div></div>';

		/**
		 * Filter the rendered product grid HTML.
		 *
		 * @since 3.0.0
		 *
		 * @param string   $output   Markup for the grid.
		 * @param \WP_Term[] $products List of product terms.
		 */
		return apply_filters( 'wzkb_product_archive_grid', $output, $products );
	}
}
