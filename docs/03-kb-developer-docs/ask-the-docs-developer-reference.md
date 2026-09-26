---
slug: ask-the-docs-developer-reference
title: "Ask the docs developer reference"
products: [knowledgebase]
sections: ["03-kb-developer-docs"]
tags: [ai, developer, knowledgebase, pro, rest-api]
status: publish
order: 0
toc: true
---

[toc]

This reference covers the REST endpoint, the Abilities API ability, and the filters and actions behind **Ask the docs** in [Knowledge Base Pro](https://webberzone.com/plugins/knowledgebase/). For setup and settings, see [Ask the docs: AI answers from your knowledge base](https://webberzone.com/support/knowledgebase/ask-the-docs/).

Ask the docs needs WordPress 7.0 or later. On older versions none of these hooks, routes or abilities are registered.

## REST endpoint

`POST /wp-json/wzkb/v1/ask` with a JSON body:

```json
{ "question": "How do I get a refund?" }
```

Questions must be 3 to 300 characters after HTML tags are stripped and whitespace is collapsed.

### Response

| Field | Type | Description |
| --- | --- | --- |
| `answered` | bool | Whether the knowledge base answered the question. |
| `answer` | string | The answer as plain text. Empty when not answered. |
| `sources` | array | Articles the answer came from, each with `id`, `title` and `url`. Empty when **Show sources** is off. |
| `sources_title` | string | Heading for `sources`, translated for the number of articles. |
| `related` | array | Up to three other published articles from the same section, or with the same tags, as the main source. Each has `id`, `title` and `url`. Empty when not answered. |
| `related_title` | string | Heading for `related`, translated for the number of articles. |
| `cached` | bool | Whether the answer came from the cache. |
| `message` | string | The fallback message when not answered. |
| `search_url` | string | The knowledge base search results URL for the question. |
| `reason` | string | `daily_cap`, `provider_error` or `invalid_response` when there is no answer for one of those reasons. Empty otherwise. |

### Errors

| Status | Code | When |
| --- | --- | --- |
| 400 | `wzkb_ai_invalid_question` | The question is too short or too long. |
| 403 | `wzkb_ai_forbidden_origin` | The `Origin` (or `Referer`) header doesn't match the site or an allowed origin. |
| 403 | `wzkb_ai_forbidden_client` | The request has no user agent, or one that looks like a bot, script or HTTP library. |
| 404 | `wzkb_ai_unavailable` | Ask the docs is disabled or no provider is available. |
| 429 | `wzkb_ai_rate_limited` | The visitor reached **Questions per visitor per hour**. The `Retry-After` header gives the seconds until the next hour. |

When the daily cap is reached, the endpoint returns 200 with `answered` set to `false` and `reason` set to `daily_cap`.

### Bot check

Before any cache or provider work, the endpoint refuses requests with an empty user agent, or one that contains any of these, ignoring case: `bot`, `crawl`, `spider`, `slurp`, `scrap`, `headless`, `phantom`, `puppeteer`, `playwright`, `selenium`, `curl`, `wget`, `python`, `httpclient`, `http-client`, `okhttp`, `axios`, `node-fetch`, `java/`, `libwww`, `guzzle`, `postman`, `insomnia`.

A script can send a browser's user agent, so the visitor limit and daily cap remain the limits on cost. Use `wzkb_ai_is_bot` to allow or block specific user agents:

```php
add_filter(
	'wzkb_ai_is_bot',
	function ( $is_bot, $user_agent ) {
		// Allow an internal monitoring tool.
		return str_contains( $user_agent, 'AcmeMonitor/' ) ? false : $is_bot;
	},
	10,
	2
);
```

## Ability

Knowledge Base Pro registers the `knowledgebase/ask` ability with the WordPress Abilities API, in the `knowledgebase` category. It takes `{ "question": "..." }`, returns the same data as the REST endpoint, and runs through the same cache and limits.

It is limited to logged-in users by default, because the Abilities API route cannot check that a request comes from your site the way the REST endpoint does. The bot check does not apply to it. Use `wzkb_ai_ability_permission` to change who can run it:

```php
add_filter( 'wzkb_ai_ability_permission', '__return_true' );
```

## Filters

| Filter | Arguments | Description |
| --- | --- | --- |
| `wzkb_ai_ability_permission` | `bool $allowed`, `mixed $input` | Whether the current user may run the ability. Default: logged in. |
| `wzkb_ai_allowed_origins` | `string[] $origins` | Extra origins allowed to call the REST endpoint. |
| `wzkb_ai_answer` | `array $result`, `string $question`, `array $articles` | The validated answer, before it is cached. |
| `wzkb_ai_answer_cache_ttl` | `int $ttl` | How long answers are cached, in seconds. Default one day. Return 0 to turn off caching. |
| `wzkb_ai_ask_box` | `string $html`, `array $args` | The HTML of a knowledge base search box that answers questions. `$args['context']` is `search_form`. |
| `wzkb_ai_content_gaps_capability` | `string $capability` | Capability needed for the Content gaps report. Default `manage_options`. |
| `wzkb_ai_is_bot` | `bool $is_bot`, `string $user_agent`, `WP_REST_Request $request` | Whether a REST request comes from a bot. |
| `wzkb_ai_log_question` | `bool $log`, `string $question`, `array $result` | Return `false` to stop logging a question. |
| `wzkb_ai_pre_prompt` | `null $response`, `string $prompt`, `string $system_instruction`, `string $question`, `array $articles` | Return a string to use as the model's raw output, or a `WP_Error` to simulate a provider failure, and skip the provider request. Useful for testing. |
| `wzkb_ai_prompt_builder` | `$builder`, `string $question`, `array $articles` | The WordPress AI Client prompt builder before it is sent. Use it to set model preferences with `using_model_preference()`. |
| `wzkb_ai_provider_options` | `array $options` | The providers offered in the **AI provider** setting. |
| `wzkb_ai_response` | `array $response`, `string $question` | The final response sent to the browser or ability. |
| `wzkb_ai_retrieved_articles` | `array $articles`, `string $question`, `int $limit` | The articles sent as context. |
| `wzkb_ai_retriever` | `Retriever_Interface $retriever` | Replace the retriever, for example with an embeddings-based search. |
| `wzkb_ai_retriever_query_args` | `array $args`, `string $question` | The `WP_Query` arguments used to find articles. |
| `wzkb_ai_skipped_blocks` | `string[] $blocks` | Blocks left out of the article text, such as navigation and listing blocks. |
| `wzkb_ai_stop_words` | `string[] $words` | Words dropped from questions before searching. |
| `wzkb_ai_system_instruction` | `string $instruction`, `string $answer_length` | The instructions sent with each request. |
| `wzkb_ai_visitor_ip` | `string $ip` | The IP address used for rate limiting. Return the client IP from a trusted header if your site is behind a proxy or CDN. |

### Custom retriever

A retriever implements `WebberZone\Knowledge_Base\Pro\AI\Retriever_Interface`. Its `retrieve( string $question, int $limit ): array` method returns articles, most relevant first, each with `id` (int), `title`, `url` and `content` (plain text).

```php
add_filter(
	'wzkb_ai_retriever',
	function ( $retriever ) {
		return new My_Embeddings_Retriever();
	}
);
```

### Visitors behind a proxy

```php
add_filter(
	'wzkb_ai_visitor_ip',
	function ( $ip ) {
		// Only trust this header if your proxy always sets it.
		return isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) : $ip;
	}
);
```

## Actions

| Action | Arguments | Description |
| --- | --- | --- |
| `wzkb_ai_question_asked` | `string $question`, `array $result`, `bool $cached` | Fires after each answered or unanswered question. |
| `wzkb_cache_cleared` | None | Fires after **Knowledge Base → Tools → Clear Cache** clears the output cache. Knowledge Base uses it to invalidate REST API responses, and Knowledge Base Pro to delete cached Ask the docs answers. |

```php
add_action(
	'wzkb_cache_cleared',
	function () {
		delete_transient( 'my_kb_cache' );
	}
);
```

## Caching

- **Answers**: transients prefixed `wzkb_ai_answer_`, kept for a day. The key includes the normalized question, the knowledge base content version, **Answer length**, **Articles sent as context**, **Maximum characters per article**, the provider and the locale.
- **Content version**: the `wzkb_rest_cache_version` option. It changes when a knowledge base article is saved, changes status or is deleted, when a section or product is created, edited or deleted, and when the cache is cleared from the Tools page. A new version changes every answer key, so stale answers are never served.
- **Provider status**: cached for an hour when a provider is available and for 10 minutes when it isn't.

## See also

- [Ask the docs: AI answers from your knowledge base](https://webberzone.com/support/knowledgebase/ask-the-docs/)
- [Knowledge Base REST API](https://webberzone.com/support/knowledgebase/knowledge-base-rest-api/)
