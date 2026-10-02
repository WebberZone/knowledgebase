---
slug: migration-guide-knowledge-base-v2-3-to-v3-0
title: "Migration Guide: Knowledge Base v2.3 to v3.0"
products: [knowledgebase]
sections: ["02-kb-advanced"]
tags: [knowledgebase]
status: publish
order: 0
toc: true
---

[toc]

[Knowledge Base](https://webberzone.com/plugins/knowledgebase/) v3.0 added **Multi-Product Mode**, which organizes articles and sections by product. The Product Migration Wizard converts a v2.3-style knowledge base, where top-level sections stood in for products, into the multi-product structure.

## Should you migrate?

Migrate if your top-level sections already represent separate products, services or product lines. Stay in single-product mode if you document one product and your current structure works. Multi-Product Mode is optional, and you can also enable it without migrating and create products by hand.

In Multi-Product Mode:

- **Products** are the top-level units, each with its own archive page.
- **Sections** belong to a product.
- **Articles** are assigned to products and sections.

## What the wizard does

| Item | Result |
| --- | --- |
| Top-level sections | Become products with the same name, slug and description. An existing product with the same slug is reused. |
| Sub-sections | Stay as sections and are linked to their parent's product. |
| Articles | Assigned to the product of their section. |
| Original top-level sections | Deleted once their content is mapped. |

Article content, metadata, permalinks, custom fields, featured images and article–section relationships don't change. Articles that aren't in any section are not assigned a product.

The migration cannot be undone except by restoring a backup, and the wizard is removed once a migration completes.

## Before you start

1. Back up your database.
2. Test on a staging site if you can.
3. List your top-level sections. Each one will become a product.

## Run the migration

1. Go to **Knowledge Base → Settings → General**, turn on **Enable Multi-Product Mode**, and save.
2. Go to **Knowledge Base → Tools** and click **Run Migration Wizard** in the **Product Migration** box.
3. Keep **Dry run (show summary only, no changes)** checked, check the backup confirmation, and click **Start Migration**. The dry run logs what would happen without changing anything.
4. Review the log. When you're satisfied, uncheck **Dry run** and click **Start Migration** again.
5. Keep the page open until the progress bar reaches 100%. Use **Copy Log** to keep a record.

The wizard works in batches of 3 top-level sections and 50 articles, saving its progress between batches so large knowledge bases don't time out.

## Check the results

- **Knowledge Base → Products** lists the new products.
- **Knowledge Base → Sections** shows each section's product in the **Product** column.
- **Knowledge Base → All Articles** shows each article's products.
- Visit your knowledge base and a product archive page, then clear any page cache.

To add products later, use **Knowledge Base → Products**. Assign a section to a product with the **Product** dropdown when editing the section, and an article with the **Products** box on the edit screen. An article can belong to more than one product.

## Troubleshooting

- **The wizard isn't on the Tools page.** A migration has already completed, or you aren't an administrator.
- **The migration stops or times out.** Check your server error log, raise PHP `max_execution_time` (300 seconds) and `memory_limit` (256M), or lower the batch sizes with the filters below.
- **Some articles have no product.** They weren't in a section before the migration. Assign products to them on the edit screen.
- **A section isn't linked to a product.** Edit the section and choose its product.
- **Duplicate products.** Merge or delete the duplicates, then reassign their sections and articles.

To undo a migration, restore your database backup. Reverting by hand means deleting the products, unlinking sections and articles, recreating the top-level sections and disabling Multi-Product Mode, which is error-prone.

When reporting a problem, include your WordPress, PHP and Knowledge Base versions, the number of articles and sections, and the copied migration log.

## Developer notes

Adjust the batch sizes with [`wzkb_migration_max_sections_per_batch`](https://webberzone.dev/knowledgebase/hooks/wzkb_migration_max_sections_per_batch/) (default `3`) and [`wzkb_migration_max_articles_per_batch`](https://webberzone.dev/knowledgebase/hooks/wzkb_migration_max_articles_per_batch/) (default `50`):

```php
add_filter( 'wzkb_migration_max_sections_per_batch', fn() => 5 );
add_filter( 'wzkb_migration_max_articles_per_batch', fn() => 100 );
```

Products use the `wzkb_product` taxonomy. A section's product is stored in the `product_id` term meta. The `wzkb_product_migration_complete` option records when the migration finished.
