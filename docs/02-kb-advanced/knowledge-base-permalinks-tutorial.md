---
slug: knowledge-base-permalinks-tutorial
title: "Knowledge Base Permalinks Tutorial"
products: [knowledgebase]
sections: ["02-kb-advanced"]
tags: [installation, knowledgebase, permalinks]
status: publish
order: 0
---

This guide explains how [Knowledge Base](https://webberzone.com/plugins/knowledgebase/) handles URLs and how to configure custom permalink structures in Pro.

## Quick start

- **Free version**: Set your slugs in **Knowledge Base** → **Settings** → **Permalinks**.
- **Pro version**: Enable custom permalinks and use placeholders like `%product_name%`, `%section_name%`, or `%product_id%` in your article structure.

## Permalink settings

All permalink settings are located in **Knowledge Base → Settings → General** under the **Permalinks** section.

### Available settings

- **Knowledge Base slug** (`kb_slug`): Sets the base path for the Knowledge Base. Default: `knowledgebase`
- **Product slug** (`product_slug`): Base path for product archives. Default: `kb/product`
- **Section slug** (`category_slug`): Base path for section archives. Default: `kb/section`
- **Tags slug** (`tag_slug`): Base path for tag archives. Default: `kb/tags`
- **Article Permalink Structure** (`article_permalink`): Custom structure for articles. *(Pro only)*

## Pro: custom permalinks engine

Pro adds a custom permalinks engine that lets you control your URL structure using placeholders.

**When this activates:**

- Pro is installed and activated
- You enter a custom structure in the article permalink field (anything other than the default `%postname%`)

**Supported placeholders:**

| Placeholder | Resolves to |
| --- | --- |
| `%product_name%` | The product slug, from your Products taxonomy |
| `%product_id%` | The product term ID *(added in 3.1.4)* |
| `%section_name%` | The top-level section slug for articles |
| `%section_id%` | The section term ID |
| `%tag_name%` | The tag slug |
| `%postname%` | The article slug |
| `%post_id%` | The article ID |
| `%author%` | The author username |

**How custom permalinks work:**

1. You define a structure using placeholders
2. Pro generates the appropriate rewrite rules
3. Articles use your custom structure
4. Taxonomy archives (products, sections, tags) use their configured slugs

**Example:**

Set `kb_slug` to `help` and `article_permalink` to: `%product_name%/%section_name%/%postname%`

Result: `https://example.com/help/wordpress/getting-started/`

`%section_name%` for articles always returns the top-level parent slug, not the full hierarchy.

### ID placeholders

`%product_id%` and `%section_id%` produce URLs built from term IDs rather than slugs, which keeps the URL stable when you rename a term. On the way back in, the plugin resolves the ID to its term before WordPress parses the request, so the archive loads exactly as a slug-based URL would. A section ID resolves to its full hierarchical path, so nested sections work.

An ID that is not a number, or that does not match a term in the matching taxonomy, returns a 404. Earlier versions fell through to another Knowledge Base archive or article instead.

> [!NOTE]
> ⓘ `%section_id%` was fixed in 3.1.4. In earlier versions it generated the wrong query variable, so section URLs loaded the wrong archive or returned a 404.

## Configuration workflow

1. Open Knowledge Base → Settings → General
2. Configure your base slugs (kb_slug, product_slug, etc.)
3. Pro users: set your custom article permalink structure
4. Save changes

From 3.1.4 the plugin flushes the rewrite rules for you on the request after you save, once the new structures have been registered. On earlier versions, and if URLs still 404 after a save, visit **Settings → Permalinks** in WordPress to flush the rules manually.

## Troubleshooting

### 404 errors after changing settings

Load any page on the site once to let the deferred rewrite flush run. If the URLs still 404, visit **Settings → Permalinks** in WordPress to flush the rules manually.

### A product or section ID URL returns 404

Confirm the ID belongs to a term in the taxonomy the placeholder refers to — `%product_id%` resolves against Products and `%section_id%` against Sections. An ID from the wrong taxonomy, or one whose term has been deleted, returns a 404 by design.

### URLs not matching your structure

- Check you’ve entered a custom structure (not just `%postname%`)
- Ensure Pro is active
- Verify your placeholder syntax

### Conflicts with other plugins

Some SEO plugins modify rewrite rules. If you experience conflicts:

- Test with other plugins disabled
- Check your rewrite rules using a plugin like <a href="https://wordpress.org/plugins/rewrite-rules-inspector/" target="_blank" rel="noreferrer noopener">Rewrite Rules Inspector</a>
- Priority is given to articles over taxonomy archives to prevent conflicts
