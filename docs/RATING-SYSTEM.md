# Knowledge Base Rating System

The Knowledge Base plugin includes a comprehensive PRO rating system that allows visitors to rate the quality of articles. This document explains how the system works, configuration options, and GDPR compliance considerations.

## Overview

The rating system provides three modes:

1. **Disabled** - No rating functionality
2. **Binary Rating** - Useful / Not Useful buttons
3. **Scale Rating** - 1-5 star rating system

## Configuration

### Admin Settings

Navigate to **Knowledge Base → Settings → Output** to configure the rating system.

#### Rating System Mode

**Setting:** `rating_system`  
**Options:**

- `disabled` - No rating system (default)
- `binary` - Useful / Not Useful buttons
- `scale` - 1-5 star rating

#### Vote Tracking Method

**Setting:** `rating_tracking_method`  
**Options:**

- `none` - No Tracking (allows multiple votes)
- `cookie` - Cookie Only (default)
- `ip` - IP Address Only
- `cookie_ip` - Cookie + IP Address
- `logged_in_only` - Logged-in Users Only

## Tracking Methods & GDPR Compliance

### No Tracking (`none`)

**How it works:**

- No cookies set
- No personal data stored
- Allows unlimited votes from same visitor
- Most privacy-friendly option

**GDPR Considerations:**

- ✅ **No consent required**
- ✅ Perfect for GDPR compliance without cookie banners
- ✅ No privacy policy disclosure needed
- ⚠️ Allows vote manipulation (use for low-stakes feedback)

### Cookie Only (`cookie`)

**How it works:**

- Sets a browser cookie when user votes
- No personal data stored on server
- Cookie can be cleared by user (allows re-voting)

**Cookie Details:**

- **Name:** `wzkb_rated_{post_id}` (e.g., `wzkb_rated_123`)
- **Value:** `1`
- **Expiry:** 365 days
- **Path:** `/`
- **SameSite:** `Lax`
- **Purpose:** Prevent duplicate votes on knowledge base articles

**GDPR Considerations:**

- ⚠️ **Requires cookie consent** under GDPR/ePrivacy
- Must be disclosed in cookie policy
- Should be added to cookie consent manager
- Consider as "Functional" or "Preference" cookie category
- No personal data stored server-side

**Cookie Consent Integration:**

```javascript
// Example: Only initialize rating if user has consented
if (userHasConsentedToCookies()) {
    // Rating system will work normally
}
```

### IP Address Only (`ip`)

**How it works:**

- Stores **hashed** visitor IP address in post meta (SHA-256 with WordPress salt)
- Checks IP hash against stored list before allowing vote
- No cookies set
- **Privacy-friendly:** Original IP cannot be recovered from hash

**Data Storage:**

- **Post Meta Key:** `_wzkb_rating_ips`
- **Data Type:** Array of SHA-256 hashed IP addresses
- **Example:** `['a3f5b...', 'c7d2e...']` (64-character hashes)
- **Hash Method:** `hash('sha256', $ip . wp_salt('nonce'))`

**GDPR Considerations:**

- ✅ **Pseudonymized data** under GDPR Article 4(5)
- ✅ **Cannot reverse** hash to obtain original IP
- ✅ **Reduced GDPR obligations** compared to raw IP storage
- ✅ **Privacy by design** - admins never see actual IPs
- Still requires privacy policy disclosure
- Lighter data protection requirements than raw IPs

### Cookie + IP Address (`cookie_ip`)

**How it works:**

- Combines both cookie and IP checking
- User must pass both checks to vote
- Most reliable duplicate prevention

**GDPR Considerations:**

- Requires both cookie consent AND privacy policy disclosure
- Highest level of tracking
- Best for preventing abuse
- Consider for high-value content

### Logged-in Users Only (`logged_in_only`)

**How it works:**

- Only authenticated WordPress users can vote
- Stores WordPress user ID in post meta
- Shows login prompt to guests

**Data Storage:**

- **Post Meta Key:** `_wzkb_rating_user_ids`
- **Data Type:** Array of WordPress user IDs
- **Example:** `[1, 5, 12]`

**GDPR Considerations:**

- ✅ Most GDPR-friendly for authenticated sites
- No cookies required for tracking
- User IDs already part of WordPress data
- Clear data controller relationship
- Prevents anonymous voting

## Data Structure

### Post Meta Keys

The rating system stores the following data in post meta:

| Meta Key | Type | Description |
|----------|------|-------------|
| `_wzkb_rating_total` | Integer | Total number of votes |
| `_wzkb_rating_sum` | Integer | Sum of all rating values |
| `_wzkb_rating_positive` | Integer | Number of positive votes (binary mode) |
| `_wzkb_ratings` | Array | Individual rating entries with timestamps |
| `_wzkb_rating_ips` | Array | Hashed IP addresses (if IP tracking enabled) |
| `_wzkb_rating_user_ids` | Array | User IDs (if logged-in only mode) |
| `_wzkb_rating_feedback` | Array | User feedback entries (PRO feature) |

### Individual Rating Entry Structure

```php
array(
    'rating'    => 1,              // Rating value (0-1 for binary, 1-5 for scale)
    'timestamp' => 1697385600,     // Unix timestamp
    'ip'        => '192.168.1.1',  // IP address (if IP tracking enabled)
    'user_id'   => 5,              // User ID (if logged-in only mode)
)
```

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
- Development: `rating.css` and `rating.js` (full source with comments)

### Manual Display

You can control the position using the filter:

```php
// Display rating before content
add_filter( 'wzkb_rating_position', function( $position, $post_id ) {
    return 'before';
}, 10, 2 );
```

### Disable for Specific Articles

```php
// Disable rating for specific post
add_filter( 'the_content', function( $content ) {
    if ( is_singular( 'wz_knowledgebase' ) && get_the_ID() === 123 ) {
        remove_filter( 'the_content', array( $GLOBALS['wzkb_pro_rating'], 'add_rating_to_content' ), 20 );
    }
    return $content;
}, 1 );
```

## AJAX Endpoint

**Action:** `wzkb_submit_rating`  
**Method:** POST  
**Nonce:** `wzkb_rating_nonce`

**Parameters:**

- `post_id` - Post ID to rate
- `rating` - Rating value (0-1 for binary, 1-5 for scale)
- `mode` - Rating mode ('binary' or 'scale')

**Response:**

```json
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

The admin posts list includes a "Rating" column showing:

- **Binary mode:** Percentage helpful (e.g., "80% (10)")
- **Scale mode:** Average rating (e.g., "4.2 / 5 (10)")
- Column is sortable

### Bayesian Average Sorting

The rating column uses **Bayesian average** (weighted rating) for intelligent sorting that balances quality with quantity.

**How It Works:**

```text
Weighted Score = (v / (v + m)) × R + (m / (v + m)) × C
```

Where:

- **v** = votes for this article
- **m** = minimum votes threshold (default: 10)
- **R** = this article's rating
- **C** = global mean rating across all articles

**Why This Matters:**

| Without Bayesian | With Bayesian |
|------------------|---------------|
| Article A: 5.0 (2 votes) ranks #1 | Article B: 4.5 (500 votes) ranks #1 |
| Article B: 4.5 (500 votes) ranks #2 | Article A: 5.0 (2 votes) ranks lower |

**Benefits:**

- ✅ Prevents new articles with few votes from dominating
- ✅ Rewards consistently good content with many votes
- ✅ Pulls low-vote articles toward the global average
- ✅ Same algorithm used by IMDb, Reddit, and Steam

**Performance:**

The global mean rating is cached for 1 hour to avoid expensive database queries on every page load. The cache is automatically invalidated when any rating is submitted.

**Customization:**

Adjust the minimum votes threshold (higher = more conservative):

```php
// Require 25 votes before trusting the rating fully
add_filter( 'wzkb_rating_bayesian_min_votes', function( $min_votes, $rating_mode ) {
    return 25;
}, 10, 2 );
```

**Examples:**

With `m = 10` and global mean of `70%`:

| Article | Votes | Raw Rating | Bayesian Score | Rank |
|---------|-------|------------|----------------|------|
| New Article | 2 | 100% | 78% | #3 |
| Popular Article | 500 | 85% | 85% | #1 |
| Established Article | 50 | 80% | 80% | #2 |

### Hooks for Developers

#### Actions

```php
// Fires after a rating is stored
do_action( 'wzkb_rating_stored', $post_id, $rating, $mode );
```

#### Filters

```php
// Change rating position
apply_filters( 'wzkb_rating_position', 'after', $post_id );

// Modify related articles arguments (existing filter)
apply_filters( 'wzkb_related_articles_args', array(), $post_id );
```

## Privacy & Data Retention

### Recommended Privacy Policy Text

Add this to your privacy policy when using the rating system:

#### For No Tracking Mode

**Article Ratings:** When you rate a knowledge base article, no personal information is collected or stored. You may rate articles multiple times.

#### For Cookie-Only Mode

**Article Ratings:** When you rate a knowledge base article, we store a cookie
on your device (wzkb_rated_[article_id]) to prevent duplicate votes. This cookie
expires after 365 days. No personal information is collected or stored on our servers.

#### For IP Address Mode

**Article Ratings:** When you rate a knowledge base article, we store a pseudonymized
identifier (cryptographic hash) derived from your IP address to prevent duplicate votes.
This hash cannot be reversed to obtain your original IP address. The data is automatically
limited to the most recent 10,000 votes per article for performance reasons.

#### For Logged-in Users Mode

**Article Ratings:** When you rate a knowledge base article, your user ID is stored
with your rating to prevent duplicate votes. This data is associated with your
WordPress account and will be deleted if you delete your account.

### Data Deletion

To delete all rating data for a specific post:

```php
delete_post_meta( $post_id, '_wzkb_rating_total' );
delete_post_meta( $post_id, '_wzkb_rating_sum' );
delete_post_meta( $post_id, '_wzkb_rating_positive' );
delete_post_meta( $post_id, '_wzkb_ratings' );
delete_post_meta( $post_id, '_wzkb_rating_ips' );
delete_post_meta( $post_id, '_wzkb_rating_user_ids' );
```

To delete rating data for a specific IP address:

```php
function wzkb_delete_ratings_by_ip( $ip_address ) {
    // Generate the same hash that would be stored
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

## Cookie Consent Manager Integration

### Popular Cookie Consent Plugins

#### CookieYes / GDPR Cookie Consent

```javascript
// Add to your theme's JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Check if CookieYes is loaded
    if (typeof CookieYes !== 'undefined') {
        CookieYes.on('consent_update', function(consent) {
            if (consent.functional) {
                // User has consented to functional cookies
                // Rating system will work normally
            }
        });
    }
});
```

#### Complianz GDPR/CCPA

Add `wzkb_rated_*` to the list of functional cookies in Complianz settings.

#### Cookie Notice & Compliance

Configure the plugin to include the rating cookie pattern in the functional category.

## Styling & Customization

### CSS Classes

- `.wzkb-rating-container` - Main container
- `.wzkb-rating-header` - Header section
- `.wzkb-rating-buttons` - Button container
- `.wzkb-rating-btn` - Individual button (binary mode)
- `.wzkb-rating-useful` - Useful button
- `.wzkb-rating-not-useful` - Not useful button
- `.wzkb-rating-stars` - Star container (scale mode)
- `.wzkb-rating-star` - Individual star button
- `.wzkb-rating-thank-you` - Thank you message
- `.wzkb-rating-login-required` - Login required message
- `.wzkb-rating-stats` - Statistics display
- `.wzkb-rating-message` - AJAX message container

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

1. Check that rating system is enabled in settings
2. Verify you're on a single KB article page
3. Check if theme overrides the single template
4. Ensure JavaScript is not blocked

### Users Can Vote Multiple Times

1. Check tracking method setting
2. Verify cookies are being set (check browser dev tools)
3. For IP tracking, ensure server is passing correct IP headers
4. Check if users are clearing cookies

### AJAX Errors

1. Verify nonce is being generated correctly
2. Check browser console for JavaScript errors
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

- **Automatic array size limits:** Max 10,000 IP hashes per article
- **Auto-cleanup:** Removes oldest 1,000 entries when limit reached
- **Efficient storage:** SHA-256 hashes are 64 characters (vs up to 39 for IPv6)
- **Rate limiting:** 10 requests per 60 seconds per user/IP
- **Race condition prevention:** Transient-based locking for concurrent votes

For additional optimization, consider:

- Using object caching for rating stats
- Archiving old rating data for historical articles

### Customizing Array Size Limits

You can adjust the automatic cleanup limits using filters:

```php
// Change IP hash array limit (default: 10,000)
add_filter( 'wzkb_rating_ip_array_limit', function() {
    return 5000; // More aggressive cleanup for high-traffic sites
} );

// Change cleanup amount (default: removes 1,000 oldest)
add_filter( 'wzkb_rating_ip_cleanup_amount', function() {
    return 2000; // Remove more entries when limit reached
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

## Support

For issues or questions about the rating system:

- GitHub: <https://github.com/WebberZone/knowledgebase/issues>
- Support Forum: <https://wordpress.org/support/plugin/knowledgebase>
- Documentation: <https://webberzone.com/support/knowledgebase/>

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
   - Proxy headers disabled by default
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

## Changelog

### Version 3.0.0

- Initial release of rating system
- Three rating modes (disabled, binary, scale)
- Five tracking methods (none, cookie, IP, cookie+IP, logged-in only)
- **IP hashing for GDPR compliance** (pseudonymization)
- **Enterprise-grade security** (rate limiting, race condition prevention, overflow protection)
- GDPR compliance options with detailed documentation
- Admin column with sortable ratings
- Responsive design with mobile support
- Accessibility features (ARIA labels, screen reader support)
- Automatic array size limits (10,000 entries with auto-cleanup)
