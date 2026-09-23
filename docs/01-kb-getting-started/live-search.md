---
slug: live-search
title: "Live Search"
products: [knowledgebase]
sections: ["01-kb-getting-started"]
tags: [knowledgebase, search]
status: publish
order: 0
toc: true
---

[toc]

[Knowledge Base](https://webberzone.com/plugins/knowledgebase/) includes a live search feature that shows article suggestions in a dropdown as visitors type in the search form — no page reload needed. Results update in real time, support keyboard navigation, and include screen reader announcements for accessibility.

## Enabling live search

Go to **Knowledge Base → Settings → Output → Search** and make sure **Enable live search** is checked.

**Default:** Enabled

## How it works

As soon as the visitor types in the KB search box, an AJAX request fetches matching articles and displays them in a dropdown below the input. Selecting a suggestion navigates directly to that article.

- **Keyboard navigation** — use the arrow keys to move through suggestions and Enter to select.
- **Accessible** — results are announced to screen readers via an ARIA live region.
- **Debounced** — requests fire after a short pause while typing, not on every keystroke.

## Using the search form

The search form can be placed anywhere using the `[[kbsearch]]` shortcode or the **Knowledge Base Search** block. Live search works with both.

```text
[[kbsearch]]
```

See [Knowledge Base Shortcodes](https://webberzone.com/support/knowledgebase/knowledge-base-shortcodes/) for full shortcode options.

## Multilingual sites

Live search uses WordPress queries to find published articles. How results follow the visitor’s language depends on your translation plugin:

- **WPML and Polylang** — article selection relies on the translation plugin’s WordPress query filters and the language context available during the AJAX request. Knowledge Base does not send a separate WPML or Polylang language parameter. Check suggestions from each translated page on your site.
- **TranslatePress** — from Knowledge Base 3.1.5, the search form sends the current TranslatePress language with its AJAX request. The response translates suggestion titles and converts article links to that language. This translates the returned results; it does not add a separate search index for translated text.

If suggestions use the wrong language, check your translation plugin’s configuration and confirm that the affected articles have translations.

## See also

- [Knowledge Base Settings](https://webberzone.com/support/knowledgebase/knowledge-base-settings/) — Output settings reference
- [Knowledge Base Shortcodes](https://webberzone.com/support/knowledgebase/knowledge-base-shortcodes/) — `[[kbsearch]]` shortcode
