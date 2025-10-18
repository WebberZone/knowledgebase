# Beacon Help Widget

The Beacon is a floating help widget that provides self-service support with search, suggested articles, and a contact form.

## Features

### Core Functionality

- **Floating Button** - Customizable button that sits at the bottom right (or left) of the screen
- **Self-Service Search** - Search knowledge base articles with debounced AJAX search (500ms)
- **Smart Suggested Articles** - Context-aware articles using Related class for KB posts, recent articles elsewhere
- **Contact Form** - HTML email contact with beautiful template when articles don't help
- **Built-in Spam Protection** - Honeypot field plus anonymous rate limiting without storing IPs
- **Stacked Navigation** - Mobile-app-style layered interface for smooth UX
- **Responsive Design** - Works beautifully on desktop, tablet, and mobile devices
- **Fixed Search Box** - Search box locked at bottom using CSS Grid for optimal UX
- **Related Articles Integration** - Reuses existing Related class logic (DRY principle)

### Design Features

- **Customizable Appearance** - Colors, position, button style, and labels
- **Dark Mode Support** - Automatically adapts to user's color scheme preference
- **Smooth Animations** - Polished transitions and hover effects
- **Accessibility** - Keyboard navigation, focus states, and ARIA labels
- **Modern UI** - Clean, professional design with rounded corners and shadows

## Configuration

### Admin Settings

Navigate to **Knowledge Base > Settings > Output** and scroll to the **Beacon Help Widget** section.

#### Basic Settings

- **Enable Beacon** - Turn the beacon on/off
- **Button Position** - Choose bottom right or bottom left
- **Button Style** - Icon only, text only, or icon + text
- **Button Text** - Custom text for the button (default: "Help")
- **Beacon Color** - Primary color for button and UI elements (default: #617DEC)

#### Content Settings

- **Greeting Message** - Welcome message when beacon opens (default: "Hi! How can we help you?")
- **Search Placeholder** - Placeholder text for search input (default: "Search for answers...")

#### Contact Form Settings

- **Enable Contact Form** - Allow visitors to send messages
- **Contact Email** - Email address for form submissions (default: admin email)

#### Display Options

- **Display Location** - Choose where beacon appears:
  - **Knowledge Base Only** (default) - Shows only on KB pages (singles, archives, taxonomies)
  - **Entire Site** - Shows sitewide
- **Show on Mobile** - Display beacon on mobile devices
- **Enable Animations** - Smooth animations and transitions

## JavaScript API

Control the beacon programmatically using the global `WZKBBeacon()` function.

### Methods

#### Open Beacon

```javascript
WZKBBeacon('open');
```

#### Close Beacon

```javascript
WZKBBeacon('close');
```

#### Toggle Beacon

```javascript
WZKBBeacon('toggle');
```

#### Search

```javascript
WZKBBeacon('search', 'installation guide');
```

#### Navigate to Screen

```javascript
WZKBBeacon('navigate', 'contact'); // 'home', 'search', or 'contact'
```

#### Open Contact Form

```javascript
WZKBBeacon('contact');
```

### Events

Listen to beacon events using jQuery:

```javascript
// Beacon opened
$(document).on('wzkb-beacon-opened', function() {
    console.log('Beacon opened');
});

// Beacon closed
$(document).on('wzkb-beacon-closed', function() {
    console.log('Beacon closed');
});

// Screen changed
$(document).on('wzkb-beacon-screen-changed', function(event, screenName) {
    console.log('Navigated to:', screenName);
});

// Search performed
$(document).on('wzkb-beacon-search', function(event, query) {
    console.log('Searched for:', query);
});

// Contact form opened
$(document).on('wzkb-beacon-contact-opened', function() {
    console.log('Contact form opened');
});

// Contact form submitted
$(document).on('wzkb-beacon-contact-submitted', function(event, formData) {
    console.log('Form submitted:', formData);
});
```

## Layout & Design

### CSS Grid Architecture

The beacon uses CSS Grid for a robust, modern layout:

```css
.wzkb-beacon-content {
    display: grid;
    grid-template-rows: 1fr auto;
    overflow: hidden;
}

.wzkb-beacon-scrollable {
    overflow-y: auto;
    overflow-x: hidden;
    min-height: 0;
    word-wrap: break-word;
}

.wzkb-beacon-search-box {
    /* Fixed at bottom */
    padding-top: 16px;
    border-top: 1px solid var(--wzkb-beacon-border);
}
```

**Benefits:**

- Search box always visible at bottom
- Articles scroll independently
- No horizontal scrollbar
- Content properly constrained to panel width
- Responsive and accessible

### Search Box Positioning

The search box appears at the bottom of:

1. **Home Screen** - Below suggested articles and contact button
2. **Search Results Screen** - Below search results

Both use the same CSS Grid layout ensuring the search box is always accessible without scrolling.

## Customization

### Customizing Labels and Strings

All beacon labels can be customized using the `wzkb_beacon_labels` filter:

```php
add_filter( 'wzkb_beacon_labels', function( $labels ) {
    // Customize any label
    $labels['greeting'] = 'Welcome! How may we assist you?';
    $labels['searchPlaceholder'] = 'Type your question...';
    $labels['contactButton'] = 'Contact Support';
    $labels['noResultsMessage'] = 'Can\'t find what you need? Get in touch!';
    $labels['suggestedArticles'] = 'Recommended Reading';
    $labels['searchResults'] = 'Results';
    $labels['contactFormTitle'] = 'Get Help';
    $labels['submitButton'] = 'Submit';
    $labels['successMessage'] = 'Thanks! We\'ll respond soon.';
    
    return $labels;
} );
```

**Available Labels:**

- `greeting` - Welcome message on home screen
- `searchPlaceholder` - Search input placeholder
- `searchButton` - Search button text
- `contactButton` - Contact button text
- `backButton` - Back button text
- `closeButton` - Close button text
- `noResults` - No results heading
- `noResultsMessage` - Message shown when no search results found
- `suggestedArticles` - Suggested articles heading
- `searchResults` - Search results heading
- `contactFormTitle` - Contact form heading
- `nameLabel` - Name field label
- `emailLabel` - Email field label
- `subjectLabel` - Subject field label
- `messageLabel` - Message field label
- `submitButton` - Submit button text
- `successMessage` - Success message after form submission
- `errorMessage` - Error message
- `requiredField` - Required field validation message

### Suggested Articles

#### How It Works

The beacon intelligently suggests articles based on context:

1. **On KB Articles** - Shows related articles using the Related class (same categories/tags)
2. **On Other Pages** - Shows recent KB articles
3. **Custom Override** - Use filter to provide specific articles

#### Custom Suggested Articles

Control which articles are suggested using the `wzkb_beacon_suggested_articles` filter:

```php
add_filter( 'wzkb_beacon_suggested_articles', function( $article_ids, $current_post_id ) {
    // Suggest specific articles based on current page
    if ( is_page( 'pricing' ) ) {
        return array( 123, 456, 789 ); // Article IDs
    }
    
    // For product pages, show product-specific articles
    if ( is_singular( 'product' ) ) {
        $product_id = get_post_meta( get_the_ID(), '_related_kb_articles', true );
        return $product_id ? explode( ',', $product_id ) : array();
    }
    
    return $article_ids; // Use default behavior
}, 10, 2 );
```

#### Customize Related Articles Query

For KB articles, customize the related articles query:

```php
add_filter( 'wzkb_related_articles_query_args', function( $args, $post ) {
    // Show more related articles in beacon
    $args['posts_per_page'] = 10;
    
    // Order by popularity instead of random
    $args['orderby'] = 'meta_value_num';
    $args['meta_key'] = 'views_count';
    
    return $args;
}, 10, 2 );
```

### Beacon Visibility

Control where the beacon appears using the `wzkb_beacon_show` filter:

```php
add_filter( 'wzkb_beacon_show', function( $show ) {
    // Hide beacon on specific pages
    if ( is_page( 'checkout' ) ) {
        return false;
    }
    
    return $show;
} );
```

### Custom Actions

Hook into beacon events:

```php
// After contact form submission
add_action( 'wzkb_beacon_contact_submitted', function( $name, $email, $subject, $message ) {
    // Send to CRM, log to database, etc.
    error_log( "Beacon contact from: $name ($email)" );
}, 10, 4 );
```

## Styling

### CSS Variables

Customize the beacon appearance using CSS variables:

```css
#wzkb-beacon-container {
    --wzkb-beacon-color: #617DEC;
    --wzkb-beacon-text-color: #1a1a1a;
    --wzkb-beacon-bg: #ffffff;
    --wzkb-beacon-border: #e5e7eb;
    --wzkb-beacon-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    --wzkb-beacon-radius: 12px;
}
```

### Custom CSS

Add custom styles to override defaults:

```css
/* Change button size */
.wzkb-beacon-button-icon {
    width: 64px;
    height: 64px;
}

/* Change panel width */
.wzkb-beacon-panel {
    width: 420px;
}

/* Custom article link styling */
.wzkb-beacon-article-link {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
```

## Technical Details

### Files

- **PHP Class**: `includes/pro/beacon/class-beacon.php`
- **JavaScript**: `includes/pro/beacon/js/beacon.js` (and `beacon.min.js`)
- **CSS**: `includes/pro/beacon/css/beacon.css` (and `beacon.min.css`)
- **Settings**: `includes/admin/class-settings.php`
- **Related Integration**: `includes/frontend/class-related.php`

### AJAX Endpoints

- `wzkb_beacon_search` - Search knowledge base articles
- `wzkb_beacon_submit` - Submit contact form

### Security

- Nonce verification on all AJAX requests
- Input sanitization (sanitize_text_field, sanitize_email, sanitize_textarea_field)
- Output escaping (esc_html, esc_attr, esc_url)
- Email validation
- XSS prevention in JavaScript
- Honeypot field on the contact form and token-based rate limiting (no IP storage)

### Performance

- Conditional loading (only when enabled)
- Minified assets in production (`SCRIPT_DEBUG` support)
- RTL support with automatic detection (`is_rtl()`)
- Debounced search (500ms delay to reduce server requests)
- Efficient DOM manipulation with jQuery
- CSS animations with GPU acceleration
- CSS Grid for optimal layout performance
- Reuses Related class query (cached results)
- No duplicate code (DRY principle)
- Lazy event binding for dynamic elements

## Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Accessibility

- Keyboard navigation (Tab, Enter, Escape)
- ARIA labels and roles
- Focus management
- Screen reader friendly
- High contrast support
- Reduced motion support
- RTL (Right-to-Left) language support

## Troubleshooting

### Beacon Not Appearing

1. Check if beacon is enabled in settings
2. Verify `wzkb_beacon_show` filter isn't returning false
3. Check for JavaScript errors in browser console
4. Ensure jQuery is loaded

### Search Not Working

1. Verify knowledge base articles exist and are published
2. Check AJAX URL in browser network tab
3. Verify nonce is being passed correctly
4. Check server error logs

### Contact Form Not Sending

1. Verify contact email is set correctly
2. Check if `wp_mail()` is working on your server
3. Test with SMTP plugin if needed
4. Check spam folder

### Styling Issues

1. Clear browser cache
2. Check for CSS conflicts with theme
3. Verify minified CSS file exists
4. Use browser inspector to check applied styles

## Best Practices

1. **Keep Suggested Articles Relevant** - Use the filter to show contextual articles
2. **Test on Mobile** - Ensure beacon works well on all devices
3. **Monitor Contact Submissions** - Set up proper email notifications
4. **Customize Colors** - Match your brand colors
5. **Add Analytics** - Track beacon usage with custom events
6. **Optimize Search** - Ensure articles have good titles and excerpts

## Examples

### Open Beacon from Custom Button

```html
<button onclick="WZKBBeacon('open')">Need Help?</button>
```

### Search on Page Load

```javascript
jQuery(document).ready(function($) {
    // Auto-search based on page
    if (window.location.pathname.includes('/pricing/')) {
        WZKBBeacon('search', 'pricing plans');
    }
});
```

### Track Beacon Usage

```javascript
$(document).on('wzkb-beacon-opened', function() {
    // Google Analytics
    gtag('event', 'beacon_opened', {
        'event_category': 'engagement',
        'event_label': 'Help Widget'
    });
});
```

### Conditional Suggested Articles

```php
add_filter( 'wzkb_beacon_suggested_articles', function( $article_ids, $current_post_id ) {
    // Show different articles based on user role
    if ( current_user_can( 'manage_options' ) ) {
        return array( 100, 101, 102 ); // Admin articles
    }
    
    return array( 200, 201, 202 ); // Regular user articles
}, 10, 2 );
```

## Future Enhancements

Potential features for future versions:

- Live chat integration
- AI-powered answer suggestions
- Multi-language support
- Proactive messages
- User identification
- Chat history
- File attachments
- Custom fields
- Analytics dashboard
- A/B testing

## Support

For questions or issues with the Beacon widget:

1. Check this documentation
2. Visit the plugin support forum
3. Contact WebberZone support

## Advanced Features

### HTML Email Template

Contact form submissions use a beautiful HTML email template:

- Modern gradient header (#667eea to #764ba2)
- Organized sections with left accent borders
- Mobile-responsive design (max-width: 600px)
- Professional typography and spacing
- Email client compatible (inline styles, table layout)
- Pre-wrapped message text preserving line breaks

### State Persistence

The beacon preserves state when closed:

- Closing beacon doesn't reset current screen
- Form data preserved until submission
- Returns to same screen when reopened
- Only resets after successful form submission
- Better UX - users don't lose their place

### Display Location Control

Control where the beacon appears:

```php
// Check display location setting
$location = wzkb_get_option( 'beacon_display_location', 'all' );

if ( 'kb_only' === $location ) {
    // Only show on KB pages
    if ( ! is_singular( 'wz_knowledgebase' ) && 
         ! is_post_type_archive( 'wz_knowledgebase' ) && 
         ! is_tax( array( 'wzkb_category', 'wzkb_tag', 'wzkb_product' ) ) ) {
        return; // Don't show beacon
    }
}
```

### Search Results UX

Enhanced search results experience:

- "Haven't found what you're looking for, drop us a line" message
- Contact button appears in no-results state
- Contact button also at bottom of results
- Search box fixed at bottom for easy refinement
- Scrollable results with locked search box

## Changelog

### Version 3.0.0

- **NEW**: CSS Grid layout for robust positioning
- **NEW**: Search box locked at bottom (home + search screens)
- **NEW**: Related articles integration using Related class
- **NEW**: Customizable labels via `wzkb_beacon_labels` filter
- **NEW**: Display location setting (KB only or sitewide)
- **NEW**: "Haven't found what you're looking for" message
- **NEW**: Contact button in search results
- **NEW**: RTL (Right-to-Left) language support with automatic detection
- **IMPROVED**: No horizontal scrollbar with `overflow-x: hidden`
- **IMPROVED**: Word wrapping for long content
- **IMPROVED**: Scrollable content area with fixed search
- **IMPROVED**: HTML email template for contact form
- **IMPROVED**: State persistence (no reset on close)
- **IMPROVED**: DRY principle - reuses Related class logic
- **FIXED**: Duplicate ID issues (now uses classes)
- **FIXED**: Search box positioning issues
- **FIXED**: Cache busting for CSS/JS updates
- **FIXED**: RTL CSS files now load automatically based on `is_rtl()`

### Version 2.0.0

- Initial release of Beacon help widget
- Self-service search functionality
- Contact form integration
- Customizable appearance
- JavaScript API
- Mobile responsive design
- Accessibility features
