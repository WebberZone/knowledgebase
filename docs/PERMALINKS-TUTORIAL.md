# Knowledge Base Permalinks

This guide explains how Knowledge Base handles URLs and how to configure custom permalink structures in [Pro](https://webberzone.com/plugins/knowledgebase/).

## Quick start

- **Free version**: Set your slugs in **Knowledge Base → Settings → Permalinks**
- **Pro version**: Enable **custom permalinks** and use placeholders like `%product_name%` or `%section_name%` in your article structure

## Permalink settings

All permalink settings are located in **Knowledge Base → Settings → General** under the **Permalinks** section.

### Available settings

- **Knowledge Base slug** (`kb_slug`): Sets the base path for the Knowledge Base. Default: `knowledgebase`
- **Product slug** (`product_slug`): Base path for product archives. Default: `kb/product`
- **Section slug** (`category_slug`): Base path for section archives. Default: `kb/section`
- **Tags slug** (`tag_slug`): Base path for tag archives. Default: `kb/tags`
- **Article Permalink Structure** (`article_permalink`): Custom structure for articles. **Pro feature only**

## Pro: custom permalinks engine

Pro adds a custom permalinks engine that lets you control your URL structure using placeholders.

**When this activates:**

- Pro is installed and activated
- You enter a custom structure in the article permalink field (anything other than the default `%postname%`)

**Supported placeholders:**

- `%product_name%` — The product slug (from your Products taxonomy)
- `%section_name%` — The top-level section slug for articles
- `%postname%` — The article slug
- `%post_id%` — The article ID
- `%author%` — The author username

**How custom permalinks work:**

1. You define a structure using placeholders
2. Pro generates the appropriate rewrite rules
3. Articles use your custom structure
4. Taxonomy archives (products, sections, tags) use their configured slugs

**Example:**

Set `kb_slug` to `help` and `article_permalink` to:

```text
%product_name%/%section_name%/%postname%
```

Result: `https://example.com/help/wordpress/getting-started/`

**Note:** `%section_name%` for articles always returns the top-level parent slug, not the full hierarchy.

## Configuration workflow

1. Open Knowledge Base → Settings → General
2. Configure your base slugs (kb_slug, product_slug, etc.)
3. Pro users: set your custom article permalink structure
4. Save changes

> **Important:** After changing permalink settings, visit Settings → Permalinks in WordPress to flush your rewrite rules. This prevents 404 errors.

## Troubleshooting

### 404 errors after changing settings

Visit Settings → Permalinks in WordPress to flush your rewrite rules.

### URLs not matching your structure

- Check you've entered a custom structure (not just `%postname%`)
- Ensure Pro is active
- Verify your placeholder syntax

### Conflicts with other plugins

Some SEO plugins modify rewrite rules. If you experience conflicts:

- Test with other plugins disabled
- Check your rewrite rules using a plugin like [Rewrite Rules Inspector](https://wordpress.org/plugins/rewrite-rules-inspector/)
- Priority is given to articles over taxonomy archives to prevent conflicts

## Summary

- Free version: configure base slugs for articles and taxonomies
- Pro version: build custom URL structures using placeholders
- Always flush rewrite rules after changing permalink settings
- Pro handles smart conflict resolution between articles and taxonomies
