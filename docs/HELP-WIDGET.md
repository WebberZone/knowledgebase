# Help Widget

The Help Widget is a floating help widget that provides self-service support directly on your website. Visitors can search your knowledge base, view suggested articles, and contact you without leaving the page.

## What It Does

The Help Widget adds a floating button (usually bottom-right corner) that opens a help panel when clicked. Inside the panel, visitors can:

- __Search__ your knowledge base articles
- __Browse__ suggested articles relevant to the current page
- __Contact__ you via a built-in form if they can't find answers

Think of it as a mini help desk that's always available on your site.

## Key Features

- __Self-Service Search__ – Visitors find answers instantly without leaving your site
- __Smart Suggestions__ – Shows relevant articles based on what page they're viewing
- __Contact Form__ – Built-in form sends you emails when visitors need help
- __Mobile Friendly__ – Works perfectly on phones, tablets, and desktops
- __Customizable Colors__ – Match your brand with 7 color settings
- __Spam Protection__ – Built-in honeypot and rate limiting
- __Dark Mode__ – Automatically adapts to user's system preference
- __Accessible__ – Keyboard navigation and screen reader support

## Configuration

### Admin Settings

Navigate to __Knowledge Base → Settings → Pro__ and scroll to the __Help Widget__ section.

#### Basic Settings

- __Enable Help Widget__ – Turn the help widget on/off.
- __Button Position__ – Choose bottom right or bottom left.
- __Button Style__ – Icon only, text only, or icon + text.
- __Button Text__ – Custom text for the button (default: “Help”).

#### Color Settings

- __Help Widget Color__ - Primary color for button and UI elements (default: #617DEC)
- __Help Widget Hover Color__ - Hover color for buttons and interactive elements (default: #4c63d2)
- __Help Widget Text Color__ - Text color for help widget button (default: #ffffff)
- __Help Widget Hover Text Color__ - Text color when hovering (default: #ffffff)
- __Panel Background Color__ - Background color for the help widget panel (default: #ffffff)
- __Panel Text Color__ - Default text color within the panel (default: #1a1a1a)
- __Link Hover Background__ - Background color when hovering over links (default: #f3f4f6)

#### Content Settings

- __Greeting Message__ – Welcome message when help widget opens (default: “Hi! How can we help you?”).
- __Search Placeholder__ – Placeholder text for search input (default: “Search for answers…”).

#### Contact Form Settings

- __Enable Contact Form__ – Allow visitors to send messages.
- __Contact Email__ – Email address for form submissions (default: admin email).
- __Minimum Message Length__ – Require visitors to enter at least 30 characters (default: 30, filterable).

#### Display Options

- __Display Location__ – Choose where help widget appears:
  - __Knowledge Base Only__ (default) – Shows only on KB pages (singles, archives, taxonomies).
  - __Entire Site__ – Shows sitewide.
- __Show on Mobile__ - Display help widget on mobile devices
- __Enable Animations__ - Smooth animations and transitions

## Troubleshooting

### Help Widget Not Appearing

1. __Check if enabled__ – Go to Knowledge Base → Settings → Pro and verify "Enable Help Widget" is checked
2. __Check display location__ – If set to "Knowledge Base Only", it won't show on other pages
3. __Clear cache__ – Clear your browser cache (Ctrl+Shift+R or Cmd+Shift+R)
4. __Check for conflicts__ – Temporarily disable other plugins to test for conflicts

### Search Not Working

1. __Verify articles exist__ – Make sure you have published knowledge base articles
2. __Check article titles__ – Search looks in article titles and content
3. __Clear cache__ – Try clearing your site's cache if using a caching plugin

### Contact Form Not Sending

1. __Check email address__ – Verify the contact email is set correctly in settings
2. __Check spam folder__ – Messages might be going to spam
3. __Test WordPress email__ – Your server must be able to send emails (test with an SMTP plugin if needed)

### Colors Not Showing

1. __Clear browser cache__ – Press Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
2. __Check theme conflicts__ – Some themes may override colors
3. __Save settings__ – Make sure you clicked "Save Changes" after setting colors

---

## For Developers

The sections below are for developers who want to customize the help widget's appearance and behavior. Organized from easiest to most advanced: CSS → PHP → JavaScript.

## CSS Customization

### CSS Variables

Customize the help widget appearance using CSS variables:

```css
#wzkb-help-widget-container {
    /* Color variables set via admin settings */
    --wzkb-help-widget-color: #617DEC;
    --wzkb-help-widget-hover-color: #4c63d2;
    --wzkb-help-widget-text-color: #ffffff;
    --wzkb-help-widget-bg: #ffffff;
    --wzkb-help-widget-panel-text: #1a1a1a;
    --wzkb-help-widget-link-hover: #f3f4f6;
    
    /* Additional CSS variables (not in settings) */
    --wzkb-help-widget-border: #e5e7eb;
    --wzkb-help-widget-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    --wzkb-help-widget-radius: 12px;
}
```

### Custom CSS Examples

Add custom styles to override defaults:

```css
/* Change button size */
.wzkb-help-widget-button-icon {
    width: 64px;
    height: 64px;
}

/* Change panel width */
.wzkb-help-widget-panel {
    width: 420px;
}

/* Custom article link styling */
.wzkb-help-widget-article-link {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
```

### CSS Grid Architecture

The help widget uses CSS Grid for a robust, modern layout:

```css
.wzkb-help-widget-content {
    display: grid;
    grid-template-rows: 1fr auto;
    height: 100%;
    overflow: hidden;
}

.wzkb-help-widget-scrollable {
    overflow-y: auto;
    padding: 0 16px 16px;
    flex: 1;
}

.wzkb-help-widget-search-box {
    padding: 16px;
    border-top: 1px solid var(--wzkb-help-widget-border);
    background: var(--wzkb-help-widget-bg);
}
```

__Benefits:__

- Search box always visible at bottom
- Articles scroll independently
- No horizontal scrollbar
- Content properly constrained to panel width
- Responsive and accessible

### Search Box Positioning

The search box appears at the bottom of:

1. __Home Screen__ - Below suggested articles and contact button
2. __Search Results Screen__ - Below search results

Both use the same CSS Grid layout ensuring the search box is always accessible without scrolling.

## Customization

### Customizing Labels and Strings

All help widget labels can be customized using the `wzkb_help_widget_labels` filter:

```php
add_filter( 'wzkb_help_widget_labels', function( $labels ) {
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

__Available Labels:__

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
- `submitButton` - Submit button text (default: "Send message")
- `successMessage` - Success message after form submission (default: "Thank you! We'll get back to you soon.")
- `errorMessage` - Error message
- `requiredField` - Required field validation message
- `messageTooShort` - Message length validation error

### Suggested Articles

The Help Widget uses the __Knowledge Base Related Articles system__ to suggest relevant content to visitors.

#### How It Works

The help widget intelligently suggests articles based on context:

1. __On KB Articles__ - Uses the Related Articles system (shows articles with same categories/tags)
2. __On Other Pages__ - Shows recent KB articles
3. __Custom Override__ - Use filter to provide specific articles for any page

#### Custom Suggested Articles

Control which articles are suggested using the `wzkb_help_widget_suggested_articles` filter:

```php
add_filter( 'wzkb_help_widget_suggested_articles', function( $article_ids, $current_post_id ) {
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
    // Show more related articles in help widget
    $args['posts_per_page'] = 10;
    
    // Order by popularity instead of random
    $args['orderby'] = 'meta_value_num';
    $args['meta_key'] = 'views_count';
    
    return $args;
}, 10, 2 );
```

### Help Widget Visibility

Control where the help widget appears using the `wzkb_help_widget_show` filter:

```php
add_filter( 'wzkb_help_widget_show', function( $show ) {
    // Hide help widget on specific pages
    if ( is_page( 'checkout' ) ) {
        return false;
    }
    
    return $show;
} );
```

### Custom Actions

Hook into help widget events:

```php
// After contact form submission
add_action( 'wzkb_help_widget_contact_submitted', function( $name, $email, $subject, $message ) {
    // Send to CRM, log to database, etc.
    error_log( "Help Widget contact from: $name ($email)" );
}, 10, 4 );
```

## Technical Details

### Files

- __PHP Class__: `includes/pro/help-widget/class-help-widget.php`
- __JavaScript__: `includes/pro/help-widget/js/help-widget.js` (and `help-widget.min.js`)
- __CSS__: `includes/pro/help-widget/css/help-widget.css` (and `help-widget.min.css`)
- __Settings__: `includes/admin/class-settings.php`
- __Related Integration__: `includes/frontend/class-related.php`

### AJAX Endpoints

- `wzkb_help_widget_search` - Search knowledge base articles
- `wzkb_help_widget_submit` - Submit contact form

### Security

- Nonce verification on all AJAX requests
- Input sanitization (sanitize_text_field, sanitize_email, sanitize_textarea_field)
- Output escaping (esc_html, esc_attr, esc_url)
- Email validation
- XSS prevention in JavaScript
- Honeypot field on the contact form and token-based rate limiting (5 submissions/hour per token, no IP storage)

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

## Advanced Troubleshooting (Developers)

### Debugging JavaScript Issues

1. Check browser console for errors (F12 → Console tab)
2. Verify `wzkb_help_widget_show` filter isn't returning false
3. Ensure jQuery is loaded before help widget script
4. Check AJAX URL in network tab

### Debugging Search Issues

1. Check AJAX endpoint: `wp-admin/admin-ajax.php?action=wzkb_help_widget_search`
2. Verify nonce is being passed correctly in requests
3. Check server error logs for PHP errors
4. Test with `WP_DEBUG` enabled

### Debugging Email Issues

1. Test if `wp_mail()` works on your server
2. Check server mail logs
3. Verify email headers are correct
4. Test with an SMTP plugin (e.g., WP Mail SMTP)

### CSS Debugging

1. Use browser inspector to check applied styles
2. Verify minified CSS file exists and loads
3. Check for theme CSS conflicts with higher specificity
4. Test with all other plugins disabled

## Best Practices

1. __Keep Suggested Articles Relevant__ - Use the filter to show contextual articles
2. __Test on Mobile__ - Ensure help widget works well on all devices
3. __Monitor Contact Submissions__ - Set up proper email notifications
4. __Customize Colors__ - Match your brand colors
5. __Add Analytics__ - Track help widget usage with custom events
6. __Optimize Search__ - Ensure articles have good titles and excerpts

## Examples

### Open Help Widget from Custom Button

```html
<button onclick="WZKBHelpWidget('open')">Need Help?</button>
```

### Search on Page Load

```javascript
jQuery(document).ready(function($) {
    // Auto-search based on page
    if (window.location.pathname.includes('/pricing/')) {
        WZKBHelpWidget('search', 'pricing plans');
    }
});
```

### Track Help Widget Usage

```javascript
$(document).on('wzkb-help-widget-opened', function() {
    // Google Analytics
    gtag('event', 'help widget_opened', {
        'event_category': 'engagement',
        'event_label': 'Help Widget'
    });
});
```

## JavaScript API (Advanced)

For advanced developers who need programmatic control over the help widget.

### Methods

Control the help widget using the global `WZKBHelpWidget()` function:

```javascript
// Open help widget
WZKBHelpWidget('open');

// Close help widget
WZKBHelpWidget('close');

// Toggle help widget
WZKBHelpWidget('toggle');

// Search for specific term
WZKBHelpWidget('search', 'installation guide');

// Navigate to specific screen
WZKBHelpWidget('navigate', 'contact'); // 'home', 'search', or 'contact'

// Open contact form directly
WZKBHelpWidget('contact');
```

### Events

Listen to help widget events using jQuery:

```javascript
// Help Widget opened
$(document).on('wzkb-help-widget-opened', function() {
    console.log('Help Widget opened');
});

// Help Widget closed
$(document).on('wzkb-help-widget-closed', function() {
    console.log('Help Widget closed');
});

// Screen changed
$(document).on('wzkb-help-widget-screen-changed', function(event, screenName) {
    console.log('Navigated to:', screenName);
});

// Search performed
$(document).on('wzkb-help-widget-search', function(event, query) {
    console.log('Searched for:', query);
});

// Contact form opened
$(document).on('wzkb-help-widget-contact-opened', function() {
    console.log('Contact form opened');
});

// Contact form submitted
$(document).on('wzkb-help-widget-contact-submitted', function(event, formData) {
    console.log('Form submitted:', formData);
});
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

For questions or issues with the Help Widget:

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

The help widget preserves state when closed:

- Closing help widget doesn't reset current screen
- Form data preserved until submission
- Returns to same screen when reopened
- Only resets after successful form submission
- Better UX - users don't lose their place

### Display Location Control

Control where the help widget appears:

```php
// Check display location setting
$location = wzkb_get_option( 'help widget_display_location', 'all' );

if ( 'kb_only' === $location ) {
    // Only show on KB pages
    if ( ! is_singular( 'wz_knowledgebase' ) && 
         ! is_post_type_archive( 'wz_knowledgebase' ) && 
         ! is_tax( array( 'wzkb_category', 'wzkb_tag', 'wzkb_product' ) ) ) {
        return; // Don't show help widget
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
