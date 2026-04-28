<?php
/**
 * Table of Contents module
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Frontend;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * TOC Class.
 *
 * @since 3.0.0
 */
class TOC {

	/**
	 * Whether the TOC has been injected on this page load.
	 *
	 * @since 3.0.0
	 * @var bool
	 */
	private static bool $injected = false;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		if ( \wzkb_get_option( 'show_toc' ) ) {
			Hook_Registry::add_filter( 'the_content', array( $this, 'inject_toc' ) );
		}
	}

	/**
	 * Filter callback: prepend TOC to article content.
	 *
	 * @since 3.0.0
	 *
	 * @param string $content Post content.
	 * @return string Content with TOC prepended, or original content unchanged.
	 */
	public function inject_toc( string $content ): string {
		if ( self::$injected || ! is_singular( 'wz_knowledgebase' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$result = self::process_content( $content, array( 'extra_class' => 'wzkb-toc--inline' ) );

		if ( empty( $result['toc'] ) ) {
			return $content;
		}

		self::$injected = true;

		return $result['toc'] . $result['content'];
	}

	/**
	 * Parse headings in content, add anchor IDs, and build TOC HTML.
	 *
	 * @since 3.0.0
	 *
	 * @param string $content Post content.
	 * @param array  $args {
	 *     Optional arguments.
	 *
	 *     @type int    $heading_depth Max heading level to include (2–6). Default from setting.
	 *     @type int    $min_headings  Minimum headings required to show TOC. Default from setting.
	 *     @type string $title         TOC title text. Default from setting.
	 *     @type string $extra_class   Additional CSS class added to the TOC nav element. Default empty.
	 * }
	 * @return array {
	 *     @type string $toc     TOC HTML, or empty string if below minimum headings.
	 *     @type string $content Content with anchor IDs added to headings, or original if TOC suppressed.
	 * }
	 */
	public static function process_content( string $content, array $args = array() ): array {
		$defaults = array(
			'heading_depth' => (int) \wzkb_get_option( 'toc_heading_depth', 4 ),
			'min_headings'  => (int) \wzkb_get_option( 'toc_min_headings', 3 ),
			'title'         => (string) \wzkb_get_option( 'toc_title', __( 'Table of Contents', 'knowledgebase' ) ),
			'extra_class'   => '',
		);
		$args     = wp_parse_args( $args, $defaults );

		$max_level = max( 2, min( 6, (int) $args['heading_depth'] ) );
		$levels    = implode( '', range( 2, $max_level ) );
		$pattern   = '/<h([' . $levels . '])(\s[^>]*)?>(.*?)<\/h\1>/si';

		$headings = array();
		$used_ids = array();

		$modified_content = preg_replace_callback(
			$pattern,
			static function ( $found ) use ( &$headings, &$used_ids ) {
				$level = (int) $found[1];
				$attrs = isset( $found[2] ) ? $found[2] : '';
				$inner = $found[3];
				$text  = wp_strip_all_tags( $inner );

				if ( preg_match( '/\bid=["\']([^"\']+)["\']/', $attrs, $id_match ) ) {
					$id      = $id_match[1];
					$new_tag = '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
				} else {
					$id      = sanitize_title( $text );
					$base_id = $id;
					$suffix  = 1;
					while ( in_array( $id, $used_ids, true ) ) {
						$id = $base_id . '-' . $suffix;
						++$suffix;
					}
					$new_tag = '<h' . $level . $attrs . ' id="' . esc_attr( $id ) . '">' . $inner . '</h' . $level . '>';
				}

				$used_ids[] = $id;
				$headings[] = array(
					'level' => $level,
					'text'  => $text,
					'id'    => $id,
				);

				return $new_tag;
			},
			$content
		);

		$min_headings = max( 1, (int) $args['min_headings'] );
		if ( count( $headings ) < $min_headings ) {
			return array(
				'toc'     => '',
				'content' => $content,
			);
		}

		return array(
			'toc'     => self::build_toc_html( $headings, $args ),
			'content' => (string) $modified_content,
		);
	}

	/**
	 * Build TOC HTML from a headings array.
	 *
	 * @since 3.0.0
	 *
	 * @param array $headings Array of heading entries, each with 'level', 'text', 'id'.
	 * @param array $args     Arguments including 'title'.
	 * @return string TOC HTML.
	 */
	private static function build_toc_html( array $headings, array $args ): string {
		if ( empty( $headings ) ) {
			return '';
		}

		$title       = isset( $args['title'] ) ? (string) $args['title'] : '';
		$extra_class = isset( $args['extra_class'] ) && '' !== $args['extra_class'] ? ' ' . esc_attr( $args['extra_class'] ) : '';
		$output      = '<nav class="wzkb-toc' . $extra_class . '" aria-label="' . esc_attr__( 'Table of Contents', 'knowledgebase' ) . '">';

		if ( '' !== $title ) {
			$output .= '<p class="wzkb-toc-title">' . esc_html( $title ) . '</p>';
		}

		$stack   = array();
		$output .= '<ul class="wzkb-toc-list">';

		foreach ( $headings as $heading ) {
			$level = (int) $heading['level'];
			$link  = '<a href="#' . esc_attr( $heading['id'] ) . '">' . esc_html( $heading['text'] ) . '</a>';

			if ( empty( $stack ) ) {
				$stack[] = $level;
			} elseif ( $level > end( $stack ) ) {
				$output .= '<ul>';
				$stack[] = $level;
			} elseif ( end( $stack ) === $level ) {
				$output .= '</li>';
			} else {
				while ( ! empty( $stack ) && end( $stack ) > $level ) {
					$output .= '</li></ul>';
					array_pop( $stack );
				}
				if ( ! empty( $stack ) && end( $stack ) === $level ) {
					$output .= '</li>';
				} else {
					$stack[] = $level;
				}
			}

			$output .= '<li>' . $link;
		}

		while ( ! empty( $stack ) ) {
			$output .= '</li></ul>';
			array_pop( $stack );
		}

		$output .= '</nav>';

		/**
		 * Filters the TOC HTML output.
		 *
		 * @since 3.0.0
		 *
		 * @param string $output   TOC HTML.
		 * @param array  $headings Headings array.
		 * @param array  $args     Arguments.
		 */
		return apply_filters( 'wzkb_toc', $output, $headings, $args );
	}
}
