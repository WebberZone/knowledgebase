# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Plugin Overview

WebberZone Knowledge Base Pro (v3.1.0 in development) is a WordPress plugin (namespace `WebberZone\Knowledge_Base`) that creates a multi-product knowledge base system. It uses a freemium model via Freemius integration — free core features with premium features in `/includes/pro/`.

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
composer phpcompat                       # PHP 7.4–8.5 compatibility check
vendor/bin/phpunit                       # Run unit tests
vendor/bin/phpunit --filter TestName    # Run a single test by name
WP_MULTISITE=1 vendor/bin/phpunit       # Run multisite unit tests
```

### JavaScript / Blocks

```bash
npm run build                            # Build all blocks (free + pro)
npm run build:free                       # Build all free blocks
npm run build:pro                        # Build all pro blocks
npm run build:assets                     # Minify CSS/JS and generate RTL (= node build-assets.js)
npm run start                            # Watch mode for all blocks
npm run lint:js                          # Lint JavaScript
npm run lint:css                         # Lint CSS
npm run format                           # Auto-format JS and CSS
```

Individual block builds: `npm run build:[kb|articles|sections|products|search|breadcrumb|related|alerts|rating]`

> **After editing any non-block JS or CSS** (files in `includes/admin/js/`, `includes/admin/css/`, `includes/admin/settings/js/`, `includes/frontend/css/`, `includes/pro/frontend/css/`, etc.) always run `node build-assets.js` to regenerate the `.min.js`, `.min.css`, and `-rtl` variants. Never hand-edit the minified or RTL files directly.
>
> **Selective asset building**: Pass flags to process only specific asset types:
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
npm run zip                              # Create full plugin zip
```

## Architecture

### Main Bootstrap Flow

1. `plugins_loaded` hook → `Main::get_instance()` (singleton)
2. `Main::init()` instantiates all component handlers and registers their hooks
3. Admin components only load on `is_admin()`; Pro components only if Freemius detects a premium license

### Key Patterns

**Autoloader** (`includes/autoloader.php`): PSR-4 style. Converts `WebberZone\Knowledge_Base\Admin\Settings` → `includes/admin/class-settings.php`.

**Hook Registry** (`includes/util/class-hook-registry.php`): Custom wrapper around WordPress actions/filters with duplicate prevention and closure support. All components register hooks through this instead of calling `add_action()`/`add_filter()` directly.

**Settings**: Global `$wzkb_settings` populated at plugin load. Read via `wzkb_get_option( $key )` or `wzkb_get_settings()`. Settings page in `includes/admin/class-settings.php`.

**Caching** (`includes/util/class-cache.php`): Term meta-based caching (not transients) with expiry timestamps. AJAX endpoint for admin cache clearing. Use atomic operations when modifying cached data.

**Free vs Pro**: The pro plugin (`knowledgebase-pro/`) is a **standalone, complete replacement** for the free plugin (`knowledgebase/`). It contains its own full copy of all free files (e.g. `includes/frontend/class-shortcodes.php`) **plus** the premium-only code in `includes/pro/`. Activating either version auto-deactivates the other. When adding or editing free-tier features, always edit the file inside `knowledgebase-pro/` — never the sibling `knowledgebase/` directory. Pro-only features are conditionally instantiated in `Main::init()` and live exclusively in `includes/pro/`.

### Component Map

| Directory | Responsibility |
| --- | --- |
| `includes/admin/` | Settings UI, columns, wizard, notices, activation |
| `includes/frontend/` | Templates, display, shortcodes, styles, search, breadcrumbs, related articles, feeds |
| `includes/blocks/` | 8 free Gutenberg blocks (React in `src/`, compiled to `build/`) |
| `includes/pro/` | Premium features: custom permalinks, rating system, help widget, KB homepage mode, GitHub import |
| `includes/pro/github/` | GitHub Markdown import: API wrapper, content converter, import processor, webhook handler, import wizard, link rewriter |
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
- **`class-content-importer.php`** — Converts Markdown → Gutenberg blocks (or classic HTML). Handles frontmatter parsing, `[toc]` → `knowledgebase/toc` block or `[kbtoc]` shortcode, image URL resolution. Image blocks always hand-build their `<figure>/<img/>` HTML — never use `outer_html()` (DOMDocument) for image output, as it introduces whitespace and non-self-closing tags that fail Gutenberg block validation.
- **`class-import-processor.php`** — Core importer: SHA change detection, taxonomy assignment (`sections`→`wzkb_category`, `tags`→`wzkb_tag`, `products`→`wzkb_product`), image sideloading, rename/delete handlers. All three taxonomy types auto-create missing terms by slug. `_wzkb_github_source_url` is populated from a constructed `github.com` blob URL (Git Trees API does not return `html_url`). Developer hooks: `wzkb_github_skip_file`, `wzkb_github_pre_import`, `wzkb_github_post_import`, `wzkb_github_markdown_html`. `fix_image_block_attrs()` runs after sideloading to rebuild `<!-- wp:image -->` comments — only non-sourced attrs (`id`, `sizeSlug`, `linkDestination`) go in the comment; `url`/`alt` are sourced and must be omitted or Gutenberg triggers "Attempt to recover".
- **`class-link-rewriter.php`** — Rewrites relative `.md` hrefs to WP post permalinks using a path-map transient (`wzkb_github_path_map`, 24 hr TTL).
- **`class-webhook-handler.php`** — REST endpoint `POST /wzkb/v1/github/webhook` (HMAC-SHA256 validated). Handles push events: added/modified/removed/renamed files. Passes `mapping['branch']` as `$ref` to `process_file()` — do not hardcode `''` here. Accepts both `.md` and `.markdown` extensions. Admin validate endpoint: `GET /wzkb/v1/github/validate`.
- **`class-import-wizard.php`** — Admin UI page (`wzkb-github-import`) for manual one-off imports. AJAX-driven: `wzkb_github_import_list_files` builds the task list (with SHA pre-skip detection), `wzkb_github_import_process_one` processes a single file and returns result data including permalink and taxonomy terms. Script: `includes/admin/js/github-import-wizard.js`, localised as `WZKBImportWizard`.

**`Import_Processor` public surface**: `get_file_list( $owner, $repo, $mapping, $ref )` wraps `list_markdown_files`; `get_pre_skip_info( $owner, $repo, $path, $tree_sha )` returns existing post data if SHA unchanged, `null` otherwise. `find_github_post()` is `protected` (not private) — subclasses can override.

**Repeater `live_update_field_options`**: pass an `id → label` map in the repeater field args as `'live_update_field_options' => [ id => name ]`; `class-settings-form.php` emits it as `data-live-update-field-options` JSON on the wrapper div; JS reads it to resolve raw values (e.g. term IDs) to human-readable titles in the repeater row header.

**Post meta keys** stored per imported article: `_wzkb_github_repo`, `_wzkb_github_path`, `_wzkb_github_sha`, `_wzkb_github_last_sync`, `_wzkb_github_source_url`, `_wzkb_github_doc_id`.

**Frontmatter fields** (YAML at top of `.md` file): `title`, `sections`/`categories`/`category`/`section` (→ `wzkb_category`), `tags`/`tag` (→ `wzkb_tag`), `products`/`product` (→ `wzkb_product`), `order`/`menu_order`, `status`, `toc` (bool). `sections` supports path notation for hierarchy: `"Parent/Child"` finds or creates `Child` as a term under `Parent`; plain slugs without `/` remain top-level.

**Repository mappings** are configured in Settings → GitHub tab as a repeater (`github_repositories`). Each mapping has: `repo_owner`, `repo_name`, `folder_path`, `product_id`, `branch`, `pat`, `default_status`, `duplicate_handling`, `delete_removed`, `status`. The per-mapping `pat` field (sensitive, encrypted) overrides the global `github_pat` for that mapping — use this when repositories belong to different owners or organisations (fine-grained PATs are scoped per owner). The global `github_pat` and `github_webhook_secret` are also `sensitive` type (encrypted at rest). `API::with_pat( $pat )` returns a cloned API instance with the override applied; `Import_Processor::api_for_mapping( $mapping )` selects the right instance automatically.

The `repo_name` field uses TomSelect autocomplete (`field_class: 'ts_autocomplete'` + `field_attributes` from `Settings::get_github_repo_search_attributes()`). The backend is `wp_ajax_wzkb_github_repo_search` (registered in `Settings::__construct()`), which queries `GET /search/repositories?q=…` via the global PAT and returns `{ id: repo-name, name: owner/repo-name }` items. The `ts_autocomplete` class is picked up automatically by `includes/admin/settings/js/tom-select-init.js`, already enqueued by `Settings_API` on settings pages — do not re-enqueue or re-implement TomSelect.

### Block Development

Blocks are in `includes/blocks/src/[block-name]/`. Each block has its own `block.json`, React `edit.js`, and server-side render via PHP. After editing block source, run `npm run build:[block-name]` — never edit files in `build/` directly.

### Public Helper Functions

`includes/functions.php` exposes the plugin's public API. Key functions:

- `wzkb_knowledge()` — render the full KB output
- `wzkb_get_option( $key )` / `wzkb_get_settings()` — read settings (prefer over `get_option()` directly)
- `wzkb_get_breadcrumb()`, `wzkb_get_search_form()`, `wzkb_get_alert()`, `wzkb_related_articles()` — frontend rendering helpers
- `wzkb_get_the_post_thumbnail()` — thumbnail retrieval (supports ACF image fields)
- `wzkb_get_kb_url()`, `wzkb_get_product_sections_list()`, `wzkb_get_term_hierarchy_path()` — URL and taxonomy helpers

Settings are stored as a single serialized array under option key `wzkb_settings`. All settings filters use the prefix `wzkb_` (e.g. `wzkb_get_option_{$key}`).

### REST API

Endpoints under `/wzkb/v1/`: `/sections` (product sections), `/knowledgebase` (list), `/knowledgebase/{id}` (single). Responses are object-cached under group `wzkb_rest` (300 s TTL); cache is invalidated on post save/delete and term changes.

## Code Quality Configuration

- **PHPCS**: `phpcs.xml.dist` — WordPress coding standards
- **PHPStan**: `phpstan.neon.dist` — Level 5 strict analysis; baseline in `phpstan-baseline.neon`; ACF Pro stubs included
- **PHPUnit**: `phpunit.xml.dist` — test configuration, tests in `phpunit/tests/`
