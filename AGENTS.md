# AGENTS.md

This file provides guidance to AI coding agents working with code in this repository.

## Response Rules

- Return only the changed function or section, not the full file
- No explanation unless asked
- No suggestions outside the scope of what was asked
- Skip preamble and trailing summaries

## Release Notes

See `dev-tools/CLAUDE.md`'s Changelog convention.

## Links

- GitHub (pro): <https://github.com/WebberZone/knowledgebase-pro>
- GitHub (free): <https://github.com/WebberZone/knowledgebase>
- Documentation: <https://webberzone.com/support/product/knowledgebase/>
- webberzone.com (free): <https://webberzone.com/plugins/knowledgebase/>
- webberzone.com (pro): <https://webberzone.com/plugins/knowledgebase/#pro>

## Plugin Overview

WebberZone Knowledge Base Pro (v3.1.0), namespace `WebberZone\Knowledge_Base`, is a WordPress multi-product knowledge base plugin. Freemium via Freemius: free core features, premium in `/includes/pro/`.

- **Plugin entry**: `knowledgebase.php` (defines constants, loads Freemius via `load-freemius.php`, registers autoloader, and directly requires `includes/options-api.php` and `includes/functions.php`)
- **PHP**: 7.4+ | **WordPress**: 6.7+
- **Custom post type**: `wz_knowledgebase` | **Taxonomies**: `wzkb_category`, `wzkb_product`, `wzkb_tag`

## Build & Development Commands

### PHP

```bash
composer install                         # Install dependencies
composer test                            # Run phpcs + phpcompat + phpstan
composer phpcs                           # WordPress coding standards check
composer phpcbf                          # Auto-fix coding standards
composer phpstan                         # Static analysis (Level 5)
composer phpcompat                       # PHP 7.4–8.6 compatibility check
vendor/bin/phpunit                       # Run unit tests
vendor/bin/phpunit --filter TestName    # Run a single test by name
WP_MULTISITE=1 vendor/bin/phpunit       # Run multisite unit tests
```

### JavaScript / Blocks

```bash
pnpm run build                            # Build all blocks (free + pro)
pnpm run build:free                       # Build all free blocks
pnpm run build:pro                        # Build all pro blocks
pnpm run build:assets                     # Minify CSS/JS and generate RTL (= node build-assets.js)
pnpm run start                            # Watch mode for all blocks
pnpm run lint:js                          # Lint JavaScript
pnpm run lint:css                         # Lint CSS
pnpm run format                           # Auto-format JS and CSS
ncu -u && pnpm install   # Update dependencies to latest and reinstall
```

Individual block builds: `pnpm run build:[kb|articles|sections|products|search|breadcrumb|related|alerts|rating|toc|section-tree]`

> **After editing any non-block JS or CSS** (files in `includes/admin/js/`, `includes/admin/css/`, `includes/admin/settings/js/`, `includes/frontend/css/`, `includes/pro/frontend/css/`, etc.), run `node build-assets.js` to regenerate `.min.js`, `.min.css`, and `-rtl` variants. Never hand-edit minified or RTL files directly.
>
> **Selective asset building**: pass flags to process specific asset types:
>
> ```bash
> node build-assets.js --css              # Process CSS only
> node build-assets.js --js               # Process JS only
> node build-assets.js --no-rtl           # Skip RTL generation
> node build-assets.js path/to/file.css   # Process specific file
> node build-assets.js includes/admin/css/ # Process directory
> ```

### Distribution

```bash
composer zip                             # Create PHP distribution zip
pnpm run zip                              # Create full plugin zip
```

## Distribution zip vendor invariant

`build-zip.sh` excludes all of `vendor/` in its rsync block, then re-adds only the directories it names. **Any vendor directory reachable from a runtime `require` or the Composer autoloader must be re-added, or the shipped zip fatals** — this is not hypothetical; it shipped broken in `top-10` and `knowledgebase`.

The copy list is derived from `composer.lock`'s non-dev `packages`, so adding a runtime dependency to `composer.json` ships it automatically. **Do not hand-list vendor directories in this script.** The derived block is byte-identical across all nine Composer plugin repos — keep it that way when editing one. This repo currently ships `vendor/erusev`, `vendor/freemius` and `vendor/shuchkin`.

A missing directory, an unreadable lock, or an empty derived list is a hard `exit 1`, never a warning: a warning ships a silently broken zip.

Verify a change by building the zip and loading the classes from the extracted tree, not by reading the script. `composer zip` runs `composer install --no-dev`, so follow it with a plain `composer install` to restore dev dependencies.

`includes/pro/github/class-content-importer.php` and `includes/pro/admin/exim/class-xlsx-exporter.php` load Parsedown and SimpleXLSXGen with unguarded `require_once` calls, so a zip missing either package is a fatal error rather than a silent degradation.

## Architecture

### Main Bootstrap Flow

1. `plugins_loaded` hook → `Main::get_instance()` (singleton)
2. `Main::init()` instantiates all component handlers and registers their hooks
3. Admin components only load on `is_admin()`; Pro components only if Freemius detects a premium license

### Key Patterns

**Autoloader** (`includes/autoloader.php`): PSR-4 style. Converts `WebberZone\Knowledge_Base\Admin\Settings` → `includes/admin/class-settings.php`.

**Hook Registry** (`includes/util/class-hook-registry.php`): Custom wrapper around WP actions/filters with duplicate prevention and closure support; all components use it instead of calling `add_action()`/`add_filter()` directly.

**Settings**: Global `$wzkb_settings` populated at plugin load. Read via `wzkb_get_option( $key )` or `wzkb_get_settings()`. Settings page in `includes/admin/class-settings.php`.

**Caching** (`includes/util/class-cache.php`): Term meta-based caching (not transients) with expiry timestamps. AJAX endpoint for admin cache clearing. Use atomic operations when modifying cached data.

**Free vs Pro**: The pro plugin (`knowledgebase-pro/`) is a **standalone, complete replacement** for the free plugin (`knowledgebase/`): it has its own full copy of all free files (e.g. `includes/frontend/class-shortcodes.php`) **plus** premium-only code in `includes/pro/`. Activating either auto-deactivates the other. Edit free-tier features inside `knowledgebase-pro/` only — never the sibling `knowledgebase/`. Pro-only features are conditionally instantiated in `Main::init()`, living exclusively in `includes/pro/`.

### Component Map

| Directory | Responsibility |
| --- | --- |
| `includes/admin/` | Settings UI, columns, wizard, notices, activation, importers, tools page, sample content |
| `includes/frontend/` | Templates, display, shortcodes, styles, search, live search, breadcrumbs, related articles, feeds, TOC, patterns, block templates |
| `includes/blocks/` | 8 free Gutenberg blocks (React in `src/`, compiled to `build/`) |
| `includes/pro/` | Premium features: custom permalinks, rating system, help widget, KB homepage mode, floating TOC, docs layout, term featured images, export/import (Markdown/SQL/XLSX), GitHub import |
| `includes/pro/blocks/` | 3 pro Gutenberg blocks: rating, toc, section-tree (React in `src/`, compiled to `build/`) |
| `includes/pro/github/` | GitHub Markdown import: API wrapper, content converter, import processor, webhook handler, import wizard, link rewriter |
| `includes/pro/widgets/` | 2 pro classic widgets: TOC, Section Tree |
| `includes/rest/` | REST API under `/wzkb/v1/` namespace |
| `includes/widgets/` | 4 classic WordPress widgets |
| `includes/util/` | Hook registry, caching utilities |

### Shortcodes

All shortcodes live in `includes/frontend/class-shortcodes.php` (free-tier, present in both free and pro builds):

| Shortcode | Description |
| --- | --- |
| `[knowledgebase]` | Render the full KB |
| `[kbsearch]` | Search form |
| `[kbbreadcrumb]` | Breadcrumb trail |
| `[kbalert]` | Alert box |
| `[kb_related_articles]` | Related articles list |
| `[kbtoc]` | Table of contents (calls `TOC::process_content()`) |

### GitHub Integration (Pro)

Imports Markdown docs from GitHub repositories into KB articles. All classes are in `includes/pro/github/`.

- **`class-api.php`** — GitHub REST API wrapper (PAT auth, Git Trees, Contents, token validation). Filter: `wzkb_github_api_args`.
- **`class-content-importer.php`** — Converts Markdown → Gutenberg blocks (or classic HTML): frontmatter parsing, `[toc]` → `knowledgebase/toc` block or `[kbtoc]` shortcode, image URL resolution. Image blocks always hand-build `<figure>/<img/>` HTML — never `outer_html()` (DOMDocument), which adds whitespace and non-self-closing tags that fail Gutenberg validation.
- **`class-import-processor.php`** — Core importer: SHA change detection, taxonomy assignment (`sections`→`wzkb_category`, `tags`→`wzkb_tag`, `products`→`wzkb_product`, all auto-creating missing terms by slug), image sideloading, rename/delete handlers. `_wzkb_github_source_url` is a constructed `github.com` blob URL (Git Trees API doesn't return `html_url`). Hooks: `wzkb_github_skip_file`, `wzkb_github_pre_import`, `wzkb_github_post_import`, `wzkb_github_markdown_html`. `fix_image_block_attrs()` rebuilds `<!-- wp:image -->` comments post-sideload — only non-sourced attrs (`id`, `sizeSlug`, `linkDestination`) belong there; `url`/`alt` must be omitted or Gutenberg triggers "Attempt to recover".
- **`class-link-rewriter.php`** — Rewrites relative `.md` hrefs to WP post permalinks using a path-map transient (`wzkb_github_path_map`, 24 hr TTL).
- **`class-webhook-handler.php`** — REST endpoint `POST /wzkb/v1/github/webhook` (HMAC-SHA256 validated); handles push events (added/modified/removed/renamed files), passing `mapping['branch']` as `$ref` to `process_file()` — never hardcode `''` here. Accepts `.md` and `.markdown`. Admin validate endpoint: `GET /wzkb/v1/github/validate`.
- **`class-import-wizard.php`** — Admin UI page (`wzkb-github-import`) for manual one-off imports; AJAX-driven: `wzkb_github_import_list_files` builds the task list (SHA pre-skip detection), `wzkb_github_import_process_one` processes one file, returning permalink and taxonomy terms. Script: `includes/admin/js/github-import-wizard.js`, localised as `WZKBImportWizard`.

**`Import_Processor` public surface**: `get_file_list( $owner, $repo, $mapping, $ref )` wraps `list_markdown_files`; `get_pre_skip_info( $owner, $repo, $path, $tree_sha )` returns existing post data if SHA unchanged, `null` otherwise. `find_github_post()` is `protected` (not private) — subclasses can override.

**Repeater `live_update_field_options`**: pass an `id → label` map in the repeater field args as `'live_update_field_options' => [ id => name ]`; `class-settings-form.php` emits it as `data-live-update-field-options` JSON on the wrapper div; JS reads it to resolve raw values (e.g. term IDs) to human-readable titles in the repeater row header.

**Post meta keys** stored per imported article: `_wzkb_github_repo`, `_wzkb_github_path`, `_wzkb_github_sha`, `_wzkb_github_last_sync`, `_wzkb_github_source_url`, `_wzkb_github_doc_id`.

**Frontmatter fields** (YAML at top of `.md` file): `title`, `sections`/`categories`/`category`/`section` (→ `wzkb_category`), `tags`/`tag` (→ `wzkb_tag`), `products`/`product` (→ `wzkb_product`), `order`/`menu_order`, `status`, `toc` (bool). `sections` supports path notation for hierarchy: `"Parent/Child"` finds or creates `Child` as a term under `Parent`; plain slugs without `/` remain top-level.

**Repository mappings** are configured in Settings → GitHub tab as a repeater (`github_repositories`), each with: `repo_owner`, `repo_name`, `folder_path`, `product_id`, `branch`, `pat`, `default_status`, `duplicate_handling`, `delete_removed`, `status`. The per-mapping `pat` (sensitive, encrypted) overrides the global `github_pat` — use it when repos belong to different owners/orgs (fine-grained PATs are scoped per owner). Global `github_pat` and `github_webhook_secret` are also `sensitive` (encrypted at rest). `API::with_pat( $pat )` returns a cloned API instance with the override applied; `Import_Processor::api_for_mapping( $mapping )` auto-selects the right instance.

`repo_name` uses TomSelect autocomplete (`field_class: 'ts_autocomplete'` + `field_attributes` from `Settings::get_github_repo_search_attributes()`). Backend: `wp_ajax_wzkb_github_repo_search` (registered in `Settings::__construct()`), querying `GET /search/repositories?q=…` via the global PAT, returning `{ id: repo-name, name: owner/repo-name }` items. `ts_autocomplete` is auto-picked up by `includes/admin/settings/js/tom-select-init.js`, already enqueued by `Settings_API` on settings pages — don't re-enqueue or re-implement TomSelect.

### Block Development

Blocks live in `includes/blocks/src/[block-name]/` (free) and `includes/pro/blocks/src/[block-name]/` (pro), each with its own `block.json`, React `edit.js`, and PHP server-side render. After editing source, run `pnpm run build:[block-name]` — never edit `build/` directly.

### Public Helper Functions

`includes/functions.php` exposes the plugin's public API. Key functions:

- `wzkb_knowledge()` — render the full KB output
- `wzkb_get_option( $key )` / `wzkb_get_settings()` — read settings (prefer over `get_option()` directly)
- `wzkb_get_breadcrumb()`, `wzkb_get_search_form()`, `wzkb_get_alert()`, `wzkb_related_articles()` — frontend rendering helpers
- `wzkb_get_the_post_thumbnail()` — thumbnail retrieval (supports ACF image fields)
- `wzkb_get_kb_url()`, `wzkb_get_product_sections_list()`, `wzkb_get_term_hierarchy_path()` — URL and taxonomy helpers

Settings are stored as a single serialized array under option key `wzkb_settings`. All settings filters use the prefix `wzkb_` (e.g. `wzkb_get_option_{$key}`).

### REST API

Endpoints under `/wzkb/v1/`: `/sections` (product sections), `/knowledgebase` (list), `/knowledgebase/{id}` (single), `/products` (products list), `/search` (search), `/related` (related articles). Responses are object-cached under group `wzkb_rest` (300 s TTL); cache is invalidated on post save/delete and term changes.

## Code Quality Configuration

- **PHPCS**: `phpcs.xml.dist` — WordPress coding standards
- **PHPStan**: `phpstan.neon.dist` — Level 5 strict analysis; baseline in `phpstan-baseline.neon`; ACF Pro stubs included
- **PHPUnit**: `phpunit.xml.dist` — test configuration, tests in `phpunit/tests/`

## Shared framework files: `@since` convention

The Settings API (`includes/admin/settings/*.php`) and Admin Banner (`includes/admin/class-admin-banner.php`) are copy-pasted shared framework files, canonical source the `Settings_API` repo. To keep `@since` tags meaningful and stable across syncs, these files follow special rules:

- Each file carries **exactly one** `@since` tag, on its **class docblock**, set to the version that class was **first introduced into this plugin** (per-file — wizard, metabox and banner classes were generally added later than the core Settings API classes).
- **Do not** add `@since` to methods, functions or properties in these files.
- When syncing from another plugin or the canonical `Settings_API` repo, **do not overwrite the class-level `@since`** — it's plugin-specific; re-apply the values below after any sync.

| File | `@since` |
|---|---|
| `includes/admin/settings/class-settings-api.php` | 2.2.0 |
| `includes/admin/settings/class-settings-form.php` | 2.3.0 |
| `includes/admin/settings/class-settings-sanitize.php` | 2.3.0 |
| `includes/admin/settings/class-settings-wizard-api.php` | 3.0.0 |
| `includes/admin/settings/class-metabox-api.php` | 2.3.0 |
| `includes/admin/class-admin-banner.php` | 3.0.0 |

