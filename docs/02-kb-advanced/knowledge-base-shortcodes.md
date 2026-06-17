---
slug: knowledge-base-shortcodes
title: "Knowledge Base shortcodes"
products: [knowledgebase]
sections: [02-kb-advanced]
tags: [knowledgebase,shortcode]
status: publish
order: 0
---

[Knowledge Base](https://webberzone.com/plugins/knowledgebase/) includes multiple shortcodes for embedding the knowledge base, search form, breadcrumbs, alerts, and related articles anywhere in your content.

## \[knowledgebase\]

You can display the knowledge base anywhere in your blog using the `[knowledgebase]` shortcode. The shortcode takes one optional attribute `category`, e.g.:

```text
[knowledgebase category="92"]
```

*category*: Category ID for which you want to display the knowledge base. You can find the ID in the Sections listing under the Knowledge Base menu in the WordPress Admin

## \[kbsearch\]

Display the search form using the `[kbsearch]` shortcode.

## \[kbbreadcrumb\]

You can display the knowledge base anywhere in your blog using the `[kbbreadcrumb]` shortcode. The shortcode takes one optional attribute `separator`, e.g.:

```text
[kbbreadcrumb separator=" >> "]
```

*separator*: The separator is used between each “crumb” of the entire breadcrumb

## \[kbtoc\]

Render a table of contents for the current article. The TOC is built from the article's headings and respects the same depth and minimum-heading settings as the auto-generated TOC.

```text
[kbtoc]
```

Optional attributes override the global settings for that placement:

| Attribute | Alias | Default | Description |
| --- | --- | --- | --- |
| `heading_depth` | `headingdepth` | Setting value | Maximum heading level to include (2–6). |
| `min_headings` | `minheadings` | Setting value | Minimum headings required before the TOC is shown. |
| `title` | — | Setting value | Label above the TOC list. Pass an empty string to hide it. |

Example:

```text
[kbtoc heading_depth="3" title="Contents"]
```

See [Table of Contents](https://webberzone.com/support/knowledgebase/table-of-contents/) for the full feature guide.

## \[kb\_related\_articles\]

Display a list of related articles below any content. Uses the same relevance engine as the automatic related articles feature.

```text
[kb_related_articles]
```

Optional attributes:

| Attribute | Default | Description |
| --- | --- | --- |
| `numberposts` | `5` | Number of related articles to show. |
| `show_thumb` | `true` | Show article thumbnails. |
| `show_date` | `true` | Show article publish dates. |
| `title` | `<h3>Related Articles</h3>` | Section heading; accepts HTML. |
| `thumb_size` | `thumbnail` | Registered image size for thumbnails. |

See [Knowledge Base Related Articles](https://webberzone.com/support/knowledgebase/customizing-related-articles-in-knowledge-base/) for developer hooks and advanced usage.

## \[kbalert\]

<div class="wp-block-image">

<figure class="aligncenter size-large">
<img src="https://webberzone.com/wp-content/uploads/2020/06/Knowledge-Base-Alerts-707x1024.png" class="wp-image-232" loading="lazy" decoding="async" srcset="https://webberzone.com/wp-content/uploads/2020/06/Knowledge-Base-Alerts-707x1024.png 707w, https://webberzone.com/wp-content/uploads/2020/06/Knowledge-Base-Alerts-207x300.png 207w, https://webberzone.com/wp-content/uploads/2020/06/Knowledge-Base-Alerts.png 736w" sizes="auto, (max-width: 707px) 100vw, 707px" width="707" height="1024" />
</figure>

</div>

You can use this shortcode to display a different set of alerts within your Knowledge Base articles or even the rest of your WordPress site. This is similar to what you’d see in Bootstrap or other plugins.

The shortcode takes three optional attributes e.g.:

```text
[[kbalert type="secondary" class="alert" text="Additional text"]]A secondary alert[[/kbalert]]
```

- *type*: alert type as per the screenshot above. Default is ‘primary’
- *class*: additional classes to include in the wrapping div as a space or comma separated list. Default is ‘alert’
- *text*: any additional text you want to include before the wrapped content. In the above example, the wrapped content is “A secondary alert”
