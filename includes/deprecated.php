<?php
/**
 * Deprecated functions and variables
 *
 * @link  https://webberzone.com
 * @since 1.5.0
 *
 * @package    WZKB
 * @subpackage WZKB/deprecated
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * WZKB Settings
 *
 * @since 1.2.0
 * @deprecated 1.5.0
 *
 * @var array WZKB Settings
 */
global $wzkb_options;
$wzkb_options = wzkb_get_settings();
