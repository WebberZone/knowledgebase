---
slug: knowledge-base-abilities-api
title: "Knowledge Base Abilities API"
products: [knowledgebase]
sections: ["03-kb-developer-docs"]
tags: [abilities-api, ai, developer, knowledgebase, pro]
status: publish
order: 0
toc: true
---

[toc]

[Knowledge Base](https://wordpress.org/plugins/knowledgebase/) registers WordPress Abilities API tools for AI agents and other integrations. The free plugin provides read-only article search and section discovery. Knowledge Base Pro adds draft article creation and the Ask the docs ability.

The Abilities API is built into WordPress 6.9 and later. Knowledge Base does not include a polyfill. All Knowledge Base abilities use the shared `webberzone` category.

## Discover and run abilities

Use `wp_get_abilities()` in PHP to inspect registered abilities. The WordPress REST API exposes them at:

```text
GET https://example.com/wp-json/wp-abilities/v1/abilities
```

Run a read-only ability with `GET` and pass its input as query parameters. Run an ability that changes site data with `POST` and pass its input as JSON. The HTTP method is determined by the ability's annotations.

```bash
curl --get \
  --data-urlencode 'input[query]=reset password' \
  --data-urlencode 'input[limit]=10' \
  'https://example.com/wp-json/wp-abilities/v1/abilities/knowledgebase/search-articles/run'
```

Create-article uses `POST` with an `input` object in the JSON body:

```bash
curl --request POST \
  --header 'Content-Type: application/json' \
  --data '{"input":{"title":"Reset a password","content":"<p>Follow these steps…</p>"}}' \
  'https://example.com/wp-json/wp-abilities/v1/abilities/knowledgebase/create-article/run'
```

Requests use WordPress REST authentication. The ability permission callback is enforced for PHP, REST and MCP calls; listing an ability does not grant permission to run it.

## Free abilities

### `knowledgebase/search-articles`

Search published Knowledge Base articles. Use a few distinctive keywords and optionally limit results to a section by its term ID or slug.

| Input | Type | Required | Description |
| --- | --- | --- | --- |
| `query` | string | yes | Search keywords or a short phrase, 2–500 characters. |
| `section` | integer or string | no | `wzkb_category` term ID or slug. String values are matched as slugs first, then numeric strings fall back to a term ID. |
| `limit` | integer | no | Maximum results, 1–50. Default: 10. |

Each result contains `id`, plain-text `title`, `url`, plain-text `excerpt`, and a `section` array. Each section contains `id`, `name`, and `slug`.

The ability requires the `read` capability and filters each result through WordPress's `read_post` capability. It also respects the `wzkb_rest_route_permission` filter for the `search` route, so visibility rules configured for the REST search endpoint also apply.

### `knowledgebase/get-sections`

Return the hierarchical `wzkb_category` section tree.

| Input | Type | Required | Description |
| --- | --- | --- | --- |
| `parent` | integer | no | Parent section ID. Omit or use `0` for top-level sections. |

Each section contains `id`, `name`, `slug`, `url`, and `children`. Supplying a parent returns its child sections and their descendants. The ability requires `read` and respects the `wzkb_rest_route_permission` filter for the `sections` route.

## Pro abilities

### `knowledgebase/create-article`

Create an article as a draft for an editor to review. The ability always sets `post_status` to `draft`; publishing remains a manual action in WordPress.

| Input | Type | Required | Description |
| --- | --- | --- | --- |
| `title` | string | yes | Article title, 1–200 characters. |
| `content` | string | yes | Article content, 1–100,000 characters. Safe post HTML is allowed. |
| `section` | integer | no | Existing `wzkb_category` term ID. |

The ability requires the Knowledge Base post type's create capability, which defaults to `edit_posts`. When `section` is supplied, the user must also be able to assign terms. It returns the new article's `id` and `edit_url`.

### `knowledgebase/ask`

Knowledge Base Pro also registers the Ask the docs ability. It accepts a `question`, returns the same answer data as the Ask the docs REST endpoint, and is available to logged-in users by default. It is registered only on WordPress 7.0 or later, where Ask the docs loads. See the [Ask the docs developer reference](https://webberzone.com/support/knowledgebase/ask-the-docs-developer-reference/) for its filters, response and limits.

## WP-CLI mapping

Knowledge Base and Knowledge Base Pro currently register no WP-CLI commands, so there are no existing CLI operations to wrap, redesign or leave CLI-only. See the Pro repository's `ABILITIES-CLI-MAPPING.md` for the Phase 2 audit record.

## See also

- [Knowledge Base REST API](https://webberzone.com/support/knowledgebase/knowledge-base-rest-api/)
- [Ask the docs developer reference](https://webberzone.com/support/knowledgebase/ask-the-docs-developer-reference/)
- [WordPress Abilities API documentation](https://developer.wordpress.org/apis/abilities-api/)
