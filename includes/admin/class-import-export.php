<?php
/**
 * Settings Import / Export.
 *
 * @package WebberZone\Knowledge_Base\Admin
 * @since 3.1.0
 */

namespace WebberZone\Knowledge_Base\Admin;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Handles settings export (JSON download) and import (JSON upload).
 *
 * @since 3.1.0
 */
class Import_Export {

	/**
	 * Constructor.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'admin_init', array( $this, 'process_export' ) );
		Hook_Registry::add_action( 'admin_init', array( $this, 'process_import' ), 9 );
		Hook_Registry::add_action( 'wzkb_tools_page_content', array( $this, 'render_card' ), 5 );
	}

	/**
	 * Stream the settings as a JSON file download.
	 *
	 * Sensitive fields are stripped before output so that encrypted values
	 * (GitHub PATs, webhook secrets) are never written to the export file.
	 *
	 * @since 3.1.0
	 *
	 * @return void
	 */
	public function process_export(): void {
		if ( empty( $_POST['wzkb_action'] ) || 'export_settings' !== $_POST['wzkb_action'] ) {
			return;
		}

		if ( ! isset( $_POST['wzkb_export_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['wzkb_export_settings_nonce'] ), 'wzkb_export_settings' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = get_option( 'wzkb_settings', array() );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		$settings = self::strip_sensitive_fields( $settings );

		ignore_user_abort( true );
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		$site_slug = sanitize_title( wp_parse_url( get_site_url(), PHP_URL_HOST ) );
		header( 'Content-Disposition: attachment; filename=' . $site_slug . '-settings-' . gmdate( 'Y-m-d' ) . '.json' );
		header( 'Expires: 0' );

		echo wp_json_encode( $settings, JSON_PRETTY_PRINT );
		exit;
	}

	/**
	 * Handle an uploaded JSON settings file and merge it into the saved settings.
	 *
	 * Sensitive keys already saved on this site are preserved — they are never
	 * overwritten by the import since the export file does not contain them.
	 *
	 * @since 3.1.0
	 *
	 * @return void
	 */
	public function process_import(): void {
		if ( empty( $_POST['wzkb_action'] ) || 'import_settings' !== $_POST['wzkb_action'] ) {
			return;
		}

		if ( ! isset( $_POST['wzkb_import_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['wzkb_import_settings_nonce'] ), 'wzkb_import_settings' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$imported = $this->read_uploaded_settings();
		if ( null === $imported ) {
			return;
		}

		$existing = get_option( 'wzkb_settings', array() );

		update_option( 'wzkb_settings', self::merge_imported_settings( is_array( $existing ) ? $existing : array(), $imported ) );

		add_settings_error(
			'wzkb-notices',
			'wzkb_import_success',
			esc_html__( 'Settings imported successfully.', 'knowledgebase' ),
			'success'
		);
	}

	/**
	 * Read and decode the uploaded settings file.
	 *
	 * Adds a settings error and returns null when the upload is unusable.
	 *
	 * @since 3.3.0
	 *
	 * @return array|null Decoded settings, or null on failure.
	 */
	protected function read_uploaded_settings(): ?array {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified by the caller.
		$file_name = isset( $_FILES['wzkb_import_file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['wzkb_import_file']['name'] ) ) : '';
		$extension = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );

		if ( 'json' !== $extension ) {
			add_settings_error(
				'wzkb-notices',
				'wzkb_import_invalid_file',
				esc_html__( 'Import failed: please upload a valid .json file.', 'knowledgebase' ),
				'error'
			);
			return null;
		}

		$tmp = isset( $_FILES['wzkb_import_file']['tmp_name'] ) ? wp_unslash( $_FILES['wzkb_import_file']['tmp_name'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( empty( $tmp ) || ! is_readable( $tmp ) ) {
			add_settings_error(
				'wzkb-notices',
				'wzkb_import_no_file',
				esc_html__( 'Import failed: no file was uploaded.', 'knowledgebase' ),
				'error'
			);
			return null;
		}

		$raw      = file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$imported = json_decode( $raw, true );

		if ( ! is_array( $imported ) ) {
			add_settings_error(
				'wzkb-notices',
				'wzkb_import_invalid_json',
				esc_html__( 'Import failed: the file does not contain valid JSON settings.', 'knowledgebase' ),
				'error'
			);
			return null;
		}

		return $imported;
	}

	/**
	 * Merge imported settings into the existing ones.
	 *
	 * Imported values win for non-sensitive keys. Sensitive values are site-specific
	 * encrypted values that are never present in an export, so the existing ones are kept.
	 *
	 * @since 3.3.0
	 *
	 * @param array $existing Saved settings.
	 * @param array $imported Settings from the uploaded file.
	 * @return array Merged settings.
	 */
	protected static function merge_imported_settings( array $existing, array $imported ): array {
		$sensitive_keys = self::get_sensitive_keys();
		$merged         = array_merge( $existing, $imported );

		foreach ( array_intersect_key( $existing, array_flip( $sensitive_keys['top_level'] ) ) as $key => $value ) {
			$merged[ $key ] = $value;
		}

		foreach ( $sensitive_keys['repeater'] as $repeater_key => $subfield_keys ) {
			if ( isset( $merged[ $repeater_key ] ) && is_array( $merged[ $repeater_key ] ) ) {
				$existing_rows           = isset( $existing[ $repeater_key ] ) && is_array( $existing[ $repeater_key ] ) ? $existing[ $repeater_key ] : array();
				$merged[ $repeater_key ] = self::restore_repeater_secrets( $merged[ $repeater_key ], $existing_rows, $subfield_keys );
			}
		}

		return $merged;
	}

	/**
	 * Restore sensitive sub-fields of repeater rows from the saved rows, matched by row_id.
	 *
	 * @since 3.3.0
	 *
	 * @param array    $rows          Imported rows.
	 * @param array    $existing_rows Saved rows.
	 * @param string[] $subfield_keys Sensitive sub-field keys.
	 * @return array Rows with sensitive sub-fields restored; new rows get an empty value.
	 */
	protected static function restore_repeater_secrets( array $rows, array $existing_rows, array $subfield_keys ): array {
		$existing_by_row_id = array();
		foreach ( $existing_rows as $row ) {
			if ( isset( $row['row_id'] ) ) {
				$existing_by_row_id[ (string) $row['row_id'] ] = $row;
			}
		}

		foreach ( $rows as $idx => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$row_id       = isset( $row['row_id'] ) ? (string) $row['row_id'] : '';
			$existing_row = $existing_by_row_id[ $row_id ] ?? array();

			// Rows are stored as { row_id, fields: { ... } }; flat rows are kept for older data.
			$nested = isset( $row['fields'] ) && is_array( $row['fields'] );
			foreach ( $subfield_keys as $subfield ) {
				if ( $nested ) {
					$rows[ $idx ]['fields'][ $subfield ] = $existing_row['fields'][ $subfield ] ?? '';
				} else {
					$rows[ $idx ][ $subfield ] = $existing_row[ $subfield ] ?? '';
				}
			}
		}

		return $rows;
	}

	/**
	 * Render the Settings Import / Export card on the Tools page.
	 *
	 * @since 3.1.0
	 *
	 * @return void
	 */
	public function render_card(): void {
		$tools_url = admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb_tools_page' );
		?>
		<div class="postbox">
			<h2 class="hndle"><span><?php esc_html_e( 'Settings Import / Export', 'knowledgebase' ); ?></span></h2>
			<div class="inside">

				<p><?php esc_html_e( 'Export your Knowledge Base settings as a JSON file, or import a previously exported file to restore them.', 'knowledgebase' ); ?></p>
				<p class="description"><?php esc_html_e( 'Note: sensitive values such as API keys and webhook secrets are excluded from the export and must be re-entered after import.', 'knowledgebase' ); ?></p>

				<form method="post" action="<?php echo esc_url( $tools_url ); ?>">
					<?php wp_nonce_field( 'wzkb_export_settings', 'wzkb_export_settings_nonce' ); ?>
					<input type="hidden" name="wzkb_action" value="export_settings" />
					<p>
						<input type="submit" class="button button-secondary" value="<?php esc_attr_e( 'Export Settings', 'knowledgebase' ); ?>" />
					</p>
				</form>

				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( $tools_url ); ?>">
					<?php wp_nonce_field( 'wzkb_import_settings', 'wzkb_import_settings_nonce' ); ?>
					<input type="hidden" name="wzkb_action" value="import_settings" />
					<p>
						<input type="file" name="wzkb_import_file" accept=".json" />
					</p>
					<p>
						<input type="submit" class="button button-secondary" value="<?php esc_attr_e( 'Import Settings', 'knowledgebase' ); ?>" />
					</p>
				</form>

			</div><!-- /.inside -->
		</div><!-- /.postbox -->
		<?php
	}

	/**
	 * Return the settings array with all sensitive field values removed.
	 *
	 * Top-level sensitive keys are unset entirely. Sensitive subfields inside
	 * repeater rows are set to an empty string so the row structure is preserved.
	 *
	 * @since 3.1.0
	 *
	 * @param array $settings Settings array to sanitize for export.
	 * @return array
	 */
	public static function strip_sensitive_fields( array $settings ): array {
		$sensitive = self::get_sensitive_keys();

		foreach ( $sensitive['top_level'] as $key ) {
			unset( $settings[ $key ] );
		}

		foreach ( $sensitive['repeater'] as $repeater_key => $subfield_keys ) {
			if ( ! isset( $settings[ $repeater_key ] ) || ! is_array( $settings[ $repeater_key ] ) ) {
				continue;
			}

			foreach ( $settings[ $repeater_key ] as $idx => $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				foreach ( $subfield_keys as $subfield ) {
					if ( isset( $row['fields'] ) && is_array( $row['fields'] ) ) {
						$settings[ $repeater_key ][ $idx ]['fields'][ $subfield ] = '';
					} else {
						$settings[ $repeater_key ][ $idx ][ $subfield ] = '';
					}
				}
			}
		}

		return $settings;
	}

	/**
	 * Derive sensitive field keys from the registered settings definition.
	 *
	 * Returns an array with two keys:
	 *  - 'top_level'  => string[] of top-level setting IDs with type 'sensitive'.
	 *  - 'repeater'   => array<string, string[]> mapping each repeater field ID to
	 *                    the list of its sensitive subfield IDs.
	 *
	 * @since 3.1.0
	 *
	 * @return array{top_level: string[], repeater: array<string, string[]>}
	 */
	private static function get_sensitive_keys(): array {
		$top_level = array();
		$repeater  = array();

		$all_settings = Settings::get_registered_settings();

		foreach ( $all_settings as $section_settings ) {
			if ( ! is_array( $section_settings ) ) {
				continue;
			}

			foreach ( $section_settings as $setting ) {
				if ( ! isset( $setting['id'], $setting['type'] ) ) {
					continue;
				}

				$id   = $setting['id'];
				$type = $setting['type'];

				if ( 'sensitive' === $type ) {
					$top_level[] = $id;
					continue;
				}

				if ( 'repeater' === $type && ! empty( $setting['fields'] ) && is_array( $setting['fields'] ) ) {
					$sensitive_subfields = array();

					foreach ( $setting['fields'] as $subfield ) {
						if ( isset( $subfield['id'], $subfield['type'] ) && 'sensitive' === $subfield['type'] ) {
							$sensitive_subfields[] = $subfield['id'];
						}
					}

					if ( ! empty( $sensitive_subfields ) ) {
						$repeater[ $id ] = $sensitive_subfields;
					}
				}
			}
		}

		return array(
			'top_level' => $top_level,
			'repeater'  => $repeater,
		);
	}
}
