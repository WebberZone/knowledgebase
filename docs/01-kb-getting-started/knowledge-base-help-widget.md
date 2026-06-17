---
slug: knowledge-base-help-widget
title: "Knowledge Base Pro Help Widget"
products: [knowledgebase]
sections: [01-kb-getting-started]
tags: [installation,knowledgebase]
status: publish
order: 0
---

[kbtoc]

The **Help Widget** is a floating widget that provides self-service support directly on your website. Visitors can search your knowledge base, view suggested articles, and contact you without leaving the page.

## What It Does

The Help Widget adds a floating button (usually the bottom-right corner) that opens a help panel when clicked. Inside the panel, visitors can:

- **Search** your knowledge base articles
- **Browse** suggested articles relevant to the current page
- **Contact** you via a built-in form if they can’t find answers

Think of it as a mini help desk that’s always available on your site.

## Key Features

- **Self-Service Search** – Visitors find answers instantly without leaving your site.
- **Smart Suggestions** – Shows relevant articles based on what page they’re viewing.
- **Contact Form** – Built-in form sends you emails when visitors need help.
- **Mobile Friendly** – Works perfectly on phones, tablets, and desktops.
- **Customizable Colors** – Match your brand with 7 color settings.
- **Spam Protection** – Built-in honeypot and rate limiting.
- **Dark Mode** – Automatically adapts to the user’s system preference.
- **Accessible** – Keyboard navigation and screen reader support.

## Configuration

### Admin Settings

Navigate to **[Knowledge Base](https://webberzone.com/plugins/knowledgebase/) → Settings → Pro** and scroll to the [**Help Widget** section](https://webberzone.com/support/knowledgebase/knowledge-base-settings/#help-widget).

## Troubleshooting

### Help Widget Not Appearing

1. **Check if enabled** – Go to Knowledge Base → Settings → Pro and verify “Enable Help Widget” is checked.
2. **Check display location** – If set to “Knowledge Base Only”, it won’t show on other pages.
3. **Clear cache** – Clear your browser cache (Ctrl+Shift+R or Cmd+Shift+R).
4. **Check for conflicts** – Temporarily disable other plugins to test for conflicts.

### Search Not Working

1. **Verify articles exist** – Make sure you have published knowledge base articles.
2. **Check article titles** – Search looks in article titles and content.
3. **Clear cache** – Try clearing your site’s cache if using a caching plugin.

### Contact Form Not Sending

1. **Check email address** – Verify the contact email is set correctly in settings.
2. **Check spam folder** – Messages might be going there.
3. **Test WordPress email** – Your server must be able to send emails (test with an SMTP plugin if needed).

### Colors Not Showing

1. **Clear browser cache** – Press Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
2. **Check theme conflicts** – Some themes may override colors
3. **Save settings** – Make sure you clicked “Save Changes” after setting colors

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

The help widget uses CSS Grid for layout:

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

**Benefits:**

- Search box always visible at the bottom
- Articles scroll independently
- No horizontal scrollbar
- Content properly constrained to panel width
- Responsive and accessible

### Search Box Positioning

The search box appears at the bottom of:

1. **Home Screen** – Below suggested articles and contact button
2. **Search Results Screen** – Below search results

Both use the same CSS Grid layout, ensuring the search box is always accessible without scrolling.

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

**Available Labels:**

- `greeting` – Welcome message on home screen
- `searchPlaceholder` – Search input placeholder
- `searchButton` – Search button text
- `contactButton` – Contact button text
- `backButton` – Back button text
- `closeButton` – Close button text
- `noResults` – No results heading
- `noResultsMessage` – Message shown when no search results found
- `suggestedArticles` – Suggested articles heading
- `searchResults` – Search results heading
- `contactFormTitle` – Contact form heading
- `nameLabel` – Name field label
- `emailLabel` – Email field label
- `subjectLabel` – Subject field label
- `messageLabel` – Message field label
- `submitButton` – Submit button text (default: “Send message”)
- `successMessage` – Success message after form submission (default: “Thank you! We’ll get back to you soon.”)
- `errorMessage` – Error message
- `requiredField` – Required field validation message
- `messageTooShort` – Message length validation error

### Suggested Articles

The Help Widget uses the **Knowledge Base Related Articles system** to suggest relevant content to visitors.

#### How It Works

The help widget intelligently suggests articles based on context:

1. **On KB Articles** – Uses the Related Articles system (shows articles with the same categories/tags)
2. **On Other Pages** – Shows recent KB articles
3. **Custom Override** – Use a filter to provide specific articles for any page

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

- **PHP Class**: `includes/pro/help-widget/class-help-widget.php`
- **JavaScript**: `includes/pro/help-widget/js/help-widget.js` (and `help-widget.min.js`)
- **CSS**: `includes/pro/help-widget/css/help-widget.css` (and `help-widget.min.css`)
- **Settings**: `includes/admin/class-settings.php`
- **Related Integration**: `includes/frontend/class-related.php`

### AJAX Endpoints

- `wzkb_help_widget_search` – Search knowledge base articles
- `wzkb_help_widget_submit` – Submit contact form

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
2. Verify `wzkb_help_widget_show` filter isn’t returning false
3. Ensure jQuery is loaded before the help widget script
4. Check the AJAX URL in the network tab

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

1. Use the browser inspector to check the applied styles
2. Verify the minified CSS file exists and loads
3. Check for theme CSS conflicts with higher specificity
4. Test with all other plugins disabled

## Best Practices

1. **Keep Suggested Articles Relevant** – Use the filter to show contextual articles
2. **Test on Mobile** – Ensure the help widget works well on all devices
3. **Monitor Contact Submissions** – Set up proper email notifications
4. **Customize Colors** – Match your brand colors
5. **Add Analytics** – Track help widget usage with custom events
6. **Optimize Search** – Ensure articles have good titles and excerpts

## Examples

### Open Help Widget from Custom Button

```html
<button onclick="WZKBHelpWidget('open')">Need Help?</button>
```

### Search on Page Load

```js
jQuery(document).ready(function($) {
    // Auto-search based on page
    if (window.location.pathname.includes('/pricing/')) {
        WZKBHelpWidget('search', 'pricing plans');
    }
});
```

### Track Help Widget Usage

```js
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

```js
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

```js
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

## Support

For questions or issues with the Help Widget:

1. Check this documentation
2. Visit the plugin support forum
3. Contact WebberZone support

## Advanced Features

### HTML Email Template

Contact form submissions use a beautiful HTML email template:

- Modern gradient header (#667eea to \#764ba2)
- Organized sections with left accent borders
- Mobile-responsive design (max-width: 600px)
- Professional typography and spacing
- Email client compatible (inline styles, table layout)
- Pre-wrapped message text preserving line breaks

### Display Location Control

Control where the help widget appears:

```php
// Check display location setting
$location = wzkb_get_option( 'help_widget_display_location', 'all' );

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

- “Haven’t found what you’re looking for, drop us a line” message
- Contact button appears in no-results state
- The contact button is also at the bottom of the results
- Search box fixed at the bottom for easy refinement
- Scrollable results with locked search box
