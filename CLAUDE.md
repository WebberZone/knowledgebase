# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Plugin Overview

WebberZone Knowledge Base (v3.0.0) is a free WordPress plugin (namespace `WebberZone\Knowledge_Base`) that creates a multi-product knowledge base system. This is the standalone free version — there is no `includes/pro/` directory here. The companion premium plugin (knowledgebase-pro) is a separate repository that extends this codebase with pro features.

- **Plugin entry**: `knowledgebase.php` (defines constants, loads Freemius via `load-freemius.php`, registers autoloader, and directly requires `includes/options-api.php` and `includes/functions.php`)
- **PHP**: 7.4+ | **WordPress**: 6.7+
- **Custom post type**: `wz_knowledgebase` | **Taxonomies**: `wzkb_category`, `wzkb_product`, `wzkb_tag`
- **Constants**: `WZKB_VERSION`, `WZKB_PLUGIN_DIR`, `WZKB_PLUGIN_URL`, `WZKB_PLUGIN_FILE`, `WZKB_DEFAULT_THUMBNAIL_URL`

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
npm run build                            # Build all blocks (runs build:free + build:pro)
npm run build:free                       # Build all free blocks
npm run build:assets                     # Minify CSS/JS and generate RTL
npm run start                            # Watch mode for free blocks
npm run lint:js                          # Lint JavaScript
npm run lint:css                         # Lint CSS
npm run format                           # Auto-format JS and CSS
```

Individual block builds: `npm run build:[kb|articles|sections|products|search|breadcrumb|related|alerts]`

Note: `package.json` also contains `build:pro` and `build:rating` scripts that reference `includes/pro/` — these are irrelevant in this repository and will fail if run here.

### Distribution

```bash
composer zip                             # Create PHP distribution zip
npm run zip                              # Create full plugin zip (wp-scripts plugin-zip)
```

## Architecture

### Main Bootstrap Flow

1. `plugins_loaded` hook → `Main::get_instance()` (singleton)
2. `Main::init()` instantiates all component handlers and registers their hooks
3. Admin components only load on `is_admin()` (deferred to `init` action for translation readiness)
4. `Main` has `$pro` and `$is_pro_enabled` properties defined but they are never set in this repository — those are only used by the pro plugin

### Key Patterns

**Autoloader** (`includes/autoloader.php`): PSR-4 style. Converts `WebberZone\Knowledge_Base\Admin\Settings` → `includes/admin/class-settings.php`.

**Hook Registry** (`includes/util/class-hook-registry.php`): Custom wrapper around WordPress actions/filters with duplicate prevention and closure support. All components register hooks through this instead of calling `add_action()`/`add_filter()` directly.

**Settings**: Global `$wzkb_settings` populated at plugin load. Read via `wzkb_get_option( $key )` or `wzkb_get_settings()`. Settings page in `includes/admin/class-settings.php`. Stored as a single serialized array under option key `wzkb_settings`. All settings filters use the prefix `wzkb_` (e.g. `wzkb_get_option_{$key}`).

**Caching** (`includes/util/class-cache.php`): Term meta-based caching (not transients) with expiry timestamps. AJAX endpoint for admin cache clearing.

**Free/Pro coexistence**: The plugin includes deactivation logic in `knowledgebase.php` — activating either the free or pro plugin automatically deactivates the other and shows an admin notice.

### Component Map

| Directory | Responsibility |
|---|---|
| `includes/admin/` | Settings UI, columns, wizard, notices, activation |
| `includes/frontend/` | Templates, display, shortcodes, styles, search, breadcrumbs, related articles, feeds, patterns |
| `includes/blocks/` | 8 free Gutenberg blocks (React in `src/`, compiled to `build/`) |
| `includes/rest/` | REST API under `/wzkb/v1/` namespace |
| `includes/widgets/` | 4 classic WordPress widgets (Articles, Sections, Breadcrumb, Products) |
| `includes/util/` | Hook registry, caching utilities, helpers |

### Block Development

Blocks are in `includes/blocks/src/[block-name]/`. Each block has its own `block.json`, React `edit.js`, and server-side render via PHP. After editing block source, run `npm run build:[block-name]` — never edit files in `build/` directly.

### Public Helper Functions

`includes/functions.php` exposes the plugin's public API. Key functions:
- `wzkb_knowledge()` — render the full KB output
- `wzkb_get_option( $key )` / `wzkb_get_settings()` — read settings (prefer over `get_option()` directly)
- `wzkb_get_breadcrumb()`, `wzkb_get_search_form()`, `wzkb_get_alert()`, `wzkb_related_articles()` — frontend rendering helpers
- `wzkb_get_the_post_thumbnail()` — thumbnail retrieval (supports ACF image fields)
- `wzkb_get_kb_url()`, `wzkb_get_product_sections_list()`, `wzkb_get_term_hierarchy_path()` — URL and taxonomy helpers

### REST API

Endpoints under `/wzkb/v1/`: `/sections` (product sections), `/knowledgebase` (list), `/knowledgebase/{id}` (single). Responses are object-cached under group `wzkb_rest` (300 s TTL); cache is invalidated on post save/delete and term changes.

## Code Quality Configuration

- **PHPCS**: `phpcs.xml.dist` — WordPress coding standards
- **PHPStan**: `phpstan.neon.dist` — Level 5 strict analysis; baseline in `phpstan-baseline.neon`
- **PHPUnit**: `phpunit.xml.dist` — test configuration, tests in `phpunit/tests/`
