---
slug: knowledge-base-settings
title: "Knowledge Base Settings"
products: [knowledgebase]
sections: [01-kb-getting-started]
tags: [knowledgebase,settings]
status: publish
order: 0
---

[kbtoc]

This document describes all available settings for the [Knowledge Base](https://webberzone.com/plugins/knowledgebase/) plugin. Access settings via **Knowledge Base → Settings** in your WordPress admin.

## General

### Multi-Product Mode

#### Enable Multi-Product Mode

Enable this option to use a dedicated “Products” menu to organize your knowledge base articles and sections by product. This system allows you to assign each article or section to one or more products, making it easier to manage documentation for different software, hardware, or service lines. If your knowledge base does not need this level of organization, you can leave this option disabled.

#### Use Knowledge Base as Homepage *(Pro only)*

Enable this option to display the Knowledge Base on the site homepage. The Knowledge Base URL will serve as the homepage, and the Knowledge Base archive URL will redirect to it.

### Permalinks

The following settings affect the knowledge base’s permalinks. These are set when registering the custom post type and taxonomy. Please visit the **Permalinks** page in the Settings menu to refresh permalinks if you get 404 errors. Learn <a href="https://webberzone.com/support/knowledgebase/knowledge-base-permalinks-tutorial/" data-type="wz_knowledgebase" data-id="9261">how Permalinks work in Free and Pro</a>.

#### Knowledge Base slug

This sets the default path for the knowledge base URL and is set when registering the custom post type.

**Default:** `knowledgebase`

#### Product slug

This slug forms part of the URL for product pages when Multi-Product Mode is enabled. The value is used when registering the custom taxonomy.

**Default:** `kb/product`

#### Section slug

Each section is a section of the knowledge base. This setting is used when registering the custom section and forms a part of the URL when browsing section archives.

**Default:** `kb/section`

#### Tags slug

Each article can have multiple tags. This setting is used when registering the custom tag and forms a part of the URL when browsing tag archives.

**Default:** `kb/tags`

#### Article Permalink Structure *(Pro only)*

Structure for article URLs.

### Performance

#### Enable cache

Cache query results to speed up knowledge base retrieval. Recommended for large knowledge bases.

**Default:** Disabled

#### Cache Time *(Pro only)*

How long should the knowledge base be cached for? Default is 1 day.

**Options:**

- No expiry
- 1 Hour
- 6 Hours
- 12 Hours
- 1 Day (default)
- 3 Days
- 1 Week
- 2 Weeks
- 30 Days
- 60 Days
- 90 Days
- 1 Year

### Uninstall options

#### Delete options on uninstall

Check this box to delete the settings on this page when the plugin is deleted via the Plugins page in your WordPress Admin.

**Default:** Enabled

#### Delete all content on uninstall

Check this box to delete all the posts, categories and tags created by the plugin. There is no way to restore the data if you choose this option.

**Default:** Disabled

### Feed options

#### Include in feed

Adds the knowledge base articles to the main RSS feed for your site.

**Default:** Enabled

#### Disable KB feed

The knowledge base articles have a default feed. This option will disable the feed. You might need to refresh your permalinks when changing this option.

**Default:** Disabled

## Output

### Knowledge base title

This will be displayed as the archive title and in other relevant places.

**Default:** `Knowledge Base`

### First section level

Knowledge Base supports an unlimited hierarchy of sections. Set to 1 if using multi-product mode (with sections as the first level for each product). Set to 2 for traditional mode (top-level sections as product categories). This determines which section level is displayed in the grid layout. The default is 2, which was the behavior before version 3.0.

**Default:** `2`

### Show article count

If selected, the number of articles will be displayed in an orange circle next to the header. You can override the color by styling `wzkb_section_count`.

**Default:** Enabled

### Show excerpt

Select to include the post excerpt after the article link.

**Default:** Disabled

### Link section title

If selected, the title of each knowledge base section will link to its own page.

**Default:** Enabled

### Show empty sections

If selected, sections with no articles will also be displayed.

**Default:** Disabled

### Max articles per section

Enter the number of articles that should be displayed in each section when viewing the knowledge base. Use -1 to display all articles (no limit). Once this limit is reached, the footer displays a “more link” to view the category.

**Default:** `5`

**Range:** -1 to 500

### Show sidebar

Add the sidebar of your theme to the built-in templates for archives, sections, and search. This will not work with Block Themes. You will need to select an appropriate block template if you are using a block theme.

**Default:** Disabled

### Show related articles

Add related articles at the bottom of the knowledge base article. Only works when using the inbuilt template.

**Default:** Enabled

### Search

#### Enable live search

Show real-time search suggestions as the visitor types in the knowledge base search form. Results appear in a dropdown below the search input with keyboard navigation and screen reader support.

**Default:** Enabled

### Table of Contents

#### Show table of contents

Auto-generate a table of contents from headings in article content. The TOC is inserted above the article body and only displays when the article has enough headings (see Minimum headings for TOC below).

**Default:** Disabled

#### TOC heading depth

Maximum heading level to include in the table of contents. `2` includes only H2; `3` includes H2 and H3, and so on.

**Default:** `4` **Range:** 2–6

#### Minimum headings for TOC

Minimum number of headings required before the table of contents is displayed. Articles with fewer headings than this threshold will not show a TOC.

**Default:** `3` **Range:** 1–20

#### TOC title

Title displayed above the table of contents. Leave empty to hide the title.

**Default:** `Table of Contents`

#### Show floating table of contents *(Pro only)*

Display a sticky/floating TOC panel that follows the reader as they scroll through a KB article. Highlights the active section automatically.

**Default:** Disabled

#### Floating TOC position *(Pro only)*

Side of the viewport where the floating TOC panel is anchored.

**Options:** Right (default), Left

## Styles

### Product archive layout

Choose how products are displayed on the main Knowledge Base archive when Multi-Product Mode is enabled. “Sections list” shows each product with its sections listed below. The “Product cards grid” displays products as a grid of cards, allowing visitors to click through to a product page.

### Show featured image on archive pages *(Pro only)*

Display the term featured image in the header of product and section archive pages. Images are set per term via the Featured Image field on the product/section edit screen. See [Term Featured Images](https://webberzone.com/support/knowledgebase/term-featured-images/) for full details.

**Default:** Enabled

### Include inbuilt styles

Uncheck this to disable this plugin from adding the inbuilt styles. You will need to add your own CSS styles if you disable this option.

**Default:** Enabled

### Documentation layout *(Pro only)*

Display the entire knowledge base as a three-column documentation site: a categorized navigation sidebar, the article content, and an "On this page" outline. Works alongside your chosen style. See [Documentation Layout](https://webberzone.com/support/knowledgebase/documentation-layout/) for full details.

**Default:** Disabled

### Knowledge Base Style

Select a visual style for your knowledge base display. Premium styles are available in the Pro version.

**Default:** `Classic`

**Available Styles:**

- Classic
- Vibrant
- Modern *(Pro only)*
- Minimal *(Pro only)*
- Boxed *(Pro only)*
- Gradient *(Pro only)*
- Compact *(Pro only)*
- Magazine *(Pro only)*
- Professional *(Pro only)*

### Number of columns

Set the number of columns to display the knowledge base archives. *This will be overridden on smaller screens to optimize display.*

**Default:** `2`

**Range:** 1-5

### Custom CSS

Enter any custom valid CSS without any wrapping `<style>` tags.

## Pro

### Article Rating

<a href="https://webberzone.com/support/knowledgebase/knowledge-base-rating-system/" data-type="wz_knowledgebase" data-id="9272">Learn how the Article Rating system works in Knowledge Base.</a>

#### Enable Rating System

Allow visitors to rate the quality of knowledge base articles.

**Options:**

- Disabled (default)
- Useful / Not Useful
- 1-5 Star Rating

#### Vote Tracking Method

Choose how to prevent duplicate votes. Each method has different privacy implications. [Learn more about tracking methods and GDPR compliance](https://webberzone.com/support/knowledgebase/knowledge-base-rating-system/#tracking-methods--gdpr-compliance).

**Options:**

- **No Tracking** – Allows multiple votes (most privacy-friendly)
- **Cookie Only** – Requires consent (default)
- **IP Address Only** – Stores personal data
- **Cookie + IP Address** – Requires both
- **Logged-in Users Only** – Best for authenticated sites

#### Show Rating Statistics

Display the average rating and vote count below the rating buttons.

**Default:** Enabled

### Help Widget

A floating help widget that provides self-service support with search, suggested articles, and a contact form. Learn more about the [Knowledge Base Help Widget](https://webberzone.com/support/knowledgebase/knowledge-base-help-widget/).

#### Enable Help Widget

Display a floating help widget on your site for self-service support.

**Default:** Disabled

#### Display Location

Choose where the help widget appears on your site.

**Options:**

- Knowledge Base Only (default)
- Entire Site

#### Button Position

Choose where the help widget button appears on the screen.

**Options:**

- Bottom Right (default)
- Bottom Left

#### Button Style

Choose how the help widget button is displayed.

**Options:**

- Icon Only (default)
- Text Only
- Icon and Text

#### Button Text

Text to display on the help widget button (when text style is selected).

**Default:** `Help`

#### Help Widget Color

Primary color for the help widget button and interface elements.

**Default:** `#617DEC`

#### Beacon Hover Color

Hover color for buttons and interactive elements.

**Default:** `#4c63d2`

#### Help Widget Text Color

Text color for the help widget button and interface elements.

**Default:** `#ffffff`

#### Help Widget Hover Text Color

Text color for the help widget button on hover.

**Default:** `#ffffff`

#### Panel Background Color

Background color for the help widget panel.

**Default:** `#ffffff`

#### Panel Text Color

Default text color within the help widget panel.

**Default:** `#1a1a1a`

#### Link Hover Background

Background color when hovering over help widget links and list items.

**Default:** `#f3f4f6`

#### Greeting Message

Welcome message shown when the help widget opens.

**Default:** `Hi! How can we help you?`

#### Search Placeholder

Placeholder text for the search input field.

**Default:** `Search for answers...`

#### Enable Contact Form

Allow visitors to send messages through the help widget.

**Default:** Enabled

#### Contact Email

Email address where help widget contact form submissions will be sent.

**Default:** Site admin email

#### Show on Mobile

Display the help widget on mobile devices.

**Default:** Enabled

#### Enable button pulse

Enable a subtle pulsing animation on the help widget button to draw attention. Disable to keep the button static.

**Default:** Enabled
