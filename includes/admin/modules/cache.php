<?php
/**
 * Help tab.
 *
 * Functions to generated the help tab on the Settings page.
 *
 * @since 1.8.0
 *
 * @package WZKB
 * @subpackage Admin/Modules/Cache
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Delete the Knowledge base cache.
 *
 * @since 1.8.0
 *
 * @param array $meta_keys Array of meta keys that hold the cache.
 */
function wzkb_cache_delete( $meta_keys = array() ) {
	global $wpdb;

	$default_meta_keys = wzkb_cache_get_keys();

	if ( ! empty( $meta_keys ) ) {
		$meta_keys = array_intersect( $default_meta_keys, (array) $meta_keys );
	} else {
		$meta_keys = $default_meta_keys;
	}

	foreach ( $meta_keys as $meta_key ) {
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"
				DELETE FROM {$wpdb->termmeta}
				WHERE meta_key = %s
				",
				$meta_key
			)
		);
	}
}


/**
 * Get the default meta keys used for the cache
 *
 * @since 1.8.0
 */
function wzkb_cache_get_keys() {

	$meta_keys = wzkb_cache_get_meta_keys();

	/**
	 * Filters the array containing the various cache keys.
	 *
	 * @since 1.8.0
	 *
	 * @param   array   $default_meta_keys  Array of meta keys
	 */
	return apply_filters( 'wzkb_cache_keys', $meta_keys );
}


/**
 * Get the _wzkb_cache keys.
 *
 * @since 1.8.0
 *
 * @return array Array of _wzkb_cache keys.
 */
function wzkb_cache_get_meta_keys() {
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
	 * Filter the array of _wzkb_cache keys.
	 *
	 * @since 1.8.0
	 *
	 * @return array Array of _wzkb_cache keys.
	 */
	return apply_filters( 'wzkb_cache_get_meta_keys', $keys );
}
