<?php
/**
 * Save settings.
 *
 * Functions to register, read, write and update settings.
 * Portions of this code have been inspired by Easy Digital Downloads, WordPress Settings Sandbox, etc.
 *
 * @link  https://webberzone.com
 * @since 1.2.0
 *
 * @package    WZKB
 * @subpackage Admin/Save_Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Sanitize the form data being submitted.
 *
 * @since  1.2.0
 * @param  array $input Input unclean array
 * @return array Sanitized array
 */
function wzkb_settings_sanitize( $input = array() ) {

	// First, we read the options collection.
	global $wzkb_options;

	// This should be set if a form is submitted, so let's save it in the $referrer variable
	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	parse_str( sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ), $referrer ); // Input var okay.

	// Get the various settings we've registered.
	$settings = wzkb_get_registered_settings();

	// Check if we need to set to defaults.
	$reset = isset( $_POST['settings_reset'] );

	if ( $reset ) {
		wzkb_settings_reset();
		$wzkb_options = get_option( 'wzkb_settings' );

		add_settings_error( 'wzkb-notices', '', __( 'Settings have been reset to their default values. Reload this page to view the updated settings', 'knowledgebase' ), 'error' );

		// Re-register post type and flush the rewrite rules.
		wzkb_register_post_type();
		flush_rewrite_rules();

		return $wzkb_options;
	}

	// Get the tab. This is also our settings' section.
	$tab = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

	$input = $input ? $input : array();

	/**
	 * Filter the settings for the tab. e.g. wzkb_settings_general_sanitize.
	 *
	 * @since  1.2.0
	 * @param  array $input Input unclean array
	 */
	$input = apply_filters( 'wzkb_settings_' . $tab . '_sanitize', $input );

	// Loop through each setting being saved and pass it through a sanitization filter.
	foreach ( $input as $key => $value ) {

		// Get the setting type (checkbox, select, etc).
		$type = isset( $settings[ $tab ][ $key ]['type'] ) ? $settings[ $tab ][ $key ]['type'] : false;

		if ( $type ) {

			/**
			 * Field type specific filter.
			 *
			 * @since  1.2.0
			 * @param  array $value Setting value.
			 * @paaram array $key Setting key.
			 */
			$input[ $key ] = apply_filters( 'wzkb_settings_sanitize_' . $type, $value, $key );
		}

		/**
		 * Field type general filter.
		 *
		 * @since  1.2.0
		 * @paaram array $key Setting key.
		 */
		$input[ $key ] = apply_filters( 'wzkb_settings_sanitize', $input[ $key ], $key );
	}

	// Loop through the whitelist and unset any that are empty for the tab being saved.
	if ( ! empty( $settings[ $tab ] ) ) {
		foreach ( $settings[ $tab ] as $key => $value ) {
			if ( empty( $input[ $key ] ) && ! empty( $wzkb_options[ $key ] ) ) {
				unset( $wzkb_options[ $key ] );
			}
		}
	}

	// Merge our new settings with the existing. Force (array) in case it is empty.
	$wzkb_options = array_merge( (array) $wzkb_options, $input );

	add_settings_error( 'wzkb-notices', '', __( 'Settings updated.', 'knowledgebase' ), 'updated' );

	// Re-register post type and flush the rewrite rules.
	wzkb_register_post_type();
	flush_rewrite_rules();

	return $wzkb_options;

}


/**
 * Sanitize text fields
 *
 * @since 1.2.0
 *
 * @param  array $input The field value
 * @return string  $input  Sanitizied value
 */
function wzkb_sanitize_text_field( $input ) {
	return sanitize_text_field( $input );
}
add_filter( 'wzkb_settings_sanitize_text', 'wzkb_sanitize_text_field' );


/**
 * Sanitize CSV fields
 *
 * @since 1.2.0
 *
 * @param  array $input The field value.
 * @return string  $input  Sanitizied value
 */
function wzkb_sanitize_csv_field( $input ) {

	return implode( ',', array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $input ) ) ) ) );
}
add_filter( 'wzkb_settings_sanitize_csv', 'wzkb_sanitize_csv_field' );


