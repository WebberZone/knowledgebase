<?php
/**
 * Register settings.
 *
 * Functions to register, read, write and update settings.
 * Portions of this code have been inspired by Easy Digital Downloads, WordPress Settings Sandbox, etc.
 *
 * @link  https://webberzone.com
 * @since 1.2.0
 *
 * @package	WZKB
 * @subpackage Admin/Register_Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since  1.2.0
 *
 * @param string $key Key of the option to fetch.
 * @param mixed  $default Default value to fetch if option is missing.
 * @return mixed
 */
function wzkb_get_option( $key = '', $default = false ) {

	global $wzkb_options;

	$value = ! empty( $wzkb_options[ $key ] ) ? $wzkb_options[ $key ] : $default;

	/**
	 * Filter the value for the option being fetched.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value  Value of the option
	 * @param mixed $key  Name of the option
	 * @param mixed $default Default value
	 */
	$value = apply_filters( 'wzkb_get_option', $value, $key, $default );

	/**
	 * Key specific filter for the value of the option being fetched.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value  Value of the option
	 * @param mixed $key  Name of the option
	 * @param mixed $default Default value
	 */
	return apply_filters( 'wzkb_get_option_' . $key, $value, $key, $default );
}


/**
 * Update an option
 *
 * Updates an wzkb setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *		  the key from the wzkb_options array.
 *
 * @since 1.2.0
 *
 * @param  string          $key   The Key to update.
 * @param  string|bool|int $value The value to set the key to.
 * @return boolean   True if updated, false if not.
 */
function wzkb_update_option( $key = '', $value = false ) {

	// If no key, exit.
	if ( empty( $key ) ) {
		return false;
	}

	// If no value, delete.
	if ( empty( $value ) ) {
		$remove_option = wzkb_delete_option( $key );
		return $remove_option;
	}

	// First let's grab the current settings.
	$options = get_option( 'wzkb_settings' );

	/**
	 * Filters the value before it is updated
	 *
	 * @since 1.2.0
	 *
	 * @param  string|bool|int $value The value to set the key to
	 * @param  string          $key   The Key to update
	 */
	$value = apply_filters( 'wzkb_update_option', $value, $key );

	// Next let's try to update the value.
	$options[ $key ] = $value;
	$did_update = update_option( 'wzkb_settings', $options );

	// If it updated, let's update the global variable.
	if ( $did_update ) {
		global $wzkb_options;
		$wzkb_options[ $key ] = $value;
	}
	return $did_update;
}


/**
 * Remove an option
 *
 * Removes an wzkb setting value in both the db and the global variable.
 *
 * @since 1.2.0
 *
 * @param  string $key The Key to update.
 * @return boolean   True if updated, false if not.
 */
function wzkb_delete_option( $key = '' ) {

	// If no key, exit.
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings.
	$options = get_option( 'wzkb_settings' );

	// Next let's try to update the value.
	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}

	$did_update = update_option( 'wzkb_settings', $options );

	// If it updated, let's update the global variable.
	if ( $did_update ) {
		global $wzkb_options;
		$wzkb_options = $options;
	}
	return $did_update;
}


/**
 * Register settings function
 *
 * @since 1.2.0
 *
 * @return void
 */
function wzkb_register_settings() {

	if ( false === get_option( 'wzkb_settings' ) ) {
		add_option( 'wzkb_settings', wzkb_settings_defaults() );
	}

	foreach ( wzkb_get_registered_settings() as $section => $settings ) {

		add_settings_section(
			'wzkb_settings_' . $section, // ID used to identify this section and with which to register options, e.g. wzkb_settings_general.
			__return_null(),	// No title, we will handle this via a separate function.
			'__return_false',	// No callback function needed. We'll process this separately.
			'wzkb_settings_' . $section  // Page on which these options will be added.
		);

		foreach ( $settings as $setting ) {

			$args = wp_parse_args( $setting, array(
					'section' => $section,
					'id'      => null,
					'name'    => '',
					'desc'    => '',
					'type'    => null,
					'options' => '',
					'max'     => null,
					'min'     => null,
					'step'    => null,
			) );

			add_settings_field(
				'wzkb_settings[' . $args['id'] . ']', // ID of the settings field. We save it within the wzkb_settings array.
				$args['name'],	   // Label of the setting.
				function_exists( 'wzkb_' . $args['type'] . '_callback' ) ? 'wzkb_' . $args['type'] . '_callback' : 'wzkb_missing_callback', // Function to handle the setting.
				'wzkb_settings_' . $section,	// Page to display the setting. In our case it is the section as defined above.
				'wzkb_settings_' . $section,	// Name of the section.
				$args
			);
		}
	}

	// Register the settings into the options table.
	register_setting( 'wzkb_settings', 'wzkb_settings', 'wzkb_settings_sanitize' );
}
add_action( 'admin_init', 'wzkb_register_settings' );


/**
 * Retrieve the array of plugin settings
 *
 * @since 1.2.0
 *
 * @return array Settings array
 */
function wzkb_get_registered_settings() {

	$wzkb_settings = array(
		/*** General settings ***/
		'general'             => apply_filters( 'wzkb_settings_general',
			array(
				'kb_slug'           => array(
					'id'               => 'kb_slug',
					'name'             => esc_html__( 'Knowledgebase slug', 'knowledgebase' ),
					'desc'             => esc_html__( 'This will set the opening path of the URL of the knowledgebase and is set when registering the custom post type', 'knowledgebase' ),
					'type'             => 'text',
					'options'          => 'knowledgebase',
				),
				'category_slug'     => array(
					'id'               => 'category_slug',
					'name'             => esc_html__( 'Category slug', 'knowledgebase' ),
					'desc'             => esc_html__( 'Each category is a section of the knowledgebase. This setting is used when registering the custom category and forms a part of the URL when browsing category archives', 'knowledgebase' ),
					'type'             => 'text',
					'options'          => 'section',
				),
				'tag_slug'          => array(
					'id'               => 'tag_slug',
					'name'             => esc_html__( 'Tag slug', 'knowledgebase' ),
					'desc'             => esc_html__( 'Each article can have multiple tags. This setting is used when registering the custom tag and forms a part of the URL when browsing tag archives', 'knowledgebase' ),
					'type'             => 'text',
					'options'          => 'kb-tags',
				),
				'uninstall_header'  => array(
					'id'               => 'uninstall_header',
					'name'             => '<h3>' . esc_html__( 'Uninstall options', 'knowledgebase' ) . '</h3>',
					'desc'             => '',
					'type'             => 'header',
					'options'          => '',
				),
				'uninstall_options' => array(
					'id'               => 'uninstall_options',
					'name'             => esc_html__( 'Delete options on uninstall', 'knowledgebase' ),
					'desc'             => esc_html__( 'Check this box to delete the settings on this page when the plugin is deleted via the Plugins page in your WordPress Admin', 'knowledgebase' ),
					'type'             => 'checkbox',
					'options'          => true,
				),
				'uninstall_data'    => array(
					'id'               => 'uninstall_data',
					'name'             => esc_html__( 'Delete all knowledgebase posts on uninstall', 'knowledgebase' ),
					'desc'             => esc_html__( 'Check this box to delete all the posts, categories and tags created by the plugin. There is no way to restore the data if you choose this option', 'knowledgebase' ),
					'type'             => 'checkbox',
					'options'          => false,
				),
			)
		),
		/*** Style settings ***/
		'styles'              => apply_filters( 'wzkb_settings_styles',
			array(
				'include_styles'    => array(
					'id'               => 'include_styles',
					'name'             => esc_html__( 'Include plugin inbuilt styles', 'knowledgebase' ),
					'desc'             => esc_html__( 'Uncheck this to disable this plugin from adding the inbuilt styles. You will need to add your own CSS styles if you disable this option', 'knowledgebase' ),
					'type'             => 'checkbox',
					'options'          => true,
				),
				'custom_css'        => array(
					'id'               => 'custom_css',
					'name'             => esc_html__( 'Custom CSS', 'knowledgebase' ),
					'desc'             => esc_html__( 'Enter any custom valid CSS without any wrapping &lt;style&gt; tags', 'knowledgebase' ),
					'type'             => 'textarea',
					'options'          => '',
				),
			)
		),
	);

	/**
	 * Filters the settings array
	 *
	 * @since 1.2.0
	 *
	 * @param array $wzkb_setings Settings array
	 */
	return apply_filters( 'wzkb_registered_settings', $wzkb_settings );

}



/**
 * Default settings.
 *
 * @since 1.2.0
 *
 * @return array Default settings
 */
function wzkb_settings_defaults() {

	$options = array();

	// Populate some default values.
	foreach ( wzkb_get_registered_settings() as $tab => $settings ) {
		foreach ( $settings as $option ) {
			// When checkbox is set to true, set this to 1.
			if ( 'checkbox' === $option['type'] && ! empty( $option['options'] ) ) {
				$options[ $option['id'] ] = '1';
			}
			// If an option is set.
			if ( in_array( $option['type'], array( 'textarea', 'text', 'csv' ), true ) && ! empty( $option['options'] ) ) {
				$options[ $option['id'] ] = $option['options'];
			}
		}
	}

	/**
	 * Filters the default settings array.
	 *
	 * @since 1.2.0
	 *
	 * @param array $options Default settings.
	 */
	return apply_filters( 'wzkb_settings_defaults', $options );
}


/**
 * Reset settings.
 *
 * @since 1.2.0
 *
 * @return void
 */
function wzkb_settings_reset() {
	delete_option( 'wzkb_settings' );
}

