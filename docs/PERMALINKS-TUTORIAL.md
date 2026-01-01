# Knowledge Base Permalinks Tutorial

This guide explains how permalinks work in the WebberZone Knowledge Base plugin and how to customise them in both the free and Pro versions. Follow the sections below to configure URLs, understand placeholder behaviour, and troubleshoot issues.

## 1. Permalink Basics (Free Version)

- __KB Slug (`kb_slug`)__: Sets the base path used when registering the Knowledge Base post type. Default is `knowledgebase`.
- __Taxonomy slugs__: `product_slug`, `category_slug`, `tag_slug` configure frontend URLs for product, section, and tag archives.
- Settings are available in *Knowledge Base → Settings → General* under the __Permalinks__ header.
- The Setup Wizard also exposes the same fields during the onboarding permalinks step.
- __Note:__ Article permalink customization (`article_permalink`) is a __Pro feature__. Free version uses the default structure.

### 1.1 Article URLs in Free Version

- Free version uses the default article structure: `{kb_slug}/%postname%`
- Example: If `kb_slug` is `knowledgebase`, articles appear at `/knowledgebase/article-name/`
- To customize article permalink structures with placeholders, upgrade to Pro.

## 2. Pro: Custom Permalinks Engine

The Pro module activates `Custom_Permalinks` when structures include placeholders beyond simple `%postname%` or single-taxonomy slugs.

### 2.1 Supported Placeholders

```text
%product_name%  %section_name%  %section_id%
%tag_name%      %post_id%       %postname%  %author%
```

These map to query vars used when building rewrite rules and are sanitised before use.

Placeholders can be filtered with `wzkb_custom_permalink_placeholders`, allowing developers to register additional tokens.

### 2.2 How Custom Permalinks Work

The Pro version automatically builds your article URLs based on the structure you define:

1. __Placeholders get replaced__ - Each placeholder like `%product_name%` or `%section_name%` is replaced with the actual product or section assigned to that article.
2. __URLs are built automatically__ - The plugin combines your KB slug with the replaced placeholders to create the final URL.
3. __Archive pages work too__ - Product, section, and tag archive pages also use your custom structures.
4. __Smart conflict handling__ - If a URL could match multiple pages, the plugin ensures the correct one loads.

__Example:__ If you set `article_permalink` to `%product_name%/%section_name%/%postname%` and create an article titled "Installation Guide" in the "Getting Started" section of the "Widget Suite" product, the URL becomes:
`/knowledgebase/widget-suite/getting-started/installation-guide/`

### 2.3 What Each Placeholder Does

- __%product_name%__ - Replaced with the product slug (e.g., `widget-suite`)
- __%section_name%__ - Replaced with the section slug (e.g., `getting-started`)
- __%section_id%__ - Replaced with the section ID number (e.g., `42`)
- __%tag_name%__ - Replaced with the tag slug (e.g., `troubleshooting`)
- __%postname%__ - Replaced with the article slug (e.g., `installation-guide`)
- __%post_id%__ - Replaced with the article ID number (e.g., `123`)
- __%author%__ - Replaced with the author username (e.g., `john-smith`)

__Tip:__ Hierarchical placeholders like `%section_name%` can include parent sections, creating nested URLs like `/parent-section/child-section/`.

## 3. Building Custom Structures

### 3.1 Example: Product → Section → Article Slug

```text
kb_slug: productdocs
article_permalink: %product_name%/%section_name%/%postname%
```

- Produces URLs like `/productdocs/widget-suite/getting-started/installing-widget-suite/`.
- Requires each article to have a product and section assigned; otherwise the placeholder resolves to an empty segment.

### 3.2 Example: Section ID with Post ID

```text
article_permalink: %section_id%/%post_id%/%postname%
```

- Helpful for legacy systems needing numeric identifiers in the URL.
- Adds rewrite tags for `tag_id` and `p` automatically via placeholder mappings.

### 3.3 Term Archive Structures

- __Sections with product context__: `category_slug` could be `%product_name%/%section_name%` to nest sections under their products.
- __Tags with parent sections__: `tag_slug` could include `%section_name%/%tag_name%` for context-aware tag archives.

## 4. Configuration Workflow

1. __Plan taxonomy usage__: Decide which placeholders are meaningful for your data hierarchy.
2. __Update Settings → Output → Permalinks__ with desired structures.
3. __Save settings__ to trigger `flush_rewrite_rules()` and regenerate rewrite rules (automatic in setup and admin UI).
4. __Assign terms__ consistently (products, sections, tags) so placeholders resolve correctly.
5. __Test URLs__:
   - Visit an article, product, section, and tag page.
   - Use the WordPress debug rewrite rules screen or `wp rewrite list` CLI if needed.

## 5. Developer Hooks & Extensibility

- `wzkb_custom_permalink_placeholders`: register extra placeholders or override default mappings.
- `wzkb_article_permalink_structure` and `wzkb_term_permalink_structure`: filter the resolved path before it is prefixed with the KB slug.
- `wzkb_after_setting_output`: used by Pro to inject placeholder documentation beneath the settings header for admins.

## 6. Troubleshooting Tips

- __404 errors__: Visit *Settings → Permalinks* and click *Save* to flush rules.
- __Empty placeholders__: Ensure articles have the required product/section/tag assignments.
- __Conflicting slugs__: Avoid reusing the KB slug for other pages; Pro will attempt to resolve clashes but unique slugs prevent ambiguity.
- __Testing root-level URLs__: When using `%postname%`, verify that article slugs do not collide with regular posts or pages.

## 7. Summary

- Free version supports basic slug changes and optional root-level article URLs.
- Pro unlocks placeholder-driven structures for articles and taxonomies with robust rewrite handling.
- Consistent taxonomy assignments and thoughtful structure planning ensure clean, SEO-friendly URLs tailored to your knowledge base.
