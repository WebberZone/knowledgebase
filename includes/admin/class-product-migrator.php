<?php
/**
 * Product Migrator: Migration wizard for Products taxonomy transition.
 *
 * @package WebberZone\Knowledge_Base\Admin
 */

namespace WebberZone\Knowledge_Base\Admin;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Product Migrator: Migration wizard for Products taxonomy transition.
 *
 * @since 3.0.0
 */
class Product_Migrator {
	/**
	 * Menu page handle.
	 *
	 * @var string
	 */
	public $menu_page;

	/**
	 * Taxonomy Product slug.
	 *
	 * @var string
	 */
	protected $taxonomy_product = 'wzkb_product';

	/**
	 * Taxonomy Section slug.
	 *
	 * @var string
	 */
	protected $taxonomy_section = 'wzkb_category';

	/**
	 * Post type Article slug.
	 *
	 * @var string
	 */
	protected $post_type_article = 'wz_knowledgebase';

	/**
	 * Constructor: Hook admin notices and menu.
	 */
	public function __construct() {
		Hook_Registry::add_action( 'admin_notices', array( $this, 'maybe_show_enable_notice' ) );
		Hook_Registry::add_action( 'admin_menu', array( $this, 'register_migration_wizard_page' ) );
		Hook_Registry::add_action( 'wp_ajax_wzkb_dismiss_product_notice', array( $this, 'dismiss_product_notice' ) );
		Hook_Registry::add_action( 'wp_ajax_wzkb_product_migration_batch', array( $this, 'handle_migration_batch' ) );
	}

	/**
	 * Show a dismissible notice to enable the Products taxonomy logic.
	 */
	public function maybe_show_enable_notice() {
		$multi_product_enabled = wzkb_get_option( 'multi_product', 'not-set-random-string' );
		if ( 'not-set-random-string' !== $multi_product_enabled ) {
			return;
		}

		if ( self::is_dismissed() ) {
			return;
		}
		if ( ! self::is_kb_admin_screen() ) {
			return;
		}
		$nonce              = wp_create_nonce( 'wzkb_dismiss_product_notice' );
		$migration_complete = get_option( 'wzkb_product_migration_complete', false );

		// Add a dismissible notice about the new Products taxonomy feature.
		echo '<div class="notice notice-warning is-dismissible wzkb-product-migrate-notice" data-nonce="' . esc_attr( $nonce ) . '" data-dismiss-action="wzkb_dismiss_product_notice">
			<p>
				<strong>' . esc_html__( 'New Multi-Products Mode available!', 'knowledgebase' ) . '</strong> 
				' . esc_html__( 'Organize your knowledge base by product with our new Multi-Products mode! You can migrate your existing content using the migration wizard. If you don\'t want to use this feature, you can dismiss this notice by saving the settings page.', 'knowledgebase' ) . '
			</p>
			<p>
				<a href="' . esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-settings#general' ) ) . '" class="button button-primary">' .
					esc_html__( 'Enable Multi-Products', 'knowledgebase' ) .
				'</a>';

		// Only show Migration Wizard link if migration is not completed yet.
		if ( ! $migration_complete ) {
			echo ' <a href="' . esc_url( admin_url( 'edit.php?post_type=wz_knowledgebase&page=wzkb-product-migration' ) ) . '" class="button">' .
					esc_html__( 'Migration Wizard', 'knowledgebase' ) .
				'</a>';
		}

		echo '</p>
		</div>';
	}

	/**
	 * Register the migration wizard admin page (submenu under KB, but no visible link unless you add one).
	 */
	public function register_migration_wizard_page() {
		$migration_complete = get_option( 'wzkb_product_migration_complete', false );
		if ( $migration_complete ) {
			return;
		}
		$this->menu_page = add_submenu_page(
			'edit.php?post_type=wz_knowledgebase',
			esc_html__( 'Product Migration', 'knowledgebase' ),
			esc_html__( 'Product Migration', 'knowledgebase' ),
			'manage_options',
			'wzkb-product-migration',
			array( $this, 'render_migration_wizard' ),
		);
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts for migration wizard.
	 *
	 * @param string $hook The current admin screen hook.
	 */
	public function enqueue_scripts( $hook ) {
		if ( $this->menu_page !== $hook ) {
			return;
		}
		$minimize = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script(
			'wzkb-product-migrator',
			plugins_url( "js/product-migrator{$minimize}.js", __FILE__ ),
			array( 'jquery' ),
			WZKB_VERSION,
			true
		);
		wp_localize_script(
			'wzkb-product-migrator',
			'wzkbProductMigrator',
			array(
				'nonce'   => wp_create_nonce( 'wzkb_product_migration' ),
				'strings' => array(
					'migration_failed'   => esc_html__( 'Migration failed', 'knowledgebase' ),
					'unknown_error'      => esc_html__( 'Unknown error', 'knowledgebase' ),
					'migration_complete' => esc_html__( 'Migration complete!', 'knowledgebase' ),
				),
			)
		);
	}

	/**
	 * Render the migration wizard screen and handle migration logic.
	 */
	public function render_migration_wizard() {

		$migration_complete = get_option( 'wzkb_product_migration_complete', false );

		// Verify user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'knowledgebase' ) );
		}

		ob_start();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Product Migration Wizard', 'knowledgebase' ); ?></h1>
			<?php if ( $migration_complete ) : ?>
				<div class="notice notice-success"><p>
					<?php
					printf(
						// translators: %s: Date and time when migration was completed.
						esc_html__( 'Migration already completed on %s.', 'knowledgebase' ),
						esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), intval( $migration_complete ) ) )
					);
					?>
				</p></div>
			<?php endif; ?>

			<p><?php echo esc_html__( 'The Migration Wizard simplifies the process of reorganizing your knowledge base by converting your existing sections into products and mapping articles to their corresponding products. This ensures a more structured, user-friendly, and discoverable knowledge base. Below is a step-by-step overview of what the wizard will do. No changes will be applied until you confirm and initiate the migration.', 'knowledgebase' ); ?></p>
			<ol>
				<li><?php echo esc_html__( 'Convert Sections to Products: Each top-level section in your knowledge base will be transformed into a distinct product, preserving its identity and purpose.', 'knowledgebase' ); ?></li>
				<li><?php echo esc_html__( 'Map Articles to Products: Articles will be intelligently assigned to their respective products based on the existing section hierarchy, ensuring no content is lost or misplaced.', 'knowledgebase' ); ?></li>
				<li><?php echo esc_html__( 'Handle Sub-Sections: Any sub-sections (descendant sections) will be processed to correctly associate their articles with the appropriate product, maintaining organizational clarity.', 'knowledgebase' ); ?></li>
				<li><?php echo esc_html__( 'Remove Old Sections: Once articles are mapped, the original top-level sections will be removed to streamline your knowledge base structure.', 'knowledgebase' ); ?></li>
				<li><?php echo esc_html__( 'Preview with Dry Run: Use the dry-run option to simulate the migration process without making any changes, allowing you to review the outcome before committing.', 'knowledgebase' ); ?></li>
				<li><?php echo esc_html__( 'Confirm and Migrate: After reviewing the dry run, you can start the migration with confidence, knowing your knowledge base will be restructured for improved organization and accessibility.', 'knowledgebase' ); ?></li>
			</ol>
			
			<form id="wzkb-migration-form" method="post">
				<label><input type="checkbox" id="wzkb-dry-run" name="wzkb_dry_run" value="1" checked="checked" /> <?php esc_html_e( 'Dry run (show summary only, no changes)', 'knowledgebase' ); ?></label><br>
				<label><input type="checkbox" id="wzkb-backup-confirm" name="wzkb_backup_confirm" value="1" /> <?php esc_html_e( 'I confirm I have backed up my database and understand this migration cannot be undone.', 'knowledgebase' ); ?></label><br><br>
				<button type="button" class="button button-primary" id="wzkb-migration-start" disabled<?php disabled( $migration_complete ); ?>><?php esc_html_e( 'Start Migration', 'knowledgebase' ); ?></button>
			</form>
			<div style="margin:20px 0; width: 100%; background:#eee; border-radius:4px;">
				<div id="wzkb-migration-progress-bar" style="width:0%; height:24px; background:#0073aa; color:#fff; text-align:center; border-radius:4px; transition:width 0.3s;"></div>
			</div>
			<div id="wzkb-migration-progress-text" style="margin-bottom:10px;"></div>
			<ul id="wzkb-migration-errors" style="color:#b32d2e;"></ul>
			<div class="card" style="max-width: 100%;">
				<h3><?php esc_html_e( 'Migration Log', 'knowledgebase' ); ?> 
					<button type="button" id="wzkb-copy-log" class="button button-secondary" style="float:right;">
						<span class="dashicons dashicons-clipboard" style="margin-right:5px; vertical-align:middle;"></span>
						<?php esc_html_e( 'Copy Log', 'knowledgebase' ); ?>
					</button>
				</h3>
				<div id="wzkb-migration-log" style="max-height: 500px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f8f8f8; font-family: monospace; margin-top: 10px;"></div>
			</div>
		</div>
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Handle migration batch.
	 */
	public function handle_migration_batch() {
		check_ajax_referer( 'wzkb_product_migration', '_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'knowledgebase' ), 403 );
		}

		$state   = isset( $_POST['state'] ) ? map_deep( wp_unslash( $_POST['state'] ), 'sanitize_text_field' ) : array();
		$state   = is_array( $state ) ? $state : array();
		$dry_run = isset( $_POST['dry_run'] ) && absint( $_POST['dry_run'] );
		$log     = is_array( get_transient( 'wzkb_migration_log' ) ) ? get_transient( 'wzkb_migration_log' ) : array();
		$step    = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 0;

		$response_data = array(
			'done'      => false,
			'progress'  => 0,
			'message'   => '',
			'next_step' => $step,
			'state'     => $state,
			'errors'    => array(),
			'dry_run'   => $dry_run,
			'log'       => array(),
		);

		switch ( $step ) {
			case 0:
				delete_transient( 'wzkb_migration_log' );
				delete_transient( 'wzkb_migration_assigned_articles' );
				delete_transient( 'wzkb_migration_article_counts' );

				$log = array();

				$response_data['message'] = '<strong>' . __( 'Initializing migration...', 'knowledgebase' ) . '</strong>';

				if ( $dry_run ) {
					$log[] = '<strong>' . __( 'Dry run mode: No changes will be made.', 'knowledgebase' ) . '</strong>';
				}

				$top_sections = get_terms(
					array(
						'taxonomy'   => $this->taxonomy_section,
						'hide_empty' => false,
						'parent'     => 0,
						'fields'     => 'all',
					)
				);

				if ( is_wp_error( $top_sections ) ) {
					wp_send_json_error(
						sprintf(
							/* translators: %s: Error message */
							__( 'Error fetching sections: %s', 'knowledgebase' ),
							$top_sections->get_error_message()
						)
					);
				}

				$top_section_ids        = array();
				$section_article_counts = array();
				$unique_article_ids     = array();
				$total_articles         = 0;
				$total_descendant_count = 0;

				foreach ( $top_sections as $section ) {
					$section_id              = (int) $section->term_id;
					$top_section_ids[]       = $section_id;
					$descendant_ids          = $this->get_all_descendant_section_ids( $section_id, $this->taxonomy_section );
					$total_descendant_count += count( $descendant_ids );
					$all_section_ids         = array_merge( array( $section_id ), $descendant_ids );

					foreach ( $all_section_ids as $sid ) {
						$articles                       = get_posts(
							array(
								'post_type'        => $this->post_type_article,
								'posts_per_page'   => -1,
								'fields'           => 'ids',
								'tax_query'        => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
									array(
										'taxonomy' => $this->taxonomy_section,
										'field'    => 'term_id',
										'terms'    => $sid,
									),
								),
								'suppress_filters' => true,
							)
						);
						$section_article_counts[ $sid ] = count( $articles );
						foreach ( $articles as $article_id ) {
							if ( ! in_array( $article_id, $unique_article_ids, true ) ) {
								$unique_article_ids[] = $article_id;
								++$total_articles;
							}
						}
					}
				}

				$state['top_section_ids']        = $top_section_ids;
				$state['section_to_product_map'] = array();
				$state['total_articles']         = $total_articles;
				$state['articles_processed']     = 0;
				$state['sections_mapped']        = 0;
				$state['top_sections_mapped']    = 0;
				$state['total_descendant_count'] = $total_descendant_count;
				$state['sections_deleted']       = 0;
				$state['processed_section_ids']  = array();
				$state['last_log_index']         = 0;

				set_transient( 'wzkb_migration_article_counts', $section_article_counts, DAY_IN_SECONDS );
				delete_transient( 'wzkb_migration_assigned_articles' );
				set_transient( 'wzkb_migration_log', $log, DAY_IN_SECONDS );

				$response_data['progress']  = 20;
				$response_data['next_step'] = 1;
				$response_data['state']     = $state;

				if ( empty( $top_section_ids ) ) {
					$response_data['done']     = true;
					$response_data['progress'] = 100;
					$response_data['message']  = __( 'No top-level sections found. Nothing to migrate.', 'knowledgebase' );
					$log[]                     = $response_data['message'];
					$response_data['log']      = $log;
					$state['last_log_index']   = count( $log );
					set_transient( 'wzkb_migration_log', $log, DAY_IN_SECONDS );
				} else {
					$response_data['log']    = $log;
					$state['last_log_index'] = count( $log );
					set_transient( 'wzkb_migration_log', $log, DAY_IN_SECONDS );
				}
				break;

			case 1:
				$response_data['message'] = '<strong>' . __( 'Creating products from top-level sections...', 'knowledgebase' ) . '</strong>';
				$log[]                    = $response_data['message'];
				$top_section_ids          = $state['top_section_ids'] ?? array();
				$section_to_product_map   = $state['section_to_product_map'] ?? array();
				$simulated_product_ids    = $state['simulated_product_ids'] ?? array();

				foreach ( $top_section_ids as $section_id ) {
					$section = get_term( $section_id, $this->taxonomy_section );
					if ( ! $section || is_wp_error( $section ) ) {
						$log[] = sprintf(
							/* translators: %d: Section ID */
							__( 'Skipped invalid section ID: %d.', 'knowledgebase' ),
							$section_id
						);
						continue;
					}
					$existing_product = get_term_by( 'slug', $section->slug, $this->taxonomy_product );
					if ( $existing_product ) {
						$product_id = (int) $existing_product->term_id;
						$log[]      = sprintf(
							/* translators: 1: Section name, 2: Section ID, 3: Product ID */
							__( 'Used existing product for section "%1$s" (ID: %2$d) with product ID: %3$d.', 'knowledgebase' ),
							$section->name,
							$section_id,
							$product_id
						);
					} else {
						$product = wp_insert_term(
							$section->name,
							$this->taxonomy_product,
							array(
								'description' => $section->description,
								'slug'        => $section->slug,
							)
						);
						if ( is_wp_error( $product ) ) {
							continue;
						}
						$product_id = (int) $product['term_id'];
						if ( $dry_run ) {
							$simulated_product_ids[] = $product_id;
						}
						$log[] = sprintf(
							/* translators: 1: Product name, 2: Product ID, 3: Section name, 4: Section ID */
							__( 'Created product "%1$s" (ID: %2$d) for section "%3$s" (ID: %4$d).', 'knowledgebase' ),
							$section->name,
							$product_id,
							$section->name,
							$section_id
						);
					}
					$section_to_product_map[ $section_id ] = $product_id;
					$state['top_sections_mapped']          = isset( $state['top_sections_mapped'] ) ? $state['top_sections_mapped'] + 1 : 1;
				}

				$state['section_to_product_map'] = $section_to_product_map;
				$state['simulated_product_ids']  = $simulated_product_ids;
				$response_data['log']            = $log;
				$response_data['state']          = $state;
				$response_data['progress']       = 20;
				$response_data['next_step']      = 2;
				$state['last_log_index']         = count( $log );
				set_transient( 'wzkb_migration_log', $log, DAY_IN_SECONDS );
				break;

			case 2:
				/**
				 * Filters the maximum number of sections processed per migration batch.
				 *
				 * Allows customization of migration batch section limit for server performance tuning.
				 *
				 * @since 3.0.0
				 * @param int $max_sections_per_batch Number of sections processed per batch. Default 3.
				 * @return int Filtered number of sections per batch.
				 */
				$max_sections_per_batch = apply_filters( 'wzkb_migration_max_sections_per_batch', 3 );

				/**
				 * Filters the maximum number of articles processed per migration batch.
				 *
				 * Allows customization of migration batch article limit for server performance tuning.
				 *
				 * @since 3.0.0
				 * @param int $max_articles_per_batch Number of articles processed per batch. Default 50.
				 * @return int Filtered number of articles per batch.
				 */
				$max_articles_per_batch = apply_filters( 'wzkb_migration_max_articles_per_batch', 50 );

				$response_data['message']   = '<strong>' . __( 'Mapping descendant sections and articles to products...', 'knowledgebase' ) . '</strong>';
				$log[]                      = $response_data['message'];
				$top_section_ids            = $state['top_section_ids'] ?? array();
				$section_to_product_map     = $state['section_to_product_map'] ?? array();
				$section_article_counts     = is_array( get_transient( 'wzkb_migration_article_counts' ) ) ? get_transient( 'wzkb_migration_article_counts' ) : array();
				$total_articles             = $state['total_articles'] ?? 0;
				$articles_processed         = $state['articles_processed'] ?? 0;
				$sections_mapped            = $state['sections_mapped'] ?? 0;
				$top_sections_mapped        = $state['top_sections_mapped'] ?? 0;
				$already_assigned_articles  = is_array( get_transient( 'wzkb_migration_assigned_articles' ) ) ? get_transient( 'wzkb_migration_assigned_articles' ) : array();
				$current_top_section_index  = isset( $state['current_top_section_index'] ) ? (int) $state['current_top_section_index'] : 0;
				$current_desc_section_index = isset( $state['current_desc_section_index'] ) ? (int) $state['current_desc_section_index'] : 0;
				$current_article_offset     = isset( $state['current_article_offset'] ) ? (int) $state['current_article_offset'] : 0;
				$descendant_ids             = $state['descendant_ids'] ?? array();
				$last_log_index             = isset( $state['last_log_index'] ) ? (int) $state['last_log_index'] : 0;

				$log[] = sprintf(
					/* translators: 1: Top index, 2: Descendant index, 3: Offset, 4: Descendant count, 5: Top sections count, 6: Articles processed, 7: Total articles */
					__( 'Incoming State: top_index=%1$d, desc_index=%2$d, offset=%3$d, desc_count=%4$d, top_sections_count=%5$d, articles_processed=%6$d/%7$d', 'knowledgebase' ),
					$current_top_section_index,
					$current_desc_section_index,
					$current_article_offset,
					count( $descendant_ids ),
					count( $top_section_ids ),
					$articles_processed,
					$total_articles
				);

				if ( empty( $top_section_ids ) || $current_top_section_index >= count( $top_section_ids ) ) {
					$response_data['progress']  = 80;
					$response_data['next_step'] = 3;
					$response_data['message']   = __( 'No more top-level sections to process. Moving to next step.', 'knowledgebase' );
					$log[]                      = $response_data['message'];
					unset( $state['current_top_section_index'], $state['descendant_ids'], $state['current_desc_section_index'], $state['current_article_offset'] );
				} else {
					$section_id = $top_section_ids[ $current_top_section_index ];
					if ( ! isset( $section_to_product_map[ $section_id ] ) ) {
						$log[] = sprintf(
							/* translators: %d: Section ID */
							__( 'Skipping section ID: %d (No product mapping found).', 'knowledgebase' ),
							$section_id
						);
						$state['current_top_section_index']  = $current_top_section_index + 1;
						$state['descendant_ids']             = array();
						$state['current_desc_section_index'] = 0;
						$state['current_article_offset']     = 0;
						$response_data['progress']           = round( max( 20, min( 80, ( $current_top_section_index / max( 1, count( $top_section_ids ) ) ) * 60 + 20 ) ), 1 );
						$response_data['next_step']          = 2;
					} else {
						$product_id = (int) $section_to_product_map[ $section_id ];

						if ( empty( $descendant_ids ) ) {
							$log[] = sprintf(
								/* translators: %d: Section ID */
								__( 'Loading descendants for section ID: %d', 'knowledgebase' ),
								$section_id
							);
							$descendant_ids = $this->get_all_descendant_section_ids( $section_id, $this->taxonomy_section );
							array_unshift( $descendant_ids, $section_id );
							$log[] = sprintf(
								/* translators: 1: Section ID, 2: Count */
								__( 'Found %2$d sections (including top-level) for section ID: %1$d.', 'knowledgebase' ),
								$section_id,
								count( $descendant_ids )
							);
							$log[]                      = 'Descendant IDs: ' . implode( ', ', array_slice( $descendant_ids, 1 ) );
							$current_desc_section_index = 0;
							$current_article_offset     = 0;
						}

						$sections_processed = 0;
						$articles_in_batch  = 0;
						$top_sections_count = count( $top_section_ids );
						while ( $sections_processed < $max_sections_per_batch && $articles_in_batch < $max_articles_per_batch && $current_top_section_index < $top_sections_count ) {
							if ( $current_desc_section_index >= count( $descendant_ids ) ) {
								$log[] = sprintf(
									/* translators: %d: Section ID */
									__( 'Completed all sections for top section ID: %d. Moving to next top section.', 'knowledgebase' ),
									$section_id
								);
								++$current_top_section_index;
								$descendant_ids             = array();
								$current_desc_section_index = 0;
								$current_article_offset     = 0;
								break;
							}

							$current_section_id = $descendant_ids[ $current_desc_section_index ];
							$section_term       = get_term( $current_section_id, $this->taxonomy_section );
							$log[]              = sprintf(
								/* translators: 1: Section name, 2: Section ID */
								__( 'Processing section "%1$s" (ID: %2$d)', 'knowledgebase' ),
								$section_term->name ?? $current_section_id,
								$current_section_id
							);

							// Only count as a descendant section if it's not the top-level section itself.
							if ( $current_section_id !== $section_id ) {
								if ( ! $dry_run ) {
									update_term_meta( $current_section_id, 'product_id', $product_id );
								}

								// Check if we've already counted this section before incrementing.
								if ( ! isset( $state['processed_section_ids'][ $current_section_id ] ) ) {
									++$sections_mapped;
									$state['processed_section_ids'][ $current_section_id ] = true;
								}

								$log[] = sprintf(
									/* translators: 1: Section name, 2: Section ID, 3: Product ID */
									__( 'Linked section "%1$s" (ID: %2$d) to product ID: %3$d.', 'knowledgebase' ),
									$section_term->name ?? $current_section_id,
									$current_section_id,
									$product_id
								);
							}

							$articles_remaining = isset( $section_article_counts[ $current_section_id ] ) ? $section_article_counts[ $current_section_id ] - $current_article_offset : $max_articles_per_batch;
							$articles_to_fetch  = min( $max_articles_per_batch, $max_articles_per_batch - $articles_in_batch, $articles_remaining );

							if ( $articles_to_fetch <= 0 ) {
								$log[] = sprintf(
									/* translators: %d: Section ID */
									__( 'No articles remaining for section ID: %d.', 'knowledgebase' ),
									$current_section_id
								);
								++$current_desc_section_index;
								$current_article_offset = 0;
								++$sections_processed;
								continue;
							}

							$articles = get_posts(
								array(
									'post_type'        => $this->post_type_article,
									'posts_per_page'   => $articles_to_fetch,
									'offset'           => $current_article_offset,
									'fields'           => 'ids',
									'tax_query'        => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
										array(
											'taxonomy' => $this->taxonomy_section,
											'field'    => 'term_id',
											'terms'    => $current_section_id,
										),
									),
									'suppress_filters' => true,
								)
							);

							$assigned_articles = array();
							foreach ( $articles as $article_id ) {
								$article_id = absint( $article_id );
								if ( isset( $already_assigned_articles[ $product_id ][ $article_id ] ) ) {
									$log[] = sprintf(
										/* translators: 1: Article ID, 2: Product ID */
										__( 'Skipped article ID: %1$d (already assigned to product ID: %2$d).', 'knowledgebase' ),
										$article_id,
										$product_id
									);
									continue;
								}
								if ( ! $dry_run ) {
									wp_set_object_terms( $article_id, $product_id, $this->taxonomy_product, true );
								}
								$assigned_articles[]                                     = sprintf( '%s (ID: %d)', get_the_title( $article_id ), $article_id );
								$already_assigned_articles[ $product_id ][ $article_id ] = true;
								++$articles_processed;

								if ( ! isset( $state['processed_article_ids'][ $article_id ] ) ) {
									$state['processed_article_ids'][ $article_id ] = true;
								}
							}

							if ( ! empty( $assigned_articles ) ) {
								$product_term = get_term( $product_id, $this->taxonomy_product );
								$product_name = $product_term->name ?? __( 'Unknown Product', 'knowledgebase' );
								$log[]        = sprintf(
									/* translators: 1: Product name, 2: Product ID, 3: List of articles */
									__( 'Assigned articles to product "%1$s" (ID: %2$d): %3$s', 'knowledgebase' ),
									$product_name,
									$product_id,
									implode( ', ', $assigned_articles )
								);
							} else {
								$log[] = sprintf(
									/* translators: %d: Section ID */
									__( 'No new articles assigned for section ID: %d.', 'knowledgebase' ),
									$current_section_id
								);
							}

							$articles_count          = count( $articles );
							$articles_in_batch      += $articles_count;
							$current_article_offset += $articles_count;

							if ( $articles_count < $articles_to_fetch || $current_article_offset >= $section_article_counts[ $current_section_id ] ) {
								$log[] = sprintf(
									/* translators: 1: Section ID, 2: Article count */
									__( 'Finished processing articles for section ID: %1$d (found %2$d articles).', 'knowledgebase' ),
									$current_section_id,
									$articles_count
								);
								++$current_desc_section_index;
								$current_article_offset = 0;
								++$sections_processed;
							}
						}

						if ( $current_top_section_index < count( $top_section_ids ) ) {
							$response_data['progress']  = $total_articles > 0
								? round( max( 20, min( 80, ( count( $state['processed_article_ids'] ?? array() ) / $total_articles ) * 60 + 20 ) ), 1 )
								: round( max( 20, min( 80, ( $current_top_section_index / max( 1, count( $top_section_ids ) ) ) * 60 + 20 ) ), 1 );
							$response_data['next_step'] = 2;
						}
					}
				}

				set_transient( 'wzkb_migration_assigned_articles', $already_assigned_articles, DAY_IN_SECONDS );
				set_transient( 'wzkb_migration_log', $log, DAY_IN_SECONDS );

				$state['current_top_section_index']  = $current_top_section_index;
				$state['descendant_ids']             = $descendant_ids;
				$state['current_desc_section_index'] = $current_desc_section_index;
				$state['current_article_offset']     = $current_article_offset;
				$state['articles_processed']         = count( $state['processed_article_ids'] ?? array() );
				$state['sections_mapped']            = $sections_mapped;
				$state['top_sections_mapped']        = $top_sections_mapped;

				$log[] = sprintf(
					/* translators: 1: Top index, 2: Descendant index, 3: Offset, 4: Descendant count, 5: Sections mapped, 6: Top sections mapped, 7: Articles processed, 8: Total articles */
					__( 'Outgoing State: top_index=%1$d, desc_index=%2$d, offset=%3$d, desc_count=%4$d, sections_mapped=%5$d, top_sections_mapped=%6$d, articles_processed=%7$d/%8$d', 'knowledgebase' ),
					$current_top_section_index,
					$current_desc_section_index,
					$current_article_offset,
					count( $descendant_ids ),
					$sections_mapped,
					$top_sections_mapped,
					$state['articles_processed'],
					$total_articles
				);

				$response_data['log']    = array_slice( $log, $last_log_index );
				$state['last_log_index'] = count( $log );
				$response_data['state']  = $state;
				set_transient( 'wzkb_migration_log', $log, DAY_IN_SECONDS );

				break;

			case 3:
				$last_log_index           = isset( $state['last_log_index'] ) ? (int) $state['last_log_index'] : 0;
				$response_data['message'] = '<strong>' . __( 'Deleting old top-level sections...', 'knowledgebase' ) . '</strong>';
				$log[]                    = $response_data['message'];
				$top_section_ids          = $state['top_section_ids'] ?? array();
				$sections_deleted         = 0;
				foreach ( $top_section_ids as $section_id ) {
					if ( ! $dry_run ) {
						$result = wp_delete_term( $section_id, $this->taxonomy_section );
						if ( ! is_wp_error( $result ) ) {
							++$sections_deleted;
						}
					} else {
						++$sections_deleted;
					}
					$log[] = sprintf(
						/* translators: %d: Section ID */
						__( 'Deleting top-level section ID: %d.', 'knowledgebase' ),
						$section_id
					);
				}
				$state['sections_deleted'] = $sections_deleted;

				if ( $dry_run && ! empty( $state['simulated_product_ids'] ) ) {
					foreach ( $state['simulated_product_ids'] as $sim_product_id ) {
						wp_delete_term( $sim_product_id, $this->taxonomy_product );
						$log[] = sprintf(
							/* translators: 1: Product ID */
							__( 'Deleted simulated product with ID: %1$d after dry run.', 'knowledgebase' ),
							$sim_product_id
						);
					}
					$state['simulated_product_ids'] = array();
				}

				$log[] = sprintf(
					/* translators: 1: Descendant sections, 2: Top-level sections, 3: Sections deleted, 4: Total sections, 5: Articles processed */
					__( 'Mapped %1$d descendant sections and %2$d top-level sections (total %4$d sections), processed %5$d articles, deleted %3$d top-level sections.', 'knowledgebase' ),
					$state['sections_mapped'],
					$state['top_sections_mapped'],
					$sections_deleted,
					$state['sections_mapped'] + $state['top_sections_mapped'],
					$state['articles_processed'] ?? 0
				);

				$log[] = sprintf(
					/* translators: %d: Expected descendant count */
					__( 'Expected descendant count from initial scan: %d', 'knowledgebase' ),
					$state['total_descendant_count'] ?? 0
				);

				if ( ! $dry_run ) {
					update_option( 'wzkb_product_migration_complete', time() );
				}

				$response_data['progress'] = 100;
				$response_data['done']     = true;
				$response_data['state']    = $state;
				$response_data['message']  = '<strong>' . ( $dry_run ? __( 'Dry run complete! No changes were made.', 'knowledgebase' ) : __( 'Migration complete!', 'knowledgebase' ) ) . '</strong>';
				$response_data['log']      = array_slice( $log, $last_log_index );
				$state['last_log_index']   = count( $log );
				$response_data['state']    = $state;

				delete_transient( 'wzkb_migration_assigned_articles' );
				delete_transient( 'wzkb_migration_article_counts' );
				delete_transient( 'wzkb_migration_log' );

				break;

			default:
				$response_data['message']  = __( 'Unknown migration step.', 'knowledgebase' );
				$response_data['errors'][] = $response_data['message'];
				$response_data['done']     = true;
				$response_data['log']      = array_slice( $log, $state['last_log_index'] ?? 0 );
				$state['last_log_index']   = count( $log );
				$response_data['state']    = $state;

				break;
		}

		wp_send_json_success( $response_data );
	}

	/**
	 * Helper function: Recursively get all descendant section IDs for a given section.
	 *
	 * @param int    $parent_id The parent section ID.
	 * @param string $taxonomy  The taxonomy name.
	 *
	 * @return int[] Array of descendant section IDs.
	 */
	private function get_all_descendant_section_ids( $parent_id, $taxonomy ) {
		$descendants = array();
		$children    = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'parent'     => $parent_id,
				'fields'     => 'ids',
			)
		);
		if ( is_wp_error( $children ) || empty( $children ) ) {
			return $descendants;
		}
		foreach ( $children as $child_id ) {
			$descendants[] = (int) $child_id;
			$descendants   = array_merge( $descendants, $this->get_all_descendant_section_ids( (int) $child_id, $taxonomy ) );
		}
		return $descendants;
	}

	/**
	 * Check if the notice has been dismissed for 90 days.
	 *
	 * @return bool True if the notice has been dismissed, false otherwise.
	 */
	public static function is_dismissed() {
		$dismissed = get_user_meta( get_current_user_id(), 'wzkb_product_notice_dismissed', true );
		if ( empty( $dismissed ) ) {
			return false;
		}
		return ( time() - (int) $dismissed ) < 90 * DAY_IN_SECONDS;
	}

	/**
	 * Check if we are on a KB admin screen.
	 *
	 * @return bool True if we are on a KB admin screen, false otherwise.
	 */
	public static function is_kb_admin_screen() {
		$screen = get_current_screen();
		return isset( $screen->post_type ) && 'wz_knowledgebase' === $screen->post_type;
	}

	/**
	 * AJAX handler for dismissing the product notice.
	 *
	 * @return void
	 */
	public function dismiss_product_notice() {
		check_ajax_referer( 'wzkb_dismiss_product_notice', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		update_user_meta( get_current_user_id(), 'wzkb_product_notice_dismissed', time() );
		wp_send_json_success();
	}

	/**
	 * Display an error message in a consistent format.
	 *
	 * @param string $message The error message.
	 */
	public function display_error( $message ) {
		echo '<div class="notice notice-error inline"><p>' . esc_html( $message ) . '</p></div>';
	}

	/**
	 * Display a success message in a consistent format.
	 *
	 * @param string $message The success message.
	 */
	public function display_success( $message ) {
		echo '<div class="notice notice-success inline"><p>' . esc_html( $message ) . '</p></div>';
	}

	/**
	 * Display a warning message in a consistent format.
	 *
	 * @param string $message The warning message.
	 */
	public function display_warning( $message ) {
		echo '<div class="notice notice-warning inline"><p>' . esc_html( $message ) . '</p></div>';
	}
}
