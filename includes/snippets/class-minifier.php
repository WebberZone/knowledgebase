<?php
/**
 * Minifier class for CSS/JS minification and combination.
 *
 * @package WebberZone\Snippetz\Snippets
 */

namespace WebberZone\Snippetz\Snippets;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use WebberZone\Snippetz\Util\Helpers;

/**
 * Minifier class.
 */
class Minifier {

	/**
	 * Upload subdirectory under uploads.
	 */
	public const UPLOAD_SUBDIR = 'snippetz';

	/**
	 * Minify CSS content.
	 *
	 * @param string $css CSS content.
	 * @return string Minified CSS.
	 */
	public static function minify_css( $css ) {
		if ( class_exists( '\MatthiasMullie\Minify\CSS' ) ) {
			$minifier = new \MatthiasMullie\Minify\CSS( $css );
			return $minifier->minify();
		}
		// Fallback to basic minification.
		$css = preg_replace( '/\/\*.*?\*\//s', '', $css );
		$css = preg_replace( '/\s+/', ' ', $css );
		$css = preg_replace( '/\s*([{}:;,])\s*/', '$1', $css );
		return trim( $css );
	}

	/**
	 * Minify JS content.
	 *
	 * @param string $js JS content.
	 * @return string Minified JS.
	 */
	public static function minify_js( $js ) {
		if ( class_exists( '\MatthiasMullie\Minify\JS' ) ) {
			$minifier = new \MatthiasMullie\Minify\JS( $js );
			return $minifier->minify();
		}
		// Fallback to basic minification.
		$js = preg_replace( '/\/\*.*?\*\//s', '', $js );
		$js = preg_replace( '/\/\/.*$/m', '', $js );
		$js = preg_replace( '/\s+/', ' ', $js );
		return trim( $js );
	}

	/**
	 * Combine and minify all CSS snippets.
	 *
	 * @return string Combined minified CSS.
	 */
	public static function combine_css() {
		$css = self::get_combined_content( 'css' );
		return self::minify_css( $css );
	}

	/**
	 * Combine and minify all JS snippets.
	 *
	 * @return string Combined minified JS.
	 */
	public static function combine_js() {
		$js = self::get_combined_content( 'js' );
		return self::minify_js( $js );
	}

	/**
	 * Get combined content for snippets.
	 *
	 * @param string $type Snippet type (css or js).
	 * @return string Combined content.
	 */
	private static function get_combined_content( $type ) {
		$locations = array( 'header', 'footer', 'content_before', 'content_after' );
		$snippets  = array();

		foreach ( $locations as $location ) {
			$location_snippets = Functions::get_snippets_by_location( $location );
			if ( is_array( $location_snippets ) ) {
				$snippets = array_merge( $snippets, $location_snippets );
			}
		}

		$combined_content = '';
		foreach ( $snippets as $snippet ) {
			if ( ! $snippet instanceof \WP_Post ) {
				continue;
			}
			if ( Functions::get_snippet_type( $snippet ) === $type ) {
				$content           = get_post_field( 'post_content', $snippet->ID );
				$content           = Helpers::process_placeholders( $content );
				$combined_content .= $content . "\n";
			}
		}
		return $combined_content;
	}

	/**
	 * Save content to uploads and store the URL in an option.
	 *
	 * @param string $content     File contents.
	 * @param string $filename    Filename.
	 * @param string $option_name Option name to store URL.
	 * @return bool True on success.
	 */
	private static function save_combined_file( $content, $filename, $option_name ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			return false;
		}

		$upload_dir = wp_upload_dir();
		$dir        = trailingslashit( $upload_dir['basedir'] ) . self::UPLOAD_SUBDIR . '/';
		wp_mkdir_p( $dir );
		$file_path = $dir . $filename;
		$written   = $wp_filesystem->put_contents( $file_path, $content, FS_CHMOD_FILE );
		if ( ! $written ) {
			return false;
		}

		$url = trailingslashit( $upload_dir['baseurl'] ) . self::UPLOAD_SUBDIR . '/' . $filename;
		return update_option( $option_name, $url );
	}

	/**
	 * Save combined CSS to file.
	 */
	public static function save_combined_css() {
		$content = self::combine_css();
		if ( empty( $content ) ) {
			return;
		}

		self::save_combined_file( $content, 'combined.css', 'ata_combined_css_url' );
	}

	/**
	 * Save combined JS to file.
	 */
	public static function save_combined_js() {
		$content = self::combine_js();
		if ( empty( $content ) ) {
			return;
		}

		self::save_combined_file( $content, 'combined.js', 'ata_combined_js_url' );
	}

	/**
	 * Get file stats.
	 *
	 * @since 2.3.0
	 *
	 * @param string $filename Filename.
	 * @return array|bool Array of file stats or false if file does not exist.
	 */
	public static function get_file_stats( $filename ) {
		$upload_dir = wp_upload_dir();
		$file_path  = trailingslashit( $upload_dir['basedir'] ) . self::UPLOAD_SUBDIR . '/' . $filename;

		if ( ! file_exists( $file_path ) ) {
			return false;
		}

		return array(
			'url'   => trailingslashit( $upload_dir['baseurl'] ) . self::UPLOAD_SUBDIR . '/' . $filename,
			'path'  => $file_path,
			'size'  => filesize( $file_path ),
			'mtime' => filemtime( $file_path ),
		);
	}
}
