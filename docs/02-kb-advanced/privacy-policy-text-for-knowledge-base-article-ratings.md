---
slug: privacy-policy-text-for-knowledge-base-article-ratings
title: "Privacy Policy Text for Knowledge Base Article Ratings"
products: [knowledgebase]
sections: [02-kb-advanced]
tags: [cookie,knowledgebase,pro,settings,trackers]
status: publish
order: 0
---

> [!WARNING]
> ⚠️ **Legal Disclaimer:** This document provides suggested text for your privacy policy based on the technical implementation of the [Knowledge Base Pro](https://webberzone.com/plugins/knowledgebase/) rating system. It is not legal advice. You should review and customize this text to ensure compliance with your jurisdiction’s privacy laws (GDPR, CCPA, etc.) and your specific use case. Consult with a privacy lawyer if you are unsure.

Copy and paste the section below that matches your configured **Vote Tracking Method** in the <a href="https://webberzone.com/support/knowledgebase/knowledge-base-settings/" data-type="wz_knowledgebase" data-id="9305">Knowledge Base plugin settings</a>.

## 1. No Tracking (allows multiple votes)

``` php
**Article Ratings**

Our knowledge base allows users to rate articles for usefulness. We do not store identifiers, such as cookies, IP addresses, or user IDs, to tie a rating to a specific visitor when this mode is enabled. Rating totals may still be processed and stored for article statistics. You may submit multiple ratings on the same article, and no identifying information is collected for duplicate-vote prevention. To prevent accidental duplicate submissions during the same browsing session, a temporary marker is stored in your browser's localStorage; it expires after 1 hour and contains no personally identifiable information. If you choose to submit optional written feedback, a hashed version of your IP address is stored alongside your feedback text.
```

## 2. Cookie Only (requires consent)

``` php
**Article Ratings**

When you rate a knowledge base article, we store a cookie in your browser to prevent duplicate votes. This cookie stores a simple marker indicating that you have already rated a specific article. The cookie expires after 365 days (1 year). You may clear this cookie at any time in your browser settings, allowing you to submit a new rating. A temporary marker is also stored in your browser's localStorage to prevent accidental duplicate submissions during the same session; it expires after 1 hour. If you choose to submit optional written feedback, a hashed version of your IP address is stored alongside your feedback text.
```

## 3. IP Address Only (stores personal data)

``` php
**Article Ratings**

When you rate a knowledge base article, we store a pseudonymized hash derived from your IP address to prevent duplicate votes. This hash is generated using SHA-256 with a site-specific WordPress salt and does not directly reveal your original IP address. The hashed identifier is stored in a rolling per-article log with a default maximum of 10,000 entries and is used solely for duplicate vote prevention. Under many privacy laws, including GDPR, this may constitute processing of personal data. You may contact us to request access to or erasure of data associated with your rating activity, where applicable. A temporary marker is also stored in your browser's localStorage to prevent accidental duplicate submissions during the same session; it expires after 1 hour. If you choose to submit optional written feedback, a hashed version of your IP address is stored alongside your feedback text.
```

## 4. Cookie + IP Address (either method blocks voting)

``` php
**Article Ratings**

When you rate a knowledge base article, we use both a browser cookie and a pseudonymized hash derived from your IP address to prevent duplicate votes. The cookie stores a simple marker indicating that you have already rated a specific article and expires after 365 days (1 year). The IP-based hash is generated using SHA-256 with a site-specific WordPress salt and does not directly reveal your original IP address. The hashed identifier is stored in a rolling per-article log with a default maximum of 10,000 entries and is used solely for duplicate vote prevention. You will be prevented from voting again if either the cookie is present or your IP-based identifier matches. A temporary marker is also stored in your browser's localStorage to prevent accidental duplicate submissions during the same session; it expires after 1 hour. If you choose to submit optional written feedback, a hashed version of your IP address is stored alongside your feedback text.
```

## 5. Logged-in Users Only (best for authenticated sites)

``` php
**Article Ratings**

Only registered and logged-in users may rate knowledge base articles. When you submit a rating, we store your WordPress user ID to prevent duplicate votes. No cookie or IP-based identifier is used to prevent duplicate votes in this mode. Your rating data is associated with your user account and stored in a rolling per-article log with a default maximum of 10,000 entries; older entries are pruned over time. You may contact us to request access to or erasure of this data, where applicable. A temporary marker is also stored in your browser's localStorage to prevent accidental duplicate submissions during the same session; it expires after 1 hour. If you choose to submit optional written feedback, a hashed version of your IP address is stored alongside your feedback text.
```

## Complete Combined Version

Use this if you want to cover all possibilities:

``` php
**Article Ratings**

Our knowledge base allows users to rate articles for usefulness. Depending on our configuration, we may use one or more of the following methods to prevent duplicate votes:

- **Browser Cookie**: A small text file stored on your device that marks that you have rated a specific article. It expires after 365 days (1 year).

- **Pseudonymized IP-Based Identifier**: A hash derived from your IP address using SHA-256 hashing and a site-specific WordPress salt. This does not directly reveal your original IP address.

- **User ID**: If you are logged in, your WordPress user ID is stored to track your rating.

- **Browser localStorage**: A temporary marker stored in your browser's localStorage to prevent accidental duplicate submissions during the same session. It expires after 1 hour and contains no personally identifiable information.

- **Feedback IP Hash**: If you submit optional written feedback, a hashed version of your IP address is stored alongside your feedback text.

All rating data is used solely to maintain rating functionality, prevent duplicate votes, and support article rating statistics. Where applicable under privacy law, you may have rights to access, rectify, or erase your personal data. Contact us to exercise these rights.
```

## Summary of Data Collected by Method

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th class="has-text-align-left" data-align="left">Tracking Method</th>
<th class="has-text-align-left" data-align="left">Data Stored</th>
<th class="has-text-align-left" data-align="left">Duration</th>
<th class="has-text-align-left" data-align="left">GDPR Status</th>
</tr>
</thead>
<tbody>
<tr>
<td>None</td>
<td>Browser localStorage marker (temporary session prevention); hashed IP in feedback if provided</td>
<td>localStorage: 1 hour; feedback IP: rolling per-article log</td>
<td>localStorage: no personal data; feedback IP: may be treated as personal data</td>
</tr>
<tr>
<td>Cookie Only</td>
<td>Browser cookie marker + localStorage marker; hashed IP in feedback if provided</td>
<td>Cookie: 365 days; localStorage: 1 hour</td>
<td>May require cookie disclosure/consent depending on applicable law</td>
</tr>
<tr>
<td>IP Only</td>
<td>Hashed IP-based identifier + localStorage marker; hashed IP in feedback if provided</td>
<td>IP hash: rolling per-article log (default max 10,000 entries); localStorage: 1 hour</td>
<td>May be treated as personal data</td>
</tr>
<tr>
<td>Cookie + IP</td>
<td>Browser cookie marker + hashed IP-based identifier + localStorage marker; hashed IP in feedback if provided</td>
<td>Cookie: 365 days; IP hash: rolling per-article log; localStorage: 1 hour</td>
<td>May be treated as personal data</td>
</tr>
<tr>
<td>Logged-in Only</td>
<td>WordPress user ID + localStorage marker; hashed IP in feedback if provided</td>
<td>User ID: rolling per-article log (default max 10,000 entries); localStorage: 1 hour</td>
<td>Personal data</td>
</tr>
</tbody>
</table>
</figure>

## Suggested Privacy Compliance Checklist

Before publishing your privacy policy, ensure you have addressed:

### For All Methods

- \[ \] **Purpose limitation**: Clearly state that data is only used for duplicate vote prevention
- \[ \] **Data retention**: Specify how long data is kept (see table above)
- \[ \] **User rights**: Inform users of their right to access, rectify, or delete their data
- \[ \] **Contact information**: Provide a way for users to exercise their rights

### For IP Address Methods (IP Only, Cookie + IP)

- \[ \] **Legal basis**: Under GDPR, specify your legal basis as appropriate for your implementation and jurisdiction
- \[ \] **Pseudonymization**: Explain that IP addresses are hashed and then stored value does not directly reveal the original IP address
- \[ \] **Data Processing Agreement**: If applicable, note whether rating data is shared with processors

### For Cookie Methods (Cookie Only, Cookie + IP)

- \[ \] **Cookie consent**: Ensure your cookie banner or cookie policy covers rating cookies where required
- \[ \] **ePrivacy rules**: Comply with applicable cookie laws in the jurisdictions relevant to your site

### For Logged-in Users Only

- \[ \] **Account-based processing**: Clarify that rating data is linked to user accounts
- \[ \] **Account deletion**: Explain what happens to ratings when an account is deleted
