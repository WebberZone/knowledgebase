<?php
/**
 * Display module
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

use WebberZone\Knowledge_Base\Util\Cache;

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

		// Set defaults if variables are empty.
		$args['limit']   = ( ! empty( absint( $args['limit'] ) ) ) ? absint( $args['limit'] ) : \wzkb_get_option( 'limit' );
		$args['columns'] = ( ! empty( absint( $args['columns'] ) ) ) ? absint( $args['columns'] ) : \wzkb_get_option( 'columns' );

		// Set default classes.
		$classes     = array();
		$classes[]   = $args['extra_class'];
		$classes[]   = $args['is_shortcode'] ? 'wzkb_shortcode' : '';
		$classes[]   = $args['is_block'] ? 'wzkb_block' : '';
		$div_classes = implode( ' ', $classes );

		/**
		 * Filter the classes added to the div wrapper of the Knowledge Base.
		 *
		 * @since 2.0.0
		 *
		 * @param string $div_classes String with the classes of the div wrapper.
		 */
		$div_classes = apply_filters( 'wzkb_div_class', $div_classes );

		$output = '<div class="wzkb ' . esc_attr( $div_classes ) . '">';

		// Are we trying to display a category?
		$category = intval( $args['category'] );

		// If $category = -1, then get the current term object and set the category to the term ID.
		if ( -1 === $category ) {
			$term = get_queried_object();
			if ( isset( $term->term_id ) ) {
				$category = $term->term_id;
			}
		}

		$level          = $category > 0 ? 1 : 0;
		$term_id        = $category > 0 ? $category : 0;
		$nested_wrapper = isset( $args['nested_wrapper'] ) ? $args['nested_wrapper'] : true;

		$output .= self::get_knowledge_base_loop( $term_id, $level, $nested_wrapper, $args );

		$output .= '</div>'; // End wzkb_section.
		$output .= '<div class="wzkb_clear"></div>';

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
		$divclasses     = array( 'wzkb_section', 'wzkb-section-level-' . $level );
		$category_level = (int) \wzkb_get_option( 'category_level' );

		if ( ( $category_level - 1 ) === $level ) {
			$divclasses[] = 'section group';
		} elseif ( $category_level === $level ) {
			$divclasses[] = 'col span_1_of_' . $args['columns'];
		}

		/**
		 * Filter to add more classes if needed.
		 *
		 * @since 1.1.0
		 *
		 * @param array $divclasses Current array of classes.
		 * @param int   $level      Level of the loop.
		 * @param int   $term_id    Term ID.
		 */
		$divclasses = apply_filters( 'wzkb_loop_div_class', $divclasses, $level, $term_id );

		$output = '<div class="' . implode( ' ', $divclasses ) . '">';

		$term = get_term( $term_id, 'wzkb_category' );

		if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
			$output .= self::get_article_header( $term, $level, $args );
			$output .= self::get_posts_by_term( $term, $level, $args );
		} elseif ( $level > 0 ) {
			/* translators: Section ID. */
			return sprintf( __( '%s is not enter a valid section ID', 'knowledgebase' ), $term_id );
		}

		$output .= '<div class="wzkb_section_wrapper">';

		// Get Knowledge Base Sections.
		$sections = get_terms(
			array(
				'taxonomy'   => 'wzkb_category',
				'orderby'    => 'slug',
				'hide_empty' => $args['show_empty_sections'] ? 0 : 1,
				'parent'     => $term_id,
			)
		);

		if ( ! $nested ) {
			$output .= '</div>'; // End wzkb_section_wrapper.
			$output .= '</div>'; // End wzkb_section.
		}

		if ( ! empty( $sections ) && ! is_wp_error( $sections ) ) {
			++$level;

			foreach ( $sections as $section ) {
				$output .= self::get_knowledge_base_loop( $section->term_id, $level, $nested, $args );
			}
		}

		if ( $nested ) {
			$output .= '</div>'; // End wzkb_section_wrapper.
			$output .= '</div>'; // End wzkb_section.
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
		$output = '<h3 class="wzkb_section_name wzkb-section-name-level-' . $level . '">';

		if ( $args['clickable_section'] ) {
			$output .= '<a href="' . get_term_link( $term ) . '" title="' . $term->name . '" >' . $term->name . '</a>';
		} else {
			$output .= $term->name;
		}

		if ( $level >= (int) \wzkb_get_option( 'category_level' ) && $args['show_article_count'] ) {
			$output .= '<div class="wzkb_section_count">' . $term->count . '</div>';
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

		// Get Knowledge Base Sections.
		$sections = get_terms(
			array(
				'taxonomy'   => 'wzkb_category',
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
}
