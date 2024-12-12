<?php
/**
 * Cache functions used by Better Search
 *
 * @since 2.3.0
 *
 * @package Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Util;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Cache Class.
 *
 * @since 2.3.0
 */
class Cache {

	/**
	 * Constructor class.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_wzkb_clear_cache', array( $this, 'ajax_clearcache' ) );
	}

	/**
	 * Function to clear the Better Search Cache with Ajax.
	 *
	 * @since 2.3.0
	 */
	public function ajax_clearcache() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		check_ajax_referer( 'wzkb-admin', 'security' );

		$count = $this->delete();

		wp_send_json_success(
			array(
				'message' => sprintf( // translators: This placeholder represents the number of entries cleared from the cache.
					_n( '%s entry cleared', '%s entries cleared', $count, 'knowledgebase' ),
					number_format_i18n( $count )
				),
			)
		);
	}

	/**
	 * Delete the Better Search cache.
	 *
	 * @since 2.3.0
	 *
	 * @param array $meta_keys Array of meta keys to delete.
	 * @return int Number of meta keys deleted.
	 */
	public static function delete( $meta_keys = array() ) {
		global $wpdb;

		$rows_deleted = 0;

		$default_meta_keys = self::get_keys();

		if ( ! empty( $meta_keys ) ) {
			$meta_keys = array_intersect( $default_meta_keys, (array) $meta_keys );
		} else {
			$meta_keys = $default_meta_keys;
		}

		foreach ( $meta_keys as $meta_key ) {
			$rows_deleted += (int) $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"
					DELETE FROM {$wpdb->termmeta}
					WHERE meta_key = %s
					",
					$meta_key
				)
			);
		}

		return $rows_deleted;
	}

	/**
	 * Get the default meta keys used for the cache
	 *
	 * @return  array   Transient meta keys
	 */
	public static function get_keys() {
		global $wpdb;

		$keys = array();

		$sql = "
			SELECT meta_key
			FROM {$wpdb->termmeta}
			WHERE `meta_key` LIKE '_wzkb_cache_%'
		";

		$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		$keys = wp_list_pluck( $results, 'meta_key' );

		/**
		 * Filters the array containing the various cache keys.
		 *
		 * @since 1.8.0
		 *
		 * @param   array   $default_meta_keys  Array of meta keys
		 */
		return apply_filters( 'wzkb_cache_keys', $keys );
	}

	/**
	 * Get the meta key based on a list of parameters.
	 *
	 * @param mixed $attr Array of attributes typically.
	 * @return string Cache meta key
	 */
	public static function get_key( $attr ) {

		$meta_key = '_wzkb_cache_' . md5( wp_json_encode( $attr ) );

		return $meta_key;
	}
}
