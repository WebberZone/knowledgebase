<?php
/**
 * Echo Knowledge Base importer adapter.
 *
 * @package WebberZone\Knowledge_Base\Admin\Importers
 * @since 3.1.0
 */

namespace WebberZone\Knowledge_Base\Admin\Importers;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Imports content from the Echo Knowledge Base plugin.
 *
 * CPT: epkb_post_type_{id} (dynamic per KB instance)
 * Taxonomies: epkb_post_type_{id}_category → wzkb_category
 *             epkb_post_type_{id}_tag      → wzkb_tag
 * Each KB instance → one wzkb_product term
 * Article views: epkb-article-views → _wzkb_views
 * Article order: epkb_articles_sequence_{id} option → menu_order
 * Category order: epkb_categories_sequence_{id} option → wzkb_position
 *
 * @since 3.1.0
 */
class Echo_KB_Importer extends Base_Importer {

	/**
	 * Prefix used by Echo KB for CPT names.
	 *
	 * @var string
	 */
	private string $cpt_prefix = 'epkb_post_type_';

	/**
	 * Category taxonomy suffix.
	 *
	 * @var string
	 */
	private string $cat_suffix = '_category';

	/**
	 * Tag taxonomy suffix.
	 *
	 * @var string
	 */
	private string $tag_suffix = '_tag';

	/**
	 * Resolved list of KB configs, keyed by KB ID.
	 *
	 * @var array<int, array>|null
	 */
	private ?array $kb_configs = null;

	/**
	 * Per-KB post counts, populated lazily by get_kb_counts().
	 *
	 * @var array<int, int>|null
	 */
	private ?array $kb_counts = null;

	/**
	 * Per-KB category position maps, keyed by KB ID. Populated lazily.
	 *
	 * @var array<int, array<int, int>>|null
	 */
	private ?array $category_pos_cache = null;

	/**
	 * Per-KB article order maps, keyed by KB ID. Populated lazily.
	 *
	 * @var array<int, array<int, int>>|null
	 */
	private ?array $article_order_cache = null;

	/**
	 * {@inheritDoc}
	 */
	public function get_label(): string {
		return 'Echo Knowledge Base';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_slug(): string {
		return 'echo-kb';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function do_detect(): bool {
		$configs = $this->get_kb_configs();
		return ! empty( $configs );
	}

	/**
	 * {@inheritDoc}
	 *
	 * Reports settings for the first (or only) KB instance.
	 */
	public function get_source_settings(): array {
		$configs = $this->get_kb_configs();

		if ( empty( $configs ) ) {
			return array(
				'base_slug'        => 'knowledge-base',
				'has_category_url' => false,
			);
		}

		$first          = reset( $configs );
		$base_slug      = ! empty( $first['kb_articles_common_path'] )
			? sanitize_title( $first['kb_articles_common_path'] )
			: 'knowledge-base';
		$has_cat_in_url = isset( $first['categories_in_url_enabled'] ) && 'on' === $first['categories_in_url_enabled'];

		return array(
			'base_slug'        => $base_slug,
			'has_category_url' => (bool) $has_cat_in_url,
			'kb_count'         => count( $configs ),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_post_count(): int {
		return array_sum( $this->get_kb_counts() );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_term_count(): int {
		$this->ensure_source_types_registered();

		$total = 0;
		foreach ( $this->get_kb_configs() as $id => $config ) {
			foreach ( array( $this->cpt_prefix . $id . $this->cat_suffix, $this->cpt_prefix . $id . $this->tag_suffix ) as $taxonomy ) {
				$count  = wp_count_terms(
					array(
						'taxonomy'   => $taxonomy,
						'hide_empty' => false,
					)
				);
				$total += is_wp_error( $count ) ? 0 : (int) $count;
			}
		}
		// +1 per KB for the wzkb_product term we'll create.
		$total += count( $this->get_kb_configs() );
		return $total;
	}

	/**
	 * {@inheritDoc}
	 *
	 * For each KB instance:
	 *  1. Create one wzkb_product term
	 *  2. Import categories as wzkb_category with product_id meta
	 *  3. Import tags as wzkb_tag
	 */
	public function import_terms(): array {
		$this->ensure_source_types_registered();

		$result = array(
			'imported' => 0,
			'skipped'  => 0,
			'errors'   => array(),
		);

		foreach ( $this->get_kb_configs() as $kb_id => $config ) {
			$kb_name = ! empty( $config['kb_name'] ) ? $config['kb_name'] : sprintf( 'Knowledge Base %d', $kb_id );

			// Create one product term per KB.
			$product_slug = sanitize_title( $kb_name );
			$product_id   = $this->insert_term( $kb_name, $this->dest_product, $product_slug, 0, $this->product_source_id( $kb_id ) );

			if ( null === $product_id ) {
				++$result['skipped'];
				$result['errors'][] = sprintf(
					/* translators: %d: KB instance ID */
					__( 'KB %d: product term could not be created; its categories were skipped.', 'knowledgebase' ),
					$kb_id
				);
				// Skip categories — they would be orphaned from their product.
				$this->import_kb_tag_terms( $kb_id, $result );
				continue;
			} else {
				++$result['imported'];
			}

			// Import category terms.
			$this->import_kb_category_terms( $kb_id, $product_id, $result );

			// Import tag terms.
			$this->import_kb_tag_terms( $kb_id, $result );
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Loops all KB instances and processes posts across them in a single offset sequence.
	 * Also migrates article view counts and derives menu_order from Echo KB sequences.
	 *
	 * @param int $offset     Pagination offset.
	 * @param int $batch_size Number of posts to process.
	 */
	public function import_posts_batch( int $offset, int $batch_size ): array {
		$this->ensure_source_types_registered();

		$result = array(
			'imported'  => 0,
			'skipped'   => 0,
			'errors'    => array(),
			'processed' => 0,
		);

		// Determine which KB and local offset this global offset falls into.
		$kb_counts = $this->get_kb_counts();
		$remaining = $offset;
		$kb_offset = 0;
		$target_kb = null;

		foreach ( $kb_counts as $kb_id => $kb_total ) {
			if ( $remaining < $kb_total ) {
				$target_kb = $kb_id;
				$kb_offset = $remaining;
				break;
			}

			$remaining -= $kb_total;
		}

		if ( null === $target_kb ) {
			return $result;
		}

		$posts = get_posts(
			array(
				'post_type'      => $this->cpt_prefix . $target_kb,
				'post_status'    => 'any',
				'posts_per_page' => $batch_size,
				'offset'         => $kb_offset,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);

		$result['processed'] = count( $posts );
		$this->preload_import_cache( wp_list_pluck( $posts, 'ID' ) );

		$cat_taxonomy = $this->cpt_prefix . $target_kb . $this->cat_suffix;
		$tag_taxonomy = $this->cpt_prefix . $target_kb . $this->tag_suffix;

		// Build article order map once per batch from Echo KB sequence option.
		$order_map = $this->get_article_order_map( $target_kb );

		foreach ( $posts as $post ) {
			if ( null !== $this->find_existing_import( $post->ID ) ) {
				++$result['skipped'];
				continue;
			}

			// Resolve menu_order from Echo KB article sequence if available.
			$menu_order = isset( $order_map[ $post->ID ] ) ? $order_map[ $post->ID ] : $post->menu_order;

			$new_id = $this->insert_post_preserving_slug(
				array(
					'post_title'     => $post->post_title,
					'post_content'   => $post->post_content,
					'post_excerpt'   => $post->post_excerpt,
					'post_status'    => $post->post_status,
					'post_author'    => $post->post_author,
					'post_date'      => $post->post_date,
					'post_date_gmt'  => $post->post_date_gmt,
					'post_name'      => $post->post_name,
					'post_type'      => $this->dest_post_type,
					'menu_order'     => $menu_order,
					'comment_status' => $post->comment_status,
				)
			);

			if ( is_wp_error( $new_id ) ) {
				$result['errors'][] = sprintf( 'Post %d: %s', $post->ID, $new_id->get_error_message() );
				continue;
			}

			$this->stamp_import_meta( $new_id, $post->ID );
			$this->copy_post_meta( $post->ID, $new_id, array( 'epkb-article-views' ) );
			$this->map_terms( $post->ID, $new_id, $cat_taxonomy, $tag_taxonomy );
			$this->map_views( $post->ID, $new_id );

			++$result['imported'];
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 *
	 * - Optionally sets kb_slug to match the Echo KB common path.
	 * - Optionally replaces the first KB main page content with [knowledgebase]
	 *   so it continues to display the KB at its original URL.
	 * - Enables multi-product mode automatically when two or more KB instances exist.
	 * - Sets article_permalink (Pro only) to preserve article URLs after deactivation.
	 *
	 * @since 3.1.0
	 * @param bool $update_slug Whether the user opted to update the KB slug and main page.
	 */
	public function finalize_import( bool $update_slug = true ): void {
		$settings = $this->get_source_settings();
		$configs  = $this->get_kb_configs();

		if ( $update_slug ) {
			// --- KB slug ----------------------------------------------------------
			$first_config = ! empty( $configs ) ? reset( $configs ) : array();
			$base_slug    = sanitize_title( $settings['base_slug'] );

			$echo_cat_slug = ! empty( $first_config['category_slug'] ) ? sanitize_title( $first_config['category_slug'] ) : 'category';
			$echo_tag_slug = ! empty( $first_config['tag_slug'] ) ? sanitize_title( $first_config['tag_slug'] ) : 'tag';

			\WebberZone\Knowledge_Base\Options_API::update_settings(
				array(
					'kb_slug'       => $base_slug,
					'category_slug' => $base_slug . '/' . $echo_cat_slug,
					'tag_slug'      => $base_slug . '/' . $echo_tag_slug,
				)
			);

			// --- Main page --------------------------------------------------------
			// Replace the first KB's main page content with the [knowledgebase]
			// shortcode so it keeps displaying the KB at its original URL.
			if ( ! empty( $first_config ) ) {
				$main_page_id = $this->get_main_page_id( $first_config );
				if ( $main_page_id && 'page' === get_post_type( $main_page_id ) ) {
					wp_update_post(
						array(
							'ID'           => $main_page_id,
							'post_content' => '[knowledgebase]',
						)
					);
				}
			}
		}

		// --- Multi-product mode -----------------------------------------------
		// Count how many wzkb_product terms were imported from this source.
		// Enable multi-product mode automatically when more than one product exists.
		$imported_product_count = get_terms(
			array(
				'taxonomy'   => $this->dest_product,
				'hide_empty' => false,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => $this->term_meta_source_ref,
						'value'   => $this->get_slug() . ':',
						'compare' => 'LIKE',
					),
				),
				'fields'     => 'count',
			)
		);
		if ( ! is_wp_error( $imported_product_count ) && (int) $imported_product_count >= 2 ) {
			\WebberZone\Knowledge_Base\Options_API::update_settings( array( 'multi_product' => 1 ) );
		}

		// --- Article permalink (Pro only) -------------------------------------
		if ( class_exists( 'WebberZone\Knowledge_Base\Pro\Custom_Permalinks' ) ) {
			\WebberZone\Knowledge_Base\Options_API::update_settings(
				array( 'article_permalink' => $this->build_article_permalink( $settings ) )
			);
		}

		flush_rewrite_rules();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_before_import_notices(): array {
		return array(
			__( 'Deactivate Echo Knowledge Base before running the import to avoid conflicts between the two plugins.', 'knowledgebase' ),
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * Extends the base preview with products, categories, tags, view counts,
	 * main page detail, and structured taxonomy/meta/settings mapping sections.
	 *
	 * @since 3.1.0
	 */
	public function get_preview_data(): array {
		$this->ensure_source_types_registered();

		$base     = parent::get_preview_data();
		$settings = $this->get_source_settings();
		$configs  = $this->get_kb_configs();
		$is_pro   = class_exists( 'WebberZone\Knowledge_Base\Pro\Custom_Permalinks' );
		$details  = array();

		$kb_count = count( $configs );

		if ( $kb_count > 1 ) {
			$details[] = sprintf(
				/* translators: %d: number of KB instances */
				_n( '%d knowledge base → Knowledge Base product', '%d knowledge bases → Knowledge Base products', $kb_count, 'knowledgebase' ),
				$kb_count
			);
		}

		// Aggregate category and tag counts across all KB instances.
		$category_count = 0;
		$tag_count      = 0;
		foreach ( $configs as $kb_id => $config ) {
			$category_count += $this->count_source_terms( $this->cpt_prefix . $kb_id . $this->cat_suffix );
			$tag_count      += $this->count_source_terms( $this->cpt_prefix . $kb_id . $this->tag_suffix );
		}

		$details[] = sprintf(
			/* translators: %d: number of categories */
			_n( '%d category → Knowledge Base section', '%d categories → Knowledge Base sections', $category_count, 'knowledgebase' ),
			$category_count
		);

		if ( $tag_count > 0 ) {
			$details[] = sprintf(
				/* translators: %d: number of tags */
				_n( '%d tag → Knowledge Base tag', '%d tags → Knowledge Base tags', $tag_count, 'knowledgebase' ),
				$tag_count
			);
		}

		// View count availability.
		$has_views = $this->has_view_data();
		if ( $has_views ) {
			$details[] = __( 'Article view counts → Knowledge Base view counts', 'knowledgebase' );
		}

		// Main page notice (first KB only).
		$first_config = reset( $configs );
		$main_page_id = $first_config ? $this->get_main_page_id( $first_config ) : 0;
		if ( $main_page_id && 'page' === get_post_type( $main_page_id ) ) {
			$details[] = sprintf(
				/* translators: %s: page title */
				__( 'Main page "%s" will be updated to display the Knowledge Base', 'knowledgebase' ),
				get_the_title( $main_page_id )
			);
		}

		// --- Detailed mapping sections ----------------------------------------
		$tax_items = array(
			'<code>epkb_post_type_{id}_category</code> → <code>wzkb_category</code>',
			'<code>epkb_post_type_{id}_tag</code> → <code>wzkb_tag</code>',
		);
		if ( $kb_count > 1 ) {
			array_unshift( $tax_items, '<code>epkb_config_{id}</code> (KB instances) → <code>wzkb_product</code>' );
		}

		$meta_items = array(
			'<code>epkb_categories_sequence_{id}</code> → <code>wzkb_position</code> ' . __( '(category ordering)', 'knowledgebase' ),
			'<code>epkb_articles_sequence_{id}</code> → ' . __( 'article menu order', 'knowledgebase' ),
		);
		if ( $has_views ) {
			$meta_items[] = '<code>epkb-article-views</code> → <code>_wzkb_views</code>';
		}

		$settings_items   = array();
		$settings_items[] = sprintf(
			/* translators: %s: slug value */
			__( 'Knowledge Base slug → <code>%s</code>', 'knowledgebase' ),
			esc_html( $settings['base_slug'] )
		);
		if ( $main_page_id && 'page' === get_post_type( $main_page_id ) ) {
			$settings_items[] = sprintf(
				/* translators: %s: page title */
				__( 'Main page "%s" content → <code>[knowledgebase]</code>', 'knowledgebase' ),
				esc_html( get_the_title( $main_page_id ) )
			);
		}
		if ( $kb_count >= 2 ) {
			$settings_items[] = __( 'Multi-product mode → enabled', 'knowledgebase' );
		}
		if ( $is_pro ) {
			$settings_items[] = sprintf(
				/* translators: %s: permalink structure tokens */
				__( 'Article permalink → <code>%s</code>', 'knowledgebase' ),
				esc_html( $this->build_article_permalink( $settings ) )
			);
		}

		$sections = array(
			array(
				'heading' => __( 'Taxonomy mapping', 'knowledgebase' ),
				'items'   => $tax_items,
			),
			array(
				'heading' => __( 'Article meta', 'knowledgebase' ),
				'items'   => $meta_items,
			),
		);

		$sections[] = array(
			'heading' => __( 'Settings auto-configured after import', 'knowledgebase' ),
			'items'   => $settings_items,
		);

		return array_merge(
			$base,
			array(
				'details'  => $details,
				'sections' => $sections,
				'url_note' => $is_pro ? '' : $base['url_note'],
			)
		);
	}

	// -------------------------------------------------------------------------
	// Private helpers.
	// -------------------------------------------------------------------------

	/**
	 * Return all Echo KB configs keyed by KB ID.
	 *
	 * Echo KB does not maintain an epkb_kb_ids list. KB instances are discovered
	 * by querying wp_options for rows whose name matches 'epkb_config_%', mirroring
	 * EPKB_KB_Config_DB::get_kb_ids().
	 *
	 * @return array<int, array>
	 */
	private function get_kb_configs(): array {
		if ( null !== $this->kb_configs ) {
			return $this->kb_configs;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$option_names = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
				'epkb_config_%'
			)
		);

		if ( empty( $option_names ) ) {
			$this->kb_configs = array();
			return $this->kb_configs;
		}

		$configs = array();
		foreach ( $option_names as $option_name ) {
			// Extract the integer ID from the suffix.
			$kb_id = (int) str_replace( 'epkb_config_', '', $option_name );
			if ( $kb_id <= 0 ) {
				continue;
			}
			$config = get_option( $option_name, array() );
			if ( ! empty( $config ) && is_array( $config ) ) {
				$configs[ $kb_id ] = $config;
			}
		}

		ksort( $configs );
		$this->kb_configs = $configs;
		return $this->kb_configs;
	}

	/**
	 * Return per-KB post counts, keyed by KB ID. Cached after the first call.
	 *
	 * @return array<int, int>
	 */
	private function get_kb_counts(): array {
		if ( null !== $this->kb_counts ) {
			return $this->kb_counts;
		}

		$counts = array();
		foreach ( $this->get_kb_configs() as $kb_id => $config ) {
			$query            = new \WP_Query(
				array(
					'post_type'      => $this->cpt_prefix . $kb_id,
					'post_status'    => 'any',
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);
			$counts[ $kb_id ] = (int) $query->found_posts;
		}

		$this->kb_counts = $counts;
		return $this->kb_counts;
	}

	/**
	 * Return a synthetic source ID for a KB-level product term (for idempotency).
	 * Uses a negative offset so it never collides with real post IDs.
	 *
	 * @param int $kb_id KB instance ID.
	 * @return int
	 */
	private function product_source_id( int $kb_id ): int {
		return -$kb_id;
	}

	/**
	 * Register the Echo KB CPT and taxonomies for each configured KB ID if not
	 * already registered.
	 *
	 * Called at the start of import_terms() and import_posts_batch() so the import
	 * works whether or not Echo Knowledge Base is currently active.
	 *
	 * @return void
	 */
	private function ensure_source_types_registered(): void {
		foreach ( $this->get_kb_configs() as $kb_id => $config ) {
			$this->register_kb_types( $kb_id );
		}
	}

	/**
	 * Register the CPT and taxonomies for a single KB instance.
	 *
	 * @param int $kb_id KB instance ID.
	 * @return void
	 */
	private function register_kb_types( int $kb_id ): void {
		$cpt     = $this->cpt_prefix . $kb_id;
		$cat_tax = $cpt . $this->cat_suffix;
		$tag_tax = $cpt . $this->tag_suffix;

		if ( ! post_type_exists( $cpt ) ) {
			register_post_type( $cpt );
		}
		if ( ! taxonomy_exists( $cat_tax ) ) {
			register_taxonomy( $cat_tax, $cpt, array( 'hierarchical' => true ) );
		}
		if ( ! taxonomy_exists( $tag_tax ) ) {
			register_taxonomy( $tag_tax, $cpt );
		}
	}

	/**
	 * Whether any Echo KB article carries a view count in post meta.
	 *
	 * @return bool
	 */
	private function has_view_data(): bool {
		foreach ( $this->get_kb_configs() as $kb_id => $config ) {
			$found = get_posts(
				array(
					'post_type'      => $this->cpt_prefix . $kb_id,
					'post_status'    => 'any',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'meta_key'       => 'epkb-article-views', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				)
			);
			if ( ! empty( $found ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Copy the Echo KB article view count to the WZ KB view meta.
	 *
	 * Echo KB stores the total view count in post meta key `epkb-article-views`.
	 * This is mapped directly to `_wzkb_views`.
	 *
	 * @param int $source_id Source post ID.
	 * @param int $dest_id   Destination post ID.
	 * @return void
	 */
	private function map_views( int $source_id, int $dest_id ): void {
		$views = get_post_meta( $source_id, 'epkb-article-views', true );
		if ( '' !== $views && (int) $views > 0 ) {
			update_post_meta( $dest_id, '_wzkb_views', (int) $views );
		}
	}

	/**
	 * Build a WZ KB article_permalink structure that mirrors Echo KB article URLs.
	 *
	 * Echo KB serves articles at:
	 *   /{kb_articles_common_path}/{category-slug}/{article-slug}/ when categories_in_url is on
	 *   /{kb_articles_common_path}/{article-slug}/                 otherwise
	 *
	 * WZ KB article_permalink stores only the part after the KB slug, so the base
	 * path is stripped and Echo KB placeholders are translated to WZ KB tokens.
	 *
	 * @since 3.1.0
	 *
	 * @param array $settings Output of get_source_settings().
	 * @return string WZ KB permalink structure string.
	 */
	private function build_article_permalink( array $settings ): string {
		if ( ! empty( $settings['has_category_url'] ) ) {
			return '%section_name%/%postname%';
		}
		return '%postname%';
	}

	/**
	 * Count terms in a source taxonomy, returning 0 when it does not exist.
	 *
	 * @param string $taxonomy Source taxonomy slug.
	 * @return int
	 */
	private function count_source_terms( string $taxonomy ): int {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return 0;
		}
		$count = wp_count_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);
		return is_wp_error( $count ) ? 0 : (int) $count;
	}

	/**
	 * Extract the first main page ID from an Echo KB config array.
	 *
	 * Echo KB stores `kb_main_pages` as a page_id → title map. The first key
	 * is the primary KB page.
	 *
	 * @param array $config Echo KB config array.
	 * @return int Page ID, or 0 when none is configured.
	 */
	private function get_main_page_id( array $config ): int {
		if ( empty( $config['kb_main_pages'] ) || ! is_array( $config['kb_main_pages'] ) ) {
			return 0;
		}
		$first_id = array_key_first( $config['kb_main_pages'] );
		return is_numeric( $first_id ) ? (int) $first_id : 0;
	}

	/**
	 * Build a map of source category_id → display position for one KB instance.
	 *
	 * Echo KB persists the category display order in the `epkb_categories_sequence_{kb_id}`
	 * option as a nested tree of category IDs. Position is derived from the key order
	 * within each level of that tree.
	 *
	 * @param int $kb_id KB instance ID.
	 * @return array<int, int> source category_id → 0-based position within its parent.
	 */
	private function get_category_position_map( int $kb_id ): array {
		if ( isset( $this->category_pos_cache[ $kb_id ] ) ) {
			return $this->category_pos_cache[ $kb_id ];
		}

		$sequence = get_option( 'epkb_categories_sequence_' . $kb_id, array() );
		$map      = array();

		if ( ! empty( $sequence ) && is_array( $sequence ) ) {
			$this->collect_category_positions( $sequence, $map );
		}

		if ( null === $this->category_pos_cache ) {
			$this->category_pos_cache = array();
		}
		$this->category_pos_cache[ $kb_id ] = $map;

		return $map;
	}

	/**
	 * Recursively collect category positions from a nested sequence tree.
	 *
	 * @param array<int|string, mixed> $level  One level of the sequence tree.
	 * @param array<int, int>          $map    Result map, modified in place.
	 * @return void
	 */
	private function collect_category_positions( array $level, array &$map ): void {
		$position = 0;
		foreach ( $level as $cat_id => $children ) {
			$map[ (int) $cat_id ] = $position++;
			if ( ! empty( $children ) && is_array( $children ) ) {
				$this->collect_category_positions( $children, $map );
			}
		}
	}

	/**
	 * Build a map of source article_id → menu_order for one KB instance.
	 *
	 * Echo KB persists the article display order in the `epkb_articles_sequence_{kb_id}`
	 * option. Its structure is: category_id → [ 0 => name, 1 => desc, article_id => title, … ].
	 * The position of an article among its siblings (0-based, after the name/desc slots)
	 * becomes the menu_order. Only the first occurrence of each article is used.
	 *
	 * @param int $kb_id KB instance ID.
	 * @return array<int, int> source article_id → menu_order
	 */
	private function get_article_order_map( int $kb_id ): array {
		if ( isset( $this->article_order_cache[ $kb_id ] ) ) {
			return $this->article_order_cache[ $kb_id ];
		}

		$sequence = get_option( 'epkb_articles_sequence_' . $kb_id, array() );
		$map      = array();

		if ( ! empty( $sequence ) && is_array( $sequence ) ) {
			foreach ( $sequence as $cat_id => $articles ) {
				if ( ! is_array( $articles ) ) {
					continue;
				}
				$position = 0;
				foreach ( $articles as $key => $value ) {
					// Keys 0 (category name) and 1 (category description) are metadata.
					if ( 0 === $key || 1 === $key ) {
						continue;
					}
					$article_id = (int) $key;
					if ( ! isset( $map[ $article_id ] ) ) {
						$map[ $article_id ] = $position;
					}
					++$position;
				}
			}
		}

		if ( null === $this->article_order_cache ) {
			$this->article_order_cache = array();
		}
		$this->article_order_cache[ $kb_id ] = $map;

		return $map;
	}

	/**
	 * Import category terms for one KB instance.
	 *
	 * Also assigns wzkb_position from the Echo KB categories sequence option.
	 *
	 * @param int      $kb_id      KB instance ID.
	 * @param int|null $product_id Destination wzkb_product term ID.
	 * @param array    $result     Import result array, modified in place.
	 * @return void
	 */
	private function import_kb_category_terms( int $kb_id, ?int $product_id, array &$result ): void {
		$taxonomy = $this->cpt_prefix . $kb_id . $this->cat_suffix;

		$raw_terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $raw_terms ) || empty( $raw_terms ) ) {
			return;
		}

		// Topological sort guarantees every parent is processed before its children.
		$terms = $this->sort_terms_topologically( $raw_terms );

		$term_id_map  = array();
		$position_map = $this->get_category_position_map( $kb_id );

		foreach ( $terms as $term ) {
			// If the parent failed to import, skip this child rather than reparenting it.
			if ( array_key_exists( $term->parent, $term_id_map ) && null === $term_id_map[ $term->parent ] ) {
				++$result['skipped'];
				$term_id_map[ $term->term_id ] = null;
				continue;
			}

			$dest_parent = isset( $term_id_map[ $term->parent ] ) ? (int) $term_id_map[ $term->parent ] : 0;

			$new_id = $this->insert_term( $term->name, $this->dest_category, $term->slug, $dest_parent, $term->term_id );

			if ( null === $new_id ) {
				++$result['skipped'];
				$term_id_map[ $term->term_id ] = null;
				continue;
			}

			$term_id_map[ $term->term_id ] = $new_id;

			if ( $product_id ) {
				update_term_meta( $new_id, 'product_id', $product_id );
			}

			// Preserve category ordering from Echo KB sequence.
			if ( isset( $position_map[ $term->term_id ] ) ) {
				update_term_meta( $new_id, 'wzkb_position', $position_map[ $term->term_id ] );
			}

			++$result['imported'];
		}
	}

	/**
	 * Import tag terms for one KB instance.
	 *
	 * @param int   $kb_id  KB instance ID.
	 * @param array $result Import result array, modified in place.
	 * @return void
	 */
	private function import_kb_tag_terms( int $kb_id, array &$result ): void {
		$taxonomy = $this->cpt_prefix . $kb_id . $this->tag_suffix;

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}

		foreach ( $terms as $term ) {
			$new_id = $this->insert_term( $term->name, $this->dest_tag, $term->slug, 0, $term->term_id );

			if ( null === $new_id ) {
				++$result['skipped'];
				continue;
			}

			++$result['imported'];
		}
	}

	/**
	 * Map source taxonomy terms to destination taxonomies on the new post.
	 *
	 * Maps epkb_post_type_{id}_category → wzkb_category,
	 *      epkb_post_type_{id}_tag      → wzkb_tag.
	 * Derives wzkb_product assignment from the product_id term meta stored on
	 * each imported wzkb_category term during import_terms().
	 *
	 * @param int    $source_id    Source post ID.
	 * @param int    $dest_id      Destination post ID.
	 * @param string $cat_taxonomy Source category taxonomy name.
	 * @param string $tag_taxonomy Source tag taxonomy name.
	 * @return void
	 */
	private function map_terms( int $source_id, int $dest_id, string $cat_taxonomy, string $tag_taxonomy ): void {
		$cat_terms = wp_get_object_terms( $source_id, $cat_taxonomy );
		$tag_terms = wp_get_object_terms( $source_id, $tag_taxonomy );

		$dest_cats = array();
		if ( ! is_wp_error( $cat_terms ) ) {
			foreach ( $cat_terms as $term ) {
				$dest_term = $this->find_existing_term_import( $term->term_id, $this->dest_category );
				if ( $dest_term ) {
					$dest_cats[] = $dest_term;
				}
			}
		}

		$dest_tags = array();
		if ( ! is_wp_error( $tag_terms ) ) {
			foreach ( $tag_terms as $term ) {
				$dest_term = $this->find_existing_term_import( $term->term_id, $this->dest_tag );
				if ( $dest_term ) {
					$dest_tags[] = $dest_term;
				}
			}
		}

		// Derive wzkb_product from product_id term meta on each imported category.
		$dest_products = array();
		foreach ( $dest_cats as $cat_id ) {
			$product_id = (int) get_term_meta( $cat_id, 'product_id', true );
			if ( $product_id ) {
				$dest_products[] = $product_id;
			}
		}
		$dest_products = array_unique( $dest_products );

		if ( ! empty( $dest_cats ) ) {
			wp_set_object_terms( $dest_id, $dest_cats, $this->dest_category );
		}
		if ( ! empty( $dest_tags ) ) {
			wp_set_object_terms( $dest_id, $dest_tags, $this->dest_tag );
		}
		if ( ! empty( $dest_products ) ) {
			wp_set_object_terms( $dest_id, $dest_products, $this->dest_product );
		}
	}
}
