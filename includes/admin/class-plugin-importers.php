<?php
/**
 * Plugin Importers admin page.
 *
 * @package WebberZone\Knowledge_Base\Admin
 * @since 3.1.0
 */

namespace WebberZone\Knowledge_Base\Admin;

use WebberZone\Knowledge_Base\Admin\Importers\Base_Importer;
use WebberZone\Knowledge_Base\Admin\Importers\BasePress_Importer;
use WebberZone\Knowledge_Base\Admin\Importers\BetterDocs_Importer;
use WebberZone\Knowledge_Base\Admin\Importers\Echo_KB_Importer;
use WebberZone\Knowledge_Base\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Plugin_Importers class.
 *
 * Registers the importer admin page and handles AJAX batch import requests
 * for BasePress, BetterDocs, and Echo Knowledge Base.
 *
 * @since 3.1.0
 */
class Plugin_Importers {

	/**
	 * Admin page hook suffix.
	 *
	 * @var string
	 */
	private string $page_hook = '';

	/**
	 * Admin page slug.
	 *
	 * @var string
	 */
	private string $page_slug = 'wzkb-plugin-importers';

	/**
	 * Registered importer instances, keyed by slug.
	 *
	 * @var array<string, Base_Importer>
	 */
	private array $importers = array();

	/**
	 * Constructor.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		$this->register_importers();

		Hook_Registry::add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		Hook_Registry::add_action( 'wp_ajax_wzkb_plugin_import_preview', array( $this, 'ajax_preview' ) );
		Hook_Registry::add_action( 'wp_ajax_wzkb_plugin_import_batch', array( $this, 'ajax_batch' ) );
	}

	/**
	 * Instantiate all importer adapters.
	 *
	 * @return void
	 */
	private function register_importers(): void {
		$adapters = array(
			new BasePress_Importer(),
			new BetterDocs_Importer(),
			new Echo_KB_Importer(),
		);

		foreach ( $adapters as $importer ) {
			$this->importers[ $importer->get_slug() ] = $importer;
		}
	}

	/**
	 * Register the hidden admin sub-menu page.
	 *
	 * @since 3.1.0
	 *
	 * @return void
	 */
	public function admin_menu(): void {
		$this->page_hook = add_submenu_page(
			'edit.php?post_type=wz_knowledgebase',
			esc_html__( 'Import from Another Plugin', 'knowledgebase' ),
			esc_html__( 'Import', 'knowledgebase' ),
			'manage_options',
			$this->page_slug,
			array( $this, 'render_page' )
		);

		remove_submenu_page( 'edit.php?post_type=wz_knowledgebase', $this->page_slug );
	}

	/**
	 * Enqueue scripts on the importer page only.
	 *
	 * @since 3.1.0
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_scripts( string $hook ): void {
		if ( $hook !== $this->page_hook ) {
			return;
		}

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style(
			'wzkb-wizard-css',
			plugins_url( 'includes/admin/settings/css/wizard' . $min . '.css', WZKB_PLUGIN_FILE ),
			array(),
			WZKB_VERSION
		);

		wp_enqueue_script(
			'wzkb-plugin-importers',
			plugins_url( 'includes/admin/js/plugin-importers' . $min . '.js', WZKB_PLUGIN_FILE ),
			array( 'jquery' ),
			WZKB_VERSION,
			true
		);

		wp_localize_script(
			'wzkb-plugin-importers',
			'WZKBPluginImporter',
			array(
				'ajax_url'      => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'wzkb_plugin_import' ),
				'edit_url_base' => admin_url( 'post.php?action=edit&post=' ),
				'strings'       => array(
					'importing'       => __( 'Importing…', 'knowledgebase' ),
					'importing_terms' => __( 'Importing terms…', 'knowledgebase' ),
					'importing_posts' => __( 'Importing articles…', 'knowledgebase' ),
					'done'            => __( 'Import complete.', 'knowledgebase' ),
					'error'           => __( 'An error occurred. Please try again.', 'knowledgebase' ),
					'confirm_slug'    => __( 'Update Knowledge Base slug to match source plugin? This changes your KB URLs.', 'knowledgebase' ),
					/* translators: 1: imported article count 2: skipped article count */
					'summary'         => __( '%1$d imported, %2$d skipped.', 'knowledgebase' ),
				),
			)
		);

		wp_enqueue_style( 'wp-spinner' );
	}

	/**
	 * Render the importer page. Shows source selector or a specific importer screen.
	 *
	 * @since 3.1.0
	 *
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$source = isset( $_GET['source'] ) ? sanitize_key( $_GET['source'] ) : '';

		if ( $source && isset( $this->importers[ $source ] ) ) {
			$this->render_importer_screen( $this->importers[ $source ] );
		} else {
			$this->render_selector_screen();
		}
	}

	/**
	 * Render the source selector — lists all importers with their detection status.
	 *
	 * @return void
	 */
	private function render_selector_screen(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Import from Another Plugin', 'knowledgebase' ); ?></h1>
			<p><?php esc_html_e( 'Choose the plugin you are migrating from. Knowledge Base will copy your articles, categories, and tags without removing your original content.', 'knowledgebase' ); ?></p>

			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Plugin', 'knowledgebase' ); ?></th>
						<th><?php esc_html_e( 'Status', 'knowledgebase' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $this->importers as $slug => $importer ) : ?>
					<?php $detected = $importer->detect(); ?>
					<tr>
						<td><strong><?php echo esc_html( $importer->get_label() ); ?></strong></td>
						<td>
							<?php if ( $detected ) : ?>
								<span style="color:#00a32a;">&#10003; <?php esc_html_e( 'Content found', 'knowledgebase' ); ?></span>
							<?php else : ?>
								<span style="color:#999;"><?php esc_html_e( 'Not detected', 'knowledgebase' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $detected ) : ?>
								<a href="<?php echo esc_url( $this->importer_url( $slug ) ); ?>" class="button button-primary">
									<?php esc_html_e( 'Import', 'knowledgebase' ); ?>
								</a>
							<?php else : ?>
								<a href="<?php echo esc_url( $this->importer_url( $slug ) ); ?>" class="button button-secondary">
									<?php esc_html_e( 'View', 'knowledgebase' ); ?>
								</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render the individual importer screen.
	 *
	 * @param Base_Importer $importer Importer adapter instance.
	 * @return void
	 */
	private function render_importer_screen( Base_Importer $importer ): void {
		$detected       = $importer->detect();
		$preview        = $detected ? $importer->get_preview_data() : null;
		$suggested_slug = $preview ? $preview['suggested_slug'] : '';
		$url_note       = $preview ? $preview['url_note'] : '';
		$back_url       = admin_url( 'edit.php?post_type=wz_knowledgebase&page=' . $this->page_slug );
		?>
		<div class="wrap">
			<h1>
				<?php
				printf(
					/* translators: %s: plugin name */
					esc_html__( 'Import from %s', 'knowledgebase' ),
					esc_html( $importer->get_label() )
				);
				?>
			</h1>
			<p><a href="<?php echo esc_url( $back_url ); ?>">&larr; <?php esc_html_e( 'Back to importers', 'knowledgebase' ); ?></a></p>

			<?php if ( ! $detected ) : ?>
				<div class="notice notice-warning inline">
					<p>
						<?php
						printf(
							/* translators: %s: plugin name */
							esc_html__( 'No %s content was found in this database. Make sure the plugin was installed and had content before trying to import.', 'knowledgebase' ),
							esc_html( $importer->get_label() )
						);
						?>
					</p>
				</div>
			<?php else : ?>

			<div id="poststuff">
				<?php // Preview card. ?>
				<div class="postbox">
					<h2 class="hndle"><span><?php esc_html_e( 'What will be imported', 'knowledgebase' ); ?></span></h2>
					<div class="inside">
						<ul style="list-style:disc;margin-left:1.5em;">
							<li>
								<?php
								printf(
									/* translators: %d: number of articles */
									esc_html( _n( '%d article', '%d articles', $preview['posts'], 'knowledgebase' ) ),
									(int) $preview['posts']
								);
								?>
							</li>
							<?php if ( ! empty( $preview['details'] ) ) : ?>
								<?php foreach ( $preview['details'] as $detail ) : ?>
									<li><?php echo esc_html( $detail ); ?></li>
								<?php endforeach; ?>
							<?php else : ?>
								<li>
									<?php
									printf(
										/* translators: %d: number of terms */
										esc_html( _n( '%d category / tag', '%d categories and tags', $preview['terms'], 'knowledgebase' ) ),
										(int) $preview['terms']
									);
									?>
								</li>
							<?php endif; ?>
						</ul>
						<?php if ( $suggested_slug ) : ?>
							<p>
								<?php
								printf(
									/* translators: %s: slug value */
									esc_html__( 'Detected base URL slug: %s', 'knowledgebase' ),
									'<code>' . esc_html( $suggested_slug ) . '</code>'
								);
								?>
							</p>
						<?php endif; ?>
						<?php if ( ! empty( $preview['sections'] ) ) : ?>
							<?php foreach ( $preview['sections'] as $section ) : ?>
								<p style="margin:1em 0 .25em;"><strong><?php echo esc_html( $section['heading'] ); ?></strong></p>
								<ul style="list-style:disc;margin-left:1.5em;margin-top:0;">
									<?php foreach ( $section['items'] as $item ) : ?>
										<li><?php echo wp_kses( $item, array( 'code' => array() ) ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endforeach; ?>
						<?php endif; ?>

						<?php if ( $url_note ) : ?>
							<div class="notice notice-info inline"><p><?php echo wp_kses( $url_note, array( 'code' => array() ) ); ?></p></div>
						<?php endif; ?>
					</div>
				</div>

				<?php // Destructive warning. ?>
				<div class="notice notice-error inline">
					<p><strong><?php esc_html_e( 'Before you continue:', 'knowledgebase' ); ?></strong></p>
					<ul style="list-style:disc;margin-left:1.5em;">
						<li><?php esc_html_e( 'Back up your database. This operation inserts new posts and terms and cannot be automatically undone.', 'knowledgebase' ); ?></li>
						<li><?php esc_html_e( 'Your original content will not be deleted — but you should verify the import before removing the source plugin.', 'knowledgebase' ); ?></li>
						<li><?php esc_html_e( 'Running the importer a second time will skip already-imported articles.', 'knowledgebase' ); ?></li>
						<?php foreach ( $importer->get_before_import_notices() as $notice ) : ?>
							<li><?php echo esc_html( $notice ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<?php // Import form. ?>
				<div id="wzkb-importer-form">
					<p>
						<label>
							<input type="checkbox" id="wzkb-importer-backup-confirm" value="1" />
							<?php esc_html_e( 'I have backed up my database and understand this will add new Knowledge Base content.', 'knowledgebase' ); ?>
						</label>
					</p>

					<?php if ( $suggested_slug ) : ?>
					<p>
						<label>
							<input type="checkbox" id="wzkb-importer-update-slug" value="1" />
							<?php
							printf(
								/* translators: %s: slug value */
								esc_html__( 'Update the "%s" entry page with the Knowledge Base shortcode and set URL slug to match source plugin URLs.', 'knowledgebase' ),
								esc_html( $suggested_slug )
							);
							?>
						</label>
					</p>
					<?php endif; ?>

					<p>
						<button
							id="wzkb-importer-start"
							class="button button-primary"
							disabled
							data-source="<?php echo esc_attr( $importer->get_slug() ); ?>"
							data-total="<?php echo esc_attr( (string) $preview['posts'] ); ?>"
							data-suggested-slug="<?php echo esc_attr( $suggested_slug ); ?>"
						>
							<?php esc_html_e( 'Start Import', 'knowledgebase' ); ?>
						</button>
					</p>

					<?php // Progress UI (hidden until import starts). ?>
					<div id="wzkb-importer-progress" style="display:none;">
						<div style="background:#e0e0e0;border-radius:3px;height:20px;margin-bottom:.5em;">
							<div id="wzkb-importer-bar" style="background:#0073aa;height:20px;width:0;border-radius:3px;transition:width .3s;"></div>
						</div>
						<p id="wzkb-importer-status"></p>
						<div id="wzkb-importer-log" style="background:#f6f7f7;border:1px solid #ddd;padding:.5em 1em;max-height:200px;overflow-y:auto;font-family:monospace;font-size:12px;"></div>
					</div>

					<div id="wzkb-importer-done" style="display:none;">
						<div class="notice notice-success inline">
							<p id="wzkb-importer-summary"></p>
						</div>

						<div style="margin-top:1.5em;">
							<p><strong><?php esc_html_e( 'What would you like to do next?', 'knowledgebase' ); ?></strong></p>
							<ul class="wizard-setup-next-actions-horizontal">
								<li>
									<button type="button" class="button button-primary button-large" onclick="jQuery.post(ajaxurl, {action:'wzkb_flush_permalinks', nonce:'<?php echo esc_js( wp_create_nonce( 'wzkb_flush_permalinks' ) ); ?>'}, function(r){ alert(r.success ? r.data.message : (r.data && r.data.message ? r.data.message : '<?php echo esc_js( __( 'Error flushing permalinks.', 'knowledgebase' ) ); ?>')); }).fail(function(){ alert('<?php echo esc_js( __( 'Error flushing permalinks.', 'knowledgebase' ) ); ?>'); });">
										<span class="dashicons dashicons-update"></span>
										<?php esc_html_e( 'Flush Permalinks', 'knowledgebase' ); ?>
									</button>
									<span class="wizard-action-description"><?php esc_html_e( 'Apply new URL structure', 'knowledgebase' ); ?></span>
								</li>
								<li>
									<a class="button button-large" href="<?php echo esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-settings' ) ); ?>">
										<span class="dashicons dashicons-admin-settings"></span>
										<?php esc_html_e( 'Configure settings', 'knowledgebase' ); ?>
									</a>
									<span class="wizard-action-description"><?php esc_html_e( 'Customize display and URLs', 'knowledgebase' ); ?></span>
								</li>
								<li class="setup-article">
									<a class="button button-large" href="<?php echo esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase' ) ); ?>">
										<span class="dashicons dashicons-list-view"></span>
										<?php esc_html_e( 'View imported articles', 'knowledgebase' ); ?>
									</a>
									<span class="wizard-action-description"><?php esc_html_e( 'Review your migrated content', 'knowledgebase' ); ?></span>
								</li>
								<li>
									<a class="button button-large" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=wzkb_product&post_type=wz_knowledgebase' ) ); ?>">
										<span class="dashicons dashicons-products"></span>
										<?php esc_html_e( 'View products', 'knowledgebase' ); ?>
									</a>
									<span class="wizard-action-description"><?php esc_html_e( 'Check imported products', 'knowledgebase' ); ?></span>
								</li>
								<li class="setup-sections">
									<a class="button button-large" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=wzkb_category&post_type=wz_knowledgebase' ) ); ?>">
										<span class="dashicons dashicons-category"></span>
										<?php esc_html_e( 'View sections', 'knowledgebase' ); ?>
									</a>
									<span class="wizard-action-description"><?php esc_html_e( 'Check imported categories', 'knowledgebase' ); ?></span>
								</li>
								<li>
									<?php $archive_link = get_post_type_archive_link( 'wz_knowledgebase' ); ?>
									<a class="button button-large" href="<?php echo esc_url( $archive_link ? (string) $archive_link : admin_url() ); ?>" target="_blank">
										<span class="dashicons dashicons-visibility"></span>
										<?php esc_html_e( 'Visit Knowledge Base', 'knowledgebase' ); ?>
									</a>
									<span class="wizard-action-description"><?php esc_html_e( 'Preview the front end', 'knowledgebase' ); ?></span>
								</li>
							</ul>
						</div>
					</div>
				</div>

			</div><!-- /#poststuff -->
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * AJAX handler: return preview data for a source plugin.
	 *
	 * @since 3.1.0
	 *
	 * @return void
	 */
	public function ajax_preview(): void {
		check_ajax_referer( 'wzkb_plugin_import', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'knowledgebase' ) ), 403 );
		}

		$source   = isset( $_POST['source'] ) ? sanitize_key( $_POST['source'] ) : '';
		$importer = $this->get_importer( $source );

		if ( ! $importer ) {
			wp_send_json_error( array( 'message' => __( 'Unknown source plugin.', 'knowledgebase' ) ) );
		}

		wp_send_json_success( $importer->get_preview_data() );
	}

	/**
	 * AJAX handler: run one import batch (phase=terms or phase=posts).
	 *
	 * @since 3.1.0
	 *
	 * @return void
	 */
	public function ajax_batch(): void {
		check_ajax_referer( 'wzkb_plugin_import', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'knowledgebase' ) ), 403 );
		}

		$source      = isset( $_POST['source'] ) ? sanitize_key( $_POST['source'] ) : '';
		$phase       = isset( $_POST['phase'] ) ? sanitize_key( $_POST['phase'] ) : 'terms';
		$offset      = isset( $_POST['offset'] ) ? (int) $_POST['offset'] : 0;
		$batch_size  = 25;
		$update_slug = ! empty( $_POST['update_slug'] );

		$importer = $this->get_importer( $source );

		if ( ! $importer ) {
			wp_send_json_error( array( 'message' => __( 'Unknown source plugin.', 'knowledgebase' ) ) );
		}

		if ( 'terms' === $phase ) {
			$result = $importer->import_terms();

			// Optionally update the KB slug to match source.
			if ( $update_slug ) {
				$suggested = $importer->get_suggested_kb_slug();
				if ( $suggested ) {
					\WebberZone\Knowledge_Base\Options_API::update_settings( array( 'kb_slug' => sanitize_title( $suggested ) ) );
				}
			}

			wp_send_json_success(
				array(
					'phase'    => 'terms',
					'imported' => $result['imported'],
					'skipped'  => $result['skipped'],
					'errors'   => $result['errors'],
					'done'     => false,
					'log'      => $this->format_log( 'terms', $result ),
				)
			);
		}

		// Phase: posts.
		$total  = $importer->get_post_count();
		$result = $importer->import_posts_batch( $offset, $batch_size );
		$next   = $offset + $result['processed'];
		$done   = $next >= $total || 0 === $result['processed'];

		if ( $done ) {
			$importer->finalize_import( $update_slug );
			flush_rewrite_rules( true );
		}

		wp_send_json_success(
			array(
				'phase'     => 'posts',
				'imported'  => $result['imported'],
				'skipped'   => $result['skipped'],
				'errors'    => $result['errors'],
				'processed' => min( $next, $total ),
				'total'     => $total,
				'done'      => $done,
				'log'       => $this->format_log( 'posts', $result, $offset ),
			)
		);
	}

	/**
	 * Format a human-readable log entry from a batch result.
	 *
	 * @param string $phase   'terms' or 'posts'.
	 * @param array  $result  Batch result array.
	 * @param int    $offset  Batch offset (posts phase only).
	 * @return string
	 */
	private function format_log( string $phase, array $result, int $offset = 0 ): string {
		if ( 'terms' === $phase ) {
			return sprintf(
				/* translators: 1: imported count 2: skipped count */
				__( 'Terms — imported: %1$d, skipped: %2$d', 'knowledgebase' ),
				$result['imported'],
				$result['skipped']
			);
		}

		$line = sprintf(
			/* translators: 1: offset 2: imported count 3: skipped count */
			__( 'Articles %1$d+ — imported: %2$d, skipped: %3$d', 'knowledgebase' ),
			$offset,
			$result['imported'],
			$result['skipped']
		);

		if ( ! empty( $result['errors'] ) ) {
			$line .= ' | ' . implode( '; ', $result['errors'] );
		}

		return $line;
	}

	/**
	 * Return the importer instance for a given slug, or null.
	 *
	 * @param string $slug Importer slug.
	 * @return Base_Importer|null
	 */
	private function get_importer( string $slug ): ?Base_Importer {
		return $this->importers[ $slug ] ?? null;
	}

	/**
	 * Build the URL for a specific importer screen.
	 *
	 * @param string $slug Importer slug.
	 * @return string
	 */
	private function importer_url( string $slug ): string {
		return admin_url( 'edit.php?post_type=wz_knowledgebase&page=' . $this->page_slug . '&source=' . $slug );
	}
}
