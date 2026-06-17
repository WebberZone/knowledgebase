---
slug: setup-wizard-and-sample-content
title: "Setup Wizard and Sample Content"
products: [knowledgebase]
sections: [01-kb-getting-started]
tags: [knowledgebase, setup, wizard, sample-content]
status: publish
order: 0
---

The setup wizard walks you through configuring Knowledge Base in a few guided steps — from choosing your structure to importing sample content so you can see a working knowledge base immediately.

## Launching the wizard

The wizard opens automatically the first time you activate the plugin. You can also run it again at any time by visiting **admin.php?page=wzkb_wizard** in your WordPress admin (replace the first part with your site's admin URL).

You can skip the wizard at any point — the skip link takes you straight to the Knowledge Base settings page, and every option in the wizard can also be changed later under **Knowledge Base → Settings**.

## The wizard steps

### 1. Knowledge Base Setup

Choose your overall structure:

- **Multi-product mode** — enable this if you document more than one product. Each product gets its own archive and its own set of sections.
- **Category level** — controls which level of your section hierarchy is treated as the top level for display.

### 2. Permalinks

Set the URL slugs for the knowledge base archive, products, sections, and tags. If you see 404 errors after changing these, flush your permalinks by visiting Settings → Permalinks and clicking Save.

### 3. Display Options

Configure how the knowledge base looks: the title, article counts, excerpts, clickable sections, number of articles per section, live search, table of contents, styles, layout, and columns.

### 4. Pro Features

Set up the premium features: the floating table of contents, the article rating system, and the help widget (display location, position, color, and greeting).

### 5. Content Structure

Create the products and sections that will organize your articles. You can add more products, sections, and subsections later from the taxonomy screens under the Knowledge Base menu.

### 6. Sample Content

Import demo sections and articles so you can explore a populated knowledge base straight away. See below.

## Sample content

The Sample Content step creates a small set of demo content that matches the structure you chose in the first step:

- **Single-product mode** — two sections (*Getting Started* and *User Guide*) with four sample articles.
- **Multi-product mode** — two products (*Nova* and *Nexus*), four sections, and eight sample articles spread across them.

The sample articles are real, published KB articles with headings, lists, and code blocks, so you can immediately see how your theme and settings render a knowledge base.

Importing sample content is safe to repeat — articles that already exist are skipped, so you will not end up with duplicates.

## Deleting sample content

When you no longer need the demo content, go to **Knowledge Base → Tools**. If sample content exists on the site, a **Sample Content** card appears with a delete button that removes all sample articles, sections, and products created by the wizard.

Deletion only touches content the wizard created — your own articles and sections are never affected. The delete cannot be undone, but you can re-import the sample content from the wizard at any time.

## See also

- [Knowledge Base settings](https://webberzone.com/support/knowledgebase/knowledge-base-settings/) — the full settings reference for everything the wizard configures.
