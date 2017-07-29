<?php
/**
 * Renders the settings page.
 * Portions of this code have been inspired by Easy Digital Downloads, WordPress Settings Sandbox, etc.
 *
 * @link https://webberzone.com
 * @since 1.2.0
 *
 * @package WZKB
 * @subpackage Admin/Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Render the settings page.
 *
 * @since 1.2.0
 *
 * @return void
 */
function wzkb_options_page() {
	$active_tab = isset( $_GET['tab'] ) && array_key_exists( sanitize_key( wp_unslash( $_GET['tab'] ) ), wzkb_get_settings_sections() ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // Input var okay.

	ob_start();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Knowledgebase Settings', 'knowledgebase' ); ?></h1>

		<?php settings_errors(); ?>

		<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<h2 class="nav-tab-wrapper" style="padding:0">
				<?php
				foreach ( wzkb_get_settings_sections() as $tab_id => $tab_name ) {

					$tab_url = esc_url(
						add_query_arg(
							array(
								'settings-updated' => false,
								'tab' => $tab_id,
							)
						)
					);

					$active = $active_tab === $tab_id ? ' nav-tab-active' : '';

					echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab ' . sanitize_html_class( $active ) . '">';
								echo esc_html( $tab_name );
					echo '</a>';

				}
				?>
			</h2>

			<div id="tab_container">
				<form method="post" action="options.php">
					<table class="form-table">
					<?php
						settings_fields( 'wzkb_settings' );
						do_settings_fields( 'wzkb_settings_' . $active_tab, 'wzkb_settings_' . $active_tab );
					?>
					</table>
					<p>
					<?php
						// Default submit button.
						submit_button(
							__( 'Submit', 'knowledgebase' ),
							'primary',
							'submit',
							false
						);

						echo '&nbsp;&nbsp;';

						// Reset button.
						$confirm = esc_js( __( 'Do you really want to reset all these settings to their default values?', 'knowledgebase' ) );
						submit_button(
							__( 'Reset', 'knowledgebase' ),
							'secondary',
							'settings_reset',
							false,
							array(
								'onclick' => "return confirm('{$confirm}');",
							)
						);
					?>
					</p>
				</form>
			</div><!-- /#tab_container-->

		</div><!-- /#post-body-content -->

		<div id="postbox-container-1" class="postbox-container">

			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<?php include_once( 'sidebar.php' ); ?>
			</div><!-- /#side-sortables -->

		</div><!-- /#postbox-container-1 -->
		</div><!-- /#post-body -->
		<br class="clear" />
		</div><!-- /#poststuff -->

	</div><!-- /.wrap -->

	<?php
	echo ob_get_clean(); // WPCS: XSS OK.
}

/**
 * Array containing the settings' sections.
 *
 * @since 1.2.0
 *
 * @return array Settings array
 */
function wzkb_get_settings_sections() {
	$wzkb_settings_sections = array(
		'general' => __( 'General', 'knowledgebase' ),
		'styles' => __( 'Styles', 'knowledgebase' ),
	);

	/**
	 * Filter the array containing the settings' sections.
	 *
	 * @since 1.2.0
	 *
	 * @param array $wzkb_settings_sections Settings array
	 */
	return apply_filters( 'wzkb_settings_sections', $wzkb_settings_sections );

}


/**
 * Miscellaneous callback funcion
 *
 * @since 1.2.0
 *
 * @param array $args Arguments passed by the setting.
 * @return void
 */
function wzkb_missing_callback( $args ) {
	printf( esc_html__( 'The callback function used for the <strong>%s</strong> setting is missing.', 'knowledgebase' ), esc_html( $args['id'] ) );
}


/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since 1.2.0
 *
 * @param array $args Arguments passed by the setting.
 * @return void
 */
function wzkb_header_callback( $args ) {

	/**
	 * After Settings Output filter
	 *
	 * @since 1.3.0
	 * @param string $html HTML string.
	 * @param array Arguments array.
	 */
	echo apply_filters( 'wzkb_after_setting_output', '', $args ); // WPCS: XSS OK.
}


/**
 * Display text fields.
 *
 * @since 1.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function wzkb_text_callback( $args ) {

	// First, we read the options collection.
	global $wzkb_options;

	if ( isset( $wzkb_options[ $args['id'] ] ) ) {
		$value = $wzkb_options[ $args['id'] ];
	} else {
		$value = isset( $args['options'] ) ? $args['options'] : '';
	}

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';

	$html = '<input type="text" id="wzkb_settings[' . sanitize_key( $args['id'] ) . ']" name="wzkb_settings[' . sanitize_key( $args['id'] ) . ']" class="' . sanitize_html_class( $size ) . '-text" value="' . esc_attr( stripslashes( $value ) ) . '" />';
	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'wzkb_after_setting_output', $html, $args ); // WPCS: XSS OK.
}


/**
 * Display textarea.
 *
 * @since 1.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function wzkb_textarea_callback( $args ) {

	// First, we read the options collection.
	global $wzkb_options;

	if ( isset( $wzkb_options[ $args['id'] ] ) ) {
		$value = $wzkb_options[ $args['id'] ];
	} else {
		$value = isset( $args['options'] ) ? $args['options'] : '';
	}

	$html = '<textarea class="large-text" cols="50" rows="5" id="wzkb_settings[' . sanitize_key( $args['id'] ) . ']" name="wzkb_settings[' . sanitize_key( $args['id'] ) . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'wzkb_after_setting_output', $html, $args ); // WPCS: XSS OK.
}


/**
 * Display checboxes.
 *
 * @since 1.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function wzkb_checkbox_callback( $args ) {

	// First, we read the options collection.
	global $wzkb_options;

	$checked = isset( $wzkb_options[ $args['id'] ] ) ? checked( 1, $wzkb_options[ $args['id'] ], false ) : '';

	$html = '<input type="checkbox" id="wzkb_settings[' . sanitize_key( $args['id'] ) . ']" name="wzkb_settings[' . sanitize_key( $args['id'] ) . ']" value="1" ' . $checked . '/>';
	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'wzkb_after_setting_output', $html, $args ); // WPCS: XSS OK.
}


/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 1.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function wzkb_multicheck_callback( $args ) {
	global $wzkb_options;
	$html = '';

	if ( ! empty( $args['options'] ) ) {
		foreach ( $args['options'] as $key => $option ) {
			if ( isset( $wzkb_options[ $args['id'] ][ $key ] ) ) {
				$enabled = $option;
			} else {
				$enabled = null;
			}

			$html .= '<input name="wzkb_settings[' . sanitize_key( $args['id'] ) . '][' . $key . ']" id="wzkb_settings[' . sanitize_key( $args['id'] ) . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked( $option, $enabled, false ) . '/> <br />';

			$html .= '<label for="wzkb_settings[' . sanitize_key( $args['id'] ) . '][' . $key . ']">' . $option . '</label><br/>';
		}

		$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'wzkb_after_setting_output', $html, $args ); // WPCS: XSS OK.
}


/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 1.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function wzkb_radio_callback( $args ) {
	global $wzkb_options;
	$html = '';

	foreach ( $args['options'] as $key => $option ) {
		$checked = false;

		if ( isset( $wzkb_options[ $args['id'] ] ) && $wzkb_options[ $args['id'] ] === $key ) {
			$checked = true;
		} elseif ( isset( $args['options'] ) && $args['options'] === $key && ! isset( $wzkb_options[ $args['id'] ] ) ) {
			$checked = true;
		}

		$html .= '<input name="wzkb_settings[' . sanitize_key( $args['id'] ) . ']"" id="wzkb_settings[' . sanitize_key( $args['id'] ) . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked( true, $checked, false ) . '/> <br />';
		$html .= '<label for="wzkb_settings[' . sanitize_key( $args['id'] ) . '][' . $key . ']">' . $option . '</label><br/>';
	}

	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'wzkb_after_setting_output', $html, $args ); // WPCS: XSS OK.
}


/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 1.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function wzkb_number_callback( $args ) {
	global $wzkb_options;

	if ( isset( $wzkb_options[ $args['id'] ] ) ) {
		$value = $wzkb_options[ $args['id'] ];
	} else {
		$value = isset( $args['options'] ) ? $args['options'] : '';
	}

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';

	$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . sanitize_html_class( $size ) . '-text" id="wzkb_settings[' . sanitize_key( $args['id'] ) . ']" name="wzkb_settings[' . sanitize_key( $args['id'] ) . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'wzkb_after_setting_output', $html, $args ); // WPCS: XSS OK.
}


/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since 1.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function wzkb_select_callback( $args ) {
	global $wzkb_options;

	if ( isset( $wzkb_options[ $args['id'] ] ) ) {
		$value = $wzkb_options[ $args['id'] ];
	} else {
		$value = isset( $args['options'] ) ? $args['options'] : '';
	}

	if ( isset( $args['chosen'] ) ) {
		$chosen = 'class="wzkb-chosen"';
	} else {
		$chosen = '';
	}

	$html = '<select id="wzkb_settings[' . sanitize_key( $args['id'] ) . ']" name="wzkb_settings[' . sanitize_key( $args['id'] ) . ']" ' . $chosen . ' />';

	foreach ( $args['options'] as $option => $name ) {
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	}

	$html .= '</select>';
	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'wzkb_after_setting_output', $html, $args ); // WPCS: XSS OK.
}


