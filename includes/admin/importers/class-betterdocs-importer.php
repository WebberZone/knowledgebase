<?php
/**
 * BetterDocs importer adapter.
 *
 * @package WebberZone\Knowledge_Base\Admin\Importers
 * @since 3.1.0
 */

namespace WebberZone\Knowledge_Base\Admin\Importers;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Imports content from the BetterDocs plugin.
 *
 * CPT: docs | Taxonomies: doc_category → wzkb_category, doc_tag → wzkb_tag
 * Multiple Knowledge Base (Pro): knowledge_base → wzkb_product. Docs are assigned
 * directly to knowledge_base terms; categories carry a doc_category_knowledge_base
 * term meta listing the KB slugs they belong to.
 *
 * Base slug: betterdocs_settings['docs_slug'] or docs_page slug when BetterDocs
 * uses a custom docs page.
 * Article URL: controlled by betterdocs_settings['permalink_structure'] (the
 * parent path), enable_category_hierarchy_slugs, and the article slug.
 * Reactions (happy/sad) and impressions live in the {prefix}betterdocs_analytics
 * table and are mapped to WZ KB binary rating + view-count meta.
 *
 * @since 3.1.0
 */
class BetterDocs_Importer extends Base_Importer {

	/**
	 * Source CPT slug.
	 *
	 * @var string
	 */
	private string $source_cpt = 'docs';

	/**
	 * Source category taxonomy.
	 *
	 * @var string
	 */
	private string $source_category = 'doc_category';

	/**
	 * Source tag taxonomy.
	 *
	 * @var string
	 */
	private string $source_tag = 'doc_tag';

	/**
	 * Source "Multiple Knowledge Base" taxonomy (BetterDocs Pro only).
	 *
	 * @var string
	 */
	private string $source_kb_taxonomy = 'knowledge_base';

	/**
	 * Cached result of build_order_map() for the current request.
	 *
	 * @var array<int, int>|null
	 */
	private ?array $order_map_cache = null;

	/**
	 * Map of source KB slug → imported wzkb_product term ID.
	 *
	 * Built during import_terms() and consulted when assigning the product_id
	 * meta to wzkb_category terms (categories store KB membership as slugs).
	 *
	 * @var array<string, int>|null
	 */
	private ?array $kb_slug_to_product = null;

	/**
	 * Whether the betterdocs_analytics table exists. Resolved lazily.
	 *
	 * @var bool|null
	 */
	private ?bool $analytics_table_exists = null;

	/**
	 * Register the BetterDocs CPT and taxonomies if not already registered.
	 *
	 * Called at the start of import_terms() and import_posts_batch() so the import
	 * works whether or not BetterDocs is currently active.
	 *
	 * @return void
	 */
	private function ensure_source_types_registered(): void {
		if ( ! post_type_exists( $this->source_cpt ) ) {
			register_post_type( $this->source_cpt );
		}
		if ( ! taxonomy_exists( $this->source_category ) ) {
			register_taxonomy( $this->source_category, $this->source_cpt, array( 'hierarchical' => true ) );
		}
		if ( ! taxonomy_exists( $this->source_tag ) ) {
			register_taxonomy( $this->source_tag, $this->source_cpt );
		}
		// The Multiple KB taxonomy is a Pro-only feature; register it only when
		// its data is present so single-KB sites are unaffected.
		if ( ! taxonomy_exists( $this->source_kb_taxonomy ) && $this->source_kb_has_data() ) {
			register_taxonomy( $this->source_kb_taxonomy, $this->source_cpt );
		}
	}

	/**
	 * Check whether the Multiple KB taxonomy has any terms in the database.
	 *
	 * Uses a direct DB query so it works even when the taxonomy is not registered
	 * (e.g. BetterDocs deactivated, or the Pro add-on not loaded).
	 *
	 * @return bool
	 */
	private function source_kb_has_data(): bool {
		global $wpdb;
		return (bool) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT 1 FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s LIMIT 1",
				$this->source_kb_taxonomy
			)
		);
	}

	/**
	 * Whether the source is running in Multiple Knowledge Base mode.
	 *
	 * Data-driven: true when the knowledge_base taxonomy actually has terms,
	 * which is more reliable than the betterdocs_settings['multiple_kb'] flag.
	 *
	 * @return bool
	 */
	private function is_multi_product(): bool {
		return $this->source_kb_has_data();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label(): string {
		return 'BetterDocs';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_slug(): string {
		return 'betterdocs';
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
		$settings = get_option( 'betterdocs_settings', array() );

		$docs_slug = isset( $settings['docs_slug'] ) ? sanitize_title( $settings['docs_slug'] ) : 'docs';

		// BetterDocs only uses the docs page slug when the built-in doc page is disabled.
		$docs_page = isset( $settings['docs_page'] ) ? (int) $settings['docs_page'] : 0;
		if ( $docs_page && empty( $settings['builtin_doc_page'] ) ) {
			$page_slug = get_post_field( 'post_name', $docs_page );
			if ( $page_slug ) {
				$docs_slug = $page_slug;
			}
		}

		$permalink_structure = $this->normalize_source_permalink_structure( isset( $settings['permalink_structure'] ) ? (string) $settings['permalink_structure'] : 'docs/' );
		$base_slug           = $this->get_permalink_base_slug( $permalink_structure, $docs_slug );

		return array(
			'base_slug'        => $base_slug ? $base_slug : 'docs',
			'permalink'        => $permalink_structure,
			'has_category_url' => false !== strpos( $permalink_structure, '%doc_category%' ),
			'has_hierarchy'    => ! empty( $settings['enable_category_hierarchy_slugs'] ),
			'is_multi_product' => $this->is_multi_product(),
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

		$total = 0;
		foreach ( array( $this->source_kb_taxonomy, $this->source_category, $this->source_tag ) as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}
			$count  = wp_count_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
				)
			);
			$total += is_wp_error( $count ) ? 0 : (int) $count;
		}

		return $total;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Three passes:
	 *  1. knowledge_base terms → wzkb_product (Multiple KB mode only).
	 *  2. doc_category (hierarchical) → wzkb_category, with product_id meta
	 *     resolved from the category's doc_category_knowledge_base slug list.
	 *  3. doc_tag (flat) → wzkb_tag.
	 */
	public function import_terms(): array {
		$this->ensure_source_types_registered();

		$result = array(
			'imported' => 0,
			'skipped'  => 0,
			'errors'   => array(),
		);

		$this->import_kb_terms( $result );
		$this->import_category_terms( $result );
		$this->import_tag_terms( $result );

		return $result;
	}

	/**
	 * Import knowledge_base terms as wzkb_product (Multiple KB mode only).
	 *
	 * Populates $this->kb_slug_to_product so category import can resolve each
	 * category's owning product from its doc_category_knowledge_base slug list.
	 *
	 * @param array $result Import result array, modified in place.
	 * @return void
	 */
	private function import_kb_terms( array &$result ): void {
		$this->kb_slug_to_product = array();

		if ( ! taxonomy_exists( $this->source_kb_taxonomy ) ) {
			return;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $this->source_kb_taxonomy,
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}

		foreach ( $terms as $term ) {
			$new_id = $this->insert_term( $term->name, $this->dest_product, $term->slug, 0, $term->term_id );

			if ( null === $new_id ) {
				++$result['skipped'];
				continue;
			}

			$this->kb_slug_to_product[ $term->slug ] = $new_id;
			++$result['imported'];
		}
	}

	/**
	 * Import doc_category terms as wzkb_category, parents before children.
	 *
	 * @param array $result Import result array, modified in place.
	 * @return void
	 */
	private function import_category_terms( array &$result ): void {
		$raw_terms = get_terms(
			array(
				'taxonomy'   => $this->source_category,
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $raw_terms ) || empty( $raw_terms ) ) {
			return;
		}

		// Topological sort guarantees every parent is processed before its children.
		$terms = $this->sort_terms_topologically( $raw_terms );

		$term_id_map = array();

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

			// Resolve and store the owning product (Multiple KB mode).
			$product_id = $this->resolve_category_product( $term, $term_id_map );
			if ( $product_id ) {
				update_term_meta( $new_id, 'product_id', $product_id );
			}

			// Preserve category ordering, including multilingual variants.
			$position = $this->get_order_meta_value( $term->term_id, 'doc_category_order' );
			if ( '' !== $position ) {
				update_term_meta( $new_id, 'wzkb_position', (int) $position );
			}

			++$result['imported'];
		}
	}

	/**
	 * Import doc_tag terms as wzkb_tag.
	 *
	 * @param array $result Import result array, modified in place.
	 * @return void
	 */
	private function import_tag_terms( array &$result ): void {
		$terms = get_terms(
			array(
				'taxonomy'   => $this->source_tag,
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
	 * Resolve the wzkb_product term ID a doc_category belongs to.
	 *
	 * BetterDocs stores KB membership on the category as an array of KB *slugs*
	 * in the doc_category_knowledge_base term meta. Child categories frequently
	 * omit this meta, so we walk up the ancestry until we find a category that
	 * declares its KB, then map that slug to the imported product term.
	 *
	 * @param \WP_Term             $term        Source category term.
	 * @param array<int, int|null> $term_id_map Map of source term_id → dest term_id (unused, kept for parity).
	 * @return int|null wzkb_product term ID, or null when none applies.
	 */
	private function resolve_category_product( \WP_Term $term, array $term_id_map ): ?int { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		if ( empty( $this->kb_slug_to_product ) ) {
			return null;
		}

		// Candidate terms: the term itself, then each ancestor (nearest first).
		$candidates = array( $term->term_id );
		$ancestors  = get_ancestors( $term->term_id, $this->source_category, 'taxonomy' );
		if ( ! empty( $ancestors ) ) {
			$candidates = array_merge( $candidates, $ancestors );
		}

		foreach ( $candidates as $candidate_id ) {
			$kb_slugs = get_term_meta( $candidate_id, 'doc_category_knowledge_base', true );
			if ( empty( $kb_slugs ) || ! is_array( $kb_slugs ) ) {
				continue;
			}
			foreach ( $kb_slugs as $slug ) {
				if ( isset( $this->kb_slug_to_product[ $slug ] ) ) {
					return $this->kb_slug_to_product[ $slug ];
				}
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Also resolves BetterDocs _docs_order term meta to set menu_order, maps the
	 * Multiple KB assignment to wzkb_product, and migrates reactions/view counts.
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

		// Build category order map once per import from _docs_order term meta.
		$order_map = $this->build_order_map();

		// Pull all analytics rows for this batch in a single query.
		$analytics = $this->get_analytics_for_posts( wp_list_pluck( $posts, 'ID' ) );

		foreach ( $posts as $post ) {
			if ( null !== $this->find_existing_import( $post->ID ) ) {
				++$result['skipped'];
				continue;
			}

			// Resolve menu_order from _docs_order if available.
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
			$this->copy_post_meta(
				$post->ID,
				$new_id,
				array(
					'_betterdocs_est_reading_text',
					'_betterdocs_article_quality_analysis',
					'_betterdocs_article_quality_analyzed_at',
					'_betterdocs_article_summary',
					'_betterdocs_article_summary_hash',
					'_betterdocs_reusable_block_ids',
					'_betterdocs_meta_views',
				)
			);

			$this->map_terms( $post->ID, $new_id );
			$this->map_reactions( $post->ID, $new_id, $analytics[ $post->ID ] ?? null );

			++$result['imported'];
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 *
	 * - Optionally sets kb_slug to match the BetterDocs docs base path.
	 * - Optionally replaces the docs page content with the [knowledgebase]
	 *   shortcode so it renders the KB at the original URL.
	 * - Enables multi-product mode automatically when two or more KBs were imported.
	 * - Sets article_permalink (Pro) to preserve article URLs after deactivation.
	 *
	 * @since 3.1.0
	 * @param bool $update_slug Whether the user opted to update the KB slug and docs page.
	 */
	public function finalize_import( bool $update_slug = true ): void {
		$settings = $this->get_source_settings();
		$bd_opts  = get_option( 'betterdocs_settings', array() );

		if ( $update_slug ) {
			// --- KB slug ------------------------------------------------------
			\WebberZone\Knowledge_Base\Options_API::update_settings(
				array( 'kb_slug' => sanitize_title( $settings['base_slug'] ) )
			);

			// --- Docs page ----------------------------------------------------
			// Replace the configured BetterDocs docs page content with the
			// [knowledgebase] shortcode so it keeps displaying the KB.
			$docs_page_id = isset( $bd_opts['docs_page'] ) ? (int) $bd_opts['docs_page'] : 0;
			if ( $docs_page_id && empty( $bd_opts['builtin_doc_page'] ) && 'page' === get_post_type( $docs_page_id ) ) {
				wp_update_post(
					array(
						'ID'           => $docs_page_id,
						'post_content' => '[knowledgebase]',
					)
				);
			}

			// --- Taxonomy slugs -------------------------------------------------
			$slug_updates = array();
			$bd_cat_slug  = ! empty( $bd_opts['category_slug'] ) ? sanitize_title( $bd_opts['category_slug'] ) : 'docs-category';
			$bd_tag_slug  = ! empty( $bd_opts['tag_slug'] ) ? sanitize_title( $bd_opts['tag_slug'] ) : 'docs-tag';
			if ( $bd_cat_slug ) {
				$slug_updates['category_slug'] = $bd_cat_slug;
			}
			if ( $bd_tag_slug ) {
				$slug_updates['tag_slug'] = $bd_tag_slug;
			}
			if ( ! empty( $slug_updates ) ) {
				\WebberZone\Knowledge_Base\Options_API::update_settings( $slug_updates );
			}
		}

		// --- Multi-product mode -----------------------------------------------
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
			__( 'Deactivate BetterDocs before running the import to avoid conflicts between the two plugins.', 'knowledgebase' ),
		);
	}

	/**
	 * Normalize BetterDocs permalink structure like BetterDocs\Rewrite does.
	 *
	 * BetterDocs stores either a literal base slug (for flat article URLs) or a
	 * structure with placeholders such as %knowledge_base% and %doc_category%.
	 * The single docs slug is appended by BetterDocs rewrite code at runtime,
	 * so the stored structure is the parent path only.
	 *
	 * @since 3.1.0
	 *
	 * @param string $structure Raw betterdocs_settings['permalink_structure'].
	 * @return string Normalized parent permalink structure.
	 */
	private function normalize_source_permalink_structure( string $structure ): string {
		$structure = trim( $structure );
		if ( '' === $structure ) {
			$structure = 'docs/';
		}

		$settings  = get_option( 'betterdocs_settings', array() );
		$base_slug = isset( $settings['docs_slug'] ) ? sanitize_title( $settings['docs_slug'] ) : 'docs';
		$docs_page = isset( $settings['docs_page'] ) ? (int) $settings['docs_page'] : 0;
		if ( $docs_page && empty( $settings['builtin_doc_page'] ) ) {
			$page_slug = get_post_field( 'post_name', $docs_page );
			if ( $page_slug ) {
				$base_slug = $page_slug;
			}
		}

		$parts = explode( '%', $structure );
		if ( '/' === $parts[0] ) {
			$structure = $base_slug . $structure;
		} elseif ( '' === $parts[0] ) {
			$structure = $base_slug . '/' . $structure;
		}

		return trim( $structure, '/' );
	}

	/**
	 * Resolve the effective article URL base from a BetterDocs permalink structure.
	 *
	 * BetterDocs lets the stored article parent structure start with a custom
	 * literal segment (e.g. support/%doc_category%). In that case the article
	 * URL base is the first literal segment, not the docs_slug archive base.
	 *
	 * @since 3.1.0
	 *
	 * @param string $permalink_structure Normalized BetterDocs parent structure.
	 * @param string $fallback_slug       Fallback docs slug.
	 * @return string Effective article base slug.
	 */
	private function get_permalink_base_slug( string $permalink_structure, string $fallback_slug ): string {
		$first_segment = explode( '/', trim( $permalink_structure, '/' ) )[0] ?? '';

		if ( '' !== $first_segment && false === strpos( $first_segment, '%' ) ) {
			return sanitize_title( $first_segment );
		}

		return sanitize_title( $fallback_slug );
	}

	/**
	 * Build a WZ KB article_permalink structure that mirrors BetterDocs URLs.
	 *
	 * BetterDocs appends the article slug to its parent permalink structure. WZ
	 * KB's article_permalink setting also stores only the part after the KB slug,
	 * so we strip the BetterDocs base slug and translate supported placeholders.
	 *
	 * @since 3.1.0
	 *
	 * @param array $settings Output of get_source_settings().
	 * @return string WZ KB permalink structure string.
	 */
	private function build_article_permalink( array $settings ): string {
		$structure = isset( $settings['permalink'] ) ? (string) $settings['permalink'] : 'docs';
		$base_slug = isset( $settings['base_slug'] ) ? trim( (string) $settings['base_slug'], '/' ) : 'docs';

		// For flat BetterDocs URLs (/docs/{article}/), keep the literal base
		// segment so WZ KB does not interpret %postname% as a root-level URL.
		if ( '' !== $base_slug && $structure !== $base_slug && 0 === strpos( $structure, $base_slug . '/' ) ) {
			$structure = trim( substr( $structure, strlen( $base_slug ) ), '/' );
		}

		$token_map = array(
			'%knowledge_base%' => '%product_name%',
			'%doc_category%'   => '%section_name%',
			'%docs%'           => '%postname%',
		);

		$structure = str_replace(
			array_keys( $token_map ),
			array_values( $token_map ),
			$structure
		);

		// BetterDocs removes the KB placeholder when Pro is inactive. We must do the
		// same if no Multiple KB source terms exist.
		if ( empty( $settings['is_multi_product'] ) ) {
			$structure = str_replace( '%product_name%', '', $structure );
		}

		$structure = trim( preg_replace( '#/+#', '/', $structure ), '/' );
		if ( false === strpos( $structure, '%postname%' ) ) {
			$structure = '' === $structure ? '%postname%' : $structure . '/%postname%';
		}

		return $structure;
	}

	/**
	 * Map source taxonomy terms to destination taxonomies on the new post.
	 *
	 * Maps doc_category → wzkb_category, doc_tag → wzkb_tag, and (Multiple KB mode)
	 * knowledge_base → wzkb_product. When a doc carries no direct knowledge_base
	 * assignment, the product is derived from its categories' product_id meta,
	 * mirroring how BetterDocs resolves the owning KB through the category.
	 *
	 * @param int $source_id Source post ID.
	 * @param int $dest_id   Destination post ID.
	 * @return void
	 */
	private function map_terms( int $source_id, int $dest_id ): void {
		$cat_terms = wp_get_object_terms( $source_id, $this->source_category );
		$tag_terms = wp_get_object_terms( $source_id, $this->source_tag );

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

		// Products: direct knowledge_base assignment first.
		$dest_products = array();
		if ( taxonomy_exists( $this->source_kb_taxonomy ) ) {
			$kb_terms = wp_get_object_terms( $source_id, $this->source_kb_taxonomy );
			if ( ! is_wp_error( $kb_terms ) ) {
				foreach ( $kb_terms as $term ) {
					$dest_term = $this->find_existing_term_import( $term->term_id, $this->dest_product );
					if ( $dest_term ) {
						$dest_products[] = $dest_term;
					}
				}
			}
		}

		// Fallback / supplement: derive product from each category's product_id meta.
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

	/**
	 * Map BetterDocs reactions and impressions to WZ KB rating + view meta.
	 *
	 * BetterDocs stores happy/sad/normal reaction counts and impressions per
	 * post (aggregated daily) in the betterdocs_analytics table. We treat happy
	 * as a positive vote and sad as a negative one (binary rating), ignoring the
	 * neutral "normal" reaction, and store impressions as the view count.
	 *
	 * @since 3.1.0
	 *
	 * @param int        $source_id Source post ID.
	 * @param int        $dest_id   Destination post ID.
	 * @param array|null $row       Pre-fetched analytics totals, or null.
	 * @return void
	 */
	private function map_reactions( int $source_id, int $dest_id, ?array $row ): void {
		// View count: prefer analytics impressions, fall back to legacy meta.
		$views = $row ? (int) $row['impressions'] : 0;
		if ( $views <= 0 ) {
			$legacy = get_post_meta( $source_id, '_betterdocs_meta_views', true );
			$views  = '' !== $legacy ? (int) $legacy : 0;
		}
		if ( $views > 0 ) {
			update_post_meta( $dest_id, '_wzkb_views', $views );
		}

		if ( ! $row ) {
			return;
		}

		$likes = (int) $row['happy'];
		$total = $likes + (int) $row['sad'];

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
	 * Fetch aggregated analytics (impressions + reactions) for a set of posts.
	 *
	 * Returns a map keyed by source post ID, each value being an associative
	 * array of summed impressions/happy/sad/normal. Empty when the analytics
	 * table is absent or no rows match.
	 *
	 * @param int[] $post_ids Source post IDs.
	 * @return array<int, array<string, int>>
	 */
	private function get_analytics_for_posts( array $post_ids ): array {
		$map = array();

		if ( empty( $post_ids ) || ! $this->analytics_table_exists() ) {
			return $map;
		}

		global $wpdb;
		$table        = $wpdb->prefix . 'betterdocs_analytics';
		$placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id,
					SUM(impressions) AS impressions,
					SUM(happy) AS happy,
					SUM(sad) AS sad,
					SUM(normal) AS normal
				FROM `{$table}`
				WHERE post_id IN ( {$placeholders} )
				GROUP BY post_id",
				$post_ids
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( empty( $rows ) ) {
			return $map;
		}

		foreach ( $rows as $row ) {
			$map[ (int) $row['post_id'] ] = array(
				'impressions' => (int) $row['impressions'],
				'happy'       => (int) $row['happy'],
				'sad'         => (int) $row['sad'],
				'normal'      => (int) $row['normal'],
			);
		}

		return $map;
	}

	/**
	 * Whether the betterdocs_analytics table exists. Result cached per request.
	 *
	 * @return bool
	 */
	private function analytics_table_exists(): bool {
		if ( null !== $this->analytics_table_exists ) {
			return $this->analytics_table_exists;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'betterdocs_analytics';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$found = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) )
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		$this->analytics_table_exists = ( $found === $table );
		return $this->analytics_table_exists;
	}

	/**
	 * Build a map of post_id → menu_order derived from _docs_order term meta.
	 *
	 * BetterDocs stores a comma-separated list of post IDs per category term
	 * in the _docs_order (or _docs_order_{locale}) term meta. The position
	 * in that list becomes menu_order.
	 *
	 * @return array<int, int> post_id → menu_order
	 */
	private function build_order_map(): array {
		if ( null !== $this->order_map_cache ) {
			return $this->order_map_cache;
		}

		$map = array();

		// Fetch all terms and prime term meta so we can support both the base
		// _docs_order key and multilingual variants such as _docs_order_en.
		$terms = get_terms(
			array(
				'taxonomy'               => $this->source_category,
				'hide_empty'             => false,
				'update_term_meta_cache' => true,
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			$this->order_map_cache = $map;
			return $map;
		}

		foreach ( $terms as $term ) {
			$order_raw = $this->get_order_meta_value( $term->term_id, '_docs_order' );
			if ( empty( $order_raw ) ) {
				continue;
			}
			$ids = array_map( 'intval', explode( ',', $order_raw ) );
			foreach ( $ids as $position => $post_id ) {
				// Only set if not already assigned by a prior category.
				if ( $post_id && ! isset( $map[ $post_id ] ) ) {
					$map[ $post_id ] = $position;
				}
			}
		}

		$this->order_map_cache = $map;
		return $this->order_map_cache;
	}

	/**
	 * Read BetterDocs order meta with fallback for multilingual variants.
	 *
	 * BetterDocs stores base keys (`_docs_order`, `doc_category_order`) and can
	 * also store language-specific variants (`_docs_order_en`,
	 * `doc_category_order_fr`). Prefer the base key when present, then fall back
	 * to the first non-empty language-suffixed value.
	 *
	 * @param int    $term_id  Source term ID.
	 * @param string $base_key Base BetterDocs order meta key.
	 * @return mixed Meta value, or empty string when absent.
	 */
	private function get_order_meta_value( int $term_id, string $base_key ) {
		$value = get_term_meta( $term_id, $base_key, true );
		if ( '' !== $value && array() !== $value ) {
			return $value;
		}

		$all_meta = get_term_meta( $term_id );
		if ( empty( $all_meta ) ) {
			return '';
		}

		$pattern = '/^' . preg_quote( $base_key, '/' ) . '_[A-Za-z0-9_-]+$/';
		foreach ( $all_meta as $key => $values ) {
			if ( ! preg_match( $pattern, (string) $key ) || empty( $values ) ) {
				continue;
			}
			$fallback = maybe_unserialize( $values[0] );
			if ( '' !== $fallback && array() !== $fallback ) {
				return $fallback;
			}
		}

		return '';
	}

	/**
	 * {@inheritDoc}
	 *
	 * Extends the base preview with products, categories, tags, reactions, views,
	 * docs page detail, and structured taxonomy/meta/settings mapping sections.
	 *
	 * @since 3.1.0
	 */
	public function get_preview_data(): array {
		$this->ensure_source_types_registered();

		$base     = parent::get_preview_data();
		$settings = $this->get_source_settings();
		$bd_opts  = get_option( 'betterdocs_settings', array() );
		$is_pro   = class_exists( 'WebberZone\Knowledge_Base\Pro\Custom_Permalinks' );
		$details  = array();

		// Counts.
		$product_count  = $this->count_source_terms( $this->source_kb_taxonomy );
		$category_count = $this->count_source_terms( $this->source_category );
		$tag_count      = $this->count_source_terms( $this->source_tag );

		if ( $settings['is_multi_product'] && $product_count > 0 ) {
			$details[] = sprintf(
				/* translators: %d: number of knowledge bases */
				_n( '%d knowledge base → Knowledge Base product', '%d knowledge bases → Knowledge Base products', $product_count, 'knowledgebase' ),
				$product_count
			);
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

		// Reactions / views availability.
		$has_reactions = $this->has_reaction_data();
		if ( $has_reactions ) {
			$details[] = __( 'Article reactions (happy/sad) → Knowledge Base ratings', 'knowledgebase' );
			$details[] = __( 'Article impressions → Knowledge Base view counts', 'knowledgebase' );
		}

		// Docs page.
		$docs_page_id = isset( $bd_opts['docs_page'] ) ? (int) $bd_opts['docs_page'] : 0;
		if ( $docs_page_id && empty( $bd_opts['builtin_doc_page'] ) && 'page' === get_post_type( $docs_page_id ) ) {
			$details[] = sprintf(
				/* translators: %s: page title */
				__( 'Docs page "%s" will be updated to display the Knowledge Base', 'knowledgebase' ),
				get_the_title( $docs_page_id )
			);
		}

		// --- Detailed mapping sections ----------------------------------------
		$tax_items = array();
		if ( $settings['is_multi_product'] ) {
			$tax_items[] = '<code>knowledge_base</code> → <code>wzkb_product</code>';
		}
		$tax_items[] = '<code>doc_category</code> → <code>wzkb_category</code>';
		$tax_items[] = '<code>doc_tag</code> → <code>wzkb_tag</code>';
		$tax_items[] = '<code>doc_category_order</code> → <code>wzkb_position</code>';

		$meta_items = array(
			'<code>_docs_order</code> → ' . __( 'article menu order', 'knowledgebase' ),
		);
		if ( $has_reactions ) {
			$meta_items[] = '<code>betterdocs_analytics.happy/sad</code> → <code>_wzkb_rating_*</code>';
			$meta_items[] = '<code>betterdocs_analytics.impressions</code> → <code>_wzkb_views</code>';
		}

		$settings_items   = array();
		$settings_items[] = sprintf(
			/* translators: %s: slug */
			__( 'Knowledge Base slug → <code>%s</code>', 'knowledgebase' ),
			esc_html( $settings['base_slug'] )
		);
		if ( $docs_page_id && empty( $bd_opts['builtin_doc_page'] ) && 'page' === get_post_type( $docs_page_id ) ) {
			$settings_items[] = sprintf(
				/* translators: %s: page title */
				__( 'Docs page "%s" content → <code>[knowledgebase]</code>', 'knowledgebase' ),
				esc_html( get_the_title( $docs_page_id ) )
			);
		}
		if ( $settings['is_multi_product'] && $product_count >= 2 ) {
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
	 * Whether any doc carries reaction or impression data in the analytics table.
	 *
	 * @return bool
	 */
	private function has_reaction_data(): bool {
		if ( ! $this->analytics_table_exists() ) {
			return false;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'betterdocs_analytics';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$has = (bool) $wpdb->get_var( "SELECT 1 FROM `{$table}` WHERE ( happy + sad + impressions ) > 0 LIMIT 1" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $has;
	}
}
