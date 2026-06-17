---
slug: knowledge-base-rest-api
title: "Knowledge Base REST API"
products: [knowledgebase]
sections: [03-kb-developer-docs]
tags: [knowledgebase,rest-api]
status: publish
order: 0
---

The <a href="https://webberzone.com/plugins/knowledgebase/" data-type="page" data-id="34">WebberZone Knowledge Base</a> plugin exposes selected functionality via the WordPress REST API.

All endpoints live under the namespace `wzkb/v1` and therefore share the base path:

```text
https://example.com/wp-json/wzkb/v1/
```

> Replace `https://example.com` with your site’s domain.

## Authentication & Permissions

- The API piggybacks on WordPress capabilities. Standard WordPress authentication mechanisms (cookies for logged-in sessions, app passwords, basic auth plugins, etc.) all work.
- All REST responses follow standard WordPress structures with `code`, `message`, and `data`.
- Cache lifetime is 300 seconds. Cache busts automatically on KB content or taxonomy changes.
- When used in a headless context, ensure Gutenberg meta `_wzkb_product_ids` and `_wzkb_section_ids` are synced.
- Permissions can be customized per route via the `wzkb_rest_route_permission` filter.
- By default, all read-only routes are public. Use the permission filter below to restrict access.

## Permission overrides

Developers can override route-level visibility without replacing the controller. The filter receives the route slug and the default public flag (`true` for read endpoints).

```php
add_filter(
    'wzkb_rest_route_permission',
    static function ( $permission, $route_slug, $is_public ) {
        if ( 'search' === $route_slug ) {
            // Require logged-in users with the custom capability.
            return 'read_private_kb';
        }

        // Fall back to default behaviour.
        return $permission;
    },
    10,
    3
);
```

Return values:

1. `bool` – grant/deny outright.
2. `string` – capability checked via `current_user_can()`.
3. `callable` – custom logic returning boolean.

## Multi-product prerequisite

Several endpoints (starting with Sections) are only available when **multi-product mode** is enabled inside Knowledge Base Pro (`Knowledge Base ▸ Settings ▸ General ▸ Enable multi-product`). If disabled, the endpoint will return a `wzkb_rest_sections_disabled` error.

## Endpoints

- [GET `/wzkb/v1/sections`](#get-wzkbv1sections)
- [GET `/wzkb/v1/knowledgebase`](#get-wzkbv1knowledgebase)
- [GET `/wzkb/v1/knowledgebase/{id}`](#get-wzkbv1knowledgebaseid)
- [GET `/wzkb/v1/products`](#get-wzkbv1products)
- [GET `/wzkb/v1/search`](#get-wzkbv1search)
- [GET `/wzkb/v1/related`](#get-wzkbv1related)

### GET `/wzkb/v1/sections`

Fetches Knowledge Base sections (`wzkb_category` terms) filtered by one or more product IDs, preserving parent/child relationships for hierarchical rendering.

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Parameter</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>products</code></td>
<td>string</td>
<td>yes</td>
<td>Comma-separated list of <code>wzkb_product</code> term IDs. Example: <code>173,178</code></td>
</tr>
</tbody>
</table>
</figure>

#### Request example

```text
GET https://example.com/wp-json/wzkb/v1/sections?products=173,178
```

#### Successful response

```text
[
  {
    "id": 201,
    "name": "Advanced",
    "parent": 0,
    "product": 173
  },
  {
    "id": 202,
    "name": "More advanced",
    "parent": 201,
    "product": 173
  },
  {
    "id": 310,
    "name": "Getting Started",
    "parent": 0,
    "product": 178
  }
]
```

- `id`: Section term ID.
- `name`: Section name.
- `parent`: Parent term ID (0 when top-level).
- `product`: Associated product ID pulled from `product_id` term meta (0 when unassigned).

#### Error responses

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>HTTP Code</th>
<th>Error code</th>
<th>Reason</th>
</tr>
</thead>
<tbody>
<tr>
<td>400</td>
<td><code>wzkb_rest_sections_disabled</code></td>
<td>Multi-product mode is disabled.</td>
</tr>
<tr>
<td>401 / 403</td>
<td><code>rest_forbidden</code></td>
<td>User lacks the capability granted via the <code>wzkb_rest_route_permission</code> filter.</td>
</tr>
</tbody>
</table>
</figure>

### GET `/wzkb/v1/knowledgebase`

List published Knowledge Base posts with optional filtering.

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Parameter</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>per_page</code></td>
<td>int</td>
<td>no</td>
<td>Results per page (1–50). Default: 10.</td>
</tr>
<tr>
<td><code>page</code></td>
<td>int</td>
<td>no</td>
<td>Page number. Default: 1.</td>
</tr>
<tr>
<td><code>search</code></td>
<td>string</td>
<td>no</td>
<td>Free-text search term.</td>
</tr>
<tr>
<td><code>product</code></td>
<td>int</td>
<td>no</td>
<td>Filter by <code>wzkb_product</code> term ID.</td>
</tr>
<tr>
<td><code>section</code></td>
<td>int</td>
<td>no</td>
<td>Filter by <code>wzkb_category</code> term ID.</td>
</tr>
</tbody>
</table>
</figure>

#### Response

```text
[
  {
    "id": 123,
    "title": "How to configure caching",
    "slug": "configure-caching",
    "excerpt": "Configure caching in three easy steps…",
    "permalink": "https://example.com/kb/configure-caching/",
    "products": [{ "id": 5, "name": "Pro", "slug": "pro" }],
    "sections": [{ "id": 18, "name": "Setup", "slug": "setup" }],
    "date": "2025-10-21T09:30:00",
    "modified": "2025-10-23T14:05:00"
  }
]
```

Headers: `X-WP-Total` (total posts) and `X-WP-TotalPages`.

### GET `/wzkb/v1/knowledgebase/{id}`

Return a single Knowledge Base post including full content when published.

### GET `/wzkb/v1/products`

List all Knowledge Base products.

```text
[
  {
    "id": 5,
    "name": "Contextual Related Posts",
    "slug": "contextual-related-posts",
    "description": "CRP-specific documentation.",
    "count": 34
  }
]
```

### GET `/wzkb/v1/search`

Lightweight search endpoint wrapping Knowledge Base queries.

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Parameter</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>query</code></td>
<td>string</td>
<td>yes</td>
<td>Search keywords (min 2 characters).</td>
</tr>
<tr>
<td><code>product</code></td>
<td>int</td>
<td>no</td>
<td>Filter by product term ID.</td>
</tr>
<tr>
<td><code>section</code></td>
<td>int</td>
<td>no</td>
<td>Filter by section term ID.</td>
</tr>
<tr>
<td><code>limit</code></td>
<td>int</td>
<td>no</td>
<td>Results to return (1–50). Default: 10.</td>
</tr>
</tbody>
</table>
</figure>

### GET `/wzkb/v1/related`

Fetch related articles for a given Knowledge Base post.

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Parameter</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>post_id</code></td>
<td>int</td>
<td>yes</td>
<td>Base Knowledge Base post ID.</td>
</tr>
<tr>
<td><code>limit</code></td>
<td>int</td>
<td>no</td>
<td>Number of related items (1–20).</td>
</tr>
</tbody>
</table>
</figure>

## Using the API in Gutenberg

The custom Gutenberg panel (`includes/admin/js/editor-sections-panel.js`) consumes `/sections` to show product-aware section lists. Other clients (mobile apps, headless front ends, internal tools) can consume the same endpoint, provided they authenticate and the site is running in multi-product mode.

## Support

The above REST API is a preliminary implementation. If you notice any issue, have any usage questions or to suggest additional endpoints, please <a href="https://webberzone.com/request-support/" data-type="page" data-id="7861">open a ticket</a>.
