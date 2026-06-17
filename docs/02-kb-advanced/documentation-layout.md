---
slug: documentation-layout
title: "Documentation Layout"
products: [knowledgebase]
sections: [02-kb-advanced]
tags: [knowledgebase, docs mode, section tree, layout, pro]
status: publish
order: 0
---

[kbtoc]

The Documentation Layout is a pro feature that transforms your knowledge base into a three-column documentation site. When enabled, every KB page — the archive, product pages, section pages, and single articles — renders with a full-width docs-style template:

- **Left column** — collapsible section tree navigation, scoped to the current product
- **Center column** — article or archive content
- **Right column** — "On this page" outline generated from the article's headings

Enable it with a single setting; no theme editing or custom templates required.

## Enabling Documentation Layout *(Pro only)*

1. Navigate to **Knowledge Base → Settings** in your WordPress admin.
2. Open the **Styles** tab.
3. Check **Documentation layout**.
4. Click **Save Changes**.

The three-column layout activates immediately across all KB pages. Your existing **Knowledge Base Style** selection continues to apply within the center column.

When Documentation Layout is active, the floating TOC panel is automatically suppressed — the right-column outline serves the same purpose.

## Section Tree

The section tree is the navigation sidebar rendered in the left column of the Documentation Layout. It is also available as a standalone **block** and a **classic widget** for use outside the docs layout.

### Behavior

The tree is context-aware by default (`auto` mode):

- On the **KB home** — shows the full tree for all products.
- On a **product archive** — shows sections for that product.
- On a **section archive** — shows the tree for the parent product, with the current section expanded.
- On a **single article** — shows the tree for the article's product, with the article's section expanded and the article link highlighted.

Sections are collapsible. The current term and article are highlighted automatically.

### Section Tree block *(Pro only)*

Insert the **Knowledge Base Section Tree** block from the block inserter. Block settings:

| Attribute | Type | Description |
|---|---|---|
| `title` | string | Optional heading above the tree. Default empty. |
| `mode` | string | `auto` (default), `full`, `product`, or `section`. |
| `productId` | number | Product term ID when `mode` is `product`. Default `0`. |
| `sectionId` | number | Section term ID when `mode` is `section`. Default `0`. |
| `depth` | number | Maximum nesting depth. `-1` for unlimited (default). |
| `highlightCurrent` | boolean | Highlight the current term and article. Default `true`. |
| `showArticles` | boolean | List article links under each section. Default `true`. |
| `articleLimit` | number | Max articles shown per section. Default `10`. |

### Section Tree widget *(Pro only)*

Navigate to **Appearance → Widgets** and add the **Knowledge Base Section Tree** widget to any sidebar. The widget exposes the same options as the block (mode, product, section, depth, article limit, title, show articles).

### PHP helpers *(Pro only)*

```php
// Return the tree HTML.
$html = wzkb_get_section_tree( $args );

// Echo the tree HTML directly.
wzkb_the_section_tree( $args );
```

`$args` accepts the same keys as the block attributes above, using snake_case: `mode`, `product_id`, `section_id`, `depth`, `highlight`, `show_articles`, `article_limit`, `title`, `extra_class`, `wrapper`.

```php
// Show the full tree for all products (disables context detection).
echo wzkb_get_section_tree( array( 'mode' => 'full' ) );

// Show only the tree for a specific product.
echo wzkb_get_section_tree( array(
    'mode'       => 'product',
    'product_id' => 42,
    'depth'      => 2,
) );

// Show a subtree rooted at a specific section.
echo wzkb_get_section_tree( array(
    'mode'       => 'section',
    'section_id' => 15,
) );
```

### Filters

**`wzkb_section_tree_article_limit`** — Override the default article limit per section.

```php
add_filter( 'wzkb_section_tree_article_limit', function( int $limit ): int {
    return 5;
} );
```

**`wzkb_section_tree_html`** — Filter the final section tree HTML before output.

```php
add_filter( 'wzkb_section_tree_html', function( string $html, array $args ): string {
    return '<nav aria-label="Knowledge Base">' . $html . '</nav>';
}, 10, 2 );
```

## Styling

The docs layout wrapper carries the body class `wzkb-docs-layout`. Target it in your theme CSS to adjust spacing, colors, or column widths.

| Class | Description |
|---|---|
| `.wzkb-docs-layout` | Body class when docs mode is active. |
| `.wzkb-docs` | Outer wrapper div for the three-column layout. Also receives `.wzkb-docs--no-toc` when the current page has no headings to outline. |
| `.wzkb-docs-sidebar-region` | Container div for the left sidebar (holds the toggle and the `<aside>`). |
| `.wzkb-docs-sidebar` | The `<aside>` element containing the navigation tree. |
| `.wzkb-docs-main` | The `<main>` element for article or archive content. |
| `.wzkb-docs-onthispage` | The `<aside>` element for the "On this page" outline. Only rendered when the article has headings. |
| `.wzkb-section-tree` | Section tree wrapper (block, widget, and docs sidebar). |
| `.wzkb-section-tree__title` | Optional heading inside the tree wrapper. |
| `.wzkb-section-tree__item--active` | Applied to the active section or article link. |

The built-in column layout uses CSS Grid applied to `.wzkb-docs`. To adjust column proportions:

```css
.wzkb-docs {
    grid-template-columns: 240px 1fr 200px;
}
```

On mobile the three columns collapse to a single-column stacked layout automatically.

## See also

- [Knowledge Base Settings](https://webberzone.com/support/knowledgebase/knowledge-base-settings/)
- [Table of Contents](https://webberzone.com/support/knowledgebase/table-of-contents/)
- [Knowledge Base Shortcodes](https://webberzone.com/support/knowledgebase/knowledge-base-shortcodes/)
