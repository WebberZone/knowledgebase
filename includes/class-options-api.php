<?php
/**
 * Options API.
 *
 * Settings read/write layer that sits in front of the Settings API. Reads are cached
 * per request and keyed by blog ID so a `switch_to_blog()` mid-request is honoured.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base;

use WebberZone\Knowledge_Base\Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Options API Class.
 *
 * @since 3.0.0
 */
class Options_API {


	/**
	 * Settings option name.
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	const SETTINGS_OPTION = 'wzkb_settings';

	/**
	 * Filter prefix.
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	const FILTER_PREFIX = 'wzkb';

	/**
	 * Per-request settings cache, keyed by blog ID.
	 *
	 * Keyed rather than a single array so that a `switch_to_blog()` in the same
	 * request reads that blog's settings instead of the ones cached before the
	 * switch. On single site the key is always 0.
	 *
	 * @since 3.1.4
	 * @var   array<int, array>
	 */
	private static $settings_cache = array();

	/**
	 * Cache key for the current blog.
	 *
	 * @since 3.1.4
	 *
	 * @return int Blog ID on multisite, 0 otherwise.
	 */
	private static function cache_key() {
		return is_multisite() ? get_current_blog_id() : 0;
	}

	/**
	 * Flush the per-request settings cache.
	 *
	 * Call after any write so a subsequent read in the same request sees the new
	 * value. Pass a blog ID to flush a single blog, or nothing to flush all.
	 *
	 * @since 3.1.4
	 *
	 * @param  int|null $blog_id Blog ID to flush. Null flushes every cached blog.
	 * @return void
	 */
	public static function flush_cache( $blog_id = null ) {
		if ( null === $blog_id ) {
			self::$settings_cache = array();
			return;
		}

		unset( self::$settings_cache[ (int) $blog_id ] );
	}

	/**
	 * Get Settings.
	 *
	 * Retrieves all plugin settings.
	 *
	 * @since 3.0.0
	 *
	 * @return array Settings array.
	 */
	public static function get_settings() {
		$cache_key = self::cache_key();

		if ( ! array_key_exists( $cache_key, self::$settings_cache ) ) {
			/**
			 * Filters the settings array.
			 *
			 * @since 3.0.0
			 *
			 * @param array $settings Settings array.
			 */
			self::$settings_cache[ $cache_key ] = apply_filters(
				self::FILTER_PREFIX . '_get_settings',
				get_option( self::SETTINGS_OPTION, array() )
			);
		}

		return self::$settings_cache[ $cache_key ];
	}

	/**
	 * Get the saved settings merged over the defaults.
	 *
	 * Newly registered settings that are not yet present in the saved option still
	 * resolve to a value.
	 *
	 * @since 3.1.4
	 *
	 * @return array Settings merged with defaults.
	 */
	public static function get_settings_with_defaults() {
		return array_merge( self::get_settings_defaults(), (array) self::get_settings() );
	}

	/**
	 * Get an option.
	 *
	 * Looks to see if the specified setting exists, returns the default if not.
	 *
	 * @since 3.0.0
	 *
	 * @param  string $key           Option to fetch.
	 * @param  mixed  $default_value Default value if the option is missing.
	 * @return mixed
	 */
	public static function get_option( $key = '', $default_value = null ) {
		$settings = self::get_settings();

		if ( null === $default_value ) {
			$default_value = self::get_default_option( $key );
		}

		$value = $settings[ $key ] ?? $default_value;

		/**
		 * Filter the value for the option being fetched.
		 *
		 * @since 3.0.0
		 *
		 * @param mixed $value         Value of the option.
		 * @param mixed $key           Name of the option.
		 * @param mixed $default_value Default value.
		 */
		$value = apply_filters( self::FILTER_PREFIX . '_get_option', $value, $key, $default_value );

		/**
		 * Key specific filter for the value of the option being fetched.
		 *
		 * @since 3.0.0
		 *
		 * @param mixed $value         Value of the option.
		 * @param mixed $key           Name of the option.
		 * @param mixed $default_value Default value.
		 */
		return apply_filters( self::FILTER_PREFIX . "_get_option_{$key}", $value, $key, $default_value );
	}

	/**
	 * Get an option from a specific blog in a multisite network.
	 *
	 * @since 3.1.4
	 *
	 * @param  int    $blog_id       Blog ID to fetch the option from.
	 * @param  string $key           Key of the option to fetch.
	 * @param  mixed  $default_value Default value to fetch if the option is missing.
	 * @return mixed
	 */
	public static function get_blog_option( $blog_id, $key = '', $default_value = false ) {
		$blog_id = (int) $blog_id;

		if ( empty( $blog_id ) ) {
			$blog_id = get_current_blog_id();
		}

		if ( get_current_blog_id() === $blog_id || ! is_multisite() ) {
			$value = self::get_option( $key, $default_value );
		} else {
			switch_to_blog( $blog_id );
			$value = self::get_option( $key, $default_value );
			restore_current_blog();
		}

		/**
		 * Filters a blog option value.
		 *
		 * @since 3.0.0
		 *
		 * @param mixed  $value   The option value.
		 * @param int    $blog_id Blog ID.
		 * @param string $key     Option key.
		 */
		return apply_filters( self::FILTER_PREFIX . "_blog_option_{$key}", $value, $blog_id, $key );
	}

	/**
	 * Update an option.
	 *
	 * Warning: passing an empty, false or null value will store that value; use
	 * `delete_option()` to remove the key entirely.
	 *
	 * @since 3.0.0
	 *
	 * @param  string          $key   The key to update.
	 * @param  string|bool|int $value The value to set the key to.
	 * @return bool True if updated, false if not.
	 */
	public static function update_option( $key = '', $value = false ) {
		if ( empty( $key ) ) {
			return false;
		}

		$options = get_option( self::SETTINGS_OPTION, array() );

		/**
		 * Filters the value before it is saved.
		 *
		 * @since 3.0.0
		 *
		 * @param mixed  $value Value of the option.
		 * @param string $key   Name of the option.
		 */
		$value = apply_filters( self::FILTER_PREFIX . '_update_option', $value, $key );

		$options[ $key ] = $value;
		$did_update      = update_option( self::SETTINGS_OPTION, $options );

		if ( $did_update ) {
			self::$settings_cache[ self::cache_key() ] = $options;
		}

		return $did_update;
	}

	/**
	 * Update all settings at once.
	 *
	 * @since 3.0.0
	 *
	 * @param  array $settings Settings array to save.
	 * @param  bool  $merge    Whether to merge with existing settings. Default true.
	 * @param  bool  $autoload Whether to autoload the option. Default true.
	 * @return bool True if updated, false otherwise.
	 */
	public static function update_settings( array $settings, bool $merge = true, bool $autoload = true ): bool {
		if ( $merge ) {
			$settings = array_merge( (array) self::get_settings(), $settings );
		}

		$did_update = update_option( self::SETTINGS_OPTION, $settings, $autoload );

		if ( $did_update ) {
			self::$settings_cache[ self::cache_key() ] = $settings;
		}

		return $did_update;
	}

	/**
	 * Remove an option.
	 *
	 * @since 3.0.0
	 *
	 * @param  string $key The key to delete.
	 * @return bool True if updated, false if not.
	 */
	public static function delete_option( $key = '' ) {
		if ( empty( $key ) ) {
			return false;
		}

		$options = get_option( self::SETTINGS_OPTION, array() );

		if ( isset( $options[ $key ] ) ) {
			unset( $options[ $key ] );
		}

		$did_update = update_option( self::SETTINGS_OPTION, $options );

		if ( $did_update ) {
			self::$settings_cache[ self::cache_key() ] = $options;
		}

		return $did_update;
	}

	/**
	 * Default settings.
	 *
	 * Built from the registered settings, so this runs the full field definitions
	 * and is only safe to call after `init`. Use `get_default_option()` for a
	 * single key when the read may happen earlier.
	 *
	 * @since 3.0.0
	 *
	 * @return array Default settings.
	 */
	public static function get_settings_defaults() {
		return Admin\Settings::settings_defaults();
	}

	/**
	 * Get the default option for a specific key.
	 *
	 * Reads from `Admin\Settings::get_defaults()` rather than
	 * `settings_defaults()`: the former is a flat array with no translation calls,
	 * so this is safe before `init` and avoids building every field definition to
	 * resolve one key.
	 *
	 * @since 3.0.0
	 *
	 * @param  string $key Key of the option to fetch.
	 * @return mixed Default value, or false if the key is not registered.
	 */
	public static function get_default_option( $key = '' ) {
		/**
		 * Filter the default settings array.
		 *
		 * Mirrors the filter applied in `Admin\Settings::settings_defaults()` so that
		 * this translation-free path honours the same hook. `get_defaults()` itself is
		 * deliberately unfiltered, so the filter runs exactly once on each path.
		 *
		 * @since 3.0.0
		 *
		 * @param array $defaults Default settings.
		 */
		$default_settings = apply_filters(
			self::FILTER_PREFIX . '_settings_defaults',
			Admin\Settings::get_defaults()
		);

		if ( array_key_exists( $key, $default_settings ) ) {
			return $default_settings[ $key ];
		}

		return false;
	}

	/**
	 * Get the registered settings types, keyed by option ID.
	 *
	 * @since 3.1.4
	 *
	 * @return array Setting types keyed by option ID.
	 */
	public static function get_registered_settings_types() {
		$options = array();

		foreach ( Admin\Settings::get_registered_settings() as $tab => $settings ) {
			foreach ( $settings as $option ) {
				if ( ! isset( $option['id'] ) ) {
					continue;
				}
				$options[ $option['id'] ] = $option['type'] ?? '';
			}
		}

		/**
		 * Filters the registered settings types.
		 *
		 * @since 3.0.0
		 *
		 * @param array $options Settings types keyed by option ID.
		 */
		return apply_filters( self::FILTER_PREFIX . '_get_settings_types', $options );
	}

	/**
	 * Reset settings to their defaults.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if updated, false if not.
	 */
	public static function reset_settings(): bool {
		$defaults   = self::get_settings_defaults();
		$did_update = update_option( self::SETTINGS_OPTION, $defaults );

		if ( $did_update ) {
			self::$settings_cache[ self::cache_key() ] = $defaults;
		}

		return $did_update;
	}
}
