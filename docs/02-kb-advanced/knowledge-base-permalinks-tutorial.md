---
slug: knowledge-base-permalinks-tutorial
title: "Knowledge Base Permalinks Tutorial"
products: [knowledgebase]
sections: ["02-kb-advanced"]
tags: [installation, knowledgebase, permalinks]
status: publish
order: 0
toc: true
---

[toc]

This guide explains how [Knowledge Base](https://webberzone.com/plugins/knowledgebase/) handles URLs and how to configure custom permalink structures in Pro.

## Quick start

- **Free version**: Set your slugs in **Knowledge Base** → **Settings** → **General** → **Permalinks**.
- **Pro version**: Enter an article structure containing `%postname%` or `%post_id%`. You can also include product, section, tag, or author placeholders.

## Permalink settings

All permalink settings are located in **Knowledge Base → Settings → General** under the **Permalinks** section.

### Available settings

- **Knowledge Base slug** (`kb_slug`): Sets the base path for the Knowledge Base. Default: `knowledgebase`
- **Product slug** (`product_slug`): Base path for product archives. Default: `kb/product`
- **Section slug** (`category_slug`): Base path for section archives. Default: `kb/section`
- **Tags slug** (`tag_slug`): Base path for tag archives. Default: `kb/tags`
- **Article Permalink Structure** (`article_permalink`): Custom structure for articles. *(Pro only)*

## Custom permalinks engine *(Pro only)*

Pro adds a custom permalinks engine that lets you control your URL structure using placeholders.

**When this activates:**

- Pro is installed and activated
- The article structure contains a placeholder and is not just `%postname%`, or a product, section, or tag slug contains a placeholder.

The article structure defaults to empty, which uses the Knowledge Base slug followed by the article slug. `%postname%` alone gives root-level URLs. A custom article structure must contain `%postname%` or `%post_id%`; otherwise the plugin keeps the default article link.

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
2. Pro matches incoming requests against those structures and checks that the captured articles and terms exist
3. Articles use your custom structure
4. Taxonomy archives (products, sections, tags) use their configured slugs

**Example:**

Set `kb_slug` to `help` and `article_permalink` to: `%product_name%/%section_name%/%postname%`

With product `wordpress`, section `getting-started`, and article `installation`, the result is `https://example.com/help/wordpress/getting-started/installation/`.

`%section_name%` for articles always returns the top-level parent slug, not the full hierarchy.

### ID placeholders

`%product_id%` and `%section_id%` produce URLs built from term IDs rather than slugs, which keeps the URL stable when you rename a term. On the way back in, the plugin resolves the ID to its term before WordPress parses the request, so the archive loads exactly as a slug-based URL would. A section ID resolves to its full hierarchical path, so nested sections work.

An ID must be numeric and belong to the matching taxonomy. From 3.1.5, a custom route that cannot resolve its article or terms is left to WordPress. It returns a 404 if no other WordPress route resolves the URL.

> [!NOTE]
> ⓘ `%section_id%` was fixed in 3.1.4. In earlier versions it generated the wrong query variable, so section URLs loaded the wrong archive or returned a 404.

## Blank slugs and fallback links

From 3.1.5, leaving both the Knowledge Base slug and article structure empty gives articles root-level URLs in Free and Pro. The dedicated Knowledge Base archive is disabled when its slug is blank. This does not make the knowledge base your homepage.

Blank product, section, and tag slugs use their defaults: `kb/product`, `kb/section`, and `kb/tags`. In Free, saved taxonomy values containing placeholders also fall back to those defaults.

In Pro, taxonomy structures define the full archive path; the Knowledge Base slug is not prepended. Article structures normally receive the Knowledge Base slug as a prefix. A structure beginning with the same first literal path segment as the Knowledge Base slug defines its own root instead.

If a placeholder has no value, such as an article with no assigned product, Pro keeps the default link rather than generating a URL with an empty path segment.

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

Confirm the ID belongs to a term in the taxonomy the placeholder refers to — `%product_id%` resolves against Products and `%section_id%` against Sections. A missing term prevents the custom route from resolving.

### URLs not matching your structure

- Check you’ve entered a custom structure (not just `%postname%`)
- Ensure Pro is active
- Verify your placeholder syntax

### Conflicts with other plugins

Some SEO plugins modify rewrite rules. If you experience conflicts:

- Test with other plugins disabled
- Check your rewrite rules using a plugin like <a href="https://wordpress.org/plugins/rewrite-rules-inspector/" target="_blank" rel="noreferrer noopener">Rewrite Rules Inspector</a>
- Pro checks valid custom article routes before custom taxonomy routes. Unresolved custom routes are left to WordPress.
- Use distinct URL paths to avoid collisions with existing pages, posts, and other archives.
- Pro removes legacy custom rewrite rules once after an update. Custom article routes support article pagination, comment feeds, embeds, and comment pagination; custom archive routes support pagination and feeds.
