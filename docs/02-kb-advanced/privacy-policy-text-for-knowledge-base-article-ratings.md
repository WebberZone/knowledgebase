---
slug: privacy-policy-text-for-knowledge-base-article-ratings
title: "Privacy Policy Text for Knowledge Base Pro"
products: [knowledgebase]
sections: ["02-kb-advanced"]
tags: [cookie, knowledgebase, pro, settings, trackers]
status: publish
order: 0
toc: true
---

[toc]

> [!WARNING]
> ⚠️ **Legal Disclaimer:** This document provides suggested text for your privacy policy based on how [Knowledge Base Pro](https://webberzone.com/plugins/knowledgebase/) handles visitor data (article ratings, the Help widget contact form, and related features). It is not legal advice. You should review and customize this text to ensure compliance with your jurisdiction’s privacy laws (GDPR, CCPA, etc.) and your specific use case. Consult with a privacy lawyer if you are unsure.

Knowledge Base Pro also adds feature-specific suggestions under **Settings → Privacy → Policy Guide**. For [Ask the Docs](https://webberzone.com/support/knowledgebase/ask-the-docs/#privacy) AI answers, see that article and name your AI provider in your policy.

## Article ratings

Copy and paste the section below that matches your configured **Vote Tracking Method** in the [Knowledge Base plugin settings](https://webberzone.com/support/knowledgebase/knowledge-base-settings/).

### 1. No Tracking (allows multiple votes)

```text
**Article Ratings**

Our knowledge base allows users to rate articles for usefulness. We do not store identifiers, such as cookies, IP addresses, or user IDs, to tie a rating to a specific visitor when this mode is enabled. Rating totals may still be processed and stored for article statistics. You may submit multiple ratings on the same article, and no identifying information is collected for duplicate-vote prevention. To prevent accidental duplicate submissions during the same browsing session, a temporary marker is stored in your browser's localStorage; it expires after 1 hour and contains no personally identifiable information. If you choose to submit optional written feedback, a hashed version of your IP address is stored alongside your feedback text.
```

### 2. Cookie Only (requires consent)

```text
**Article Ratings**

When you rate a knowledge base article, we store a cookie in your browser to prevent duplicate votes. This cookie stores a simple marker indicating that you have already rated a specific article. The cookie expires after 365 days (1 year). You may clear this cookie at any time in your browser settings, allowing you to submit a new rating. A temporary marker is also stored in your browser's localStorage to prevent accidental duplicate submissions during the same session; it expires after 1 hour. If you choose to submit optional written feedback, a hashed version of your IP address is stored alongside your feedback text.
```

### 3. IP Address Only (stores personal data)

```text
**Article Ratings**

When you rate a knowledge base article, we store a pseudonymized hash derived from your IP address to prevent duplicate votes. This hash is generated using SHA-256 with a site-specific WordPress salt and does not directly reveal your original IP address. The hashed identifier is stored in a rolling per-article log with a default maximum of 10,000 entries and is used solely for duplicate vote prevention. Under many privacy laws, including GDPR, this may constitute processing of personal data. You may contact us to request access to or erasure of data associated with your rating activity, where applicable. A temporary marker is also stored in your browser's localStorage to prevent accidental duplicate submissions during the same session; it expires after 1 hour. If you choose to submit optional written feedback, a hashed version of your IP address is stored alongside your feedback text.
```

### 4. Cookie + IP Address (either method blocks voting)

```text
**Article Ratings**

When you rate a knowledge base article, we use both a browser cookie and a pseudonymized hash derived from your IP address to prevent duplicate votes. The cookie stores a simple marker indicating that you have already rated a specific article and expires after 365 days (1 year). The IP-based hash is generated using SHA-256 with a site-specific WordPress salt and does not directly reveal your original IP address. The hashed identifier is stored in a rolling per-article log with a default maximum of 10,000 entries and is used solely for duplicate vote prevention. You will be prevented from voting again if either the cookie is present or your IP-based identifier matches. A temporary marker is also stored in your browser's localStorage to prevent accidental duplicate submissions during the same session; it expires after 1 hour. If you choose to submit optional written feedback, a hashed version of your IP address is stored alongside your feedback text.
```

### 5. Logged-in Users Only (best for authenticated sites)

```text
**Article Ratings**

Only registered and logged-in users may rate knowledge base articles. When you submit a rating, we store your WordPress user ID to prevent duplicate votes. No cookie or IP-based identifier is used to prevent duplicate votes in this mode. Your rating data is associated with your user account and stored in a rolling per-article log with a default maximum of 10,000 entries; older entries are pruned over time. You may contact us to request access to or erasure of this data, where applicable. A temporary marker is also stored in your browser's localStorage to prevent accidental duplicate submissions during the same session; it expires after 1 hour. If you choose to submit optional written feedback, a hashed version of your IP address is stored alongside your feedback text.
```

### Complete Combined Version

Use this if you want to cover all possibilities:

```text
**Article Ratings**

Our knowledge base allows users to rate articles for usefulness. Depending on our configuration, we may use one or more of the following methods to prevent duplicate votes:

- **Browser Cookie**: A small text file stored on your device that marks that you have rated a specific article. It expires after 365 days (1 year).

- **Pseudonymized IP-Based Identifier**: A hash derived from your IP address using SHA-256 hashing and a site-specific WordPress salt. This does not directly reveal your original IP address.

- **User ID**: If you are logged in, your WordPress user ID is stored to track your rating.

- **Browser localStorage**: A temporary marker stored in your browser's localStorage to prevent accidental duplicate submissions during the same session. It expires after 1 hour and contains no personally identifiable information.

- **Feedback IP Hash**: If you submit optional written feedback, a hashed version of your IP address is stored alongside your feedback text.

All rating data is used solely to maintain rating functionality, prevent duplicate votes, and support article rating statistics. Where applicable under privacy law, you may have rights to access, rectify, or erase your personal data. Contact us to exercise these rights.
```

### Summary of Data Collected by Method

| Tracking Method | Data Stored | Duration | GDPR Status |
| --- | --- | --- | --- |
| None | Browser localStorage marker (temporary session prevention); hashed IP in feedback if provided | localStorage: 1 hour; feedback IP: rolling per-article log | localStorage: no personal data; feedback IP: may be treated as personal data |
| Cookie Only | Browser cookie marker + localStorage marker; hashed IP in feedback if provided | Cookie: 365 days; localStorage: 1 hour | May require cookie disclosure/consent depending on applicable law |
| IP Only | Hashed IP-based identifier + localStorage marker; hashed IP in feedback if provided | IP hash: rolling per-article log (default max 10,000 entries); localStorage: 1 hour | May be treated as personal data |
| Cookie + IP | Browser cookie marker + hashed IP-based identifier + localStorage marker; hashed IP in feedback if provided | Cookie: 365 days; IP hash: rolling per-article log; localStorage: 1 hour | May be treated as personal data |
| Logged-in Only | WordPress user ID + localStorage marker; hashed IP in feedback if provided | User ID: rolling per-article log (default max 10,000 entries); localStorage: 1 hour | Personal data |

## Help widget contact form

Use this if **Enable Contact Form** is on under **Knowledge Base → Settings → Pro → Help Widget**.

```text
**Help widget contact form**

When you send a message through our help widget, we collect your name, email address, subject and message and email them to our support team so we can reply.

To reduce spam, we limit how many messages someone can send per hour. If you are not logged in, we process your IP address only long enough to create a salted, one-way hash. That hash is used as an internal label on an hourly counter. We do not store your IP address in plain text in the database, and we do not use the hash to identify you outside that counter. The counter is deleted when the hour ends. Logged-in visitors are limited by their WordPress user account instead.

By default, each visitor can send up to five messages per hour and the site accepts up to thirty help widget messages per hour in total. The contact form does not place a long-term cookie or localStorage token for rate limiting.
```

| Data | When | Duration | Notes |
| --- | --- | --- | --- |
| Name, email, subject, message | Each successful submit | Until you delete the email | Sent to your **Contact Email** setting; not stored in the WordPress database by the plugin |
| Hourly message counter (anonymous) | Each submit attempt counted toward the limit | About one hour (current window) | Option name includes a salted hash derived from the IP address; value is a number |
| Hourly message counter (logged in) | Same | About one hour | Keyed by user account, not IP |
| Site-wide hourly counter | Each submit attempt counted toward the site cap | About one hour | Not tied to a visitor |

Anonymous rate limiting follows the same salted-hash pattern described for [Ask the Docs](https://webberzone.com/support/knowledgebase/ask-the-docs/#privacy). If visitors behind a proxy share one limit, use the `wzkb_help_widget_visitor_ip` filter only with trusted headers. See the [Help widget developer reference](https://webberzone.com/support/knowledgebase/knowledge-base-help-widget/#developer-reference).

## Suggested Privacy Compliance Checklist

Before publishing your privacy policy, ensure you have addressed:

### For article ratings (all methods)

- [ ] **Purpose limitation**: Clearly state that data is only used for duplicate vote prevention
- [ ] **Data retention**: Specify how long data is kept (see table above)
- [ ] **User rights**: Inform users of their right to access, rectify, or delete their data
- [ ] **Contact information**: Provide a way for users to exercise their rights

### For IP Address Methods (IP Only, Cookie + IP)

- [ ] **Legal basis**: Under GDPR, specify your legal basis as appropriate for your implementation and jurisdiction
- [ ] **Pseudonymization**: Explain that IP addresses are hashed and then stored value does not directly reveal the original IP address
- [ ] **Data Processing Agreement**: If applicable, note whether rating data is shared with processors

### For Cookie Methods (Cookie Only, Cookie + IP)

- [ ] **Cookie consent**: Ensure your cookie banner or cookie policy covers rating cookies where required
- [ ] **ePrivacy rules**: Comply with applicable cookie laws in the jurisdictions relevant to your site

### For Logged-in Users Only (ratings)

- [ ] **Account-based processing**: Clarify that rating data is linked to user accounts
- [ ] **Account deletion**: Explain what happens to ratings when an account is deleted

### For Help widget contact form

- [ ] **Purpose limitation:** State that form data is used to handle support requests.
- [ ] **Retention:** Explain that emailed messages are kept according to your email and support practices.
- [ ] **Contact information:** Provide a way for users to exercise access or erasure rights for messages you still hold.
- [ ] **Legal basis (anonymous rate limits):** Under GDPR, specify your legal basis for processing IP-derived data for abuse prevention (often legitimate interest).
- [ ] **Pseudonymization:** Explain that the IP is hashed with a site-specific salt and not stored in plain text.
- [ ] **Shared limits:** Note that visitors behind the same network address (for example, office NAT) may share one hourly limit.
