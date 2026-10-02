---
slug: knowledge-base-rating-system
title: "Knowledge Base Pro Rating System"
products: [knowledgebase]
sections: ["01-kb-getting-started"]
tags: [knowledgebase, pro]
status: publish
order: 0
toc: true
---

[toc]

[Knowledge Base Pro](https://webberzone.com/plugins/knowledgebase/) lets visitors rate articles, collects feedback on low ratings, and ranks articles by a weighted score that balances rating with vote count.

## Settings

The settings are under **Knowledge Base → Settings → Pro**, in the **Article Rating** section.

| Setting | Default | What it does |
| --- | --- | --- |
| **Enable Rating System** | Disabled | **Useful / Not Useful** buttons, a **1-5 Star Rating**, or disabled. |
| **Vote Tracking Method** | Cookie Only | How duplicate votes are prevented. See below. |
| **Show Rating Statistics** | On | Shows the average rating and vote count below the rating buttons. |

Ratings appear after the content of single knowledge base articles. Use the [`wzkb_rating_position`](https://webberzone.dev/knowledgebase/hooks/wzkb_rating_position/) filter to show them before the content instead.

## Tracking Methods & GDPR Compliance

Each method has different privacy implications. Copy-ready privacy policy wording for each one is in [Privacy Policy Text for Knowledge Base Article Ratings](https://webberzone.com/support/knowledgebase/privacy-policy-text-for-knowledge-base-article-ratings/).

| Method | How duplicates are blocked | Privacy considerations |
| --- | --- | --- |
| **No Tracking** | Not blocked; visitors can vote repeatedly. | No cookies or personal data. Fine for low-stakes feedback. |
| **Cookie Only** | A `wzkb_rated_{post_id}` cookie, kept for 365 days. | Needs cookie consent under GDPR/ePrivacy and a cookie-policy entry. Nothing is stored on the server. |
| **IP Address Only** | A SHA-256 hash of the IP address with the WordPress salt, stored with the article. | Pseudonymized data: the IP cannot be recovered and admins never see it, but it still needs a privacy policy disclosure. |
| **Cookie + IP Address** | Either check blocks a repeat vote. | Needs both cookie consent and a privacy policy disclosure. Strongest protection against abuse. |
| **Logged-in Users Only** | The user ID is stored; guests see a login prompt. | No cookies. Suited to sites where readers sign in. |

If your site is behind a trusted proxy or CDN, IP tracking sees the proxy's address. Enable proxy headers with the [`wzkb_rating_use_proxy_headers`](https://webberzone.dev/knowledgebase/hooks/wzkb_rating_use_proxy_headers/) filter only if you trust the proxy, because the headers can otherwise be spoofed.

## Low ratings and feedback

When a visitor clicks **Not Useful**, or gives one or two stars, the rating box asks **How can we improve this article?** The visitor can leave an optional comment of up to 500 characters, or click **Skip**.

Each low rating emails the site admin address, and a comment submitted afterwards sends a second email with the comment. Change the recipient with [`wzkb_low_rating_notification_email`](https://webberzone.dev/knowledgebase/hooks/wzkb_low_rating_notification_email/), or turn the emails off with [`wzkb_send_low_rating_notification`](https://webberzone.dev/knowledgebase/hooks/wzkb_send_low_rating_notification/).

Review comments under **Knowledge Base → Ratings Feedback**. Search them, show **Low Ratings Only**, or click **Export to CSV**.

## Rating column and ranking

The articles list in the admin has a sortable **Rating** column: the percentage of helpful votes in binary mode, or the average out of 5 in star mode, each with the vote count.

Sorting uses a Bayesian average, so an article with two perfect votes doesn't outrank one with hundreds of good ones:

*Weighted score = (v × R + m × C) / (v + m)*

Here **v** is the article's vote count, **R** its average rating, **C** the average across all articles, and **m** the prior weight, `10` by default. With **C** at 70%, two votes at 100% score 75%, while 500 votes at 85% score about 85%. Raise **m** with [`wzkb_rating_bayesian_prior_weight`](https://webberzone.dev/knowledgebase/hooks/wzkb_rating_bayesian_prior_weight/) to require more votes before a rating is trusted.

The score is stored when a vote is submitted, so sorting stays fast.

## Styling

Override these classes in your theme or the **Custom CSS** setting:

| Class | Element |
| --- | --- |
| `.wzkb-rating-container` | Main container |
| `.wzkb-rating-useful`, `.wzkb-rating-not-useful` | Binary buttons |
| `.wzkb-rating-star` | Each star |
| `.wzkb-rating-stats` | Statistics |
| `.wzkb-rating-thank-you` | Thank-you message |
| `.wzkb-rating-login-required` | Login prompt |

## Troubleshooting

- **Ratings don't appear.** Check that **Enable Rating System** isn't Disabled, that you're on a single article, and that JavaScript isn't blocked. A theme that replaces the single article template can also hide them.
- **Visitors can vote more than once.** Check the tracking method. Cookie tracking can be bypassed by clearing cookies, and IP tracking needs your server to see the real visitor IP.
- **Page caching.** Ratings work with cached pages, because votes are checked and sent in the browser.

## Developer notes

Each article stores its totals in `_wzkb_rating_total`, `_wzkb_rating_sum` and `_wzkb_rating_positive`, and cached scores in `_wzkb_average_rating`, `_wzkb_positive_ratio` and `_wzkb_bayesian_rating`. Individual votes, IP hashes, user IDs and feedback are kept in `_wzkb_ratings`, `_wzkb_rating_ips`, `_wzkb_rating_user_ids` and `_wzkb_rating_feedback`. Delete these meta keys to reset an article's ratings. To erase one visitor's votes for a GDPR request, compute `hash( 'sha256', $ip . wp_salt( 'nonce' ) )` from their IP address and remove that hash from `_wzkb_rating_ips` on each article.

Votes are limited to 10 per 60 seconds per visitor, and each vote log keeps at most 10,000 entries, dropping the oldest 10% when full.

| Hook | Use |
| --- | --- |
| [`wzkb_rating_stored`](https://webberzone.dev/knowledgebase/hooks/wzkb_rating_stored/) | Action after a vote is saved. |
| [`wzkb_rating_rate_limits`](https://webberzone.dev/knowledgebase/hooks/wzkb_rating_rate_limits/) | Change the vote rate limit. |
| [`wzkb_rating_max_log_size`](https://webberzone.dev/knowledgebase/hooks/wzkb_rating_max_log_size/) | Change the 10,000-entry log limit. |
| [`wzkb_rating_session_expiry`](https://webberzone.dev/knowledgebase/hooks/wzkb_rating_session_expiry/) | How long a visitor's rating session lasts. Default one hour. |
