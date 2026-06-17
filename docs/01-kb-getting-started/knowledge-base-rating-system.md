---
slug: knowledge-base-rating-system
title: "Knowledge Base Pro Rating System"
products: [knowledgebase]
sections: [01-kb-getting-started]
tags: [knowledgebase,pro]
status: publish
order: 0
---

[kbtoc]

The [Knowledge Base Pro](https://webberzone.com/plugins/knowledgebase/) plugin includes a comprehensive rating system that allows visitors to rate the quality of articles. This document explains how the system works, configuration options, and GDPR compliance considerations.

## Overview

The rating system provides three modes:

1. **Disabled** – No rating functionality
2. **Binary Rating** – Useful / Not Useful buttons
3. **Scale Rating** – 1-5 star rating system

## Tracking Methods & GDPR Compliance

There are five tracking options to choose from, each with different implications from a GDPR (or equivalent privacy law) perspective. Let’s look at them one-by-one.

### No Tracking (`none`)

**How it works:**

- No cookies set
- No personal data stored
- Allows unlimited votes from the same visitor
- Most privacy-friendly option

**GDPR Considerations:**

- ✅ **No consent required**
- ✅ Perfect for GDPR compliance without cookie banners
- ✅ No privacy policy disclosure needed
- ⚠️ Allows vote manipulation (use for low-stakes feedback)

### Cookie Only (`cookie`)

**How it works:**

- Sets a browser cookie when the user votes
- No personal data is stored on the server
- The user can clear cookies (allows re-voting)

**Cookie Details:**

- **Name:** `wzkb_rated_{post_id}` (e.g., `wzkb_rated_123`)
- **Value:** `1`
- **Expiry:** 365 days
- **Path:** `/`
- **SameSite:** `Lax`
- **Purpose:** Prevent duplicate votes on knowledge base articles

**GDPR Considerations:**

- ⚠️ **Requires cookie consent** under GDPR/ePrivacy
- Must be disclosed in the cookie policy
- Should be added to the cookie consent manager
- Considered as “Functional” or “Preference” cookie category
- No personal data is stored server-side

### IP Address Only (`ip`)

**How it works:**

- Stores **hashed** visitor IP address in post meta (SHA-256 with WordPress salt)
- Checks IP hash against stored list before allowing vote
- No cookies set
- **Privacy-friendly:** Original IP cannot be recovered from the hash

**Data Storage:**

- **Post Meta Key:** `_wzkb_rating_ips`
- **Data Type:** Array of SHA-256 hashed IP addresses
- **Example:** `['a3f5b...', 'c7d2e...']` (64-character hashes)
- **Hash Method:** `hash('sha256', $ip . wp_salt('nonce'))`

**GDPR Considerations:**

- ✅ **Pseudonymized data** under GDPR Article 4(5)
- ✅ **Cannot reverse** hash to obtain original IP
- ✅ **Reduced GDPR obligations** compared to raw IP storage
- ✅ **Privacy by design** – admins never see actual IPs
- Still requires privacy policy disclosure
- Lighter data protection requirements than raw IPs

### Cookie + IP Address (`cookie_ip`)

**How it works:**

- Combines both cookie and IP checking
- The user must pass both checks to vote
- Most reliable duplicate prevention

**GDPR Considerations:**

- Requires both cookie consent AND privacy policy disclosure
- Highest level of tracking
- Best for preventing abuse
- Consider using this setting for high-value content

### Logged-in Users Only (`logged_in_only`)

**How it works:**

- Only authenticated WordPress users can vote
- Stores WordPress user ID in post meta
- Shows a login prompt to guests

**Data Storage:**

- **Post Meta Key:** `_wzkb_rating_user_ids`
- **Data Type:** Array of WordPress user IDs
- **Example:** `[1, 5, 12]`

**GDPR Considerations:**

- ✅ Most GDPR-friendly for authenticated sites
- No cookies required for tracking
- User IDs are already part of WordPress data
- Clear data controller relationship
- Prevents anonymous voting

## Data Structure

### Post Meta Keys

The rating system stores the following data in post meta:

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Meta Key</th>
<th>Type</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>_wzkb_rating_total</code></td>
<td>Integer</td>
<td>Total number of votes</td>
</tr>
<tr>
<td><code>_wzkb_rating_sum</code></td>
<td>Integer</td>
<td>Sum of all rating values</td>
</tr>
<tr>
<td><code>_wzkb_rating_positive</code></td>
<td>Integer</td>
<td>Number of positive votes (binary mode)</td>
</tr>
<tr>
<td><code>_wzkb_ratings</code></td>
<td>Array</td>
<td>Individual rating entries with timestamps</td>
</tr>
<tr>
<td><code>_wzkb_rating_ips</code></td>
<td>Array</td>
<td>Hashed IP addresses (if IP tracking enabled)</td>
</tr>
<tr>
<td><code>_wzkb_rating_user_ids</code></td>
<td>Array</td>
<td>User IDs (if logged-in only mode)</td>
</tr>
<tr>
<td><code>_wzkb_rating_feedback</code></td>
<td>Array</td>
<td>User feedback entries (PRO feature)</td>
</tr>
<tr>
<td><code>_wzkb_average_rating</code></td>
<td>Float</td>
<td>Cached average rating (binary: 0-1, scale: 1-5)</td>
</tr>
<tr>
<td><code>_wzkb_positive_ratio</code></td>
<td>Float</td>
<td>Cached positive ratio normalized to 0-1</td>
</tr>
<tr>
<td><code>_wzkb_bayesian_rating</code></td>
<td>Float</td>
<td>Cached Bayesian score for intelligent scoring</td>
</tr>
</tbody>
</table>
</figure>

## Frontend Display

### Automatic Display

Ratings are automatically displayed at the bottom of single knowledge base articles via the `the_content` filter.

### Asset Loading

The plugin automatically loads minified assets (`.min.css` and `.min.js`) in production for optimal performance. To use unminified assets for debugging, add this to your `wp-config.php`:

```php
define( 'SCRIPT_DEBUG', true );
```

**Asset Files:**

- Production: `rating.min.css` and `rating.min.js` (minified)
- Development: `rating.css` and `rating.js` (complete source with comments)

### Manual Display

You can control the position using the filter:

```php
// Display rating before content.
add_filter( 'wzkb_rating_position', function( $position, $post_id ) {
    return 'before';
}, 10, 2 );
```

## AJAX Endpoint

**Action:** `wzkb_submit_rating`\
**Method:** POST\
**Nonce:** `wzkb_rating_nonce`

**Parameters:**

- `post_id` – Post ID to rate
- `rating` – Rating value (0-1 for binary, 1-5 for scale)
- `mode` – Rating mode (‘binary’ or ‘scale’)

**Response:**

```text
{
    "success": true,
    "data": {
        "message": "Thank you for your feedback!",
        "stats": {
            "total": 10,
            "sum": 42,
            "positive": 8,
            "average": 4.2
        },
        "display": "Average rating: 4.2 / 5 (10 votes)"
    }
}
```

## Admin Features

### Rating Column

The admin posts list includes a “Rating” column showing:

- **Binary mode:** Percentage helpful (e.g., “80% (10)”)
- **Scale mode:** Average rating (e.g., “4.2 / 5 (10)”)
- The column is sortable

### Bayesian Average Sorting

The rating column uses **Bayesian average** (weighted rating) for intelligent sorting that balances quality with quantity.

**How it works:**

*Weighted Score = (v / (v + m)) × R + (m / (v + m)) × C*\

Where:

- **v** = votes for this article
- **m** = minimum votes threshold (default: 10)
- **R** = this article’s rating
- **C** = global mean rating across all articles

**Why this matters:**

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Without Bayesian</th>
<th>With Bayesian</th>
</tr>
</thead>
<tbody>
<tr>
<td>Article A: 5.0 (2 votes) ranks #1</td>
<td>Article B: 4.5 (500 votes) ranks #1</td>
</tr>
<tr>
<td>Article B: 4.5 (500 votes) ranks #2</td>
<td>Article A: 5.0 (2 votes) ranks lower</td>
</tr>
</tbody>
</table>
</figure>

**Benefits:**

- ✅ Prevents new articles with few votes from dominating
- ✅ Rewards consistently good content with many votes
- ✅ Pulls low-vote articles toward the global average
- ✅ Same algorithm used by IMDb, Reddit, and Steam

**Performance:**

The Bayesian score is pre-calculated and stored in \`\_wzkb_bayesian_rating\` post meta when ratings are submitted. This eliminates the need for complex sorting calculations. Additionally, the global mean rating is cached for 1 hour to optimize the Bayesian calculation itself. The cache is automatically invalidated when any rating is submitted.

**Customization:**

Adjust the minimum votes threshold (higher = more conservative):

```php
// Require 25 votes before trusting the rating fully.
add_filter( 'wzkb_rating_bayesian_prior_weight', function( $prior_weight, $mode ) {
    return 25;
}, 10, 2 );
```

**Examples:**

With `m = 10` and the global mean of `70%`:

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Article</th>
<th>Votes</th>
<th>Raw Rating</th>
<th>Bayesian Score</th>
<th>Rank</th>
</tr>
</thead>
<tbody>
<tr>
<td>New Article</td>
<td>2</td>
<td>100%</td>
<td>78%</td>
<td>#3</td>
</tr>
<tr>
<td>Popular Article</td>
<td>500</td>
<td>85%</td>
<td>85%</td>
<td>#1</td>
</tr>
<tr>
<td>Established Article</td>
<td>50</td>
<td>80%</td>
<td>80%</td>
<td>#2</td>
</tr>
</tbody>
</table>
</figure>

### Hooks for Developers

#### Actions

```php
// Fires after a rating is stored.
do_action( 'wzkb_rating_stored', $post_id, $rating, $mode );
```

#### Filters

```php
// Change rating position.
apply_filters( 'wzkb_rating_position', 'after', $post_id );

// Adjust Bayesian prior weight (minimum votes threshold).
apply_filters( 'wzkb_rating_bayesian_prior_weight', 10, $mode );

// Customize rate limiting.
apply_filters( 'wzkb_rating_rate_limits', array( 'max_requests' => 10, 'time_window' => 60 ) );

// Enable proxy headers for IP detection (use with caution).
apply_filters( 'wzkb_rating_use_proxy_headers', false );
```

## Privacy & Data Retention

### Recommended Privacy Policy Text

Add this to your privacy policy when using the rating system:

#### For No Tracking Mode

**Article Ratings:** When you rate a knowledge base article, no personal information is collected or stored. You may rate articles multiple times.

#### For Cookie-Only Mode

**Article Ratings:** When you rate a knowledge base article, we store a cookie on your device (wzkb_rated\_\[article_id\]) to prevent duplicate votes. This cookie expires after 365 days. No personal information is collected or stored on our servers.

#### For IP Address Mode

**Article Ratings:** When you rate a knowledge base article, we store a pseudonymized identifier (cryptographic hash) derived from your IP address to prevent duplicate votes. This hash cannot be reversed to obtain your original IP address. The data is automatically limited to the most recent 10,000 votes per article for performance reasons.

#### For Logged-in Users Mode

**Article Ratings:** When you rate a knowledge base article, your user ID is stored with your rating to prevent duplicate votes. This data is associated with your WordPress account and will be deleted if you delete your account.

### Data Deletion

To delete all rating data for a specific post:

```php
// Delete primary rating data.
delete_post_meta( $post_id, '_wzkb_rating_total' );
delete_post_meta( $post_id, '_wzkb_rating_sum' );
delete_post_meta( $post_id, '_wzkb_rating_positive' );
delete_post_meta( $post_id, '_wzkb_ratings' );
delete_post_meta( $post_id, '_wzkb_rating_ips' );
delete_post_meta( $post_id, '_wzkb_rating_user_ids' );
delete_post_meta( $post_id, '_wzkb_rating_feedback' );

// Delete derived/cached data.
delete_post_meta( $post_id, '_wzkb_average_rating' );
delete_post_meta( $post_id, '_wzkb_positive_ratio' );
delete_post_meta( $post_id, '_wzkb_bayesian_rating' );
```

To delete rating data for a specific IP address:

```php
function wzkb_delete_ratings_by_ip( $ip_address ) {
    // Generate the same hash that would be stored.
    $ip_hash = hash( 'sha256', $ip_address . wp_salt( 'nonce' ) );
    
    $args = array(
        'post_type'      => 'wz_knowledgebase',
        'posts_per_page' => -1,
        'meta_key'       => '_wzkb_rating_ips',
    );
    
    $posts = get_posts( $args );
    
    foreach ( $posts as $post ) {
        $ip_hashes = get_post_meta( $post->ID, '_wzkb_rating_ips', true );
        if ( is_array( $ip_hashes ) ) {
            $ip_hashes = array_diff( $ip_hashes, array( $ip_hash ) );
            update_post_meta( $post->ID, '_wzkb_rating_ips', $ip_hashes );
        }
    }
}
```

**Note:** Since IPs are hashed, you need the original IP address to generate the matching hash for deletion.

## Styling & Customization

### CSS Classes

- `.wzkb-rating-container` – Main container
- `.wzkb-rating-header` – Header section
- `.wzkb-rating-buttons` – Button container
- `.wzkb-rating-btn` – Individual button (binary mode)
- `.wzkb-rating-useful` – Useful button
- `.wzkb-rating-not-useful` – Not useful button
- `.wzkb-rating-stars` – Star container (scale mode)
- `.wzkb-rating-star` – Individual star button
- `.wzkb-rating-thank-you` – Thank you message
- `.wzkb-rating-login-required` – Login required message
- `.wzkb-rating-stats` – Statistics display
- `.wzkb-rating-message` – AJAX message container

### Custom CSS Example

```css
/* Change button colors */
.wzkb-rating-useful {
    background: #00a32a !important;
}

.wzkb-rating-not-useful {
    background: #d63638 !important;
}

/* Change star color */
.wzkb-rating-star:hover,
.wzkb-rating-star.wzkb-star-hover {
    color: #f39c12 !important;
}
```

## Troubleshooting

### Ratings Not Appearing

1. Check that the rating system is enabled in settings
2. Verify you’re on a single KB article page
3. Check if the theme overrides the single template
4. Ensure JavaScript is not blocked

### Users Can Vote Multiple Times

1. Check the tracking method setting
2. Verify cookies are being set (check browser dev tools)
3. For IP tracking, ensure the server is passing the correct IP headers
4. Check if users are clearing cookies

### AJAX Errors

1. Verify nonce is being generated correctly
2. Check the browser console for JavaScript errors
3. Ensure jQuery is loaded
4. Check server error logs for PHP errors

## Performance Considerations

### Caching

The rating system works with page caching because:

- Initial HTML is static
- Vote checking happens via AJAX
- Cookie checking happens client-side

### Database Optimization

The rating system includes automatic optimizations:

- **Automatic array size limits:** Max 10,000 entries per tracking array (ratings, IP hashes, user IDs); configurable via `wzkb_rating_max_log_size`
- **Auto-cleanup:** Removes the oldest 10 % of entries when the limit is reached
- **Efficient storage:** SHA-256 hashes are 64 characters (vs up to 39 for IPv6)
- **Rate limiting:** 10 requests per 60 seconds per user/IP
- **Race condition prevention:** Transient-based locking for concurrent votes

For additional optimization, consider:

- Using object caching for rating stats
- Archiving old rating data for historical articles

### Customizing Array Size Limits

You can adjust the automatic cleanup limit using a filter. The same limit applies to `_wzkb_ratings`, `_wzkb_rating_ips`, and `_wzkb_rating_user_ids`. When the limit is reached, the oldest 10 % of entries are removed automatically.

```php
// Change the maximum array size (default: 10,000)
add_filter( 'wzkb_rating_max_log_size', function() {
    return 5000; // More aggressive cleanup for high-traffic sites
} );
```

### Customizing Rate Limits

```php
// Adjust rate limiting (default: 10 requests per 60 seconds)
add_filter( 'wzkb_rating_rate_limits', function( $limits ) {
    return array(
        'max_requests' => 5,   // Stricter limit
        'time_window'  => 120, // Longer window
    );
} );
```

## Security Features

### Built-in Security Protections

1. **CSRF Protection**
    - Nonce verification on all AJAX requests
    - WordPress nonce system integration
2. **Rate Limiting**
    - Default: 10 requests per 60 seconds per user/IP
    - Prevents abuse and stats manipulation
    - Configurable via `wzkb_rating_rate_limits` filter
3. **Input Validation**
    - All inputs sanitized and validated
    - Post status verification (only published posts)
    - Rating value range checks
    - Post type verification
4. **Race Condition Prevention**
    - Transient-based locking mechanism
    - Prevents concurrent vote conflicts
    - 5-second lock timeout with retry logic
5. **Integer Overflow Protection**
    - Maximum vote count: 2,147,483,647
    - Automatic limit enforcement
6. **IP Spoofing Prevention**
    - Prioritizes `REMOTE_ADDR` (cannot be spoofed)
    - Proxy headers are disabled by default
    - Optional proxy support via `wzkb_rating_use_proxy_headers` filter
7. **Cookie Security**
    - `SameSite=Lax` for CSRF protection
    - `Secure` flag on HTTPS sites
    - 365-day expiry
8. **Privacy by Design**
    - IP addresses hashed with SHA-256
    - WordPress salt added for uniqueness
    - Hashes cannot be reversed
    - Admins never see actual IPs

### For CDN/Proxy Users

If your site is behind a trusted proxy or CDN (Cloudflare, AWS CloudFront, etc.):

```php
// Enable proxy header usage (use with caution!)
add_filter( 'wzkb_rating_use_proxy_headers', '__return_true' );
```

**Warning:** Only enable this if you trust your proxy/CDN configuration. Improper use can allow IP spoofing.
