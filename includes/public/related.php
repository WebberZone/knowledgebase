<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link  https://webberzone.com
 * @since 2.1.0
 *
 * @package    WZKB
 * @subpackage WZKB/public/Related_Posts
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Get related knowledge base articles.
 *
 * @since 2.1.0
 *
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type int          $numberposts Total number of posts to retrieve. Is an alias of $posts_per_page in WP_Query. Accepts -1 for all. Default 5.
 *     @type bool         $echo        Echo or return?
 *     @type WP_Post      $post        Post ID or WP_Post object. Default current post.
 *     @type string|int[] $exclude     Post IDs to exclude. Can be in CSV format or an array.
 *     @type bool         $show_thumb  Show thumbnail?
 *     @type bool         $show_date   Show date?
 *     @type string       $title       Title of the related posts.
 *     @type string       $thumb_size  Thumbnail size.
 * }
 * @return void|string Void if 'echo' argument is true, the post excerpt if 'echo' is false.
 */
function wzkb_related_articles( $args = array() ) {
	$defaults = array(
		'numberposts' => 5,
		'echo'        => true,
		'post'        => get_post(),
		'exclude'     => array(),
		'show_thumb'  => true,
		'show_date'  => true,
		'title'       => '<h3>' . __( 'Related Articles', 'knowledgebase' ) . '</h3>',
		'thumb_size'  => 'thumbnail',
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	// Assign post to a separate variable for easy processing.
	$post = $args['post'];

	$exclude = array( $post->ID );
	if ( ! empty( $args['exclude'] ) ) {
		$exclude = array_merge( $exclude, wp_parse_id_list( $args['exclude'] ) );
	}

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

	$related_args = array(
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'post_type'           => 'wz_knowledgebase',
		'posts_per_page'      => $args['numberposts'],
		'post_status'         => 'publish',
		'post__not_in'        => $exclude,
		'orderby'             => 'rand',
		'tax_query'           => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'relation' => 'OR',
			array(
				'taxonomy' => 'wzkb_category',
				'field'    => 'slug',
				'terms'    => $categories_list,
			),
			array(
				'taxonomy' => 'wzkb_tag',
				'field'    => 'slug',
				'terms'    => $tags_list,
			),
		),
	);

	/**
	 * Filters the related articles arguments before it is passed to WP_Query.
	 *
	 * @since 2.1.0
	 *
	 * @param array $related_args WP_Query arguments.
	 * @param array $args         Parameters passed to function and merged with defaults.
	 */
	$related_args = apply_filters( 'wzkb_related_articles_query_args', $related_args, $args );

	$query = new WP_Query( $related_args );

	$output = '';

	if ( $query->have_posts() ) {
		$output .= '<div class="wzkb-related-articles">' . $args['title'] . '<ul>';

		while ( $query->have_posts() ) {
			$query->the_post();
			$output .= '<li class="wzkb-related-article-name post-' . get_the_ID() . '">';

			$output .= '<a href="' . get_permalink( get_the_ID() ) . '" rel="bookmark" title="' . get_the_title( get_the_ID() ) . '">';

			if ( $args['show_thumb'] ) {
				$output .= wzkb_get_the_post_thumbnail();
			}

			$output .= get_the_title( get_the_ID() );

			$output .= '</a>';

			if ( $args['show_date'] ) {
				$output .= '<span class="wzkb-related-article-date"> ' . get_the_date( get_option( 'date_format', 'd/m/y' ) ) . '</span> ';
			}

			$output .= '</li>';
		}

		$output .= '</ul></div>';
	}

	if ( $args['echo'] ) {
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $output;
	}
}
