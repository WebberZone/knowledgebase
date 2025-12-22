<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

use WP_Post;
use WebberZone\Knowledge_Base\Util\Helpers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Class to handle related articles functionality.
 *
 * @since 2.3.0
 */
class Related {

	/**
	 * Constructor
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
	}

	/**
	 * Get related articles query.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args {
	 *     Optional. Array of parameters.
	 *
	 *     @type int          $numberposts Total number of posts to retrieve. Is an alias of $posts_per_page in WP_Query. Accepts -1 for all. Default 5.
	 *     @type WP_Post      $post        Post ID or WP_Post object. Default current post.
	 *     @type string|int[] $exclude     Post IDs to exclude. Can be in CSV format or an array.
	 * }
	 * @return \WP_Query Related articles query object.
	 */
	public static function get_related_articles_query( $args = array() ) {
		$defaults = array(
			'numberposts' => 5,
			'post'        => get_post(),
			'exclude'     => array(),
		);

		// Parse incomming $args into an array and merge it with $defaults.
		$args = wp_parse_args( $args, $defaults );
		$args = Helpers::sanitize_args( $args );

		// Assign post to a separate variable for easy processing.
		$post = $args['post'] instanceof WP_Post ? $args['post'] : get_post( $args['post'] );

		if ( ! $post instanceof WP_Post ) {
			return new \WP_Query(
				array(
					'post_type'      => 'wz_knowledgebase',
					'posts_per_page' => 0,
					'no_found_rows'  => true,
				)
			);
		}

		$exclude = array( (int) $post->ID );
		if ( ! empty( $args['exclude'] ) ) {
			$exclude = array_merge( $exclude, wp_parse_id_list( $args['exclude'] ) );
		}
		$exclude = array_unique( array_map( 'intval', $exclude ) );

		$categories = get_the_terms( $post, 'wzkb_category' );
		$tags       = get_the_terms( $post, 'wzkb_tag' );

		if ( empty( $categories ) ) {
			$categories = array();
		}
		if ( empty( $tags ) ) {
			$tags = array();
		}

		$categories_list = wp_list_pluck( $categories, 'slug' );
		$tags_list       = wp_list_pluck( $tags, 'slug' );
		$category_ids    = wp_list_pluck( $categories, 'term_id' );
		$tag_ids         = wp_list_pluck( $tags, 'term_id' );

		$tax_query = array();

		if ( ! empty( $categories_list ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wzkb_category',
				'field'    => 'slug',
				'terms'    => $categories_list,
			);
		}

		if ( ! empty( $tags_list ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wzkb_tag',
				'field'    => 'slug',
				'terms'    => $tags_list,
			);
		}

		$related_args = array(
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'post_type'           => 'wz_knowledgebase',
			'posts_per_page'      => $args['numberposts'],
			'post_status'         => 'publish',
			'post__not_in'        => $exclude,
			'orderby'             => 'date',
			'order'               => 'DESC',
		);

		$has_context = ! empty( $tax_query );

		if ( ! empty( $tax_query ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Tax query is necessary to determine contextual related content.
			$related_args['tax_query'] = ( count( $tax_query ) > 1 )
				? array_merge( array( 'relation' => 'OR' ), $tax_query )
				: $tax_query;
		} else {
			/**
			 * Filters the fallback query arguments used when no taxonomy context exists.
			 *
			 * @since 3.0.0
			 *
			 * @param array $related_args Default query arguments (latest articles).
			 * @param array $args         Original arguments passed to get_related_articles_query().
			 */
			$related_args = apply_filters( 'wzkb_related_articles_fallback_args', $related_args, $args );
		}

		/**
		 * Filters the related articles arguments before it is passed to WP_Query.
		 *
		 * @since 2.1.0
		 *
		 * @param array $related_args WP_Query arguments.
		 * @param array $args         Parameters passed to function and merged with defaults.
		 */
		$related_args = apply_filters( 'wzkb_related_articles_query_args', $related_args, $args );

		$cache_group = 'wzkb_related_articles';
		$cache_salt  = wp_json_encode( $related_args );
		if ( false === $cache_salt ) {
			$cache_salt = serialize( $related_args ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		}
		$last_changed = function_exists( 'wp_cache_get_last_changed' ) ? wp_cache_get_last_changed( 'posts' ) : microtime();
		$cache_key    = 'wzkb_related_' . md5( $cache_salt ) . ':' . $last_changed;
		$cached_query = wp_cache_get( $cache_key, $cache_group );

		if ( false !== $cached_query ) {
			return $cached_query;
		}

		$query = new \WP_Query( $related_args );

		if ( $has_context ) {
			self::sort_query_by_relevance( $query, $category_ids, $tag_ids, $args );
		}

		/**
		 * Filters the cache TTL for related article queries.
		 *
		 * @since 3.0.0
		 *
		 * @param int   $ttl          Cache duration in seconds.
		 * @param array $related_args Arguments passed to WP_Query.
		 * @param array $args         Original arguments passed to get_related_articles_query().
		 */
		$cache_ttl = apply_filters( 'wzkb_related_articles_cache_ttl', HOUR_IN_SECONDS, $related_args, $args );
		wp_cache_set( $cache_key, $query, $cache_group, absint( $cache_ttl ) );

		return $query;
	}

	/**
	 * Get related knowledge base articles.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args {
	 *     Optional. Array of parameters.
	 *
	 *     @type int          $numberposts  Total number of posts to retrieve. Is an alias of $posts_per_page in WP_Query. Accepts -1 for all. Default 5.
	 *     @type WP_Post      $post         Post ID or WP_Post object. Default current post.
	 *     @type string|int[] $exclude      Post IDs to exclude. Can be in CSV format or an array.
	 *     @type bool         $show_thumb   Show thumbnail? Default true.
	 *     @type bool         $show_excerpt Show excerpt? Falls back to first 55 words of post content if no excerpt. Default false.
	 *     @type bool         $show_date    Show date? Default true.
	 *     @type string       $title        Title text. Can be pre-formatted HTML (for backward compatibility) or plain text. Default is Related Articles wrapped in H3 tag.
	 *     @type string       $heading_tag  HTML heading tag (h2, h3, h4, h5, h6). When provided, title is treated as plain text and wrapped in this tag. Default empty (uses title as-is).
	 *     @type string       $thumb_size   Thumbnail size. Default 'thumbnail'.
	 * }
	 * @return string Related knowledge base articles HTML, or empty string if no posts found.
	 */
	public static function get_related_articles( $args = array() ) {
		$defaults = array(
			'numberposts'  => 5,
			'post'         => get_post(),
			'exclude'      => array(),
			'show_thumb'   => true,
			'show_excerpt' => false,
			'show_date'    => true,
			'title'        => '<h3>' . __( 'Related Articles', 'knowledgebase' ) . '</h3>',
			'heading_tag'  => '',
			'thumb_size'   => 'thumbnail',
		);

		// Parse incomming $args into an array and merge it with $defaults.
		$args = wp_parse_args( $args, $defaults );

		$query = self::get_related_articles_query( $args );

		$output = '';

		if ( $query->have_posts() ) {
			$date_format = get_option( 'date_format' );
			$date_format = $date_format ? $date_format : 'F j, Y';

			// If heading_tag is provided, use it to build the title. Otherwise use the title as-is for backward compatibility.
			if ( ! empty( $args['heading_tag'] ) ) {
				$heading_tag = in_array( $args['heading_tag'], array( 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ? $args['heading_tag'] : 'h3';
				$title_html  = '<' . $heading_tag . '>' . esc_html( $args['title'] ) . '</' . $heading_tag . '>';
			} else {
				$title_html = $args['title'];
			}
			$output .= '<div class="wzkb-related-articles">' . $title_html . '<ul>';

			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id   = get_the_ID();
				$title     = get_the_title( $post_id );
				$permalink = get_permalink( $post_id );

				$output .= '<li class="wzkb-related-article-name post-' . $post_id . '">';

				$output .= '<a href="' . esc_url( $permalink ) . '" rel="bookmark" title="' . esc_attr( $title ) . '">';

				if ( $args['show_thumb'] ) {
					$output .= wzkb_get_the_post_thumbnail(
						array(
							'post' => $post_id,
							'size' => $args['thumb_size'],
						)
					);
				}

				$output .= esc_html( $title );

				$output .= '</a>';

				if ( $args['show_excerpt'] ) {
					$excerpt = get_the_excerpt();
					if ( empty( $excerpt ) ) {
						$content = get_post_field( 'post_content', $post_id );
						$excerpt = wp_trim_words( wp_strip_all_tags( $content ), 55 );
					}
					if ( ! empty( $excerpt ) ) {
						$output .= '<div class="wzkb-excerpt">' . wp_kses_post( wp_trim_words( $excerpt, 20 ) ) . '</div>';
					}
				}

				if ( $args['show_date'] ) {
					$timestamp = get_post_timestamp( $post_id );
					if ( $timestamp ) {
						$output .= '<span class="wzkb-related-article-date"> ' . esc_html( wp_date( $date_format, $timestamp ) ) . '</span> ';
					}
				}

				$output .= '</li>';
			}

			$output .= '</ul></div>';

			wp_reset_postdata();
		}

		return $output;
	}

	/**
	 * Sort the related query by a simple relevance score using shared taxonomies and recency.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Query $query         Related posts query.
	 * @param int[]     $category_ids  Category term IDs from the origin post.
	 * @param int[]     $tag_ids       Tag term IDs from the origin post.
	 * @param array     $args          Original query arguments.
	 */
	protected static function sort_query_by_relevance( \WP_Query $query, array $category_ids, array $tag_ids, array $args ): void {
		if ( empty( $query->posts ) ) {
			return;
		}

		$category_ids = array_filter( array_map( 'intval', $category_ids ) );
		$tag_ids      = array_filter( array_map( 'intval', $tag_ids ) );

		if ( empty( $category_ids ) && empty( $tag_ids ) ) {
			return;
		}

		$category_weight = (int) apply_filters( 'wzkb_related_category_weight', 2, $args );
		$tag_weight      = (int) apply_filters( 'wzkb_related_tag_weight', 1, $args );

		$origin_cats_lookup = array_fill_keys( $category_ids, true );
		$origin_tags_lookup = array_fill_keys( $tag_ids, true );

		$scored_posts       = array();
		$original_positions = array();
		$current_time       = time();

		foreach ( $query->posts as $index => $post ) {
			$original_positions[ $post->ID ] = $index;

			$score            = 0;
			$category_matches = 0;
			$tag_matches      = 0;

			if ( ! empty( $origin_cats_lookup ) ) {
				$post_categories  = wp_get_object_terms( $post->ID, 'wzkb_category', array( 'fields' => 'ids' ) );
				$post_categories  = array_filter( array_map( 'intval', (array) $post_categories ) );
				$category_matches = count( array_intersect( $post_categories, array_keys( $origin_cats_lookup ) ) );
				$score           += $category_matches * max( 1, $category_weight );
			}

			if ( ! empty( $origin_tags_lookup ) ) {
				$post_tags   = wp_get_object_terms( $post->ID, 'wzkb_tag', array( 'fields' => 'ids' ) );
				$post_tags   = array_filter( array_map( 'intval', (array) $post_tags ) );
				$tag_matches = count( array_intersect( $post_tags, array_keys( $origin_tags_lookup ) ) );
				$score      += $tag_matches * max( 1, $tag_weight );
			}

			$post_timestamp = get_post_timestamp( $post );
			if ( $post_timestamp ) {
				$age           = max( 0, $current_time - $post_timestamp );
				$recency_boost = max( 0, ( YEAR_IN_SECONDS - min( $age, YEAR_IN_SECONDS ) ) / YEAR_IN_SECONDS );
				$score        += apply_filters( 'wzkb_related_recency_boost', $recency_boost, $post, $args );
			}

			$scored_posts[] = array(
				'post'             => $post,
				'score'            => apply_filters( 'wzkb_related_post_score', $score, $post, $args ),
				'category_matches' => $category_matches,
				'tag_matches'      => $tag_matches,
				'post_timestamp'   => $post_timestamp ? $post_timestamp : 0,
			);
		}

		usort(
			$scored_posts,
			static function ( $a, $b ) use ( $original_positions ) {
				if ( $a['score'] !== $b['score'] ) {
					return ( $a['score'] < $b['score'] ) ? 1 : -1;
				}

				if ( $a['category_matches'] !== $b['category_matches'] ) {
					return ( $a['category_matches'] < $b['category_matches'] ) ? 1 : -1;
				}

				if ( $a['tag_matches'] !== $b['tag_matches'] ) {
					return ( $a['tag_matches'] < $b['tag_matches'] ) ? 1 : -1;
				}

				if ( $a['post_timestamp'] !== $b['post_timestamp'] ) {
					return ( $a['post_timestamp'] < $b['post_timestamp'] ) ? 1 : -1;
				}

				$position_a = $original_positions[ $a['post']->ID ] ?? 0;
				$position_b = $original_positions[ $b['post']->ID ] ?? 0;

				return $position_a <=> $position_b;
			}
		);

		$query->posts = wp_list_pluck( $scored_posts, 'post' );
		$query->rewind_posts();
	}
}
