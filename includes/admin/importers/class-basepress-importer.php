<?php
/**
 * BasePress importer adapter.
 *
 * @package WebberZone\Knowledge_Base\Admin\Importers
 * @since 3.1.0
 */

namespace WebberZone\Knowledge_Base\Admin\Importers;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Imports content from the BasePress plugin.
 *
 * CPT: knowledgebase | Taxonomy: knowledgebase_cat
 * Term hierarchy: parent=0 → wzkb_product; parent>0 → wzkb_category
 * Article URL: /{kb_slug}/{product}/{section}/{article}/
 *
 * @since 3.1.0
 */
class BasePress_Importer extends Base_Importer {

	/**
	 * Source CPT slug.
	 *
	 * @var string
	 */
	private string $source_cpt = 'knowledgebase';

	/**
	 * Source taxonomy slug.
	 *
	 * @var string
	 */
	private string $source_taxonomy = 'knowledgebase_cat';

	/**
	 * Source tag taxonomy slug (BasePress premium only).
	 *
	 * @var string
	 */
	private string $source_tag_taxonomy = 'knowledgebase_tag';

	/**
	 * Register the BasePress CPT and taxonomies if not already registered.
	 *
	 * Called at the start of import_terms() and import_posts_batch() so the import
	 * works whether or not BasePress is currently active.
	 *
	 * @return void
	 */
	private function ensure_source_types_registered(): void {
		if ( ! post_type_exists( $this->source_cpt ) ) {
			register_post_type( $this->source_cpt );
		}
		if ( ! taxonomy_exists( $this->source_taxonomy ) ) {
			register_taxonomy( $this->source_taxonomy, $this->source_cpt );
		}
		if ( ! taxonomy_exists( $this->source_tag_taxonomy ) && $this->source_tag_has_data() ) {
			register_taxonomy( $this->source_tag_taxonomy, $this->source_cpt );
		}
	}

	/**
	 * Check whether the source tag taxonomy has any terms in the database.
	 *
	 * Uses a direct DB query so it works even when the taxonomy is not registered.
	 *
	 * @return bool
	 */
	private function source_tag_has_data(): bool {
		global $wpdb;
		return (bool) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT 1 FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s LIMIT 1",
				$this->source_tag_taxonomy
			)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label(): string {
		return 'BasePress';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_slug(): string {
		return 'basepress';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function do_detect(): bool {
		return post_type_exists( $this->source_cpt )
			|| $this->get_post_count() > 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_source_settings(): array {
		$options    = get_option( 'basepress_settings', array() );
		$entry_page = isset( $options['entry_page'] ) ? (int) $options['entry_page'] : 0;

		// Build base slug from the entry page slug, walking up ancestors.
		$base_slug = '';
		if ( $entry_page ) {
			$slug      = get_post_field( 'post_name', $entry_page );
			$ancestors = array_reverse( get_ancestors( $entry_page, 'page' ) );

			$parts = array();
			foreach ( $ancestors as $ancestor_id ) {
				$parts[] = get_post_field( 'post_name', $ancestor_id );
			}
			$parts[]   = $slug;
			$base_slug = implode( '/', array_filter( $parts ) );
		}

		// Detect multi-product mode by reading the authoritative BasePress setting.
		// single_product_mode is a boolean flag stored as key presence (1 when on, absent when off).
		$is_multi_product = empty( $options['single_product_mode'] );

		return array(
			'base_slug'        => $base_slug ? $base_slug : 'knowledgebase',
			'has_category_url' => true, // BasePress always has section (and optionally product) in URL.
			'is_multi_product' => $is_multi_product,
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_post_count(): int {
		$query = new \WP_Query(
			array(
				'post_type'      => $this->source_cpt,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		return (int) $query->found_posts;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_term_count(): int {
		$this->ensure_source_types_registered();
		$count = wp_count_terms(
			array(
				'taxonomy'   => $this->source_taxonomy,
				'hide_empty' => false,
			)
		);
		return is_wp_error( $count ) ? 0 : (int) $count;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Two passes:
	 *  1. parent=0 terms → wzkb_product
	 *  2. child terms → wzkb_category with product_id term meta
	 */
	public function import_terms(): array {
		$this->ensure_source_types_registered();

		$result = array(
			'imported' => 0,
			'skipped'  => 0,
			'errors'   => array(),
		);

		$raw_terms = get_terms(
			array(
				'taxonomy'   => $this->source_taxonomy,
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $raw_terms ) || empty( $raw_terms ) ) {
			return $result;
		}

		// Topological sort guarantees every parent is processed before its children,
		// regardless of the term_id / DB ordering.
		$all_terms = $this->sort_terms_topologically( $raw_terms );

		// Map source term_id → new dest term_id for hierarchy resolution.
		$term_id_map = array();

		// Source term IDs that were imported as wzkb_product (root terms).
		// Used in Pass 2 to avoid passing a wzkb_product term ID as the parent
		// of a wzkb_category term, which would be a cross-taxonomy parent reference.
		$root_source_ids = array();

		// Pass 1: top-level terms become products.
		foreach ( $all_terms as $term ) {
			if ( 0 !== (int) $term->parent ) {
				continue;
			}

			$new_id = $this->insert_term( $term->name, $this->dest_product, $term->slug, 0, $term->term_id );

			if ( null === $new_id ) {
				++$result['skipped'];
				$term_id_map[ $term->term_id ] = null;
				continue;
			}

			$root_source_ids[ $term->term_id ] = true;
			$term_id_map[ $term->term_id ]     = $new_id;

			// Preserve product ordering.
			$position = get_term_meta( $term->term_id, 'basepress_position', true );
			if ( '' !== $position ) {
				update_term_meta( $new_id, 'wzkb_position', (int) $position );
			}

			++$result['imported'];
		}

		// Pass 2: child terms become sections, with product_id pointing to their root ancestor.
		// The topological sort guarantees each term's parent is already in $term_id_map.
		foreach ( $all_terms as $term ) {
			if ( 0 === (int) $term->parent ) {
				continue;
			}

			// If the parent term failed to import, skip this child too.
			if ( array_key_exists( $term->parent, $term_id_map ) && null === $term_id_map[ $term->parent ] ) {
				++$result['skipped'];
				$term_id_map[ $term->term_id ] = null;
				continue;
			}

			// Direct children of root terms must be top-level in wzkb_category (dest_parent=0).
			// Their parent was imported as wzkb_product (different taxonomy), so passing that
			// term ID as parent would create a cross-taxonomy reference that get_term() cannot resolve.
			$dest_parent = ( isset( $root_source_ids[ $term->parent ] ) || ! isset( $term_id_map[ $term->parent ] ) )
				? 0
				: (int) $term_id_map[ $term->parent ];

			$new_id = $this->insert_term( $term->name, $this->dest_category, $term->slug, $dest_parent, $term->term_id );

			if ( null === $new_id ) {
				++$result['skipped'];
				$term_id_map[ $term->term_id ] = null;
				continue;
			}

			$term_id_map[ $term->term_id ] = $new_id;

			// Walk up to find the root product term for product_id meta.
			$product_id = $this->find_root_product( $term, $term_id_map );
			if ( $product_id ) {
				update_term_meta( $new_id, 'product_id', $product_id );
			}

			// Preserve section ordering.
			$position = get_term_meta( $term->term_id, 'basepress_position', true );
			if ( '' !== $position ) {
				update_term_meta( $new_id, 'wzkb_position', (int) $position );
			}

			++$result['imported'];
		}

		// Import tag terms (BasePress premium only — skipped silently if no data).
		if ( taxonomy_exists( $this->source_tag_taxonomy ) ) {
			$tag_terms = get_terms(
				array(
					'taxonomy'   => $this->source_tag_taxonomy,
					'hide_empty' => false,
				)
			);
			if ( ! is_wp_error( $tag_terms ) ) {
				foreach ( $tag_terms as $term ) {
					$new_id = $this->insert_term( $term->name, $this->dest_tag, $term->slug, 0, $term->term_id );
					if ( null === $new_id ) {
						++$result['skipped'];
					} else {
						++$result['imported'];
					}
				}
			}
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
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

		$posts = get_posts(
			array(
				'post_type'      => $this->source_cpt,
				'post_status'    => 'any',
				'posts_per_page' => $batch_size,
				'offset'         => $offset,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);

		$result['processed'] = count( $posts );
		$this->preload_import_cache( wp_list_pluck( $posts, 'ID' ) );

		foreach ( $posts as $post ) {
			// Idempotency: skip if already imported.
			if ( null !== $this->find_existing_import( $post->ID ) ) {
				++$result['skipped'];
				continue;
			}

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
					'menu_order'     => $post->menu_order,
					'comment_status' => $post->comment_status,
				)
			);

			if ( is_wp_error( $new_id ) ) {
				$result['errors'][] = sprintf( 'Post %d: %s', $post->ID, $new_id->get_error_message() );
				continue;
			}

			$this->stamp_import_meta( $new_id, $post->ID );
			$this->copy_post_meta(
				$post->ID,
				$new_id,
				array( 'basepress_views', 'basepress_votes', 'basepress_votes_count', 'basepress_score', 'basepress_post_icon', 'basepress_template_name' )
			);

			$this->map_votes( $post->ID, $new_id );
			$this->map_source_meta( $post->ID, $new_id );
			$this->map_terms( $post->ID, $new_id );

			++$result['imported'];
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 *
	 * - Optionally sets kb_slug to match the BasePress entry page path.
	 * - Optionally replaces the entry page content with [knowledgebase] shortcode
	 *   so it renders the KB at the original URL without clashing with CPT rewrites.
	 * - Sets article_permalink to preserve article URLs after deactivation.
	 *
	 * @since 3.1.0
	 * @param bool $update_slug Whether the user opted to update the KB slug and entry page.
	 */
	public function finalize_import( bool $update_slug = true ): void {
		$settings   = $this->get_source_settings();
		$bp_options = get_option( 'basepress_settings', array() );

		if ( $update_slug ) {
			// --- KB slug ------------------------------------------------------
			$base_slug = $settings['base_slug'];
			\WebberZone\Knowledge_Base\Options_API::update_settings( array( 'kb_slug' => sanitize_title( $base_slug ) ) );

			// --- Entry page ---------------------------------------------------
			// Replace the BasePress entry page content with the [knowledgebase]
			// shortcode so it continues to display the KB at its original URL.
			$entry_page_id = isset( $bp_options['entry_page'] ) ? (int) $bp_options['entry_page'] : 0;
			if ( $entry_page_id && 'page' === get_post_type( $entry_page_id ) ) {
				wp_update_post(
					array(
						'ID'           => $entry_page_id,
						'post_content' => '[knowledgebase]',
					)
				);
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
			$structure = $this->build_article_permalink( $settings, $bp_options );
			\WebberZone\Knowledge_Base\Options_API::update_settings( array( 'article_permalink' => $structure ) );
		}

		flush_rewrite_rules();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_before_import_notices(): array {
		return array(
			__( 'Deactivate BasePress before running the import to avoid conflicts between the two plugins.', 'knowledgebase' ),
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * Extends the base preview with products, sections, ratings, entry page detail,
	 * and structured sections showing taxonomy, meta, and settings mappings.
	 *
	 * @since 3.1.0
	 */
	public function get_preview_data(): array {
		$base       = parent::get_preview_data();
		$settings   = $this->get_source_settings();
		$bp_options = get_option( 'basepress_settings', array() );
		$details    = array();

		// Product count (root terms) and section count (child terms).
		$product_count = get_terms(
			array(
				'taxonomy'   => $this->source_taxonomy,
				'parent'     => 0,
				'hide_empty' => false,
				'fields'     => 'count',
			)
		);
		$product_count = is_wp_error( $product_count ) ? 0 : (int) $product_count;
		$section_count = max( 0, $base['terms'] - $product_count );

		if ( $settings['is_multi_product'] ) {
			$details[] = sprintf(
				/* translators: %d: number of products */
				_n( '%d product → Knowledge Base product', '%d products → Knowledge Base products', $product_count, 'knowledgebase' ),
				$product_count
			);
		}

		$details[] = sprintf(
			/* translators: %d: number of sections */
			_n( '%d section → Knowledge Base section', '%d sections → Knowledge Base sections', $section_count, 'knowledgebase' ),
			$section_count
		);

		// Ratings: check whether any article carries vote data.
		$has_votes = ! empty(
			get_posts(
				array(
					'post_type'      => $this->source_cpt,
					'post_status'    => 'any',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'meta_key'       => 'basepress_votes', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				)
			)
		);

		// Entry page.
		$entry_page_id = isset( $bp_options['entry_page'] ) ? (int) $bp_options['entry_page'] : 0;
		if ( $entry_page_id && 'page' === get_post_type( $entry_page_id ) ) {
			$details[] = sprintf(
				/* translators: %s: page title */
				__( 'Entry page "%s" will be updated to display the Knowledge Base', 'knowledgebase' ),
				get_the_title( $entry_page_id )
			);
		}

		// --- Detailed mapping sections ----------------------------------------
		$is_pro = class_exists( 'WebberZone\Knowledge_Base\Pro\Custom_Permalinks' );

		// Taxonomy.
		$tax_items = array();
		if ( $settings['is_multi_product'] ) {
			$tax_items[] = '<code>knowledgebase_cat</code> (top-level) → <code>wzkb_product</code>';
			$tax_items[] = '<code>knowledgebase_cat</code> (children) → <code>wzkb_category</code>';
		} else {
			$tax_items[] = '<code>knowledgebase_cat</code> (root) → <code>wzkb_product</code> (single product)';
			$tax_items[] = '<code>knowledgebase_cat</code> (children) → <code>wzkb_category</code>';
		}
		$tax_items[] = '<code>basepress_position</code> → <code>wzkb_position</code> (ordering)';
		if ( $this->source_tag_has_data() ) {
			$tax_items[] = '<code>knowledgebase_tag</code> → <code>wzkb_tag</code>';
		}

		// Article meta.
		$meta_items = array(
			'<code>basepress_views</code> → <code>_wzkb_views</code>',
			'<code>basepress_post_icon</code> → <code>_wzkb_post_icon</code>',
		);
		if ( $has_votes ) {
			$meta_items[] = $is_pro
				? '<code>basepress_votes</code> → <code>_wzkb_rating_total</code>, <code>_wzkb_rating_positive</code>, <code>_wzkb_average_rating</code>, <code>_wzkb_bayesian_rating</code>'
				: '<code>basepress_votes</code> → <code>_wzkb_rating_total</code>, <code>_wzkb_rating_positive</code>, <code>_wzkb_average_rating</code>';
		}

		// Settings auto-configured.
		$settings_items = array();
		$slug           = $base['suggested_slug'];
		if ( $slug ) {
			$settings_items[] = sprintf(
				/* translators: %s: slug value */
				__( 'KB slug → <code>%s</code> (from entry page path)', 'knowledgebase' ),
				esc_html( $slug )
			);
		}
		if ( $entry_page_id && 'page' === get_post_type( $entry_page_id ) ) {
			$settings_items[] = sprintf(
				/* translators: %s: page title */
				__( 'Entry page "%s" content → <code>[knowledgebase]</code>', 'knowledgebase' ),
				esc_html( get_the_title( $entry_page_id ) )
			);
		}

		if ( $settings['is_multi_product'] && $product_count >= 2 ) {
			$settings_items[] = __( 'Multi-product mode → enabled', 'knowledgebase' );
		}

		if ( $is_pro ) {
			$permalink        = $this->build_article_permalink( $settings, $bp_options );
			$settings_items[] = sprintf(
				/* translators: %s: permalink structure tokens */
				__( 'Article permalink → <code>%s</code>', 'knowledgebase' ),
				esc_html( $permalink )
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

		if ( ! empty( $settings_items ) ) {
			$sections[] = array(
				'heading' => __( 'Settings auto-configured after import', 'knowledgebase' ),
				'items'   => $settings_items,
			);
		}

		return array_merge(
			$base,
			array(
				'details'  => $details,
				'sections' => $sections,
				'url_note' => $is_pro ? '' : $base['url_note'],
			)
		);
	}

	/**
	 * Translate BasePress article_permalink_structure into a WZ KB article_permalink value.
	 *
	 * Shared by finalize_import() and get_preview_data() to avoid duplication.
	 *
	 * @since 3.1.0
	 *
	 * @param array $settings   Output of get_source_settings().
	 * @param array $bp_options Raw basepress_settings option.
	 * @return string WZ KB permalink structure string.
	 */
	private function build_article_permalink( array $settings, array $bp_options ): string {
		$raw_structure = $settings['is_multi_product']
			? '%knowledge_base%/%product%/%article_section%'
			: '%knowledge_base%/%article_section%';

		if ( ! empty( $bp_options['article_permalink_structure'] ) ) {
			$raw_structure = $bp_options['article_permalink_structure'];
		}

		$token_map = array(
			'%knowledge_base%/' => '',
			'%knowledge_base%'  => '',
			'%article_section%' => '%section_name%',
			'%parent_sections%' => '%section_name%',
			'%product%'         => '%product_name%',
			'%product_slug%'    => '%product_name%',
			'%section_slug%'    => '%section_name%',
			'%article_slug%'    => '%postname%',
			'%article%'         => '%postname%',
		);

		$structure = str_replace(
			array_keys( $token_map ),
			array_values( $token_map ),
			$raw_structure
		);

		if ( false === strpos( $structure, '%postname%' ) ) {
			$structure = trim( $structure, '/' ) . '/%postname%';
		}

		return $structure;
	}

	/**
	 * Rename BasePress-specific meta keys to their WZ KB equivalents.
	 *
	 * Keys handled here are excluded from copy_post_meta() above.
	 *
	 * @since 3.1.0
	 *
	 * @param int $source_id Source post ID.
	 * @param int $dest_id   Destination post ID.
	 * @return void
	 */
	private function map_source_meta( int $source_id, int $dest_id ): void {
		$views = get_post_meta( $source_id, 'basepress_views', true );
		if ( '' !== $views ) {
			update_post_meta( $dest_id, '_wzkb_views', (int) $views );
		}

		$icon = get_post_meta( $source_id, 'basepress_post_icon', true );
		if ( '' !== $icon ) {
			update_post_meta( $dest_id, '_wzkb_post_icon', sanitize_text_field( $icon ) );
		}
	}

	/**
	 * Map BasePress votes to WZ KB binary rating meta.
	 *
	 * BasePress stores votes as a serialized array ['like' => n, 'dislike' => n].
	 * We write the aggregate rating meta directly, including a Bayesian score
	 * computed against the global mean at import time.
	 *
	 * @since 3.1.0
	 *
	 * @param int $source_id Source post ID.
	 * @param int $dest_id   Destination post ID.
	 * @return void
	 */
	private function map_votes( int $source_id, int $dest_id ): void {
		$votes = get_post_meta( $source_id, 'basepress_votes', true );

		if ( ! is_array( $votes ) || ! isset( $votes['like'], $votes['dislike'] ) ) {
			return;
		}

		$likes = (int) $votes['like'];
		$total = $likes + (int) $votes['dislike'];

		if ( $total <= 0 ) {
			return;
		}

		$positive_ratio = $likes / $total;

		update_post_meta( $dest_id, '_wzkb_rating_total', $total );
		update_post_meta( $dest_id, '_wzkb_rating_positive', $likes );
		update_post_meta( $dest_id, '_wzkb_average_rating', $positive_ratio );
		update_post_meta( $dest_id, '_wzkb_positive_ratio', $positive_ratio );

		if ( class_exists( \WebberZone\Knowledge_Base\Pro\Rating\Rating::class ) ) {
			update_post_meta( $dest_id, '_wzkb_bayesian_rating', \WebberZone\Knowledge_Base\Pro\Rating\Rating::compute_bayesian_score( $total, $likes, 'binary' ) );
		}
	}

	/**
	 * Map knowledgebase_cat terms from source post to wzkb_product / wzkb_category on dest post.
	 *
	 * Root-level BasePress terms are imported as wzkb_product; child terms as wzkb_category.
	 * Articles assigned only to a root term get a wzkb_product assignment here.
	 *
	 * @param int $source_id Source post ID.
	 * @param int $dest_id   Destination post ID.
	 * @return void
	 */
	private function map_terms( int $source_id, int $dest_id ): void {
		$source_terms = wp_get_object_terms( $source_id, $this->source_taxonomy );

		if ( is_wp_error( $source_terms ) || empty( $source_terms ) ) {
			return;
		}

		$dest_category_ids = array();
		$dest_product_ids  = array();

		foreach ( $source_terms as $term ) {
			// Child terms were imported as wzkb_category.
			$dest_term_id = $this->find_existing_term_import( $term->term_id, $this->dest_category );
			if ( $dest_term_id ) {
				$dest_category_ids[] = $dest_term_id;
				continue;
			}
			// Root-level terms were imported as wzkb_product.
			$dest_term_id = $this->find_existing_term_import( $term->term_id, $this->dest_product );
			if ( $dest_term_id ) {
				$dest_product_ids[] = $dest_term_id;
			}
		}

		// BasePress only assigns articles to their leaf section term, never to the
		// root product term. Derive the wzkb_product assignment from the product_id
		// term meta we stored on each wzkb_category term during import_terms().
		foreach ( $dest_category_ids as $cat_id ) {
			$product_id = (int) get_term_meta( $cat_id, 'product_id', true );
			if ( $product_id ) {
				$dest_product_ids[] = $product_id;
			}
		}
		$dest_product_ids = array_unique( $dest_product_ids );

		if ( ! empty( $dest_category_ids ) ) {
			wp_set_object_terms( $dest_id, $dest_category_ids, $this->dest_category );
		}
		if ( ! empty( $dest_product_ids ) ) {
			wp_set_object_terms( $dest_id, $dest_product_ids, $this->dest_product );
		}

		// Map tag terms (BasePress premium only).
		if ( taxonomy_exists( $this->source_tag_taxonomy ) ) {
			$source_tag_terms = wp_get_object_terms( $source_id, $this->source_tag_taxonomy );
			if ( ! is_wp_error( $source_tag_terms ) && ! empty( $source_tag_terms ) ) {
				$dest_tag_ids = array();
				foreach ( $source_tag_terms as $term ) {
					$dest_term_id = $this->find_existing_term_import( $term->term_id, $this->dest_tag );
					if ( $dest_term_id ) {
						$dest_tag_ids[] = $dest_term_id;
					}
				}
				if ( ! empty( $dest_tag_ids ) ) {
					wp_set_object_terms( $dest_id, $dest_tag_ids, $this->dest_tag );
				}
			}
		}
	}

	/**
	 * Walk up the source term's ancestry to find the wzkb_product term ID.
	 *
	 * @param \WP_Term             $term       Source term.
	 * @param array<int, int|null> $id_map     Map of source term_id → dest term_id.
	 * @return int|null wzkb_product term ID, or null if not found.
	 */
	private function find_root_product( \WP_Term $term, array $id_map ): ?int {
		$ancestors = get_ancestors( $term->term_id, $this->source_taxonomy, 'taxonomy' );

		if ( empty( $ancestors ) ) {
			return null;
		}

		// Root is the last ancestor (highest level).
		$root_source_id = end( $ancestors );

		$dest_product = $this->find_existing_term_import( $root_source_id, $this->dest_product );

		return $dest_product;
	}
}
