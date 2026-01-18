<?php
/**
 * Register ATA Custom Post Type admin columns.
 *
 * @link  https://webberzone.com
 * @since 1.7.0
 *
 * @package WebberZone\Snippetz
 */

namespace WebberZone\Snippetz\Snippets;

use WebberZone\Snippetz\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ATA Metabox class to add custom columns to the ata_snippets post type listing in admin.
 *
 * @since 1.7.0
 */
class Admin_Columns {

	/**
	 * Meta key for disable snippet.
	 *
	 * @var string
	 */
	const DISABLE_META_KEY = '_ata_disable_snippet';

	/**
	 * Constructor class.
	 */
	public function __construct() {
		Hook_Registry::add_filter( 'manage_ata_snippets_posts_columns', array( $this, 'manage_post_columns' ), 10 );
		Hook_Registry::add_filter( 'manage_edit-ata_snippets_sortable_columns', array( $this, 'set_sortable_columns' ) );
		Hook_Registry::add_action( 'manage_ata_snippets_posts_custom_column', array( $this, 'manage_posts_custom_column' ), 10, 2 );
		Hook_Registry::add_action( 'admin_head', array( $this, 'custom_css' ) );
		Hook_Registry::add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Add AJAX action.
		Hook_Registry::add_action( 'wp_ajax_ata_toggle_snippet', array( $this, 'ajax_toggle_snippet' ) );
	}

	/**
	 * Add columns to the custom post type ata_snippets.
	 *
	 * @param array $columns An associative array of column headings.
	 */
	public function manage_post_columns( $columns ) {
		// Create a new array so we can modify array position.
		$new_columns = array(
			'cb'      => $columns['cb'],
			'enabled' => '<span class="ata-column-narrow">' . __( 'Status', 'add-to-all' ) . '</span>',
		);

		// Loop through the existing columns and evaluate the column.
		foreach ( $columns as $key => $title ) {
			if ( 'cb' !== $key ) {
				$new_columns[ $key ] = $title;
			}

			if ( 'title' === $key ) {
				$new_columns['type']      = __( 'Type', 'add-to-all' );
				$new_columns['shortcode'] = __( 'Shortcode', 'add-to-all' );
			}
		}

		return $new_columns;
	}

	/**
	 * Make custom column sortable.
	 *
	 * @param array $columns The existing sortable columns.
	 * @return array The modified sortable columns.
	 */
	public function set_sortable_columns( $columns ) {
		$columns['type'] = 'type';
		return $columns;
	}

	/**
	 * Add custom column content.
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id    Post ID.
	 */
	public function manage_posts_custom_column( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'enabled':
				$disabled = get_post_meta( $post_id, self::DISABLE_META_KEY, true );

				printf(
					'<div class="ata-snippet-toggle-wrapper" data-post-id="%1$s" data-nonce="%2$s"></div>',
					esc_attr( (string) $post_id ),
					esc_attr( wp_create_nonce( 'ata_toggle_snippet_' . $post_id ) )
				);
				wp_localize_script(
					'ata-admin-snippets',
					'ataSnippetData_' . $post_id,
					array(
						'disabled' => ! empty( $disabled ),
						'ajaxurl'  => admin_url( 'admin-ajax.php' ),
					)
				);
				break;

			case 'type':
				$styles       = Functions::get_snippet_type_styles( get_post( $post_id ) );
				$snippet_type = $styles['type'];
				if ( ! get_post_meta( $post_id, '_ata_snippet_type', true ) ) {
					update_post_meta( $post_id, '_ata_snippet_type', $snippet_type );
				}
				$url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : admin_url( 'edit.php?post_type=ata_snippets' );
				$url = add_query_arg( 'type', $snippet_type, $url );

				printf(
					'<a class="snippet-type-badge" href="%1$s" data-snippet-type="%2$s" style="font-size:0.8em;padding:5px;background:%3$s;color:%4$s;border:1px solid %4$s;border-radius:5px;">%5$s</a>',
					esc_url( $url ),
					esc_attr( $snippet_type ),
					esc_attr( $styles['background'] ),
					esc_attr( $styles['color'] ),
					esc_html( strtoupper( $snippet_type ) )
				);
				break;

			case 'shortcode':
				$shortcode = "[ata_snippet id={$post_id}]";

				$output = sprintf(
					'<span class="ata_shortcode"><input type="text" readonly="readonly" value="%1$s" class="large-text code" onfocus="this.select();" onclick="navigator.clipboard.writeText(this.value);" title="%2$s" /></span>',
					esc_attr( $shortcode ),
					__( 'Copy to clipboard', 'add-to-all' )
				);

				echo trim( $output ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;

			default:
				break;
		}
	}

	/**
	 * Add custom CSS to the admin head.
	 */
	public function custom_css() {
		$screen = get_current_screen();

		if ( ! $screen || 'ata_snippets' !== $screen->post_type ) {
			return;
		}
		?>
		<style>
			.column-enabled {
				width: 60px;
			}
			.column-type {
				width: 90px;
			}
			.column-shortcode {
				width: 200px;
			}
			.ata_shortcode input.code {
				min-width: 100%;
			}
			.ata-column-narrow {
				display: block;
			}
			.ata-snippet-toggle-wrapper {
				text-align: center;
			}
		</style>
		<?php
	}

	/**
	 * Order by or filtery by type meta value.
	 *
	 * @param \WP_Query $query The current query object.
	 */
	public function pre_get_posts( $query ) {
		// Check if we are on the admin screen for ata_snippets and sorting by type column.
		if ( is_admin() && 'ata_snippets' === $query->get( 'post_type' ) ) {
			if ( 'type' === $query->get( 'orderby' ) ) {
				$query->set( 'meta_key', '_ata_snippet_type' );
				$query->set( 'orderby', 'meta_value' );
			}
		}

		// Filter by type if type is set in the query.
		if ( isset( $_GET['type'] ) && ! empty( $_GET['type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$meta_query = array(
				array(
					'key'   => '_ata_snippet_type',
					'value' => strtolower( sanitize_text_field( wp_unslash( $_GET['type'] ) ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				),
			);
			// Add the meta query to the main query object.
			$query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Enqueue admin scripts.
	 */
	public function admin_scripts() {
		$minimize = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$screen   = get_current_screen();

		if ( 'edit-ata_snippets' === $screen->id ) {
			wp_enqueue_script(
				'ata-admin-snippets',
				plugins_url( 'includes/snippets/js/admin-snippets' . $minimize . '.js', WZ_SNIPPETZ_FILE ),
				array( 'wp-element', 'wp-i18n', 'wp-components' ),
				WZ_SNIPPETZ_VERSION,
				true
			);

			wp_enqueue_style(
				'wp-components'
			);
		}
	}

	/**
	 * Toggle snippet status via AJAX.
	 */
	public function ajax_toggle_snippet() {
		if ( ! isset( $_POST['post_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No post ID provided.', 'add-to-all' ) ) );
		}

		$post_id = absint( wp_unslash( $_POST['post_id'] ) );

		check_ajax_referer( 'ata_toggle_snippet_' . $post_id, 'nonce' );

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this snippet.', 'add-to-all' ) ) );
		}

		$disabled   = get_post_meta( $post_id, self::DISABLE_META_KEY, true );
		$new_status = ! $disabled;

		update_post_meta( $post_id, self::DISABLE_META_KEY, $new_status );

		wp_send_json_success(
			array(
				'disabled' => $new_status,
			)
		);
	}
}
