---
slug: knowledge-base-help-widget
title: "Knowledge Base Pro Help Widget"
products: [knowledgebase]
sections: ["01-kb-getting-started"]
tags: [installation, knowledgebase]
status: publish
order: 0
toc: true
---

[toc]

The **Help Widget** in [Knowledge Base Pro](https://webberzone.com/plugins/knowledgebase/) is a floating button that opens a help panel on your site. Visitors can search your knowledge base, browse suggested articles, get an AI answer with [Ask the Docs](https://webberzone.com/support/knowledgebase/ask-the-docs/), or send you a message, without leaving the page.

## Set up the Help Widget

Go to **Knowledge Base → Settings → Pro** and turn on **Enable Help Widget** in the **Help Widget** section. From there you can:

- show it on the **Entire Site** or **Knowledge Base Only**;
- place the button at the bottom right or bottom left, as an icon, text or both;
- set seven colors to match your brand;
- change the greeting and search placeholder;
- turn the contact form on and set the address it sends to;
- hide it on mobile, or turn off the button pulse.

[Knowledge Base Settings](https://webberzone.com/support/knowledgebase/knowledge-base-settings/#help-widget) describes each setting.

## What visitors see

- **Suggested articles.** On a knowledge base article, the panel suggests related articles. Elsewhere, or when there are none, it shows the five most recent articles.
- **Search.** Typing searches your articles, using the same results as the knowledge base search, including [Better Search](https://wordpress.org/plugins/better-search/) when it is active.
- **Contact form.** When search doesn't help, visitors can send a message to your contact email. A honeypot field and a limit of five messages per hour per visitor block spam, without storing IP addresses.

The panel follows the visitor's light or dark mode preference, works with the keyboard and screen readers, and supports right-to-left languages.

## Ask the Docs in the Help Widget

With Ask the Docs enabled and **Use in the Help widget** turned on under **Knowledge Base → Settings → AI (Beta)**:

- Typing still shows matching articles.
- Pressing Enter or the search button also shows a short answer above the articles, with its sources and related articles.
- When there is no answer, the **Fallback message** appears above the articles.
- Once a visitor reaches their hourly limit, or the site reaches its daily cap, only the articles are shown.

## Troubleshooting

- **The widget doesn't appear.** Check that **Enable Help Widget** is on, that **Display Location** isn't **Knowledge Base Only** on a non-knowledge-base page, and that **Show on Mobile** is on if you're testing on a phone. Then clear your page cache.
- **Search finds nothing.** Make sure you have published knowledge base articles, and clear your page cache.
- **Contact messages don't arrive.** Check the **Contact Email** setting and your spam folder. If WordPress can't send email from your server, use an SMTP plugin.
- **Colors don't change.** Save the settings, clear your browser and page caches, and check whether your theme overrides the colors.

## Developer reference

### Filters and actions

| Hook | Use |
| --- | --- |
| [`wzkb_help_widget_show`](https://webberzone.dev/knowledgebase/hooks/wzkb_help_widget_show/) | Return `false` to hide the widget on a page. |
| [`wzkb_help_widget_suggested_articles`](https://webberzone.dev/knowledgebase/hooks/wzkb_help_widget_suggested_articles/) | Return article IDs to suggest for the current page. |
| [`wzkb_related_articles_query_args`](https://webberzone.dev/knowledgebase/hooks/wzkb_related_articles_query_args/) | Change the related-articles query used for suggestions on knowledge base articles. |
| [`wzkb_help_widget_labels`](https://webberzone.dev/knowledgebase/hooks/wzkb_help_widget_labels/) | Change any text in the panel. |
| [`wzkb_help_widget_message_min_length`](https://webberzone.dev/knowledgebase/hooks/wzkb_help_widget_message_min_length/) | Change the minimum contact message length. |
| [`wzkb_help_widget_contact_submitted`](https://webberzone.dev/knowledgebase/hooks/wzkb_help_widget_contact_submitted/) | Action after a contact message is sent, with the name, email, subject and message. |

For example, to suggest specific articles on the pricing page and hide the widget at checkout:

```php
add_filter( 'wzkb_help_widget_suggested_articles', function ( $article_ids, $current_id ) {
    return is_page( 'pricing' ) ? array( 123, 456 ) : $article_ids;
}, 10, 2 );

add_filter( 'wzkb_help_widget_show', function ( $show ) {
    return is_page( 'checkout' ) ? false : $show;
} );
```

The labels you can change with `wzkb_help_widget_labels` are `greeting`, `searchPlaceholder`, `searchButton`, `contactButton`, `backButton`, `closeButton`, `noResults`, `noResultsMessage`, `suggestedArticles`, `searchResults`, `contactFormTitle`, `nameLabel`, `emailLabel`, `subjectLabel`, `messageLabel`, `submitButton`, `successMessage`, `errorMessage`, `requiredField` and `messageTooShort`.

### JavaScript API

Control the widget with the global `WZKBHelpWidget()` function:

```js
WZKBHelpWidget( 'open' );                 // Also 'close' and 'toggle'.
WZKBHelpWidget( 'search', 'installation' );
WZKBHelpWidget( 'navigate', 'contact' );  // 'home', 'search' or 'contact'.
WZKBHelpWidget( 'contact' );
```

For example, `<button onclick="WZKBHelpWidget('open')">Need help?</button>` opens it from your own button.

The widget triggers these jQuery events on `document`: `wzkb-help-widget-opened`, `wzkb-help-widget-closed`, `wzkb-help-widget-screen-changed` (with the screen name), `wzkb-help-widget-search` (with the query), `wzkb-help-widget-contact-opened` and `wzkb-help-widget-contact-submitted` (with the form data). Use them to track usage:

```js
jQuery( document ).on( 'wzkb-help-widget-opened', function () {
    gtag( 'event', 'help_widget_opened' );
} );
```

### CSS

The colors from the settings are exposed as CSS custom properties on `#wzkb-help-widget-container`: `--wzkb-help-widget-color`, `--wzkb-help-widget-hover-color`, `--wzkb-help-widget-text-color`, `--wzkb-help-widget-hover-text-color`, `--wzkb-help-widget-bg`, `--wzkb-help-widget-panel-text` and `--wzkb-help-widget-link-hover`. `--wzkb-help-widget-border`, `--wzkb-help-widget-shadow` and `--wzkb-help-widget-radius` have no setting:

```css
#wzkb-help-widget-container {
    --wzkb-help-widget-radius: 4px;
}

.wzkb-help-widget-panel {
    width: 420px;
}
```
