<?php
/**
 * Knowledge Base Options API.
 *
 * @package WebberZone\Knowledge_Base
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Include the Options_API class if not already loaded.
if ( ! class_exists( 'WebberZone\Knowledge_Base\Options_API' ) ) {
	require_once __DIR__ . '/class-options-api.php';
}

/**
 * Get Settings.
 *
 * Retrieves all plugin settings
 *
 * @since 1.2.0
 * @return array Settings
 */
function wzkb_get_settings() {
	return WebberZone\Knowledge_Base\Options_API::get_settings();
}

/**
 * Get an option.
 *
 * Looks to see if the specified setting exists and returns the default value if it doesn't.
 *
 * @since 1.2.0
 *
 * @param string $key            Option to fetch.
 * @param mixed  $default_value  Default option.
 *
 * @return mixed The option value or the default value if the option does not exist.
 */
function wzkb_get_option( $key = '', $default_value = null ) {
	return WebberZone\Knowledge_Base\Options_API::get_option( $key, $default_value );
}

/**
 * Update an option
 *
 * Updates a setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *        the key from the wzkb_options array.
 *
 * @since 1.2.0
 *
 * @param  string          $key   The Key to update.
 * @param  string|bool|int $value The value to set the key to.
 * @return boolean   True if updated, false if not.
 */
function wzkb_update_option( $key = '', $value = false ) {
	return WebberZone\Knowledge_Base\Options_API::update_option( $key, $value );
}

/**
 * Remove an option
 *
 * Removes a setting value in both the db and the global variable.
 *
 * @since 1.2.0
 *
 * @param  string $key The Key to update.
 * @return boolean   True if updated, false if not.
 */
function wzkb_delete_option( $key = '' ) {
	return WebberZone\Knowledge_Base\Options_API::delete_option( $key );
}

/**
 * Default settings.
 *
 * @since 1.2.0
 *
 * @return array Default settings
 */
function wzkb_settings_defaults() {
	return WebberZone\Knowledge_Base\Options_API::get_settings_defaults();
}

/**
 * Get the default option for a specific key
 *
 * @since 1.2.0
 *
 * @param string $key Key of the option to fetch.
 * @return mixed
 */
function wzkb_get_default_option( $key = '' ) {
	return WebberZone\Knowledge_Base\Options_API::get_default_option( $key );
}

/**
 * Reset settings.
 *
 * @since 1.0.0
 * @return bool Success status.
 */
function wzkb_settings_reset() {
	return WebberZone\Knowledge_Base\Options_API::reset_settings();
}

/**
 * Update all settings at once.
 *
 * @since 3.0.0
 *
 * @param array $settings Settings array to save.
 * @param bool  $merge    Whether to merge with existing settings. Default true.
 * @param bool  $autoload Whether to autoload the option. Default true.
 * @return bool True if settings were updated, false otherwise.
 */
function wzkb_update_settings( array $settings, bool $merge = true, bool $autoload = true ) {
	return WebberZone\Knowledge_Base\Options_API::update_settings( $settings, $merge, $autoload );
}

if ( ! function_exists( 'wz_tag_search' ) ) :
	/**
	 * Function to add an action to search for tags using Ajax.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	function wz_tag_search() {

		if ( ! isset( $_REQUEST['tax'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_die();
		}

		$taxonomy = sanitize_key( $_REQUEST['tax'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tax      = get_taxonomy( $taxonomy );
		if ( ! $tax ) {
			wp_die();
		}

		if ( ! current_user_can( $tax->cap->assign_terms ) ) {
			wp_die();
		}

		$s = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$comma = _x( ',', 'tag delimiter', 'knowledgebase' );
		if ( ',' !== $comma ) {
			$s = str_replace( $comma, ',', $s );
		}
		if ( false !== strpos( $s, ',' ) ) {
			$s = explode( ',', $s );
			$s = $s[ count( $s ) - 1 ];
		}
		$s = trim( $s );

		/** This filter has been defined in /wp-admin/includes/ajax-actions.php */
		$term_search_min_chars = (int) apply_filters( 'term_search_min_chars', 2, $tax, $s );

		/*
		 * Require $term_search_min_chars chars for matching (default: 2)
		 * ensure it's a non-negative, non-zero integer.
		 */
		if ( ( 0 === $term_search_min_chars ) || ( strlen( $s ) < $term_search_min_chars ) ) {
			wp_die();
		}

		$results = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'name__like' => $s,
				'fields'     => 'names',
				'hide_empty' => false,
			)
		);

		echo wp_json_encode( $results );
		wp_die();
	}
	add_action( 'wp_ajax_wz_tag_search', 'wz_tag_search' );
endif;
