---
slug: importing-from-other-knowledge-base-plugins
title: "Importing from BasePress, BetterDocs and Echo Knowledge Base"
products: [knowledgebase]
sections: [01-kb-getting-started]
tags: [knowledgebase, import, migration]
status: publish
order: 0
---

Switching to Knowledge Base from another documentation plugin? The built-in plugin importer copies your articles, categories, and tags from BasePress, BetterDocs, or Echo Knowledge Base into Knowledge Base — without deleting anything from the original plugin.

## Opening the importer

Go to **Knowledge Base → Tools** in your WordPress admin and click **Import** in the Plugin Importer card. You'll see a list of supported source plugins with a detection status next to each:

- **Content found** — the importer detected articles from that plugin in your database. Click **Import** to continue.
- **Not detected** — no content from that plugin was found. The source plugin must have been installed and have content for the importer to work.

The source plugin does not need to be active — the importer reads its content directly from the database.

## What gets imported

Before anything runs, the importer shows a preview of exactly what will be copied:

- The number of articles and the number of categories and tags.
- A detailed mapping of how the source plugin's taxonomies translate to Knowledge Base products, sections, and tags. For example, BasePress knowledge bases become Knowledge Base **products** and their child categories become **sections**.
- Article meta that will be carried over.
- Any settings the importer will adjust, such as enabling multi-product mode when the source plugin has multiple knowledge bases.
- The detected base URL slug of the source plugin (see below).

## Matching your existing URLs

If the importer detects the source plugin's base URL slug, it offers a checkbox to:

- Set the Knowledge Base URL slug to match the source plugin's URLs, and
- Update the matching entry page with the `[knowledgebase]` shortcode.

Check this box if you want your existing knowledge base URLs to keep working after the migration. This changes your Knowledge Base URLs, so skip it if you have already set up Knowledge Base at a different address.

## Running the import

1. Check the confirmation checkbox stating you have backed up your database. The **Start Import** button stays disabled until you do.
2. Click **Start Import**.
3. The importer first copies categories and tags, then imports articles in batches of 25 with a progress bar and a running log.

When the import finishes, a summary shows how many articles were imported and skipped, along with quick links to flush permalinks, review the imported articles, products, and sections, adjust settings, or visit your knowledge base.

## Before you import

- **Back up your database.** The import inserts new posts and terms and cannot be automatically undone.
- **Your original content is not deleted.** Verify the imported articles before deactivating or removing the source plugin.
- **Re-running is safe.** The importer remembers which source articles it has already imported and skips them, so running it a second time will not create duplicates.

## After the import

- **Flush permalinks** (use the button on the success screen, or go to Settings → Permalinks and click Save) so the new knowledge base URLs work.
- Review your articles under **Knowledge Base** and your sections and products under their respective taxonomy screens.
- Check the front end of your knowledge base to confirm everything displays as expected.

## See also

- [Exporting and importing articles](https://webberzone.com/support/knowledgebase/exporting-importing-articles/) — move content between Knowledge Base installs.
- [Knowledge Base permalinks tutorial](https://webberzone.com/support/knowledgebase/knowledge-base-permalinks-tutorial/) — understand how Knowledge Base URLs are built.
