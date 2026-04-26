# WebberZone Knowledgebase — Dev Tools

Scripts to seed a fresh WordPress install with WebberZone Knowledge Base demo content via WP-CLI.

## Files

| File | Purpose |
|---|---|
| `seed-kb.sh` | Main runner — pass your WP path as argument |
| `seed-kb-content.php` | PHP eval-file that creates all categories, tags, and articles |
| `seed-kb-config.php` | Edit this to customise categories, tags, and article content |

## Usage

```bash
# Install and activate the plugin, then run:
bash dev-tools/seed-kb.sh /path/to/wordpress

# Example:
bash dev-tools/seed-kb.sh /Users/ajay/Documents/Dev/Sites/wc.test
```

## What gets created

- **6 top-level categories** (Getting Started, WordPress Core, WooCommerce, Plugins, Themes & Design, Performance & Security)
- **21 subcategories** spread across them
- **20 tags** (woocommerce, gutenberg, block-editor, security, performance, caching, seo, payments, shipping, orders, plugins, themes, php, mysql, rest-api, hooks, shortcodes, multisite, cron, debugging)
- **105+ articles** with realistic WordPress/WooCommerce help-doc content and Picsum header images

## Requirements

- WP-CLI installed and on `$PATH`
- WebberZone Knowledge Base plugin installed (the runner installs + activates it automatically)
- PHP 7.4+

## Re-running

The script is idempotent for categories and tags (it checks for duplicates). Articles are always created fresh, so avoid running it twice on the same site unless you clean up first:

```bash
wp post delete $(wp post list --post_type=wz_knowledgebase --format=ids) --force --path=/your/wp
```
