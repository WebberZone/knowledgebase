# Knowledge Base REST API

The WebberZone Knowledge Base plugin exposes selected functionality via the WordPress REST API. All endpoints live under the namespace `wzkb/v1` and therefore share the base path:

```text
https://example.com/wp-json/wzkb/v1/
```

> Replace `https://example.com` with your site's domain.

## Authentication & Permissions

- The API piggybacks on WordPress capabilities. Standard WordPress authentication mechanisms (cookies for logged-in sessions, app passwords, basic auth plugins, etc.) all work.
- All REST responses follow standard WordPress structures with `code`, `message`, and `data`.
- Cache lifetime is 300 seconds. Cache busts automatically on KB content or taxonomy changes.
- When used in a headless context ensure Gutenberg meta `_wzkb_product_ids` and `_wzkb_section_ids` are synced.
- Permissions can be customized per route via the `wzkb_rest_route_permission` filter.
- By default all read-only routes are public. Use the permission filter below if you need to restrict access.

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

| Parameter  | Type   | Required | Description                                                         |
|------------|--------|----------|---------------------------------------------------------------------|
| `products` | string | yes      | Comma-separated list of `wzkb_product` term IDs. Example: `173,178` |

#### Request example

```http
GET https://example.com/wp-json/wzkb/v1/sections?products=173,178
```

#### Successful response

```json
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

| HTTP Code | Error code                    | Reason                              |
|-----------|-------------------------------|-------------------------------------|
| 400       | `wzkb_rest_sections_disabled` | Multi-product mode is disabled.     |
| 401 / 403 | `rest_forbidden`              | User lacks `edit_posts` capability. |

### GET `/wzkb/v1/knowledgebase`

List published Knowledge Base posts with optional filtering.

| Parameter  | Type   | Required | Description                                       |
| ---------- | ------ | -------- | --------------------------------------------------|
| `per_page` | int    | no       | Results per page (1–50). Default: 10.             |
| `page`     | int    | no       | Page number. Default: 1.                          |
| `search`   | string | no       | Free-text search term.                            |
| `product`  | int    | no       | Filter by `wzkb_product` term ID.                 |
| `section`  | int    | no       | Filter by `wzkb_category` term ID.                |

#### Response

```json
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

```json
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

| Parameter | Type   | Required | Description                              |
|-----------|--------|----------|------------------------------------------|
| `query`   | string | yes      | Search keywords (min 2 characters).      |
| `product` | int    | no       | Filter by product term ID.               |
| `section` | int    | no       | Filter by section term ID.               |
| `limit`   | int    | no       | Results to return (1–50). Default: 10.   |

### GET `/wzkb/v1/related`

Fetch related articles for a given Knowledge Base post.

| Parameter | Type | Required | Description                           |
|-----------|------|----------|---------------------------------------|
| `post_id` | int  | yes      | Base Knowledge Base post ID.          |
| `limit`   | int  | no       | Number of related items (1–20).       |

## Using the API in Gutenberg

The custom Gutenberg panel (`includes/admin/js/editor-sections-panel.js`) consumes `/sections` to show product-aware section lists. Other clients (mobile apps, headless front ends, internal tools) can consume the same endpoint, provided they authenticate and the site runs multi-product mode.

## Support

For usage questions or to suggest additional endpoints, please open a ticket via [WebberZone.com/Support/KnowledgeBase](https://webberzone.com/support/knowledgebase/).
