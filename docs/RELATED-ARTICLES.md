---
description: Knowledge Base Related Articles
---

# Knowledge Base Related Articles

The Knowledge Base plugin ships with a first-party Related Articles engine that can surface contextual links below each article, power the Related Articles block, and feed the Help Widget. This document now starts with a non-technical explainer before diving into the developer reference.

---

## Part A – User-Friendly Overview

### What Related Articles Do

- **Keeps readers engaged:** Shows a short list of helpful articles underneath the one they just finished.
- **Works everywhere:** Appears automatically on classic knowledge base templates, inside the Related Articles block, and inside the Help Widget.
- **Always relevant:** Picks other posts that share the same sections or tags as the article the visitor is reading.

### Turning the Feature On or Off

1. Go to **Settings → Knowledge Base → Output**.
2. Toggle **Show related articles** on to display the section, or off to hide it.
3. Save your settings—no extra code is required.

### Choosing Where Related Articles Show Up

- **Automatic placement:** When enabled, related articles appear below knowledge base articles that use the plugin’s templates.
- **Shortcode:** Ask your developer (or add to a custom template) to place `[wzkb_related_articles]` wherever you want the list to appear.
- **Gutenberg Block:** In the block editor, add the **Knowledge Base → Related Articles** block to any template part or single post layout.
- **Help Widget:** If you use the Help Widget, visitors will see suggested articles pulled from the same related articles engine.

### Customizing the Look Without Code

- **Block controls:** When you insert the Related Articles block you can change the title, pick the heading level (H2–H6), choose how many posts to show, and toggle thumbnails, excerpts, and publish dates.
- **Shortcode helper:** Inside classic templates, the shortcode respects the same settings that you configure on the settings page. Your developer can adjust the shortcode parameters if you need something unique.

### Troubleshooting for Non‑Developers

| Issue | Quick Fix |
| --- | --- |
| No related articles appear | Make sure the article shares at least one section or tag with another article. |
| Wrong articles show up | Confirm the articles are in the right sections/tags. Related posts currently do not filter by product out of the box. |
| Want a different layout | Use the block editor and place the Related Articles block where you prefer, then adjust its toggles. |

Ready for more detail? Continue to the developer reference below.

---

## Part B – Developer Reference

---

### 1. Enabling & Display Locations

| Location | How it renders | Notes |
| --- | --- | --- |
| **Default single template** | Automatic, controlled by **Settings → Knowledge Base → Output → Show related articles** | Applies to classic templates provided by the plugin. |
| **Shortcode** | `wzkb_related_articles()` helper | Useful inside custom templates or theme files. |
| **Gutenberg Block** | `Knowledge Base → Related Articles` block | Works in both classic and block themes (requires v3.0+). |
| **Help Widget** | Reuses the Related Articles query for contextual suggestions | Documented in `docs/HELP-WIDGET.md`. |

Disable the feature globally by unchecking **Show related articles** in the settings page, or omit the shortcode/block on specific templates.

---

### 2. Template Function & Parameters

```php
wzkb_related_articles( array $args = array() );
```

| Argument | Type | Default | Description |
| --- | --- | --- | --- |
| `numberposts` | `int` | `5` | Maximum related posts to display. |
| `post` | `int\|WP_Post` | Current post | Force a different origin article. |
| `exclude` | `array\|string` | `array()` | IDs to skip (array or CSV). |
| `show_thumb` | `bool` | `true` | Display thumbnails using `wzkb_get_the_post_thumbnail()`. |
| `show_excerpt` | `bool` | `false` | Show excerpts (falls back to first 55 words of content). |
| `show_date` | `bool` | `true` | Append the publish date (respects site date format). |
| `title` | `string` | `<h3>Related Articles</h3>` | Section heading; accepts HTML for legacy compatibility. |
| `heading_tag` | `string` | `''` | When defined (`h2`–`h6`), `title` is treated as plain text and wrapped automatically. |
| `thumb_size` | `string` | `thumbnail` | Registered image size passed to the Media Handler. |

Set `echo` to `false` when using the helper inside PHP logic where you need to capture the HTML instead of printing it directly.

---

### 3. Gutenberg Block Quick Start

1. Insert the **Knowledge Base → Related Articles** block.
2. Configure:
   - **Section Title** – custom text for the heading.
   - **Heading Level** – semantic `h2`‑`h6`.
   - **Show thumbnail / excerpt / date** toggles.
   - **Maximum items** – between 1 and 20.
3. The block renders only on single knowledge base articles on the front end. In the editor, it displays a notice when no related content is available.

---

### 4. How Relevance Is Calculated

The engine builds a `WP_Query` scoped to the `wz_knowledgebase` post type, excluding the current article. When the origin article has sections or tags:

1. A taxonomy query matches other articles with any shared `wzkb_category` or `wzkb_tag`.
2. Results are ranked **after** the SQL query using `sort_query_by_relevance()`:
   - Each matching category contributes `category_weight` points (default **2**).
   - Each matching tag contributes `tag_weight` points (default **1**).
   - A **recency boost** (0–1) favors articles published within the last year.
   - Tie‑breakers: category matches → tag matches → publish timestamp → original order.
3. If no taxonomy context exists (e.g., uncategorized article), the query falls back to the latest knowledge base posts. Use the `wzkb_related_articles_fallback_args` filter to override this behaviour.

All scoring operations occur in PHP, so the SQL statement you see via Query Monitor will still show `ORDER BY post_date DESC`. The post array is reordered after the query returns.

---

### 5. Key Filters & Actions

| Hook | Description |
| --- | --- |
| `wzkb_related_articles_query_args` | Last chance to adjust the `WP_Query` args (e.g., add meta queries). |
| `wzkb_related_articles_fallback_args` | Modify the arguments used when no categories/tags are available. |
| `wzkb_related_articles_cache_ttl` | Control the object cache lifetime (defaults to `HOUR_IN_SECONDS`). |
| `wzkb_related_category_weight` / `wzkb_related_tag_weight` | Override default weights (ints). |
| `wzkb_related_recency_boost` | Customize the recency multiplier (0–1). |
| `wzkb_related_post_score` | Final opportunity to tweak each post’s score. |
| `wzkb_related_block_output` | Filter the Gutenberg block markup. |

Example: increase category weight and reduce recency influence.

```php
add_filter( 'wzkb_related_category_weight', fn() => 3 );
add_filter( 'wzkb_related_recency_boost', function( $boost, $post ) {
 return 0.25 * $boost;
}, 10, 2 );
```

---

### 6. Performance & Caching

- Related queries are cached in the `wzkb_related_articles` object cache group using the query args plus the `posts` cache `last_changed` value.
- Flushing the posts cache (`wp cache flush`, editing content, or publishing new articles) automatically invalidates the related cache.
- For deterministic debugging, temporarily disable the cache by filtering `wzkb_related_articles_cache_ttl` to `0`.

---

### 7. Advanced Usage & Troubleshooting

1. **Custom templates** – If you override the single template, call `wzkb_related_articles()` manually where you want the block to appear.
2. **Block themes** – The Gutenberg block is the recommended approach for block theme templates because it handles layout, settings, and tie‑ins with the Related class.
3. **No results** – Ensure the origin article shares sections or tags with other posts. Otherwise, configure `wzkb_related_articles_fallback_args` to show the latest articles or preferred taxonomy.
4. **Multi‑product sites** – Use `wzkb_related_articles_query_args` to inject a `wzkb_product` tax query so each product shows its own ecosystem of articles. (Native support is on the roadmap.)
5. **Debugging** – Temporarily hook into `wzkb_related_post_score` and log `$score` to inspect how each article ranked. Remember to remove logging after troubleshooting.

---

### 8. Roadmap & Pro Enhancements

- **Product‑aware recommendations** – Planned improvement to automatically respect the active product context.
- **Engagement weighting (Pro)** – Upcoming feature to blend Knowledge Base ratings and analytics signals into the related articles score.
- **Analytics events** – Future enhancements will expose frontend events so you can track impressions and clicks.

Contributions and suggestions are welcome via GitHub issues. Use the filters and hooks above to tailor Related Articles today while we continue expanding the feature set.
