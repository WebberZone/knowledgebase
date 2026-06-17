---
slug: knowledge-base-styles-customization-guide
title: "Knowledge Base Styles Customization Guide"
products: [knowledgebase]
sections: [02-kb-advanced]
tags: [css,knowledgebase,styles]
status: publish
order: 0
---

<div class="wp-block-kadence-tableofcontents">

</div>

[Knowledge Base](https://webberzone.com/plugins/knowledgebase/) styles control how your knowledge base articles and sections look on your website. Think of them as pre-designed themes that determine colors, layouts, spacing, and visual effects.

## What Are Styles?

Each style is a complete visual design for your knowledge base:

- **Layout** – How sections and articles are arranged
- **Colors** – Background, text, borders, and accent colors
- **Typography** – Font sizes and spacing
- **Effects** – Hover states, shadows, and transitions

The plugin includes 9 professionally designed styles (2 free, 7 pro) that you can switch between instantly.

### Why Customize?

While each style looks excellent out of the box, you may want to:

- Match your brand colors
- Adjust contrast for better readability
- Create a unique look for your knowledge base
- Coordinate with your theme’s design

### Easy Customization with CSS Variables

All styles use **CSS Custom Properties (CSS Variables)**, which means:

- ✅ Change colors in one place, update everywhere
- ✅ No plugin files to edit – add CSS through WordPress
- ✅ Changes survive plugin updates
- ✅ Simple copy-paste – no coding skills required

### Benefits of CSS Variables

- ✅ **Easy to customize** – Change colors in one place
- ✅ **No file editing** – Add custom CSS through WordPress settings
- ✅ **Update-safe** – Changes persist through plugin updates
- ✅ **Real-time preview** – See changes immediately
- ✅ **No coding required** – Simple copy-paste customization

## Available Styles

Knowledge Base includes nine professionally designed styles to match your site’s design.

### Free Styles (2)

- **Legacy** – Float-based layout for compatibility with older themes
- **Classic** – Modern Grid Layout with CSS Grid for contemporary designs

### Pro Styles (7)

- **Card Layout** – Modern cards with hover effects and shadows
- **Minimal** – Clean, minimalist design with subtle borders
- **Boxed** – Strong borders and structured sections
- **Gradient** – Colorful gradient accents for vibrant designs
- **Compact** – Dense information layout for maximum content
- **Magazine** – Editorial two-column design for rich content
- **Professional** – Corporate business style with formal aesthetics

### Key Features

All styles include:

- ✅ **4 Variants** – Main, minified, RTL, and minified RTL versions
- ✅ **RTL Support** – Full right-to-left language support (Arabic, Hebrew, etc.)
- ✅ **Optimized Performance** – Minified CSS files for faster loading
- ✅ **Mobile Responsive** – Works perfectly on all devices
- ✅ **Automatic Fallback** – Graceful degradation if Pro is deactivated

### How Styles Work

- **The free version** shows only two styles (Classic and Legacy)
- **Pro version** automatically adds 7 additional premium styles
- Only the selected style loads on your pages (no bloat)
- Switching styles is instant – just select from the dropdown

## Quick Start

### 3-Step Customization Process

1. **Choose your style** from the dropdown (Settings → Knowledge Base → Styles)
2. **Find the CSS variables** for that style (see sections below)
3. **Add custom CSS** to override colors (Settings → Knowledge Base → Styles → Custom CSS)

### Example: Change Primary Color

```css
:root {
    --wzkb-color-primary: #e63946; /* Change this to your brand color */
}
```

That’s it! The knowledge base will now use your brand color throughout.

## Where to Add Custom CSS

### Method 1: Plugin Settings (Recommended)

**Path:** WordPress Admin → Knowledge Base → Settings → Styles

1. Scroll to **the “Custom CSS”** field at the bottom
2. Paste your CSS variables
3. Click **“Save Changes”**
4. View your knowledge base to see changes

### Method 2: WordPress Customizer

**Path:** Appearance → Customize → Additional CSS

1. Click **“Additional CSS”**
2. Add your CSS variables
3. Preview changes in real-time
4. Click **“Publish”**

### Method 3: Child Theme (For Developers or Advanced users)

Add to your child theme’s `style.css`:

```css
/* Knowledge Base Custom Colors */
:root {
    --wzkb-color-primary: #e63946; /* Change this to your brand color */
}
```

## Free Styles Customization

### Classic Style

**Default Colors:**

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #667eea;
    --wzkb-color-text: #333;
    --wzkb-color-text-medium: #666;
    --wzkb-color-white: #fff;
    --wzkb-color-orange: orange;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #f8f9fa;
    --wzkb-bg-ghostwhite: ghostwhite;

    /* Border colors */
    --wzkb-border-light: #e0e0e0;
    --wzkb-border-medium: #ddd;

    /* Breadcrumb colors */
    --wzkb-breadcrumb-bg: #f8f9fa;
    --wzkb-breadcrumb-text: #2d3748;
    --wzkb-breadcrumb-link: #667eea;
    --wzkb-breadcrumb-separator: #666;
    --wzkb-breadcrumb-border: #e0e0e0;
}
```

**Customization Example:**

```css
/* Modern Blue Theme for Classic */
:root {
    --wzkb-color-primary: #0066cc;
    --wzkb-breadcrumb-link: #0066cc;
    --wzkb-bg-light: #f0f7ff;
    --wzkb-border-light: #99ccff;
}
```

### Legacy Style

**Important Note:** The Legacy style uses hardcoded CSS values and **does not support CSS variables**. This is the original style maintained for backward compatibility.

If you need to customize the Legacy style, you’ll need to override the CSS rules directly:

```css
/* Example: Change Legacy style colors */
.wzkb h3 a,
.wzkb h4 a {
    color: #0066cc; /* Change link color */
}

.wzkb-section-name-level-1 {
    background-color: #f0f7ff; /* Change section background */
    border-color: #0066cc; /* Change border color */
}

.wzkb-articles-list li:before {
    color: #0066cc; /* Change icon color */
}
```

**Recommendation:** For easier customization, consider switching to the Classic style or any Pro style, all of which support CSS variables.

## Pro Styles Customization

### Card Layout (modern.css)

**Default Colors:**

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #667eea;
    --wzkb-color-text: #2d3748;
    --wzkb-color-text-medium: #666;
    --wzkb-color-white: #fff;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #f8f9fa;

    /* Border colors */
    --wzkb-border-light: #e0e0e0;
    --wzkb-border-medium: #ddd;

    /* Breadcrumb colors */
    --wzkb-breadcrumb-text: #2d3748;
    --wzkb-breadcrumb-link: #667eea;
    --wzkb-breadcrumb-separator: #666;
}
```

**Customization Example:**

```css
/* Purple Card Theme */
:root {
    --wzkb-color-primary: #7c3aed;
    --wzkb-breadcrumb-link: #7c3aed;
    --wzkb-bg-light: #faf5ff;
    --wzkb-border-light: #e9d5ff;
}
```

### Minimal Style

**Default Colors:**

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #667eea;
    --wzkb-color-text: #2d3748;
    --wzkb-color-text-medium: #666;
    --wzkb-color-white: #fff;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #f8f9fa;

    /* Border colors */
    --wzkb-border-light: #e0e0e0;

    /* Breadcrumb colors */
    --wzkb-breadcrumb-text: #2d3748;
    --wzkb-breadcrumb-link: #667eea;
    --wzkb-breadcrumb-separator: #666;
}
```

**Customization Example:**

```css
/* Warm Minimal Theme */
:root {
    --wzkb-color-primary: #78350f;
    --wzkb-breadcrumb-link: #78350f;
    --wzkb-color-text: #451a03;
    --wzkb-bg-light: #fef3c7;
    --wzkb-border-light: #fde68a;
}
```

### Boxed Style

**Default Colors:**

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #667eea;
    --wzkb-color-text: #2d3748;
    --wzkb-color-text-medium: #666;
    --wzkb-color-white: #fff;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #f8f9fa;

    /* Border colors */
    --wzkb-border-light: #e0e0e0;
    --wzkb-border-medium: #ddd;

    /* Breadcrumb colors */
    --wzkb-breadcrumb-bg: #f8f9fa;
    --wzkb-breadcrumb-text: #2d3748;
    --wzkb-breadcrumb-link: #667eea;
    --wzkb-breadcrumb-separator: #666;
    --wzkb-breadcrumb-border: #e0e0e0;
}
```

**Customization Example:**

```css
/* Teal Boxed Theme */
:root {
    --wzkb-color-primary: #0d9488;
    --wzkb-breadcrumb-link: #0d9488;
    --wzkb-bg-light: #f0fdfa;
    --wzkb-border-light: #5eead4;
}
```

### Gradient Style

**Default Colors:**

```css
:root {
    /* Gradient colors - customizable via settings */
    --wzkb-gradient-start: #667eea;
    --wzkb-gradient-middle: #764ba2;
    --wzkb-gradient-end: #f093fb;
    --wzkb-gradient-primary: #667eea;
    --wzkb-gradient-shadow: rgba(102, 126, 234, 0.4);

    /* Text colors */
    --wzkb-text-primary: #2d3748;
    --wzkb-text-light: #666;
    --wzkb-text-white: #ffffff;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #f8f9fa;
    --wzkb-bg-gradient-light: rgba(102, 126, 234, 0.08);
    --wzkb-bg-gradient-light-end: rgba(118, 75, 162, 0.08);

    /* Border colors */
    --wzkb-border-light: #e9ecef;
    --wzkb-border-gradient: rgba(102, 126, 234, 0.15);

    /* Link colors */
    --wzkb-link-color: #667eea;

    /* Breadcrumb colors */
    --wzkb-breadcrumb-text: #2d3748;
    --wzkb-breadcrumb-link: #667eea;
    --wzkb-breadcrumb-separator: #666;
}
```

**Customization Example:**

```css
/* Sunset Gradient Theme */
:root {
    --wzkb-gradient-start: #ff6b6b;
    --wzkb-gradient-middle: #ee5a6f;
    --wzkb-gradient-end: #c06c84;
    --wzkb-gradient-primary: #ff6b6b;
    --wzkb-gradient-shadow: rgba(255, 107, 107, 0.4);
    --wzkb-link-color: #ff6b6b;
    --wzkb-breadcrumb-link: #ff6b6b;
}
```

### Compact Style

**Default Colors:**

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #2980b9;
    --wzkb-color-dark: #2c3e50;
    --wzkb-color-text: #34495e;
    --wzkb-color-text-medium: #7f8c8d;
    --wzkb-color-white: #fff;

    /* Background colors */
    --wzkb-bg-dark: #34495e;
    --wzkb-bg-badge: #7f8c8d;

    /* Border colors */
    --wzkb-border-light: #e0e0e0;
    --wzkb-border-medium: #bdc3c7;
    --wzkb-border-dotted: #ccc;

    /* Breadcrumb colors */
    --wzkb-breadcrumb-text: #34495e;
    --wzkb-breadcrumb-link: #2980b9;
    --wzkb-breadcrumb-separator: #7f8c8d;
}
```

**Customization Example:**

```css
/* Compact Orange Theme */
:root {
    --wzkb-color-primary: #f59e0b;
    --wzkb-breadcrumb-link: #f59e0b;
    --wzkb-bg-dark: #78350f;
    --wzkb-border-light: #fde68a;
}
```

### Magazine Style

**Default Colors:**

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #c0392b;
    --wzkb-color-dark: #2c3e50;
    --wzkb-color-darker: #34495e;
    --wzkb-color-text: #2c3e50;
    --wzkb-color-white: #fff;
    --wzkb-color-link: #2980b9;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #f8f9fa;
    --wzkb-bg-dark: #2c3e50;

    /* Border colors */
    --wzkb-border-light: #e8e8e8;
    --wzkb-border-medium: #bdc3c7;
    --wzkb-border-accent: #c0392b;

    /* Breadcrumb colors */
    --wzkb-breadcrumb-text: #2c3e50;
    --wzkb-breadcrumb-link: #2980b9;
    --wzkb-breadcrumb-separator: #7f8c8d;
}
```

**Customization Example:**

```css
/* Tech Magazine Theme */
:root {
    --wzkb-color-primary: #0ea5e9;
    --wzkb-color-link: #0ea5e9;
    --wzkb-breadcrumb-link: #0ea5e9;
    --wzkb-bg-light: #f0f9ff;
    --wzkb-border-accent: #0ea5e9;
}
```

### Professional Style

**Default Colors:**

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #0066cc;
    --wzkb-color-dark: #1a3a52;
    --wzkb-color-darker: #2c5282;
    --wzkb-color-text: #1a3a52;
    --wzkb-color-white: #fff;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #f7f9fb;
    --wzkb-bg-section: #fdfdfd;
    --wzkb-bg-dark: #1a3a52;

    /* Border colors */
    --wzkb-border-light: #f0f2f5;
    --wzkb-border-medium: #dde1e6;
    --wzkb-border-primary: #0066cc;

    /* Breadcrumb colors */
    --wzkb-breadcrumb-text: #1a3a52;
    --wzkb-breadcrumb-link: #2c5282;
    --wzkb-breadcrumb-separator: #718096;
}
```

**Customization Example:**

```css
/* Enterprise Green Theme */
:root {
    --wzkb-color-primary: #047857;
    --wzkb-breadcrumb-link: #047857;
    --wzkb-bg-light: #ecfdf5;
    --wzkb-bg-dark: #065f46;
    --wzkb-border-primary: #059669;
}
```

## Advanced Customization

### Multi-Product Color Schemes

If you’re using multi-product mode, you can target specific products:

```css
/* Product 1: Blue theme */
.wzkb-product-1 {
    --wzkb-color-primary: #3b82f6;
    --wzkb-bg-light: #eff6ff;
}

/* Product 2: Green theme */
.wzkb-product-2 {
    --wzkb-color-primary: #10b981;
    --wzkb-bg-light: #ecfdf5;
}
```

### Dark Mode Support

Add dark mode using media queries:

```css
@media (prefers-color-scheme: dark) {
    :root {
        --wzkb-color-primary: #60a5fa;
        --wzkb-color-text: #f3f4f6;
        --wzkb-bg-white: #1f2937;
        --wzkb-bg-light: #111827;
        --wzkb-border-light: #374151;
    }
}
```

### Hover and Interactive States

Most styles have hover states you can customize:

```css
:root {
    /* Normal state */
    --wzkb-color-primary: #2563eb;
    
    /* Hover state */
    --wzkb-color-hover: #1d4ed8;
    
    /* Shadow on hover */
    --wzkb-shadow-hover: 0 4px 12px rgba(37, 99, 235, 0.3);
}
```

## Common Customization Examples

### Example 1: Brand Color Consistency

Match your knowledge base to your brand:

```css
/* Replace with your brand colors */
:root {
    --wzkb-color-primary: #FF6B35;        /* Your primary brand color */
    --wzkb-color-dark: #CC5529;           /* Darker shade */
    --wzkb-color-accent: #FFA07A;         /* Lighter accent */
    --wzkb-bg-light: #FFF5F1;             /* Very light tint */
    --wzkb-border-light: #FFCDB8;         /* Border tint */
}
```

### Example 2: High Contrast (Accessibility)

Improve readability with higher contrast:

```css
:root {
    --wzkb-color-text: #000;              /* Pure black text */
    --wzkb-bg-white: #fff;                /* Pure white background */
    --wzkb-color-primary: #0066cc;        /* WCAG AA compliant blue */
    --wzkb-border-light: #666;            /* Visible borders */
}
```

### Example 3: Subtle and Minimal

Create a very subtle, minimal look:

```css
:root {
    --wzkb-color-primary: #4a5568;        /* Muted gray-blue */
    --wzkb-color-text: #2d3748;           /* Dark gray text */
    --wzkb-bg-light: #fafafa;             /* Very light gray */
    --wzkb-bg-lighter: #f7f7f7;           /* Slightly darker gray */
    --wzkb-border-light: #e8e8e8;         /* Subtle borders */
    --wzkb-shadow-card: none;             /* No shadows */
}
```

### Example 4: Bold and Vibrant

Make it pop with bold colors:

```css
:root {
    --wzkb-color-primary: #ff0080;        /* Hot pink */
    --wzkb-color-accent: #00d4ff;         /* Cyan */
    --wzkb-bg-light: #fff0f8;             /* Pink tint */
    --wzkb-border-light: #ffb3d9;         /* Pink border */
    --wzkb-shadow-card: 0 4px 8px rgba(255, 0, 128, 0.2);
}
```

### Example 5: Seasonal Themes

#### Holiday/Winter Theme

```css
:root {
    --wzkb-color-primary: #c41e3a;        /* Christmas red */
    --wzkb-color-accent: #0f8a5f;         /* Christmas green */
    --wzkb-bg-light: #f5f5f5;             /* Snow white */
    --wzkb-border-light: #c9c9c9;         /* Silver */
}
```

#### Summer Theme

```css
:root {
    --wzkb-color-primary: #ffa500;        /* Orange */
    --wzkb-color-accent: #00ced1;         /* Turquoise */
    --wzkb-bg-light: #fffacd;             /* Light yellow */
    --wzkb-border-light: #ffd700;         /* Gold */
}
```

## Troubleshooting

### My Custom Colors Aren’t Showing

**Solution 1:** Clear your browser cache

- Press `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)

**Solution 2:** Check CSS syntax

```css
/* ✅ Correct */
:root {
    --wzkb-color-primary: #2563eb;
}

/* ❌ Wrong - missing semicolon */
:root {
    --wzkb-color-primary: #2563eb
}

/* ❌ Wrong - wrong selector */
.root {
    --wzkb-color-primary: #2563eb;
}
```

**Solution 3:** Use `!important` if the theme overrides variables

```css
:root {
    --wzkb-color-primary: #2563eb !important;
}
```

### Colors Look Different on Mobile

**Solution:** Some CSS may be cached. Add this to force reload:

```css
/* Force fresh styles */
:root {
    --wzkb-color-primary: #2563eb !important;
}
```

### Specific Elements Not Changing

**Solution:** Target elements directly:

```css
/* Override section headings specifically */
.wzkb h3.wzkb-section-name {
    background: #your-color !important;
    color: #fff !important;
}
```

### Color Contrast Issues

**Tool:** Check contrast at <https://webaim.org/resources/contrastchecker/>

**WCAG AA Standard:** Minimum 4.5:1 ratio for normal text

```css
/* Good contrast example */
:root {
    --wzkb-color-text: #1f2937;          /* Dark text */
    --wzkb-bg-white: #ffffff;             /* White background */
    /* Ratio: 15.8:1 ✅ */
}
```

## Color Palette Generators

Need help choosing colors? Try these tools:

- **[Coolors.co](https://coolors.co/)** – Generate color palettes
- **[Adobe Color](https://color.adobe.com/)** – Color wheel and harmony rules
- **[Contrast Checker](https://webaim.org/resources/contrastchecker/)** – Ensure accessibility
