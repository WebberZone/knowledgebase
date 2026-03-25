<?php
/**
 * Cache functions used by Knowledge Base
 *
 * @since 2.3.0
 *
 * @package Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Util;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

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
		Hook_Registry::add_action( 'wp_ajax_wzkb_clear_cache', array( $this, 'ajax_clearcache' ) );
	}

	/**
	 * Function to clear the Knowledge Base Cache with Ajax.
	 *
	 * @since 2.3.0
	 */
	public function ajax_clearcache() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		check_ajax_referer( 'wzkb-admin', 'security' );

		$count = self::delete();

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
	 * Delete the Knowledge Base cache.
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

	/**
	 * Get the timestamp meta key for a cache entry.
	 *
	 * @since 3.0.0
	 *
	 * @param string $cache_key The cache key.
	 * @return string Timestamp meta key
	 */
	public static function get_timestamp_key( $cache_key ) {
		return $cache_key . '_timestamp';
	}

	/**
	 * Check if a cache entry has expired.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $term_id   Term ID.
	 * @param string $cache_key Cache key.
	 * @return bool True if expired or no expiry set, false if still valid.
	 */
	public static function is_expired( $term_id, $cache_key ) {
		$cache_expiry = (int) \wzkb_get_option( 'cache_expiry', DAY_IN_SECONDS );

		// If expiry is 0, cache never expires.
		if ( 0 === $cache_expiry ) {
			return false;
		}

		$timestamp_key = self::get_timestamp_key( $cache_key );
		$cached_time   = get_term_meta( $term_id, $timestamp_key, true );

		// If no timestamp exists, consider it expired.
		if ( false === $cached_time || '' === $cached_time ) {
			return true;
		}

		$cached_time = (int) $cached_time;

		$expiry_time = $cached_time + $cache_expiry;

		return time() > $expiry_time;
	}

	/**
	 * Store cache data with timestamp.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $term_id   Term ID.
	 * @param string $cache_key Cache key.
	 * @param mixed  $data      Data to cache.
	 * @return bool True on success, false on failure.
	 */
	public static function set( $term_id, $cache_key, $data ) {
		$timestamp_key = self::get_timestamp_key( $cache_key );

		// Delete existing cache entries first to ensure clean update.
		delete_term_meta( $term_id, $cache_key );
		delete_term_meta( $term_id, $timestamp_key );

		// Store timestamp first to ensure atomicity.
		$timestamp_stored = add_term_meta( $term_id, $timestamp_key, time(), true );

		// Only store data if timestamp was successfully stored.
		if ( ! $timestamp_stored ) {
			return false;
		}

		$data_stored = add_term_meta( $term_id, $cache_key, $data, true );

		// If data storage fails, clean up the orphaned timestamp.
		if ( ! $data_stored ) {
			delete_term_meta( $term_id, $timestamp_key );
			return false;
		}

		return true;
	}

	/**
	 * Get cache data if not expired.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $term_id   Term ID.
	 * @param string $cache_key Cache key.
	 * @return mixed|false Cached data if valid, false if expired or not found.
	 */
	public static function get( $term_id, $cache_key ) {
		// Check if cache has expired.
		if ( self::is_expired( $term_id, $cache_key ) ) {
			// Clean up expired cache.
			self::delete_expired_entry( $term_id, $cache_key );
			return false;
		}

		return get_term_meta( $term_id, $cache_key, true );
	}

	/**
	 * Delete an expired cache entry and its timestamp.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $term_id   Term ID.
	 * @param string $cache_key Cache key.
	 * @return bool True on success, false on failure.
	 */
	public static function delete_expired_entry( $term_id, $cache_key ) {
		$timestamp_key = self::get_timestamp_key( $cache_key );

		$data_deleted      = delete_term_meta( $term_id, $cache_key );
		$timestamp_deleted = delete_term_meta( $term_id, $timestamp_key );

		return $data_deleted || $timestamp_deleted;
	}
}
