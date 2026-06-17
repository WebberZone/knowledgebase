---
slug: table-of-contents
title: "Table of Contents"
products: [knowledgebase]
sections: [02-kb-advanced]
tags: [knowledgebase,toc]
status: publish
order: 7
---

[Knowledge Base](https://webberzone.com/plugins/knowledgebase/) can automatically generate a table of contents for each article by scanning its headings, injecting anchor IDs, and rendering a nested linked list above the content. Readers can jump straight to any section without scrolling.

## How it works

When the TOC is enabled, the plugin scans the article's H2–H6 headings, adds a unique `id` attribute to each one (used as the scroll target), and renders a nested `<ul>` above the content. The TOC only appears when the article has at least the configured minimum number of headings, so short articles don't get a TOC they don't need.

Existing heading IDs set by third-party TOC blocks (Kadence, Stackable, etc.) are preserved — the plugin will not overwrite them.

## Enabling the TOC

Go to **Knowledge Base → Settings → Output** and scroll to the **Table of Contents** section.

**Show table of contents** — check this box to enable automatic TOC generation for all articles.

## Settings

| Setting | Description | Default |
| --- | --- | --- |
| **TOC heading depth** | Maximum heading level included (2 = H2 only, 3 = H2–H3, …, 6 = H2–H6). | 4 |
| **Minimum headings for TOC** | Articles with fewer headings than this threshold won't show a TOC. | 3 |
| **TOC title** | Label shown above the TOC list. Leave blank to hide the title entirely. | `Table of Contents` |

## Placing the TOC manually

By default the TOC is inserted automatically above the article body. If you want to place it at a specific spot in the content instead, use the `[kbtoc]` shortcode or the **Knowledge Base TOC** block anywhere in the article. The automatic insertion is skipped when a manual placement is detected.

```text
[kbtoc]
```

The shortcode accepts optional attributes to override the global settings for that placement:

| Attribute | Alias | Default | Description |
| --- | --- | --- | --- |
| `heading_depth` | `headingdepth` | From settings | Maximum heading level to include (2–6). |
| `min_headings` | `minheadings` | From settings | Minimum heading count required to display the TOC. |
| `title` | — | From settings | Label shown above the TOC list. Pass an empty string to hide it. |

Example:

```text
[kbtoc heading_depth="3" title="Contents"]
```

You can also insert a TOC using the `[toc]` marker in Markdown when importing articles from GitHub — see [Syncing Docs with GitHub](https://webberzone.com/support/knowledgebase/syncing-docs-with-github/).

> [!NOTE]
> ⓘ When writing Markdown for GitHub import, `[[toc]]` (double brackets) is treated as escaped and renders as the literal text `[toc]` — only `[toc]` (single brackets) triggers TOC insertion.

## Floating TOC *(Pro only)*

The floating TOC is a sticky side-panel that stays visible as the reader scrolls. It automatically highlights the heading currently in view, giving readers a live position indicator.

Enable it under **Knowledge Base → Settings → Output → Show floating table of contents**.

**Floating TOC position** — choose whether the panel is anchored to the **Left** or **Right** side of the viewport (default: Right).

The panel slides in and out horizontally from the viewport edge. When minimized, a narrow tab peeks from the edge; clicking it slides the full panel into view. On mobile, the panel switches to a bottom-bar accordion instead.

## WPML and Polylang

The TOC title string is translatable. In WPML or Polylang setups, you can register `wzkb_settings` in the translation manager, or use the `wzkb_toc` filter (see below) to return a language-specific title.

## Developer hooks

### `wzkb_toc` filter

Filters the complete TOC HTML before it is inserted into the article.

```php
add_filter(
    'wzkb_toc',
    function ( string $toc_html, array $headings, array $args ): string {
        // Wrap in a custom container.
        return '<div class="my-toc-wrapper">' . $toc_html . '</div>';
    },
    10,
    3
);
```

Return an empty string to suppress the TOC for a specific post.

### `wzkb_get_toc()` and `wzkb_toc()`

Call these in custom templates to output the TOC directly.

```php
// Return TOC HTML for the current post (uses current post content by default).
$toc = wzkb_get_toc();

// Echo TOC HTML directly.
wzkb_toc();

// Or pass a specific content string.
$toc = wzkb_get_toc( get_the_content() );
```

Both functions respect the global TOC settings and return an empty string when the TOC would not normally display (e.g. too few headings).

## See also

- [Knowledge Base Settings](https://webberzone.com/support/knowledgebase/knowledge-base-settings/) — full Output settings reference
- [Syncing Docs with GitHub](https://webberzone.com/support/knowledgebase/syncing-docs-with-github/) — using `[toc]` in Markdown imports
