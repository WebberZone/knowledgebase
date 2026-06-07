<?php
/**
 * Abstract base class for plugin importers.
 *
 * @package WebberZone\Knowledge_Base\Admin\Importers
 * @since 3.1.0
 */

namespace WebberZone\Knowledge_Base\Admin\Importers;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Abstract Base_Importer class.
 *
 * Provides shared logic for all plugin-to-KB importers: slug preservation,
 * idempotency meta, post/term copy helpers, and AJAX batch scaffolding.
 *
 * @since 3.1.0
 */
abstract class Base_Importer {

	/**
	 * Destination post type.
	 *
	 * @var string
	 */
	protected string $dest_post_type = 'wz_knowledgebase';

	/**
	 * Destination category taxonomy.
	 *
	 * @var string
	 */
	protected string $dest_category = 'wzkb_category';

	/**
	 * Destination product taxonomy.
	 *
	 * @var string
	 */
	protected string $dest_product = 'wzkb_product';

	/**
	 * Destination tag taxonomy.
	 *
	 * @var string
	 */
	protected string $dest_tag = 'wzkb_tag';

	/**
	 * Post meta key storing the composite import reference "{slug}:{source_id}".
	 *
	 * A single write is atomic; replaces the prior two-key approach that could
	 * leave posts in an undetectable partial-stamp state after a crash.
	 *
	 * @var string
	 */
	protected string $meta_source_ref = '_wzkb_import_source_ref';

	/**
	 * Term meta key storing the composite import reference "{slug}:{source_term_id}".
	 *
	 * Scoped per importer slug, preventing cross-importer collision when two
	 * source plugins share overlapping numeric term IDs.
	 *
	 * @var string
	 */
	protected string $term_meta_source_ref = '_wzkb_import_source_term_ref';

	/**
	 * Per-request cache for detect() result.
	 *
	 * @var bool|null
	 */
	private ?bool $detect_cache = null;

	/**
	 * Per-request cache: source_post_id → dest_post_id (or null = checked, not found).
	 *
	 * @var array<int, int|null>
	 */
	private array $import_id_cache = array();

	/**
	 * Per-request cache: "{dest_taxonomy}:{source_term_id}" → dest_term_id|null.
	 *
	 * Prevents repeated meta_query lookups for the same term within a batch.
	 *
	 * @var array<string, int|null>
	 */
	private array $term_import_cache = array();

	// -------------------------------------------------------------------------
	// Abstract interface — each adapter must implement these.
	// -------------------------------------------------------------------------

	/**
	 * Human-readable plugin name shown in the UI.
	 *
	 * @return string
	 */
	abstract public function get_label(): string;

	/**
	 * Machine slug used in URL params and meta values (e.g. 'basepress').
	 *
	 * @return string
	 */
	abstract public function get_slug(): string;

	/**
	 * Return true if the source plugin has data available to import.
	 *
	 * Result is cached for the lifetime of the object so repeated calls
	 * (e.g. Tools page card + selector screen) never re-query the database.
	 *
	 * @return bool
	 */
	final public function detect(): bool {
		if ( null === $this->detect_cache ) {
			$this->detect_cache = $this->do_detect();
		}
		return $this->detect_cache;
	}

	/**
	 * Perform the actual detection check. Override in each adapter.
	 *
	 * @return bool
	 */
	abstract protected function do_detect(): bool;

	/**
	 * Return key source settings needed for the import preview UI.
	 *
	 * Must include at minimum:
	 *   'base_slug'       => string  (URL base slug of source plugin)
	 *   'has_category_url' => bool   (whether category appears in article URL)
	 *
	 * @return array<string, mixed>
	 */
	abstract public function get_source_settings(): array;

	/**
	 * Total number of source posts available to import.
	 *
	 * @return int
	 */
	abstract public function get_post_count(): int;

	/**
	 * Total number of source terms available to import.
	 *
	 * @return int
	 */
	abstract public function get_term_count(): int;

	/**
	 * Import all terms from the source plugin.
	 *
	 * Must handle hierarchy (parents before children) and set product_id term
	 * meta on wzkb_category terms where applicable.
	 *
	 * @return array{imported: int, skipped: int, errors: string[]}
	 */
	abstract public function import_terms(): array;

	/**
	 * Import a batch of posts.
	 *
	 * @param int $offset     Pagination offset.
	 * @param int $batch_size Number of posts to process.
	 * @return array{imported: int, skipped: int, errors: string[], processed: int}
	 */
	abstract public function import_posts_batch( int $offset, int $batch_size ): array;

	// -------------------------------------------------------------------------
	// Shared helpers.
	// -------------------------------------------------------------------------

	/**
	 * Suggest the WZ KB base slug based on the source plugin's current URL base.
	 *
	 * @return string
	 */
	public function get_suggested_kb_slug(): string {
		$settings = $this->get_source_settings();
		return ! empty( $settings['base_slug'] ) ? (string) $settings['base_slug'] : 'knowledgebase';
	}

	/**
	 * Return a note about URL depth changes after migration.
	 *
	 * Returns an upsell when the source plugin includes sections in article URLs
	 * but the current license cannot use custom permalink structures to match them.
	 * Returns empty when URLs will match, or when the source URLs are already flat.
	 *
	 * @return string
	 */
	public function get_url_depth_note(): string {
		$settings = $this->get_source_settings();

		if ( empty( $settings['has_category_url'] ) ) {
			return '';
		}

		$is_pro = class_exists( 'WebberZone\Knowledge_Base\Pro\Custom_Permalinks' );

		if ( $is_pro ) {
			return sprintf(
				/* translators: 1: plugin name */
				__( '%1$s includes the section in article URLs. After import, verify your Knowledge Base permalink settings to ensure article URLs match your original structure.', 'knowledgebase' ),
				esc_html( $this->get_label() )
			);
		}

		return sprintf(
			/* translators: 1: plugin name, 2: example URL pattern */
			__( '%1$s includes the section in article URLs. Without a Pro license, articles will be served at %2$s. Upgrade to Knowledge Base Pro to configure custom permalink structures that preserve the original URL depth.', 'knowledgebase' ),
			esc_html( $this->get_label() ),
			'<code>/{kb-slug}/{article}/</code>'
		);
	}

	/**
	 * Insert a post and preserve the original slug, bypassing WordPress's
	 * cross-post-type uniqueness check.
	 *
	 * Safe because wz_knowledgebase and the source CPT have different URL
	 * prefixes, so identical post_name values do not cause real permalink
	 * conflicts.
	 *
	 * @param array $data Arguments for wp_insert_post.
	 * @return int|\WP_Error New post ID on success, WP_Error on failure.
	 */
	protected function insert_post_preserving_slug( array $data ) {
		$original_slug = $data['post_name'] ?? '';

		if ( ! empty( $original_slug ) ) {
			$filter = function ( $slug, $post_id, $post_status, $post_type ) use ( $original_slug ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
				if ( $this->dest_post_type === $post_type ) {
					return $original_slug;
				}
				return $slug;
			};
			add_filter( 'wp_unique_post_slug', $filter, 10, 4 );
		}

		$result = wp_insert_post( $data, true );

		if ( ! empty( $original_slug ) ) {
			remove_filter( 'wp_unique_post_slug', $filter, 10 );
		}

		return $result;
	}

	/**
	 * Copy all postmeta from a source post to a destination post,
	 * excluding internal WordPress keys and source-plugin-specific keys
	 * that have no meaning in WZ KB.
	 *
	 * @param int      $source_id Source post ID.
	 * @param int      $dest_id   Destination post ID.
	 * @param string[] $skip_keys Additional meta keys to skip.
	 * @return void
	 */
	protected function copy_post_meta( int $source_id, int $dest_id, array $skip_keys = array() ): void {
		$all_meta = get_post_meta( $source_id );

		if ( empty( $all_meta ) ) {
			return;
		}

		// Keys that are meaningless or harmful to carry to a new post ID.
		$default_skip = array(
			'_edit_lock',
			'_edit_last',
			'_wp_old_slug',
			'_wp_old_date',
			$this->meta_source_ref,
		);

		$skip = array_merge( $default_skip, $skip_keys );

		foreach ( $all_meta as $key => $values ) {
			if ( in_array( $key, $skip, true ) ) {
				continue;
			}
			foreach ( $values as $value ) {
				add_post_meta( $dest_id, $key, maybe_unserialize( $value ) );
			}
		}
	}

	/**
	 * Return the WZ KB post ID that was previously imported from a given source post,
	 * or null if not yet imported.
	 *
	 * Checks the in-memory batch cache first (populated by preload_import_cache).
	 *
	 * @param int $source_id Source plugin post ID.
	 * @return int|null
	 */
	protected function find_existing_import( int $source_id ): ?int {
		if ( array_key_exists( $source_id, $this->import_id_cache ) ) {
			return $this->import_id_cache[ $source_id ];
		}

		$existing = get_posts(
			array(
				'post_type'      => $this->dest_post_type,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => $this->meta_source_ref,
						'value' => $this->get_slug() . ':' . $source_id,
					),
				),
				'fields'         => 'ids',
			)
		);

		$result                              = ! empty( $existing ) ? (int) $existing[0] : null;
		$this->import_id_cache[ $source_id ] = $result;
		return $result;
	}

	/**
	 * Bulk-load import idempotency data for a set of source post IDs.
	 *
	 * Call this once per batch (before the per-post loop) to replace N individual
	 * WP_Query calls with a single query.
	 *
	 * @param int[] $source_ids Source post IDs in the current batch.
	 * @return void
	 */
	protected function preload_import_cache( array $source_ids ): void {
		if ( empty( $source_ids ) ) {
			return;
		}

		// Mark all as not-found; hits will override below.
		foreach ( $source_ids as $sid ) {
			if ( ! array_key_exists( $sid, $this->import_id_cache ) ) {
				$this->import_id_cache[ $sid ] = null;
			}
		}

		// Build the composite reference values to match against.
		$ref_values = array();
		foreach ( $source_ids as $sid ) {
			$ref_values[] = $this->get_slug() . ':' . $sid;
		}

		// Omitting 'fields' => 'ids' so WordPress returns full WP_Post objects
		// and primes the meta cache, making the get_post_meta calls below cache hits.
		$found = get_posts(
			array(
				'post_type'      => $this->dest_post_type,
				'post_status'    => 'any',
				'posts_per_page' => count( $ref_values ),
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => $this->meta_source_ref,
						'value'   => $ref_values,
						'compare' => 'IN',
					),
				),
			)
		);

		foreach ( $found as $post ) {
			$ref   = get_post_meta( $post->ID, $this->meta_source_ref, true );
			$parts = explode( ':', $ref, 2 );
			$sid   = isset( $parts[1] ) ? (int) $parts[1] : 0;
			if ( $sid ) {
				$this->import_id_cache[ $sid ] = $post->ID;
			}
		}
	}

	/**
	 * Stamp import-tracking meta onto a newly created WZ KB post.
	 *
	 * Uses a single atomic write ("{slug}:{source_id}") so a partial failure
	 * never leaves the post in an undetectable half-stamped state.
	 *
	 * @param int $new_id    Newly created post ID.
	 * @param int $source_id Original source post ID.
	 * @return void
	 */
	protected function stamp_import_meta( int $new_id, int $source_id ): void {
		update_post_meta( $new_id, $this->meta_source_ref, $this->get_slug() . ':' . $source_id );
	}

	/**
	 * Find an existing WZ KB term that was previously imported from a source term
	 * by this importer.
	 *
	 * The composite ref value ("{slug}:{source_term_id}") scopes the lookup to
	 * this importer, preventing false matches when two importers share numeric IDs.
	 *
	 * @param int    $source_term_id Source term ID.
	 * @param string $taxonomy       Destination taxonomy to search.
	 * @return int|null
	 */
	protected function find_existing_term_import( int $source_term_id, string $taxonomy ): ?int {
		$cache_key = $taxonomy . ':' . $source_term_id;
		if ( array_key_exists( $cache_key, $this->term_import_cache ) ) {
			return $this->term_import_cache[ $cache_key ];
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => $this->term_meta_source_ref,
						'value' => $this->get_slug() . ':' . $source_term_id,
					),
				),
				'fields'     => 'ids',
				'number'     => 1,
			)
		);

		$result                                = ! empty( $terms ) && ! is_wp_error( $terms ) ? (int) $terms[0] : null;
		$this->term_import_cache[ $cache_key ] = $result;
		return $result;
	}

	/**
	 * Insert or retrieve a WZ KB term, preserving slug and hierarchy.
	 *
	 * Returns the new/existing term ID, or null on failure.
	 *
	 * @param string $name           Term name.
	 * @param string $taxonomy       Destination taxonomy.
	 * @param string $slug           Desired slug.
	 * @param int    $parent_term_id Parent term ID in destination taxonomy (0 = top-level).
	 * @param int    $source_term_id Original source term ID for idempotency tracking.
	 * @return int|null
	 */
	protected function insert_term( string $name, string $taxonomy, string $slug, int $parent_term_id, int $source_term_id ): ?int {
		// Idempotency: already imported by this importer?
		$existing = $this->find_existing_term_import( $source_term_id, $taxonomy );
		if ( null !== $existing ) {
			return $existing;
		}

		$term_ref = $this->get_slug() . ':' . $source_term_id;

		$result = wp_insert_term(
			$name,
			$taxonomy,
			array(
				'slug'   => $slug,
				'parent' => $parent_term_id,
			)
		);

		if ( is_wp_error( $result ) ) {
			$error_code = $result->get_error_code();

			if ( 'term_exists' === $error_code ) {
				$existing_id = (int) $result->get_error_data( 'term_exists' );
				if ( $existing_id ) {
					$existing_term = get_term( $existing_id, $taxonomy );
					if ( $existing_term instanceof \WP_Term && (int) $existing_term->parent === $parent_term_id ) {
						// Same name, same parent — adopt only if the term is unclaimed
						// or already claimed by this same source term (idempotent re-run).
						$existing_ref = get_term_meta( $existing_id, $this->term_meta_source_ref, true );
						if ( '' === $existing_ref || $existing_ref === $term_ref ) {
							update_term_meta( $existing_id, $this->term_meta_source_ref, $term_ref );
							$this->term_import_cache[ $taxonomy . ':' . $source_term_id ] = $existing_id;
							return $existing_id;
						}
						// Already claimed by a different source — fall through to retry.
					}
					// Parent mismatch or conflict — fall through to retry without slug.
				}
			}

			// term_exists (conflict/mismatch) or duplicate_term_slug:
			// retry without an explicit slug so WordPress generates a unique one.
			if ( 'term_exists' === $error_code || 'duplicate_term_slug' === $error_code ) {
				$retry = wp_insert_term( $name, $taxonomy, array( 'parent' => $parent_term_id ) );
				if ( ! is_wp_error( $retry ) ) {
					$term_id = (int) $retry['term_id'];
					update_term_meta( $term_id, $this->term_meta_source_ref, $term_ref );
					$this->term_import_cache[ $taxonomy . ':' . $source_term_id ] = $term_id;
					return $term_id;
				}
			}

			return null;
		}

		$term_id = (int) $result['term_id'];
		update_term_meta( $term_id, $this->term_meta_source_ref, $term_ref );
		$this->term_import_cache[ $taxonomy . ':' . $source_term_id ] = $term_id;

		return $term_id;
	}

	/**
	 * Sort a flat list of WP_Term objects so that every parent appears before
	 * its children, regardless of term_id ordering in the database.
	 *
	 * Uses a BFS from the implicit root (parent = 0). Terms whose parent is
	 * absent from the list (orphaned terms) are appended at the end so they
	 * are still processed rather than silently dropped.
	 *
	 * @param \WP_Term[] $terms Unsorted terms from a single taxonomy.
	 * @return \WP_Term[]
	 */
	protected function sort_terms_topologically( array $terms ): array {
		$by_id    = array();
		$children = array();

		foreach ( $terms as $term ) {
			$by_id[ $term->term_id ]           = $term;
			$children[ (int) $term->parent ][] = $term->term_id;
		}

		$sorted = array();
		$queue  = array( 0 ); // BFS from the virtual root (parent = 0).

		while ( ! empty( $queue ) ) {
			$parent_id = array_shift( $queue );
			foreach ( $children[ $parent_id ] ?? array() as $child_id ) {
				$sorted[] = $by_id[ $child_id ];
				$queue[]  = $child_id;
			}
		}

		// Append orphaned terms (parent not in this result set) at the end.
		if ( count( $sorted ) < count( $by_id ) ) {
			$seen = array();
			foreach ( $sorted as $term ) {
				$seen[ $term->term_id ] = true;
			}
			foreach ( $by_id as $term_id => $term ) {
				if ( ! isset( $seen[ $term_id ] ) ) {
					$sorted[] = $term;
				}
			}
		}

		return $sorted;
	}

	/**
	 * Called once after all post batches complete.
	 *
	 * Override in subclasses to apply post-import configuration such as permalink settings.
	 *
	 * @since 3.1.0
	 * @param bool $update_slug Whether the user opted to update the KB slug.
	 * @return void
	 */
	public function finalize_import( bool $update_slug = true ): void {}

	/**
	 * Return additional warning strings shown in the "Before you continue" box.
	 *
	 * Override in subclasses to add importer-specific pre-flight instructions.
	 * Each string is rendered as a plain-text list item.
	 *
	 * @return string[]
	 */
	public function get_before_import_notices(): array {
		return array();
	}

	/**
	 * Build a preview data array for the AJAX preview response.
	 *
	 * @return array{posts: int, terms: int, suggested_slug: string, url_note: string, details: list<string>, sections: list<array{heading: string, items: list<string>}>}
	 */
	public function get_preview_data(): array {
		return array(
			'posts'          => $this->get_post_count(),
			'terms'          => $this->get_term_count(),
			'suggested_slug' => $this->get_suggested_kb_slug(),
			'url_note'       => $this->get_url_depth_note(),
			'details'        => array(),
			'sections'       => array(),
		);
	}
}
