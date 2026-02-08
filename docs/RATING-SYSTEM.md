# Knowledge Base Rating System

The Knowledge Base plugin includes a comprehensive PRO rating system that allows visitors to rate the quality of articles. This document explains how the system works, configuration options, and GDPR compliance considerations.

## Overview

The rating system provides three modes:

1. __Disabled__ - No rating functionality
2. __Binary Rating__ - Useful / Not Useful buttons
3. __Scale Rating__ - 1-5 star rating system

## Configuration

### Admin Settings

Navigate to __Knowledge Base → Settings → Output__ to configure the rating system.

#### Rating System Mode

__Setting:__ `rating_system`  
__Options:__

- `disabled` - No rating system (default)
- `binary` - Useful / Not Useful buttons
- `scale` - 1-5 star rating

#### Vote Tracking Method

__Setting:__ `rating_tracking_method`  
__Options:__

- `none` - No Tracking (allows multiple votes)
- `cookie` - Cookie Only (default)
- `ip` - IP Address Only
- `cookie_ip` - Cookie + IP Address
- `logged_in_only` - Logged-in Users Only

## Tracking Methods & GDPR Compliance

### No Tracking (`none`)

__How it works:__

- No cookies set
- No personal data stored on server
- Uses __localStorage__ for session protection (prevents spam clicking within 1-hour session)
- Allows re-voting after session expiry or page reload after 1 hour
- Most privacy-friendly option

__Session Protection:__

- __Storage__: Browser localStorage with timestamp-based expiry
- __Duration__: 1 hour (configurable via `wzkb_rating_session_expiry` filter)
- __Behavior__: Prevents multiple votes within session, allows re-voting after expiry
- __Cleanup__: Automatically removes expired entries on page load or click attempt

__GDPR Considerations:__

- ✅ __No consent required__ (localStorage doesn't need consent)
- ✅ Perfect for GDPR compliance without cookie banners
- ✅ No privacy policy disclosure needed
- ✅ Session protection prevents abuse while maintaining privacy
- ⚠️ Allows vote manipulation after session expiry (by design)

### Cookie Only (`cookie`)

__How it works:__

- Sets a browser cookie when user votes
- Uses __localStorage__ for immediate session protection (prevents spam clicking)
- Cookie provides persistent tracking (365 days)
- No personal data stored on server
- Cookie can be cleared by user (allows re-voting)

__Session Protection:__

- __Storage__: Browser localStorage with timestamp-based expiry (1 hour)
- __Purpose__: Immediate session protection while cookie sets
- __Behavior__: Prevents spam clicking within session
- __Cleanup__: Automatically removes expired entries on page load

__Cookie Details:__

- __Name:__ `wzkb_rated_{post_id}` (e.g., `wzkb_rated_123`)
- __Value:__ `1`
- __Expiry:__ 365 days
- __Path:__ `/`
- __SameSite:__ `Lax`
- __Purpose:__ Prevent duplicate votes on knowledge base articles

__GDPR Considerations:__

- ⚠️ __Requires cookie consent__ under GDPR/ePrivacy
- Must be disclosed in cookie policy
- Should be added to cookie consent manager
- Consider as "Functional" or "Preference" cookie category
- No personal data stored server-side

__Cookie Consent Integration:__

```javascript
// Example: Only initialize rating if user has consented
if (userHasConsentedToCookies()) {
    // Rating system will work normally
}
```

### IP Address Only (`ip`)

__How it works:__

- Stores __hashed__ visitor IP address in post meta (SHA-256 with WordPress salt)
- Uses __localStorage__ for immediate session protection (prevents spam clicking)
- Checks IP hash against stored list before allowing vote
- No cookies set
- __Privacy-friendly:__ Original IP cannot be recovered from hash

__Session Protection:__

- __Storage__: Browser localStorage with timestamp-based expiry (1 hour)
- __Purpose__: Immediate session protection while IP check processes
- __Behavior__: Prevents spam clicking within session
- __Cleanup__: Automatically removes expired entries on page load

__Data Storage:__

- __Post Meta Key:__ `_wzkb_rating_ips`
- __Data Type:__ Array of SHA-256 hashed IP addresses
- __Example:__ `['a3f5b...', 'c7d2e...']` (64-character hashes)
- __Hash Method:__ `hash('sha256', $ip . wp_salt('nonce'))`

__GDPR Considerations:__

- ✅ __Pseudonymized data__ under GDPR Article 4(5)
- ✅ __Cannot reverse__ hash to obtain original IP
- ✅ __Reduced GDPR obligations__ compared to raw IP storage
- ✅ __Privacy by design__ - admins never see actual IPs
- Still requires privacy policy disclosure
- Lighter data protection requirements than raw IPs

### Cookie + IP Address (`cookie_ip`)

__How it works:__

- Combines both cookie and IP checking
- Uses __localStorage__ for immediate session protection (prevents spam clicking)
- User must pass both checks to vote
- Most reliable duplicate prevention

__Session Protection:__

- __Storage__: Browser localStorage with timestamp-based expiry (1 hour)
- __Purpose__: Immediate session protection while cookie+IP checks process
- __Behavior__: Prevents spam clicking within session
- __Cleanup__: Automatically removes expired entries on page load

__GDPR Considerations:__

- Requires both cookie consent AND privacy policy disclosure
- Highest level of tracking
- Best for preventing abuse
- Consider for high-value content

### Logged-in Users Only (`logged_in_only`)

__How it works:__

- Only authenticated WordPress users can vote
- Uses __localStorage__ for immediate session protection (prevents spam clicking)
- Stores WordPress user ID in post meta for persistent tracking
- Shows login prompt to guests

__Session Protection:__

- __Storage__: Browser localStorage with timestamp-based expiry (1 hour)
- __Purpose__: Immediate session protection while user ID check processes
- __Behavior__: Prevents spam clicking within session
- __Cleanup__: Automatically removes expired entries on page load

__Data Storage:__

- __Post Meta Key:__ `_wzkb_rating_user_ids`
- __Data Type:__ Array of WordPress user IDs
- __Example:__ `[1, 5, 12]`

__GDPR Considerations:__

- ✅ Most GDPR-friendly for authenticated sites
- No cookies required for tracking
- User IDs already part of WordPress data
- Clear data controller relationship
- Prevents anonymous voting

## Data Structure

### Post Meta Keys

The rating system stores the following data in post meta:

#### Primary Data

| Meta Key | Type | Description |
|----------|------|-------------|
| `_wzkb_rating_total` | Integer | Total number of votes |
| `_wzkb_rating_sum` | Integer | Sum of all rating values (scale mode only) |
| `_wzkb_rating_positive` | Integer | Number of positive votes (binary mode only) |
| `_wzkb_ratings` | Array | Individual rating entries with timestamps |
| `_wzkb_rating_ips` | Array | Hashed IP addresses (if IP tracking enabled) |
| `_wzkb_rating_user_ids` | Array | User IDs (if logged-in only mode) |
| `_wzkb_rating_feedback` | Array | User feedback entries (PRO feature) |

#### Derived/Cached Data (for performance)

| Meta Key | Type | Description |
|----------|------|-------------|
| `_wzkb_average_rating` | Float | Cached average rating (binary: 0-1, scale: 1-5) |
| `_wzkb_positive_ratio` | Float | Cached positive ratio normalized to 0-1 |
| `_wzkb_bayesian_rating` | Float | Cached Bayesian score for intelligent sorting |

### Individual Rating Entry Structure

```php
array(
    'rating'    => 1,              // Rating value (0-1 for binary, 1-5 for scale)
    'mode'      => 'binary',       // Rating mode ('binary' or 'scale')
    'timestamp' => 1697385600,     // Unix timestamp
)
```

__Note:__ IP addresses and user IDs are stored separately in `_wzkb_rating_ips` and `_wzkb_rating_user_ids` meta keys respectively, not within individual rating entries.

## Frontend Display

### Automatic Display

Ratings are automatically displayed at the bottom of single knowledge base articles via the `the_content` filter.

### Asset Loading

The plugin automatically loads minified assets (`.min.css` and `.min.js`) in production for optimal performance. To use unminified assets for debugging, add this to your `wp-config.php`:

```php
define( 'SCRIPT_DEBUG', true );
```

__Asset Files:__

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

## AJAX Endpoint

__Action:__ `wzkb_submit_rating`  
__Method:__ POST  
__Nonce:__ `wzkb_rating_nonce`

__Parameters:__

- `post_id` - Post ID to rate
- `rating` - Rating value (0-1 for binary, 1-5 for scale)
- `mode` - Rating mode ('binary' or 'scale')

__Response:__

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

- __Binary mode:__ Percentage helpful (e.g., "80% (10)")
- __Scale mode:__ Average rating (e.g., "4.2 / 5 (10)")
- Column is sortable

### Bayesian Average Sorting

The rating system uses a Bayesian average (weighted rating) algorithm to intelligently sort articles by rating in the admin column. This prevents articles with very few votes from dominating the rankings.

__Formula:__

```text
Weighted Score = (v × R + m × C) / (v + m)
```

Where:

- __v__ = votes for this article
- __m__ = minimum votes threshold (default: 10)
- __R__ = this article's rating
- __C__ = global mean rating across all articles

__Why This Matters:__

| Without Bayesian | With Bayesian |
|------------------|---------------|
| Article A: 5.0 (2 votes) ranks #1 | Article B: 4.5 (500 votes) ranks #1 |
| Article B: 4.5 (500 votes) ranks #2 | Article A: 5.0 (2 votes) ranks lower |

__Benefits:__

- ✅ Prevents new articles with few votes from dominating
- ✅ Rewards consistently good content with many votes
- ✅ Pulls low-vote articles toward the global average
- ✅ Same algorithm used by IMDb, Reddit, and Steam

__Performance:__

The Bayesian score is pre-calculated and stored in `_wzkb_bayesian_rating` post meta when ratings are submitted. This eliminates the need for complex calculations during sorting. Additionally, the global mean rating is cached for 1 hour to optimize the Bayesian calculation itself. The cache is automatically invalidated when any rating is submitted.

__Customization:__

Adjust the prior weight threshold (higher = more conservative):

```php
// Require 25 votes before trusting the rating fully
add_filter( 'wzkb_rating_bayesian_prior_weight', function( $prior_weight, $mode ) {
    return 25;
}, 10, 2 );
```

__Examples:__

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

// Adjust Bayesian prior weight (minimum votes threshold)
apply_filters( 'wzkb_rating_bayesian_prior_weight', 10, $mode );

// Customize rate limiting
apply_filters( 'wzkb_rating_rate_limits', array( 'max_requests' => 10, 'time_window' => 60 ) );

// Enable proxy headers for IP detection (use with caution)
apply_filters( 'wzkb_rating_use_proxy_headers', false );
```

## Privacy & Data Retention

### Recommended Privacy Policy Text

Add this to your privacy policy when using the rating system:

#### For No Tracking Mode

__Article Ratings:__ When you rate a knowledge base article, no personal information is collected or stored. You may rate articles multiple times.

#### For Cookie-Only Mode

__Article Ratings:__ When you rate a knowledge base article, we store a cookie
on your device (wzkb_rated_[article_id]) to prevent duplicate votes. This cookie
expires after 365 days. No personal information is collected or stored on our servers.

#### For IP Address Mode

__Article Ratings:__ When you rate a knowledge base article, we store a pseudonymized
identifier (cryptographic hash) derived from your IP address to prevent duplicate votes.
This hash cannot be reversed to obtain your original IP address. The data is automatically
limited to the most recent 10,000 votes per article for performance reasons.

#### For Logged-in Users Mode

__Article Ratings:__ When you rate a knowledge base article, your user ID is stored
with your rating to prevent duplicate votes. This data is associated with your
WordPress account and will be deleted if you delete your account.

### Data Deletion

To delete all rating data for a specific post:

```php
// Delete primary rating data
delete_post_meta( $post_id, '_wzkb_rating_total' );
delete_post_meta( $post_id, '_wzkb_rating_sum' );
delete_post_meta( $post_id, '_wzkb_rating_positive' );
delete_post_meta( $post_id, '_wzkb_ratings' );
delete_post_meta( $post_id, '_wzkb_rating_ips' );
delete_post_meta( $post_id, '_wzkb_rating_user_ids' );
delete_post_meta( $post_id, '_wzkb_rating_feedback' );

// Delete derived/cached data
delete_post_meta( $post_id, '_wzkb_average_rating' );
delete_post_meta( $post_id, '_wzkb_positive_ratio' );
delete_post_meta( $post_id, '_wzkb_bayesian_rating' );
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

__Note:__ Since IPs are hashed, you need the original IP address to generate the matching hash for deletion.

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
5. __For "none" tracking mode__: Multiple votes after 1 hour is expected behavior - localStorage expires automatically

### LocalStorage Not Working

1. Open browser dev tools → Application → Local Storage
2. Look for `wzkb_rated_*` keys (e.g., `wzkb_rated_123`)
3. Check key value contains JSON with timestamp and expiry
4. Verify timestamp is in the future (not expired)
5. Check browser console for JavaScript errors
6. Ensure localStorage is not disabled (private browsing mode)

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

- __Automatic array size limits:__ Max 10,000 IP hashes per article
- __Auto-cleanup:__ Removes oldest 1,000 entries when limit reached
- __Efficient storage:__ SHA-256 hashes are 64 characters (vs up to 39 for IPv6)
- __Rate limiting:__ 10 requests per 60 seconds per user/IP
- __Race condition prevention:__ Transient-based locking for concurrent votes

For additional optimization, consider:

- Using object caching for rating stats
- Archiving old rating data for historical articles

__Note:__ The array size limits (10,000 max, 1,000 cleanup) are hardcoded and cannot be customized via filters.

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

1. __CSRF Protection__
   - Nonce verification on all AJAX requests
   - WordPress nonce system integration

2. __Rate Limiting__
   - Default: 10 requests per 60 seconds per user/IP
   - Prevents abuse and stats manipulation
   - Configurable via `wzkb_rating_rate_limits` filter

3. __Input Validation__
   - All inputs sanitized and validated
   - Post status verification (only published posts)
   - Rating value range checks
   - Post type verification
   - Client-side postId validation for localStorage operations

4. __Race Condition Prevention__
   - Transient-based locking mechanism
   - Prevents concurrent vote conflicts
   - 5-second lock timeout with retry logic

5. __Integer Overflow Protection__
   - Maximum vote count: 2,147,483,647
   - Automatic limit enforcement

6. __IP Spoofing Prevention__
   - Prioritizes `REMOTE_ADDR` (cannot be spoofed)
   - Proxy headers disabled by default
   - Optional proxy support via `wzkb_rating_use_proxy_headers` filter

7. __Cookie Security__
   - `SameSite=Lax` for CSRF protection
   - `Secure` flag on HTTPS sites
   - 365-day expiry

8. __Privacy by Design__
   - IP addresses hashed with SHA-256
   - WordPress salt added for uniqueness
   - Hashes cannot be reversed
   - Admins never see actual IPs

### Client-Side Session Protection

1. __LocalStorage Security__
   - Universal session protection across all tracking methods
   - Timestamp-based expiry (default: 1 hour)
   - Automatic cleanup of expired entries
   - Input validation prevents invalid data corruption
   - Graceful degradation when localStorage disabled

2. __JavaScript Hardening__
    - Dead code removal reduces attack surface
    - Consistent error handling prevents UI inconsistencies
    - Input validation on all localStorage operations
    - Try-catch blocks around localStorage access
    - XSS prevention via proper HTML escaping

3. __Feedback Form Security__
    - Textarea input sanitization on server-side
    - Client-side validation before submission
    - CSRF protection via nonce verification
    - Accessibility features (focus management)

### For CDN/Proxy Users

If your site is behind a trusted proxy or CDN (Cloudflare, AWS CloudFront, etc.):

```php
// Enable proxy header usage (use with caution!)
add_filter( 'wzkb_rating_use_proxy_headers', '__return_true' );
```

__Warning:__ Only enable this if you trust your proxy/CDN configuration. Improper use can allow IP spoofing.

## Changelog

### Version 3.0.0

- Initial release of rating system
- Three rating modes (disabled, binary, scale)
- Five tracking methods (none, cookie, IP, cookie+IP, logged-in only)
- __IP hashing for GDPR compliance__ (pseudonymization)
- __Enterprise-grade security__ (rate limiting, race condition prevention, overflow protection)
- GDPR compliance options with detailed documentation
- Admin column with sortable ratings
- Responsive design with mobile support
- Accessibility features (ARIA labels, screen reader support)
- Automatic array size limits (10,000 entries with auto-cleanup)
