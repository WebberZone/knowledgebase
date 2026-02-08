# Migration Guide: Knowledge Base v2.3 to v3.0

## Overview

Knowledge Base v3.0 introduces __Multi-Product Mode__, a powerful organizational feature that allows you to structure your knowledge base by products. This guide explains the migration process from the single-product structure (v2.3 and earlier) to the new multi-product architecture.

---

## What's New in v3.0?

### Multi-Product Mode

In v2.3 and earlier, your knowledge base had a flat structure:

- __Sections__ (categories) organized your articles
- All content was in a single knowledge base

In v3.0, you can now organize by products:

- __Products__ are top-level organizational units
- __Sections__ belong to specific products
- __Articles__ are assigned to products and sections
- Each product can have its own dedicated archive page

### Benefits

- ✅ __Better Organization__ - Separate documentation for different products
- ✅ __Improved Navigation__ - Users find content faster
- ✅ __Scalability__ - Manage multiple product lines easily
- ✅ __SEO Friendly__ - Dedicated URLs for each product
- ✅ __Optional__ - You can keep using single-product mode if preferred

---

## Styles & CSS Changes

- Knowledge Base v3 uses a style selector under __Settings → Knowledge Base → Styles__.
- The free styles are __Classic__ and __Vibrant__ (additional styles are available in Pro).
- For custom CSS overrides, prefer targeting stable classes such as `.wzkb-section-name` and `.wzkb-section-name-level-*` instead of hard-coding heading tags.
- On production sites (when `SCRIPT_DEBUG` is off), the plugin will load the `*.min.css` variant when available.

---

## Should You Migrate?

### Migrate to Multi-Product Mode If

- You have multiple products/services
- Your knowledge base covers different product lines
- You want separate documentation sections for each product
- Your top-level sections represent different products

### Stay in Single-Product Mode If

- You have a single product or service
- Your current structure works well
- You prefer a simpler, flat organization
- You don't need product-level separation

---

## Before You Start

### Prerequisites

1. __Backup Your Database__ ⚠️
   - Create a complete database backup
   - The migration process modifies your content structure
   - While the wizard includes a dry-run mode, backups are essential

2. __Review Your Current Structure__
   - List all your top-level sections
   - Understand which articles belong to which sections
   - Identify any sub-sections (child sections)

3. __Plan Your Products__
   - Each top-level section will become a product
   - Sub-sections will remain as sections under their parent product
   - Articles will be assigned to their corresponding products

### System Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Knowledge Base v3.0 or higher
- Administrator access

---

## Migration Process

### Step 1: Enable Multi-Product Mode

1. Navigate to __Knowledge Base → Settings__
2. Go to the __General__ tab
3. Find the __Multi-Product Mode__ setting
4. Check the box to enable it
5. Click __Save Changes__

### Step 2: Access the Migration Wizard

After enabling Multi-Product Mode, you'll see a notice in your WordPress admin:

> __New Multi-Products Mode available!__
> Organize your knowledge base by product with our new Multi-Products mode! You can migrate your existing content using the migration wizard.

Click the __Migration Wizard__ button to start.

Alternatively, navigate to: __Knowledge Base → Product Migration__

### Step 3: Understand What the Wizard Does

The Migration Wizard performs the following operations:

1. __Convert Sections to Products__
   - Each top-level section becomes a product
   - Product name, slug, and description are preserved
   - Example: "WordPress Plugin" section → "WordPress Plugin" product

2. __Map Articles to Products__
   - Articles are assigned to their corresponding products
   - Based on existing section relationships
   - No articles are lost or duplicated

3. __Handle Sub-Sections__
   - Sub-sections (child sections) are linked to parent products
   - Section hierarchy is maintained
   - Example: "Installation" sub-section under "WordPress Plugin" → linked to "WordPress Plugin" product

4. __Remove Old Top-Level Sections__
   - Original top-level sections are deleted after migration
   - Sub-sections are preserved and linked to products
   - This streamlines your structure

### Step 4: Run a Dry Run (Recommended)

Before making any changes:

1. __Check the "Dry run" checkbox__ (enabled by default)
2. Click __Start Migration__
3. Review the migration log
4. Verify the proposed changes
5. Check for any errors or warnings

__Dry Run Features:__

- ✅ Simulates the entire migration process
- ✅ Shows exactly what will happen
- ✅ Creates temporary products for preview
- ✅ Makes NO permanent changes
- ✅ Deletes simulated products after completion

### Step 5: Confirm and Migrate

Once you're satisfied with the dry run results:

1. __Uncheck the "Dry run" checkbox__
2. __Check the backup confirmation checkbox__:
   > "I confirm I have backed up my database and understand this migration cannot be undone."
3. Click __Start Migration__
4. Wait for the process to complete
5. Review the migration log

__Migration Progress:__

- Real-time progress bar
- Detailed migration log
- Error reporting (if any)
- Copy log button for record-keeping

### Step 6: Verify the Results

After migration:

1. __Check Your Products__
   - Navigate to __Knowledge Base → Products__
   - Verify all products were created correctly
   - Review product names and slugs

2. __Check Your Sections__
   - Navigate to __Knowledge Base → Sections__
   - Verify sub-sections are linked to correct products
   - Check the "Product" column

3. __Check Your Articles__
   - Navigate to __Knowledge Base → All Articles__
   - Verify articles are assigned to correct products
   - Review the "Product" column

4. __Test Frontend Display__
   - Visit your knowledge base archive page
   - Check product archive pages
   - Verify article display and navigation

---

## Migration Details

### What Gets Migrated?

| Item | Action | Result |
| ------ | ------ | ------ |
| Top-level sections | Converted to products | New product taxonomy terms |
| Sub-sections | Linked to parent products | Remain as sections with product association |
| Articles | Assigned to products | Product taxonomy term added |
| Section names | Preserved | Copied to product names |
| Section slugs | Preserved | Copied to product slugs |
| Section descriptions | Preserved | Copied to product descriptions |

### What Doesn't Change?

- ✅ Article content
- ✅ Article metadata
- ✅ Article URLs (permalinks)
- ✅ Section hierarchy (sub-sections)
- ✅ Article-section relationships
- ✅ Custom fields
- ✅ Featured images

### Database Changes

The migration modifies:

- `wp_terms` - Creates new product terms
- `wp_term_taxonomy` - Adds product taxonomy entries
- `wp_term_relationships` - Links articles to products
- `wp_termmeta` - Links sections to products (`product_id` meta)

---

## Batch Processing

The migration wizard uses intelligent batch processing to handle large knowledge bases:

### Performance Features

- __Batch Size Limits__
  - Default: 3 sections per batch
  - Default: 50 articles per batch
  - Prevents server timeouts
  - Customizable via filters

- __Progress Tracking__
  - Real-time progress bar
  - Detailed step-by-step logging
  - State persistence between batches
  - Resume capability if interrupted

- __Memory Management__
  - Transient-based state storage
  - Efficient query optimization
  - Prevents memory exhaustion

### Customization Filters

```php
// Increase sections per batch (for powerful servers)
add_filter( 'wzkb_migration_max_sections_per_batch', function( $max ) {
    return 5; // Default: 3
} );

// Increase articles per batch
add_filter( 'wzkb_migration_max_articles_per_batch', function( $max ) {
    return 100; // Default: 50
} );
```

---

## Migration Steps Explained

### Step 0: Initialization (Progress: 0-20%)

__What Happens:__

- Clears previous migration data
- Scans all top-level sections
- Counts articles in each section
- Calculates total articles and sections
- Prepares state for batch processing

__Log Output:__

```text
Initializing migration...
Dry run mode: No changes will be made.
```

### Step 1: Create Products (Progress: 20%)

__What Happens:__

- Converts each top-level section to a product
- Preserves name, slug, and description
- Checks for existing products (prevents duplicates)
- Creates section-to-product mapping

__Log Output:__

```text
Creating products from top-level sections...
Created product "WordPress Plugin" (ID: 123) for section "WordPress Plugin" (ID: 45).
```

### Step 2: Map Sections & Articles (Progress: 20-80%)

__What Happens:__

- Processes sections in batches
- Links sub-sections to parent products (via `product_id` term meta)
- Assigns articles to products
- Prevents duplicate assignments
- Tracks progress across batches

__Log Output:__

```text
Mapping descendant sections and articles to products...
Processing section "Installation" (ID: 46)
Linked section "Installation" (ID: 46) to product ID: 123.
Assigned articles to product "WordPress Plugin" (ID: 123): Getting Started (ID: 789), Configuration (ID: 790)
```

### Step 3: Cleanup (Progress: 80-100%)

__What Happens:__

- Deletes original top-level sections
- Removes temporary dry-run products (if dry run)
- Marks migration as complete
- Clears transient data
- Generates final summary

__Log Output:__

```text
Deleting old top-level sections...
Deleting top-level section ID: 45.
Mapped 12 descendant sections and 3 top-level sections (total 15 sections), processed 87 articles, deleted 3 top-level sections.
Migration complete!
```

---

## Troubleshooting

### Migration Wizard Not Appearing

__Problem:__ Can't find the Migration Wizard link.

__Solution:__

1. Ensure Multi-Product Mode is NOT yet enabled in settings
2. Check you're on a Knowledge Base admin screen
3. Verify you have administrator permissions
4. If migration was already completed, the wizard is hidden

### Migration Fails or Times Out

__Problem:__ Migration stops or shows errors.

__Solution:__

1. Check server error logs
2. Increase PHP `max_execution_time` (recommended: 300 seconds)
3. Increase PHP `memory_limit` (recommended: 256M)
4. Reduce batch sizes using filters (see Customization Filters above)
5. Contact your hosting provider for server resource limits

### Articles Not Assigned to Products

__Problem:__ Some articles don't have products assigned.

__Solution:__

1. Check if articles were in sections before migration
2. Verify articles are assigned to sections (not just floating)
3. Re-run migration if needed (see "Re-running Migration" below)
4. Manually assign products via article edit screen

### Sections Not Linked to Products

__Problem:__ Sub-sections don't show product association.

__Solution:__

1. Check the "Product" column in Sections list
2. Verify `product_id` term meta exists
3. Edit section and select product manually if needed

### Duplicate Products Created

__Problem:__ Multiple products with same name.

__Solution:__

1. The wizard checks for existing products by slug
2. If duplicates exist, manually merge them
3. Delete duplicate products
4. Reassign sections and articles to correct product

---

## Re-running Migration

### Can I Run Migration Again?

__No.__ Once migration is complete, the wizard is permanently disabled. This prevents accidental re-migration and data corruption.

### What If I Need to Undo?

#### Option 1: Restore from Backup

- Restore your database backup from before migration
- This is the safest and most reliable method

#### Option 2: Manual Reversion

1. Delete all products: __Knowledge Base → Products__ → Bulk delete
2. Unlink sections from products: Remove `product_id` term meta
3. Remove product assignments from articles
4. Recreate top-level sections manually
5. Disable Multi-Product Mode in settings

__Note:__ Manual reversion is complex and error-prone. Database restoration is strongly recommended.

---

## Post Migration

### Configure Product Settings

1. __Review Product Permalinks__
   - Navigate to __Knowledge Base → Settings → General__
   - Check product slug settings
   - Update if needed

2. __Customize Product Archives__
   - Each product has its own archive page
   - URL format: `yoursite.com/knowledgebase/product/product-slug/`
   - Customize via theme templates if needed

3. __Update Navigation Menus__
   - Add product links to your navigation
   - Use __Appearance → Menus__
   - Products appear under "Knowledge Base Products"

### Create New Products

After migration, you can create additional products:

1. Navigate to __Knowledge Base → Products__
2. Click __Add New Product__
3. Enter name, slug, and description
4. Click __Add New Product__

### Assign Sections to Products

When creating or editing sections:

1. Find the __Product__ dropdown
2. Select the parent product
3. Save the section

### Assign Articles to Products

When creating or editing articles:

1. Find the __Products__ meta box (right sidebar)
2. Select one or more products
3. Publish or update the article

---

## Best Practices

### Before Migration

- ✅ Create a complete database backup
- ✅ Run a dry run first
- ✅ Review the migration log carefully
- ✅ Test on a staging site if possible
- ✅ Document your current structure

### During Migration

- ✅ Don't close the browser window
- ✅ Don't navigate away from the page
- ✅ Wait for the process to complete
- ✅ Monitor the progress bar and log
- ✅ Copy the log for your records

### After Migration

- ✅ Verify all products were created
- ✅ Check section-product associations
- ✅ Test article display on frontend
- ✅ Update navigation menus
- ✅ Clear site caches (if using caching plugins)
- ✅ Test search functionality

---

## FAQ

### Q: Is migration reversible?

__A:__ Not automatically. You must restore from a database backup to revert. This is why backups are critical.

### Q: Will my article URLs change?

__A:__ No. Article permalinks remain unchanged. Only the organizational structure changes.

### Q: Can I skip migration and enable Multi-Product Mode?

__A:__ Yes. You can enable Multi-Product Mode without migrating. You'll start with an empty product taxonomy and can manually create products and assign content.

### Q: What happens to articles not in any section?

__A:__ Articles without sections won't be assigned to any product. You'll need to manually assign them after migration.

### Q: Can I have articles in multiple products?

__A:__ Yes. Articles can be assigned to multiple products. The migration assigns each article to one product based on its section, but you can add more products manually afterward.

### Q: How long does migration take?

__A:__ Depends on your knowledge base size:

- Small (< 100 articles): 1-2 minutes
- Medium (100-500 articles): 2-5 minutes
- Large (500+ articles): 5-15 minutes

### Q: Will migration affect my site's performance?

__A:__ The migration runs in the admin area and uses batch processing to minimize impact. Frontend performance is not affected during migration.

### Q: Can I customize the migration process?

__A:__ Yes. Use the provided filters to adjust batch sizes and other parameters (see Customization Filters section).

### Q: What if I have thousands of articles?

__A:__ The batch processing system handles large knowledge bases efficiently. You may want to:

- Increase server resources temporarily
- Adjust batch sizes via filters
- Run migration during low-traffic periods

---

## Support

### Getting Help

- __Documentation:__ [webberzone.com/plugins/knowledgebase/](https://webberzone.com/plugins/knowledgebase/)
- __Support Forum:__ [webberzone.com/support/product/knowledgebase/](https://webberzone.com/support/product/knowledgebase/)
- __Contact:__ Use the support forum for migration assistance

### Reporting Issues

When reporting migration issues, include:

1. WordPress version
2. PHP version
3. Knowledge Base version
4. Number of articles and sections
5. Complete migration log (use Copy Log button)
6. Any error messages from browser console
7. Server error logs (if available)

---

## Version History

### v3.0.0

- Initial release of Multi-Product Mode
- Migration Wizard introduced
- Batch processing system
- Dry-run capability
- Progress tracking and logging

---

## Technical Details

### Database Schema

__Products Taxonomy:__

- Taxonomy: `wzkb_product`
- Hierarchical: No
- Public: Yes
- Rewrite: Yes

__Section-Product Relationship:__

- Stored in: `wp_termmeta`
- Meta key: `product_id`
- Meta value: Product term ID

__Article-Product Relationship:__

- Stored in: `wp_term_relationships`
- Links articles to product terms

### Transients Used During Migration

- `wzkb_migration_log` - Migration log entries (24 hours)
- `wzkb_migration_assigned_articles` - Tracks assigned articles (24 hours)
- `wzkb_migration_article_counts` - Article counts per section (24 hours)

### Options

- `wzkb_product_migration_complete` - Timestamp of completion
- `wzkb_product_notice_dismissed` - User meta for notice dismissal (90 days)

---

## Conclusion

The migration from single-product to multi-product mode is a significant structural change that offers powerful organizational benefits. By following this guide and using the dry-run feature, you can migrate your knowledge base safely and efficiently.

__Remember:__ Always backup your database before starting the migration!

For additional assistance, visit our support forum or consult the plugin documentation.
