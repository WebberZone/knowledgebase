# Knowledge Base Style Customization Guide

## Table of Contents

1. [Introduction](#introduction)
2. [Available Styles](#available-styles)
3. [Quick Start](#quick-start)
4. [Where to Add Custom CSS](#where-to-add-custom-css)
5. [Free Styles Customization](#free-styles-customization)
6. [Pro Styles Customization](#pro-styles-customization)
7. [Advanced Customization](#advanced-customization)
8. [Common Customization Examples](#common-customization-examples)
9. [Troubleshooting](#troubleshooting)

## Introduction

Knowledge Base styles control how your knowledge base articles and sections look on your website. Think of them as pre-designed themes that determine colors, layouts, spacing, and visual effects.

### What Are Styles?

Each style is a complete visual design for your knowledge base:

- __Layout__ - How sections and articles are arranged
- __Colors__ - Background, text, borders, and accent colors
- __Typography__ - Font sizes and spacing
- __Effects__ - Hover states, shadows, and transitions

The plugin includes 9 professionally designed styles (2 free, 7 pro) that you can switch between instantly.

### Why Customize?

While each style looks great out of the box, you may want to:

- Match your brand colors
- Adjust contrast for better readability
- Create a unique look for your knowledge base
- Coordinate with your theme's design

### Easy Customization with CSS Variables

All styles use __CSS Custom Properties (CSS Variables)__, which means:

- ✅ Change colors in one place, update everywhere
- ✅ No plugin files to edit - add CSS through WordPress
- ✅ Changes survive plugin updates
- ✅ Simple copy-paste - no coding skills required

## Available Styles

Knowledge Base includes 9 professionally designed styles to match your site's design.

### Free Styles (2)

- __Legacy__ - Float-based layout for compatibility with older themes
- __Classic__ - Modern Grid Layout with CSS Grid for contemporary designs

### Pro Styles (7)

- __Card Layout__ - Modern cards with hover effects and shadows
- __Minimal__ - Clean, minimalist design with subtle borders
- __Boxed__ - Strong borders and structured sections
- __Gradient__ - Colorful gradient accents for vibrant designs
- __Compact__ - Dense information layout for maximum content
- __Magazine__ - Editorial two-column design for rich content
- __Professional__ - Corporate business style with formal aesthetics

### Key Features

All styles include:

- ✅ __4 Variants__ - Main, minified, RTL, and minified RTL versions
- ✅ __RTL Support__ - Full right-to-left language support (Arabic, Hebrew, etc.)
- ✅ __Optimized Performance__ - Minified CSS files for faster loading
- ✅ __Mobile Responsive__ - Works perfectly on all devices
- ✅ __Automatic Fallback__ - Graceful degradation if Pro is deactivated

### How Styles Work

- __Free version__ shows only 2 styles (Classic and Legacy)
- __Pro version__ automatically adds 7 additional premium styles
- Only the selected style loads on your pages (no bloat)
- Switching styles is instant - just select from the dropdown

## Quick Start

### 3-Step Customization Process

1. __Choose your style__ from the dropdown (Settings → Knowledge Base → Styles)
2. __Find the CSS variables__ for that style (see sections below)
3. __Add custom CSS__ to override colors (Settings → Knowledge Base → Styles → Custom CSS)

### Example: Change Primary Color

```css
:root {
    --wzkb-color-primary: #e63946; /* Your brand color */
}
```

That's it! The knowledge base will now use your brand color throughout.

## Where to Add Custom CSS

### Method 1: Plugin Settings (Recommended)

__Path:__ WordPress Admin → Knowledge Base → Settings → Styles

1. Scroll to __"Custom CSS"__ field
2. Paste your CSS variables
3. Click __"Save Changes"__
4. View your knowledge base to see changes

### Method 2: WordPress Customizer

__Path:__ Appearance → Customize → Additional CSS

1. Click __"Additional CSS"__
2. Add your CSS variables
3. Preview changes in real-time
4. Click __"Publish"__

### Method 3: Child Theme (For Developers)

Add to your child theme's `style.css`:

```css
/* Knowledge Base Custom Colors */
:root {
    --wzkb-color-primary: #your-color;
}
```

## Free Styles Customization

### Classic Style

__Default CSS Variables:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #4c51bf;
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
    --wzkb-breadcrumb-link: #1630A2;
    --wzkb-breadcrumb-separator: var(--wzkb-color-dark);
    --wzkb-breadcrumb-border: #e0e0e0;

    /* Layout variables */
    --wzkb-wrapper-max-width: 1280px;

    /* List bullet */
    --wzkb-list-bullet: "›";

    /* Additional variables */
    --wzkb-gradient-begin: var(--wzkb-color-primary);
    --wzkb-gradient-end: #1a3a52;
    --wzkb-product-border: var(--wzkb-border-subtle);
    --wzkb-shadow-color: rgba(0, 0, 0, 0.08);
    --wzkb-shadow-hover: rgba(22, 33, 82, 0.16);
    --wzkb-shadow-medium: rgba(27, 39, 94, 0.08);
    --wzkb-shadow-large: rgba(14, 21, 47, 0.12);
    --wzkb-border-subtle: rgba(91, 107, 174, 0.15);
    --wzkb-color-text-dark: #202a44;
    --wzkb-color-accent: #2457ff;
    --wzkb-color-dark: #1a3a52;
}
```

__Customization Example:__

```css
/* Classic Blue Theme */
:root {
    --wzkb-color-primary: #4c51bf;
    --wzkb-breadcrumb-link: #1630A2;
    --wzkb-color-text: #333;
    --wzkb-bg-light: #f8f9fa;
    --wzkb-border-light: #e0e0e0;
}
```

### Legacy Style

__Default CSS Variables:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #4c51bf;
    --wzkb-color-text: #333;
    --wzkb-color-text-medium: #666;
    --wzkb-color-white: #fff;
    --wzkb-color-accent: #d45b00;

    /* Background colors */
    --wzkb-bg-ghostwhite: ghostwhite;
    --wzkb-bg-orange: orange;

    /* Border colors */
    --wzkb-border-light: #ddd;
    --wzkb-border-faint: rgba(0, 0, 0, 0.2);

    /* CTA color */
    --wzkb-color-cta: #0073aa;

    /* Layout variables */
    --wzkb-wrapper-max-width: 1280px;
}
```

__Customization Example:__

```css
/* Legacy Green Theme */
:root {
    --wzkb-color-primary: #10b981;
    --wzkb-color-accent: #059669;
    --wzkb-bg-orange: #f59e0b;
    --wzkb-color-text: #374151;
    --wzkb-border-light: #d1d5db;
}
```

## Pro Styles Customization

### Card Layout (modern.css)

__Default CSS Variables:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #3732a0;
    --wzkb-color-dark: #1f2937;
    --wzkb-color-text: #333;
    --wzkb-color-text-medium: #4a4a4a;
    --wzkb-color-text-light: #4a4a4a;
    --wzkb-color-white: #fff;
    --wzkb-color-orange: #FF8C00;
    --wzkb-color-black: #000;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #f8f9fa;
    --wzkb-bg-section: #fff;

    /* Border colors */
    --wzkb-border-light: #e5e7eb;
    --wzkb-border-medium: #e0e0e0;

    /* Layout variables */
    --wzkb-card-padding: 18px 20px 22px;
    --wzkb-grid-gap: 16px;
    --wzkb-wrapper-max-width: 1280px;

    /* Breadcrumb colors */
    --wzkb-breadcrumb-bg: #f8f9fa;
    --wzkb-breadcrumb-text: #1f2937;
    --wzkb-breadcrumb-link: var(--wzkb-color-primary);
    --wzkb-breadcrumb-separator: var(--wzkb-color-dark);
    --wzkb-breadcrumb-border: #e5e7eb;

    /* List bullet */
    --wzkb-list-bullet: "›";

    /* Gradient variables */
    --wzkb-gradient-begin: var(--wzkb-color-orange);
    --wzkb-gradient-end: #ff7a00;

    /* Additional variables */
    --wzkb-badge-gradient-end: var(--wzkb-gradient-end);
    --wzkb-border-accent: #e0e0e0;
    --wzkb-pagination-border: #ccc;
    --wzkb-related-border: #f0f0f0;
    --wzkb-grid-bg-start: #f8fafc;
    --wzkb-grid-bg-end: #f1f5f9;
    --wzkb-product-border: rgba(55, 50, 160, 0.12);
    --wzkb-shadow-color: rgba(0, 0, 0, 0.08);
    --wzkb-shadow-focus: rgba(102, 126, 234, 0.2);
    --wzkb-shadow-hover: rgba(0, 0, 0, 0.1);
}
```

__Customization Example:__

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

__Default CSS Variables:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #4c51bf;
    --wzkb-color-dark: #2c3e50;
    --wzkb-color-darkest: #1a1a1a;
    --wzkb-color-text: #555;
    --wzkb-color-white: #fff;

    /* Background colors */
    --wzkb-bg-light: #fafafa;

    /* Border colors */
    --wzkb-border-light: #eee;
    --wzkb-border-medium: #ddd;
    --wzkb-border-thin: #e8e8e8;

    /* Breadcrumb colors */
    --wzkb-breadcrumb-bg: #fafafa;
    --wzkb-breadcrumb-text: #2c3e50;
    --wzkb-breadcrumb-link: #4c51bf;
    --wzkb-breadcrumb-separator: var(--wzkb-color-dark);
    --wzkb-breadcrumb-border: #e8e8e8;

    /* Layout variables */
    --wzkb-wrapper-max-width: 1280px;

    /* List bullet */
    --wzkb-list-bullet: "›";

    /* Additional variables */
    --wzkb-search-border: #e0e0e0;
    --wzkb-shadow-color: rgba(0, 0, 0, 0.04);
}
```

__Customization Example:__

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

__Default CSS Variables:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #1f2937;
    --wzkb-color-dark: #111827;
    --wzkb-color-text: #374151;
    --wzkb-color-text-medium: #6b7280;
    --wzkb-color-white: #fff;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #f9fafb;
    --wzkb-bg-lighter: #f3f4f6;
    --wzkb-bg-section-even: #f3f4f6;

    /* Border colors */
    --wzkb-border-strong: #d1d5db;
    --wzkb-border-medium: #e5e7eb;
    --wzkb-border-dark: #374151;

    /* Breadcrumb colors */
    --wzkb-breadcrumb-bg: #f3f4f6;
    --wzkb-breadcrumb-text: #374151;
    --wzkb-breadcrumb-link: #374151;
    --wzkb-breadcrumb-separator: var(--wzkb-color-dark);
    --wzkb-breadcrumb-border: #d1d5db;

    /* Layout variables */
    --wzkb-wrapper-max-width: 1280px;

    /* List bullet */
    --wzkb-list-bullet: "›";

    /* Additional variables */
    --wzkb-color-product-title: #202a44;
    --wzkb-color-product-description: #5a6275;
    --wzkb-bg-product-wrapper: #f8f9fa;
    --wzkb-border-product: #e9ecef;

    /* Widget accents */
    --wzkb-widget-card-border-color: rgba(31, 41, 55, 0.3);
    --wzkb-widget-card-border-hover: var(--wzkb-color-dark);
    --wzkb-widget-card-bg-start: #fdfdfd;
    --wzkb-widget-card-bg-end: #f5f6f8;
    --wzkb-widget-card-bg-hover-start: #eef2f6;
    --wzkb-widget-card-bg-hover-end: #e2e7ee;
    --wzkb-widget-card-shadow-hover: 0 10px 22px rgba(31, 41, 55, 0.15);
    --wzkb-widget-nested-border-color: rgba(55, 65, 81, 0.3);
    --wzkb-widget-nested-border-hover: var(--wzkb-color-primary);
    --wzkb-widget-nested-bg-hover: rgba(243, 244, 246, 0.95);
    --wzkb-widget-link-color: #1c3d5a;
    --wzkb-widget-link-hover: var(--wzkb-color-dark);

    /* Sidebar variables */
    --wzkb-sidebar-widget-padding: 20px;
    --wzkb-sidebar-widget-margin-top: 10px;
    --wzkb-sidebar-ul-margin-left: 10px;
    --wzkb-sidebar-li-margin-left: 10px;
}
```

__Customization Example:__

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

__Default CSS Variables:__

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
    --wzkb-link-color: #4c51bf;

    /* Breadcrumb colors */
    --wzkb-breadcrumb-text: #2d3748;
    --wzkb-breadcrumb-link: #4c51bf;
    --wzkb-breadcrumb-separator: var(--wzkb-color-dark);

    /* Layout variables */
    --wzkb-wrapper-max-width: 1280px;

    /* Additional variables */
    --wzkb-card-text: #4a5568;
    --wzkb-gradient-begin: var(--wzkb-gradient-start);
    --wzkb-gradient-end: var(--wzkb-gradient-middle);
    --wzkb-border-dotted: #ccc;
    --wzkb-bg-gradient-light-start: rgba(102, 126, 234, 0.03);
    --wzkb-bg-gradient-light-end: rgba(118, 75, 162, 0.03);
    --wzkb-border-light-rgba: rgba(102, 126, 234, 0.1);
}
```

__Customization Example:__

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

__Default CSS Variables:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #1e5f8e;
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
    --wzkb-breadcrumb-link: #1e5f8e;
    --wzkb-breadcrumb-separator: var(--wzkb-color-dark);

    /* Layout variables */
    --wzkb-wrapper-max-width: 1280px;

    /* List bullet */
    --wzkb-list-bullet: "›";

    /* Additional variables */
    --wzkb-bg-product-wrapper: #f5f5f5;
    --wzkb-border-product: #e0e0e0;
    --wzkb-color-product-title: #333;
    --wzkb-color-product-description: #666;

    /* Sidebar variables */
    --wzkb-sidebar-widget-padding: 20px;
    --wzkb-sidebar-widget-margin-top: 10px;
    --wzkb-sidebar-ul-margin-left: 10px;
    --wzkb-sidebar-li-margin-left: 10px;
}
```

__Customization Example:__

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

__Default CSS Variables:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #c0392b;
    --wzkb-color-dark: #2c3e50;
    --wzkb-color-darker: #34495e;
    --wzkb-color-text: #2c3e50;
    --wzkb-color-white: #fff;
    --wzkb-color-link: #1e5f8e;

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
    --wzkb-breadcrumb-link: #000000;
    --wzkb-breadcrumb-separator: var(--wzkb-color-dark);
    --wzkb-breadcrumb-border: rgba(0, 0, 0, 0.1);

    /* Layout variables */
    --wzkb-wrapper-max-width: 1400px;

    /* List bullet */
    --wzkb-list-bullet: "›";

    /* Gradient variables */
    --wzkb-gradient-begin: var(--wzkb-color-primary);
    --wzkb-gradient-end: var(--wzkb-border-accent);

    /* Additional variables */
    --wzkb-color-icon: #5a6c7d;
    --wzkb-border-card: #ddd;
    --wzkb-color-secondary: #555;
    --wzkb-border-double: #ccc;

    /* Sidebar variables */
    --wzkb-sidebar-widget-padding: 20px;
    --wzkb-sidebar-widget-margin-top: 10px;
    --wzkb-sidebar-ul-margin-left: 10px;
    --wzkb-sidebar-li-margin-left: 10px;
    --wzkb-sidebar-widget-padding-small: 12px;
    --wzkb-sidebar-li-margin-bottom: 8px;
    --wzkb-sidebar-section-margin-bottom: 16px;
}
```

__Featured Article Styling:__

```css
/* First-child (featured) article with dark gradient */
.wzkb-articles-list li:first-child {
    grid-column: 1 / -1;
    background: linear-gradient(
        135deg,
        var(--wzkb-bg-dark) 0%,
        var(--wzkb-color-darker) 100%
    );
    padding: 24px;
    border-left: 6px solid var(--wzkb-border-accent);
}

/* White text on dark background for featured article */
.wzkb-articles-list li:first-child a,
.wzkb-articles-list li:first-child .wzkb-article-excerpt,
.wzkb-articles-list li:first-child .wzkb-article-meta,
.wzkb-articles-list li:first-child .wzkb-article-name {
    color: var(--wzkb-color-white);
}

/* Section headers with improved contrast */
.wzkb-section-name-level-1 {
    background: var(--wzkb-bg-light); /* Light background */
    color: var(--wzkb-color-text);   /* Dark text */
    border: 1px solid var(--wzkb-border-medium);
    border-left: 6px solid var(--wzkb-border-accent);
    border-radius: 0 8px 8px 0;
    padding: 8px 16px;
}
```

__Customization Example:__

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

__Default CSS Variables:__

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
    --wzkb-breadcrumb-bg: #f7f9fb;
    --wzkb-breadcrumb-text: #1a3a52;
    --wzkb-breadcrumb-link: #2c5282;
    --wzkb-breadcrumb-separator: var(--wzkb-color-dark);
    --wzkb-breadcrumb-border: #f0f2f5;

    /* Layout variables */
    --wzkb-wrapper-max-width: 1280px;

    /* List bullet */
    --wzkb-list-bullet: "›";

    /* Additional variables */
    --wzkb-gradient-begin: var(--wzkb-color-primary);
    --wzkb-gradient-end: var(--wzkb-color-dark);
    --wzkb-product-border: rgba(55, 50, 160, 0.12);
    --wzkb-shadow-color: rgba(0, 0, 0, 0.06);
    --wzkb-shadow-hover: rgba(0, 0, 0, 0.12);
    --wzkb-shadow-subtle: rgba(0, 0, 0, 0.02);
    --wzkb-color-accent: #667eea;
}
```

__Typography:__

```css
/* Article links now match text size for consistency */
.wzkb-articles-list li a {
    font-size: 16px; /* Updated from 15px */
    font-weight: 500;
}

/* Section headers with improved contrast */
.wzkb-section-name-level-1 {
    background: var(--wzkb-bg-light); /* Light background */
    color: var(--wzkb-color-text);   /* Dark text */
    border: 1px solid var(--wzkb-border-medium);
    border-radius: 4px;
    padding: 8px 16px;
}
```

__Customization Example:__

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

If you're using multi-product mode, you can target specific products:

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

### My Custom Colors Aren't Showing

__Solution 1:__ Clear your browser cache

- Press `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)

__Solution 2:__ Check CSS syntax

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

__Solution 3:__ Use `!important` if theme overrides variables

```css
:root {
    --wzkb-color-primary: #2563eb !important;
}
```

### Colors Look Different on Mobile

__Solution:__ Some CSS may be cached. Add this to force reload:

```css
/* Force fresh styles */
:root {
    --wzkb-color-primary: #2563eb !important;
}
```

### Specific Elements Not Changing

__Solution:__ Target elements directly:

```css
/* Override section headings specifically */
.wzkb h3.wzkb-section-name {
    background: #your-color !important;
    color: #fff !important;
}
```

### Color Contrast Issues

__Tool:__ Check contrast at <https://webaim.org/resources/contrastchecker/>

__WCAG AA Standard:__ Minimum 4.5:1 ratio for normal text

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

- __Coolors.co__ - Generate color palettes
- __Adobe Color__ - Color wheel and harmony rules
- __Material Design Colors__ - Pre-defined palettes
- __Paletton__ - Color scheme designer
- __Contrast Checker__ - Ensure accessibility

## Need More Help?

- __Documentation:__ <https://webberzone.com/support/knowledgebase/>
- __Support Forums:__ <https://wordpress.org/support/plugin/knowledgebase/>
- __GitHub Issues:__ <https://github.com/WebberZone/knowledgebase/issues>
