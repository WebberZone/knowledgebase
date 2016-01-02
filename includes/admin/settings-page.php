<?php
/**
 * Renders the settings page.
 * Portions of this code have been inspired by Easy Digital Downloads, WordPress Settings Sandbox, etc.
 *
 * @link       https://webberzone.com
 * @since      1.2.0
 *
 * @package    WZKB
 * @subpackage Admin/Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Render the settings page.
 *
 * @since	1.2.0
 *
 * @return	void
 */
function wzkb_options_page() {
	$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], wzkb_get_settings_sections() ) ? $_GET['tab'] : 'general';

	ob_start();
	?>
	<div class="wrap">
		<h1><?php _e( 'Knowledgebase Settings', 'knowledgebase' ); // WPCS: XSS OK. ?></h1>

		<?php settings_errors(); ?>

		<h2 class="nav-tab-wrapper">
			<?php
			foreach ( wzkb_get_settings_sections() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $tab_id,
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
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
					// Default submit button
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

	</div><!-- /.wrap -->

	<?php
	echo ob_get_clean();
}

/**
 * Array containing the settings' sections.
 *
 * @since	1.2.0
 *
 * @return	array	Settings array
 */
function wzkb_get_settings_sections() {
	$wzkb_settings_sections = array(
		'general' => __( 'General', 'knowledgebase' ),
		'styles' => __( 'Styles', 'knowledgebase' ),
	);

	/**
	 * Filter the array containing the settings' sections.
	 *
	 * @since	1.2.0
	 *
	 * @param	array	$wzkb_settings_sections	Settings array
	 */
	return apply_filters( 'wzkb_settings_sections', $wzkb_settings_sections );

}


/**
 * Miscellaneous callback funcion
 *
 * @since	1.2.0
 *
 * @return	void
 */
function wzkb_missing_callback( $args = array() ) {

}


/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since	1.2.0
 * @param	array $args   Arguments passed by the setting
 * @return	void
 */
function wzkb_header_callback( $args ) {
	echo '<hr/>';
}


/**
 * Display text fields.
 *
 * @since	1.2.0
 *
 * @param	array $args   Array of arguments
 * @return	void
 */
function wzkb_text_callback( $args ) {

	// First, we read the options collection
	global $wzkb_options;

	if ( isset( $wzkb_options[ $args['id'] ] ) ) {
		$value = $wzkb_options[ $args['id'] ];
	} else {
		$value = isset( $args['options'] ) ? $args['options'] : '';
	}

	$html  = '<input type="text" id="wzkb_settings[' . $args['id'] . ']" name="wzkb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '" />';
	$html .= '<p class="description">'  . $args['desc'] . '</p>';

	echo $html;
}


/**
 * Display textarea.
 *
 * @since	1.2.0
 *
 * @param	array $args   Array of arguments
 * @return	void
 */
function wzkb_textarea_callback( $args ) {

	// First, we read the options collection
	global $wzkb_options;

	if ( isset( $wzkb_options[ $args['id'] ] ) ) {
		$value = $wzkb_options[ $args['id'] ];
	} else {
		$value = isset( $args['options'] ) ? $args['options'] : '';
	}

	$html = '<textarea class="large-text" cols="50" rows="5" id="wzkb_settings[' . $args['id'] . ']" name="wzkb_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<p class="description">' . $args['desc'] . '</p>';

	echo $html;
}


/**
 * Display checboxes.
 *
 * @since	1.2.0
 *
 * @param	array $args   Array of arguments
 * @return	void
 */
function wzkb_checkbox_callback( $args ) {

	// First, we read the options collection
	global $wzkb_options;

	$checked = isset( $wzkb_options[ $args['id'] ] ) ? checked( 1, $wzkb_options[ $args['id'] ], false ) : '';

	$html  = '<input type="checkbox" id="wzkb_settings[' . $args['id'] . ']" name="wzkb_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
	$html .= '<p class="description">'  . $args['desc'] . '</p>';

	echo $html;
}


/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since	1.2.0
 *
 * @param	array $args   Array of arguments
 * @return	void
 */
function wzkb_multicheck_callback( $args ) {
	global $wzkb_options;

	if ( ! empty( $args['options'] ) ) {
		foreach ( $args['options'] as $key => $option ) {
			if ( isset( $wzkb_options[ $args['id'] ][ $key ] ) ) {
				$enabled = $option;
			} else {
				$enabled = null;
			}

			echo '<input name="wzkb_settings[' . $args['id'] . '][' . $key . ']" id="wzkb_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked( $option, $enabled, false ) . '/> <br />';

			echo '<label for="wzkb_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
		}

		echo '<p class="description">' . $args['desc'] . '</p>';
	}
}


/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since	1.2.0
 *
 * @param	array $args   Array of arguments
 * @return	void
 */
function wzkb_radio_callback( $args ) {
	global $wzkb_options;

	foreach ( $args['options'] as $key => $option ) {
		$checked = false;

		if ( isset( $wzkb_options[ $args['id'] ] ) && $wzkb_options[ $args['id'] ] == $key ) {
			$checked = true;
		} elseif ( isset( $args['options'] ) && $args['options'] == $key && ! isset( $wzkb_options[ $args['id'] ] ) ) {
			$checked = true;
		}

			echo '<input name="wzkb_settings[' . $args['id'] . ']"" id="wzkb_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked( true, $checked, false ) . '/> <br />';
			echo '<label for="wzkb_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
	}

	echo '<p class="description">' . $args['desc'] . '</p>';
}


/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since	1.2.0
 *
 * @param	array $args   Array of arguments
 * @return	void
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
	$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="wzkb_settings[' . $args['id'] . ']" name="wzkb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<p class="description">' . $args['desc'] . '</p>';

	echo $html;
}


/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since	1.2.0
 *
 * @param	array $args   Array of arguments
 * @return	void
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

	$html = '<select id="wzkb_settings[' . $args['id'] . ']" name="wzkb_settings[' . $args['id'] . ']" ' . $chosen . ' />';

	foreach ( $args['options'] as $option => $name ) {
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	}

	$html .= '</select>';
	$html .= '<p class="description">' . $args['desc'] . '</p>';

	echo $html;
}


