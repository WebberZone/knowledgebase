# Knowledge Base Permalinks Tutorial

This guide explains how permalinks work in the WebberZone Knowledge Base plugin and how to customise them in both the free and Pro versions. Follow the sections below to configure URLs, understand placeholder behaviour, and troubleshoot issues.

## 1. Permalink Settings Overview

All permalink settings are located in **Knowledge Base → Settings → General** under the **Permalinks** section. The Setup Wizard also exposes these fields during onboarding.

### 1.1 Available Settings

- **Knowledge Base slug** (`kb_slug`): Sets the base path for the Knowledge Base post type. Default: `knowledgebase`. Used in free version and as fallback in Pro.
- **Product slug** (`product_slug`): Base path for product archives. Default: `kb/product`. Supports placeholders in Pro (e.g., `support/product`).
- **Section slug** (`category_slug`): Base path for section archives. Default: `kb/section`. Supports placeholders in Pro (e.g., `support/product/%product_name%/%section_name%`).
- **Tags slug** (`tag_slug`): Base path for tag archives. Default: `kb/tags`. Supports placeholders in Pro (e.g., `support/tag`).
- **Article Permalink Structure** (`article_permalink`): Custom structure for article URLs. **Pro feature only**. Default: empty (uses `{kb_slug}/%postname%`).

### 1.2 Free Version Behavior

- Free version uses standard WordPress permastruct rewrite rules for all taxonomies.
- Article URLs follow the pattern: `{kb_slug}/%postname%`
- Example: If `kb_slug` is `knowledgebase`, articles appear at `/knowledgebase/article-name/`
- Taxonomy slugs can be customized but do not support placeholders.
- To use placeholders and custom article structures, upgrade to Pro.

## 2. Pro: Custom Permalinks Engine

The Pro module activates custom permalink handling when:

- A custom `article_permalink` structure is set (with or without placeholders)
- Taxonomy slugs include placeholders (e.g., `support/product/%product_name%/%section_name%`)

### 2.1 Supported Placeholders

```text
%product_name%  %section_name%  %section_id%
%tag_name%      %post_id%       %postname%  %author%
```

These map to query variables used when building rewrite rules and are sanitised before use.

Placeholders can be filtered with `wzkb_custom_permalink_placeholders`, allowing developers to register additional tokens.

### 2.2 How Custom Permalinks Work

The Pro version automatically builds your article and taxonomy URLs based on the structures you define:

1. **Rewrite rules are generated** - Custom rewrite rules are created for articles, products, sections, and tags based on your structures.
2. **Placeholders get replaced** - Each placeholder like `%product_name%` or `%section_name%` is replaced with the actual product or section assigned to that article.
3. **URLs are built automatically** - The plugin combines your custom structure with the replaced placeholders to create the final URL.
4. **Smart conflict handling** - When multiple structures share the same base path (e.g., article and section structures both starting with `support/product/`), the plugin prioritizes article rules to prevent misrouting.
5. **Permastruct is disabled** - When custom structures are used, WordPress's default taxonomy permastruct rules are disabled to avoid conflicts. Custom rewrite rules handle all routing instead.

**Example:** If you set:

- `kb_slug`: `support/knowledgebase`
- `article_permalink`: `support/product/%product_name%/%section_name%/%postname%`

And create an article titled "Installation Guide" in the "Getting Started" section of the "Widget Suite" product, the URL becomes:
`/support/product/widget-suite/getting-started/installation-guide/`

### 2.3 What Each Placeholder Does

- **%product_name%** - Replaced with the product slug (e.g., `widget-suite`)
- **%section_name%** - Replaced with the section slug (e.g., `getting-started`)
- **%section_id%** - Replaced with the section ID number (e.g., `42`)
- **%tag_name%** - Replaced with the tag slug (e.g., `troubleshooting`)
- **%postname%** - Replaced with the article slug (e.g., `installation-guide`)
- **%post_id%** - Replaced with the article ID number (e.g., `123`)
- **%author%** - Replaced with the author username (e.g., `john-smith`)

**Tip:** Hierarchical placeholders like `%section_name%` can include parent sections, creating nested URLs like `/parent-section/child-section/`.

## 3. Building Custom Structures

### 3.1 Example: Custom Prefix with Product → Section → Article

```text
kb_slug: support/knowledgebase
product_slug: support/product
category_slug: support/product/%product_name%/%section_name%
article_permalink: support/product/%product_name%/%section_name%/%postname%
```

- Produces article URLs like `/support/product/widget-suite/getting-started/installing-widget-suite/`
- Produces section URLs like `/support/product/widget-suite/getting-started/`
- Produces product URLs like `/support/product/widget-suite/`
- Custom prefix `support/` is shared across all structures to avoid KB slug prepending
- Requires each article to have a product and section assigned; otherwise the placeholder resolves to an empty segment

### 3.2 Example: Simple Structures Without Placeholders

```text
kb_slug: knowledgebase
product_slug: kb/product
category_slug: kb/section
tag_slug: kb/tags
article_permalink: %product_name%/%section_name%/%postname%
```

- Product URLs: `/kb/product/widget-suite/` (term name appended automatically)
- Section URLs: `/kb/section/getting-started/` (term name appended automatically)
- Tag URLs: `/kb/tags/troubleshooting/` (term name appended automatically)
- Article URLs: `/knowledgebase/widget-suite/getting-started/installing-widget-suite/`

### 3.3 Example: Numeric Identifiers

```text
article_permalink: %section_id%/%post_id%/%postname%
```

- Helpful for legacy systems needing numeric identifiers in the URL
- Produces URLs like `/42/123/article-slug/`
- Rewrite tags for `section_id` and `post_id` are registered automatically

### 3.4 Term Archive Structures with Placeholders

- **Sections with product context**: `category_slug` set to `support/product/%product_name%/%section_name%` nests sections under their products
- **Tags with section context**: `tag_slug` set to `support/product/%product_name%/%section_name%/%tag_name%` provides full context
- When taxonomy slugs include placeholders, custom rewrite rules are generated to handle the dynamic segments

## 4. Configuration Workflow

1. **Plan taxonomy usage**: Decide which placeholders are meaningful for your data hierarchy.
2. **Update Knowledge Base → Settings → General → Permalinks** with desired structures.
3. **Save settings** to trigger rewrite rule regeneration (automatic in setup and admin UI).
4. **Flush rewrite rules**: Visit **Settings → Permalinks** and click **Save Changes** to ensure WordPress recognizes the new rules.
5. **Assign terms** consistently (products, sections, tags) so placeholders resolve correctly.
6. **Test URLs**:
   - Visit an article, product, section, and tag page.
   - Verify the URL matches your configured structure.
   - Use the WordPress Rewrite Rules Inspector or `wp rewrite list` CLI if needed.

## 5. How Pro Handles Custom Structures

### 5.1 Rewrite Rule Generation

When Pro is enabled and custom structures are detected:

1. **Custom rewrite rules are created** for articles, products, sections, and tags based on your configured structures
2. **Taxonomy permastruct is disabled** to prevent conflicts with custom rules
3. **Article rules are prioritized** to ensure article URLs are matched before product/section archives
4. **Child section rules are conditionally disabled** when article structures have more segments than section structures, preventing misrouting

### 5.2 Permalink Generation

When generating permalinks for articles and taxonomies:

1. **Placeholders are replaced** with actual term/post values
2. **Term slugs are appended** automatically for taxonomy structures without explicit placeholders
3. **KB slug is intelligently prepended** only when the custom structure doesn't already start with the KB slug's first segment
4. **Final URL is constructed** and returned via `get_term_link()` and `get_permalink()` filters

### 5.3 Fallback Behavior Without Pro

If Pro is not enabled but custom structures are configured:

- **Permastruct rewrite rules remain active** to ensure URLs work
- **Custom article structures are ignored** and default `{kb_slug}/%postname%` is used
- **Taxonomy slugs work** but placeholders are not supported
- **No custom rewrite rules are generated** - WordPress handles routing via permastruct

## 6. Developer Hooks & Extensibility

- `wzkb_custom_permalink_placeholders`: Register extra placeholders or override default mappings.
- `wzkb_article_permalink_structure` and `wzkb_term_permalink_structure`: Filter the resolved path before it is prefixed with the KB slug.
- `wzkb_after_setting_output`: Used by Pro to inject placeholder documentation beneath the settings header for admins.

## 7. Troubleshooting Tips

- **404 errors**: Visit **Settings → Permalinks** and click **Save Changes** to flush rewrite rules. This is the most common fix.
- **Empty placeholders**: Ensure articles have the required product/section/tag assignments. Missing assignments result in empty URL segments.
- **Wrong content loading**: If an article URL loads a product/section archive instead, flush rewrite rules. Pro prioritizes article rules, but rule order depends on proper flushing.
- **Conflicting slugs**: Avoid reusing the KB slug for other pages. Pro attempts to resolve clashes by detecting shared prefixes, but unique slugs prevent ambiguity.
- **Taxonomy URLs not working**: If product/section/tag URLs return 404, ensure their slugs are configured in settings and rewrite rules are flushed.
- **Testing root-level URLs**: When using `%postname%` without a prefix, verify that article slugs do not collide with regular posts or pages.
- **Pro not enabled**: Custom article structures are ignored if Pro is not active. Free version uses default `{kb_slug}/%postname%` structure.

## 8. Summary

- **Free version** supports basic slug customization for taxonomies and default article URLs using the KB slug.
- **Pro version** unlocks placeholder-driven structures for articles and taxonomies with intelligent rewrite rule generation and conflict resolution.
- **Custom prefixes** (e.g., `support/product/`) allow you to organize URLs outside the KB slug hierarchy.
- **Consistent taxonomy assignments** and thoughtful structure planning ensure clean, SEO-friendly URLs tailored to your knowledge base.
- **Rewrite rule flushing** is essential after any permalink setting changes to ensure WordPress recognizes the new URL patterns.
