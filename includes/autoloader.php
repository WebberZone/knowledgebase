<?php
/**
 * Autoloads classes from the WebberZone\Knowledge_Base namespace.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Autoloader for WebberZone\Knowledge_Base classes.
 *
 * @param string $class_name The name of the class to load.
 * @return void
 */
function autoload( $class_name ): void {
	$namespace = __NAMESPACE__;

	// Ensure the class belongs to our namespace.
	if ( false === strpos( $class_name, $namespace ) ) {
		return;
	}

	// Base plugin directory (one level up from /includes/).
	$plugin_dir = dirname( __DIR__ );

	// Remove the top-level namespace.
	$project_namespace = $namespace . '\\';
	$length            = strlen( $project_namespace );
	$class_file        = substr( $class_name, $length );

	// Skip classes in the vendor directory.
	if ( false !== strpos( strtolower( $class_file ), 'vendor' ) ) {
		return;
	}

	// Convert to lowercase and replace underscores with dashes.
	$class_file = str_replace( '_', '-', strtolower( $class_file ) );

	// Prepend `class-` to the filename (last class part).
	$class_parts                = explode( '\\', $class_file );
	$last_index                 = count( $class_parts ) - 1;
	$class_parts[ $last_index ] = 'class-' . $class_parts[ $last_index ];

	// Construct the full file path directly.
	$location = $plugin_dir . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $class_parts ) . '.php';

	if ( is_file( $location ) ) {
		require_once $location;
	}
}
spl_autoload_register( __NAMESPACE__ . '\autoload' );
