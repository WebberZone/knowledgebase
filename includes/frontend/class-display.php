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
	 * Constructor class.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		new Breadcrumbs();
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
			'category'            => 0,
			'product'             => 0,
			'is_shortcode'        => false,
			'is_block'            => false,
			'extra_class'         => '',
			'show_article_count'  => \wzkb_get_option( 'show_article_count' ),
			'show_excerpt'        => \wzkb_get_option( 'show_excerpt' ),
			'clickable_section'   => \wzkb_get_option( 'clickable_section' ),
			'show_empty_sections' => \wzkb_get_option( 'show_empty_sections' ),
			'limit'               => \wzkb_get_option( 'limit' ),
			'columns'             => \wzkb_get_option( 'columns' ),
		);

		$args = wp_parse_args( $args, $defaults );
		$args = Helpers::sanitize_args( $args );

		// Set defaults if variables are empty.
		$args['limit']   = ( ! empty( absint( $args['limit'] ) ) ) ? absint( $args['limit'] ) : \wzkb_get_option( 'limit' );
		$args['columns'] = ( ! empty( absint( $args['columns'] ) ) ) ? absint( $args['columns'] ) : \wzkb_get_option( 'columns' );

		// Set default classes.
		$div_classes = self::build_wrapper_classes( $args );

		$output = '<div class="wzkb ' . esc_attr( $div_classes ) . '">';

		$product          = intval( $args['product'] );
		$category         = intval( $args['category'] );
		$is_multi_product = \wzkb_get_option( 'multi_product' );

		// Harmonize: If product = -1, auto-detect current product term (like category does).
		if ( -1 === $product ) {
			$queried_object = get_queried_object();
			if ( isset( $queried_object->term_id ) && isset( $queried_object->taxonomy ) && 'wzkb_product' === $queried_object->taxonomy ) {
				$product = intval( $queried_object->term_id );
			}
		}

		if ( $is_multi_product ) {
			if ( $product > 0 ) {
				// Product-specific view in multi-product mode.
				$output .= self::render_product_sections( $product, $args );
			} elseif ( 0 === $product && 0 === $category ) {
				// Products archive view in multi-product mode.
				$products = self::fetch_terms( 'wzkb_product' );
				if ( ! empty( $products ) && ! is_wp_error( $products ) ) {
					foreach ( $products as $product_term ) {
						$output .= '<div class="wzkb-product wzkb-product-' . esc_attr( $product_term->term_id ) . '">';
						// Display product title as clickable if clickable_section is enabled.
						$output .= '<h2 class="wzkb-product-title">';
						if ( $args['clickable_section'] ) {
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_term_link is properly escaped below.
							$output .= '<a href="' . esc_url( get_term_link( $product_term ) ) . '" title="' . esc_attr( $product_term->name ) . '">' . esc_html( $product_term->name ) . '</a>';
						} else {
							$output .= esc_html( $product_term->name );
						}
						$output .= '</h2>';
						if ( $product_term->description ) {
							$output .= '<div class="wzkb-product-description">' . esc_html( $product_term->description ) . '</div>';
						}
						$output .= self::render_product_sections( $product_term->term_id, $args );
						$output .= '</div>';
					}
				} else {
					$output .= '<div class="wzkb-no-products">' . esc_html__( 'No products found.', 'knowledgebase' ) . '</div>';
				}
			} else {
				// Fallback to category view even in multi-product mode.
				$output .= self::render_category_view( $category, $args );
			}
		} else {
			// Single product mode (default): Simple category-based structure.
			$output .= self::render_category_view( $category, $args );
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
	 * Generic method to fetch terms with optional meta query filters.
	 *
	 * @param string $taxonomy     Taxonomy to query.
	 * @param array  $query_args   Base arguments for get_terms().
	 * @param array  $meta_query   Optional meta query array.
	 * @return array|\WP_Error    Array of terms or WP_Error on failure.
	 */
	public static function fetch_terms( $taxonomy, $query_args = array(), $meta_query = array() ) {
		$default_args = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'slug',
			'order'      => 'ASC',
		);
		$args         = wp_parse_args( $query_args, $default_args );

		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}

		return get_terms( $args );
	}

	/**
	 * Render all top-level sections for a given product term ID.
	 *
	 * @param int   $product_id Product term ID.
	 * @param array $args       Arguments for display.
	 * @return string           Rendered HTML for sections.
	 */
	public static function render_product_sections( $product_id, $args ) {
		$product = get_term( $product_id, 'wzkb_product' );
		if ( is_wp_error( $product ) || ! $product ) {
			return '<p>' . esc_html__( 'Invalid product ID.', 'knowledgebase' ) . '</p>';
		}

		$sections = self::fetch_terms(
			'wzkb_category',
			array(
				'parent'     => 0,
				'hide_empty' => $args['show_empty_sections'] ? 0 : 1,
			),
			array(
				array(
					'key'     => 'product_id',
					'value'   => $product_id,
					'compare' => '=',
				),
			)
		);

		$output = '';
		if ( ! empty( $sections ) && ! is_wp_error( $sections ) ) {
			// Add section wrapper if category_level is 1.
			$category_level = (int) \wzkb_get_option( 'category_level' );
			if ( 1 === $category_level ) {
				$output .= '<div class="section group">';
			}

			foreach ( $sections as $section ) {
				$output .= self::get_knowledge_base_loop( $section->term_id, 1, true, $args );
			}

			if ( 1 === $category_level ) {
				$output .= '</div>';
			}
		} else {
			$output .= '<p>' . esc_html__( 'No sections found for this product.', 'knowledgebase' ) . '</p>';
		}

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
	public static function get_knowledge_base_loop( $term_id, $level, $nested = true, $args = array() ) {
		// Recursion guard: skip if this term_id has already been rendered.
		if ( isset( $args['visited_term_ids'] ) && in_array( $term_id, $args['visited_term_ids'], true ) ) {
			return '';
		}
		// Add this term_id to visited_term_ids for recursion tracking.
		if ( ! isset( $args['visited_term_ids'] ) ) {
			$args['visited_term_ids'] = array();
		}
		$args['visited_term_ids'][] = $term_id;

		// Special handling for root level (term_id = 0) in single product mode.
		if ( 0 === $term_id && 0 === $level ) {
			$output = '';

			// Get top-level sections.
			$sections = self::fetch_terms(
				'wzkb_category',
				array(
					'parent'     => 0,
					'hide_empty' => $args['show_empty_sections'] ? 0 : 1,
				)
			);

			if ( ! empty( $sections ) && ! is_wp_error( $sections ) ) {
				// Add section wrapper if category_level is 2.
				$category_level = (int) \wzkb_get_option( 'category_level' );
				if ( 2 === $category_level ) {
					$output .= '<div class="section group">';
				}

				foreach ( $sections as $section ) {
					$output .= self::get_knowledge_base_loop( $section->term_id, 1, true, $args );
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
			return sprintf( __( '%s is not enter a valid section ID', 'knowledgebase' ), $term_id );
		}

		$category_level = (int) \wzkb_get_option( 'category_level' );
		$divclasses     = array( 'wzkb-section', 'wzkb-section-level-' . $level );

		if ( 1 === $category_level && 1 === $level ) {
			$divclasses[] = 'col span_1_of_' . $args['columns'];
		} elseif ( 2 === $category_level && 2 === $level ) {
			$divclasses[] = 'col span_1_of_' . $args['columns'];
		}

		$output = '<div class="' . esc_attr( implode( ' ', $divclasses ) ) . '">';

		$output .= self::get_article_header( $term, $level, $args );
		$output .= self::get_posts_by_term( $term, $level, $args );

		$output .= '<div class="wzkb-section-wrapper">';

		// Get Knowledge Base Sections.
		$sections = self::fetch_terms(
			'wzkb_category',
			array(
				'orderby'    => 'slug',
				'hide_empty' => $args['show_empty_sections'] ? 0 : 1,
				'parent'     => $term_id,
			)
		);

		if ( ! $nested ) {
			$output .= '</div>'; // End wzkb-section-wrapper.
			$output .= '</div>'; // End wzkb-section.
		}

		if ( ! empty( $sections ) && ! is_wp_error( $sections ) ) {
			++$level;

			foreach ( $sections as $section ) {
				$output .= self::get_knowledge_base_loop( $section->term_id, $level, $nested, $args );
			}
		}

		if ( $nested ) {
			$output .= '</div>'; // End wzkb-section-wrapper.
			$output .= '</div>'; // End wzkb-section.
		}

		return $output;
	}

	/**
	 * Returns query results for a specific term.
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Term $term The Term.
	 * @return \WP_Query Query results for the given term.
	 */
	public static function get_term_posts( $term ) {
		// Get the term children for the current term.
		$termchildren = get_term_children( $term->term_id, 'wzkb_category' );

		// Get all the posts for the current term excluding posts located in its child terms.
		$args = array(
			'posts_per_page' => -1,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
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

		// Support caching to speed up retrieval.
		if ( ! empty( \wzkb_get_option( 'cache' ) ) ) {
			$meta_key = Cache::get_key( $args );
			$query    = get_term_meta( $term->term_id, $meta_key, true );
		}

		if ( empty( $query ) ) {
			$query = new \WP_Query( $args );
		}

		// Support caching to speed up retrieval.
		if ( ! empty( \wzkb_get_option( 'cache' ) ) ) {
			add_term_meta( $term->term_id, $meta_key, $query, true );
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
	 * @return string Formatted output of posts for a given term.
	 */
	public static function get_posts_by_term( $term, $level, $args = array() ) {
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
	 * Header of the articles list.
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Term $term  Current term.
	 * @param int      $level Current level in the recursive loop.
	 * @param array    $args  Parameters array.
	 * @return string Formatted header output.
	 */
	public static function get_article_header( $term, $level, $args = array() ) {
		$output = '<h3 class="wzkb-section-name wzkb-section-name-level-' . $level . '">';

		if ( $args['clickable_section'] ) {
			$output .= '<a href="' . get_term_link( $term ) . '" title="' . $term->name . '" >' . $term->name . '</a>';
		} else {
			$output .= $term->name;
		}

		if ( $level >= (int) \wzkb_get_option( 'category_level' ) && $args['show_article_count'] ) {
			$output .= '<div class="wzkb-section-count">' . $term->count . '</div>';
		}

		$output .= '</h3> ';

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
	public static function get_articles_loop( $term, $level, $query, $args = array() ) {
		$limit  = 0;
		$output = '<ul class="wzkb-articles-list term-' . $term->term_id . '">';

		while ( $query->have_posts() ) :
			$query->the_post();

			$output .= '<li class="wzkb-article-name post-' . get_the_ID() . '">';
			$output .= '<a href="' . get_permalink() . '" rel="bookmark" title="' . get_the_title() . '">' . get_the_title() . '</a>';

			if ( $args['show_excerpt'] ) {
				$output .= '<div class="wzkb-article-excerpt post-' . get_the_ID() . '">' . get_the_excerpt() . '</div>';
			}

			$output .= '</li>';

			++$limit;

			if ( $limit >= $args['limit'] && ! is_tax( 'wzkb_category', $term->term_id ) ) {
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
	public static function get_article_footer( $term, $level, $query, $args = array() ) {
		$output = '';

		if ( $query->found_posts > $args['limit'] && ! is_tax( 'wzkb_category', $term->term_id ) ) {
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
			'depth'          => 0,  // Depth of nesting.
			'before_li_item' => '', // Before list item - just after <li>.
			'after_li_item'  => '', // Before list item - just before </li>.
		);

		$args = wp_parse_args( $args, $defaults );
		$args = Helpers::sanitize_args( $args );

		// Get Knowledge Base Sections.
		$sections = self::fetch_terms(
			'wzkb_category',
			array(
				'orderby'    => 'slug',
				'hide_empty' => \wzkb_get_option( 'show_empty_sections' ) ? 0 : 1,
				'parent'     => $term_id,
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
				$output .= self::get_categories_list( $term->term_id, $level, $args );
				$output .= $args['after_li_item'] . '</li>';

				// Exit the loop if we are at the depth.
				if ( 0 < $args['depth'] && $level >= $args['depth'] ) {
					break;
				}
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
		$sections = self::get_sections_by_product( $product_id );
		$output   = '';

		if ( ! empty( $sections ) ) {
			$output .= '<ul class="wzkb_product_sections wzkb_ul_level_' . (int) $level . '">';
			++$level;
			foreach ( $sections as $section ) {
				$output .= '<li>';
				$output .= '<a href="' . esc_url( get_term_link( $section ) ) . '">' . esc_html( $section->name ) . '</a>';
				$output .= self::get_categories_list( $section->term_id, $level, $args );
				$output .= '</li>';
			}
			$output .= '</ul>';
		}

		return $output;
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

		return self::get_knowledge_base_loop( $term_id, $level, $nested_wrapper, $args );
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
		/**
		 * Filter the classes added to the div wrapper of the Knowledge Base.
		 *
		 * @since 2.0.0
		 *
		 * @param string $div_classes String with the classes of the div wrapper.
		 */
		return apply_filters( 'wzkb_div_class', implode( ' ', $classes ) );
	}
}
