# Knowledge Base Style Customization Guide

__Version:__ 3.0.0  
__Last Updated:__ October 12, 2025  
__Audience:__ Site Administrators, Theme Developers

---

## Table of Contents

1. [Introduction](#introduction)
2. [Quick Start](#quick-start)
3. [Where to Add Custom CSS](#where-to-add-custom-css)
4. [Free Styles Customization](#free-styles-customization)
5. [Pro Styles Customization](#pro-styles-customization)
6. [Advanced Customization](#advanced-customization)
7. [Common Customization Examples](#common-customization-examples)
8. [Troubleshooting](#troubleshooting)

---

## Introduction

All Knowledge Base styles use __CSS Custom Properties (CSS Variables)__ for easy color customization. This means you can change the entire color scheme of your knowledge base without editing any plugin files.

### Benefits of CSS Variables

- ✅ __Easy to customize__ - Change colors in one place
- ✅ __No file editing__ - Add custom CSS through WordPress settings
- ✅ __Update-safe__ - Changes persist through plugin updates
- ✅ __Real-time preview__ - See changes immediately
- ✅ __No coding required__ - Simple copy-paste customization

---

## Quick Start

### 3-Step Customization Process

1. __Choose your style__ from the dropdown (Settings → Knowledge Base → Output)
2. __Find the CSS variables__ for that style (see sections below)
3. __Add custom CSS__ to override colors (Settings → Knowledge Base → Output → Custom CSS)

### Example: Change Primary Color

```css
:root {
    --wzkb-color-primary: #e63946; /* Your brand color */
}
```

That's it! The knowledge base will now use your brand color throughout.

---

## Where to Add Custom CSS

### Method 1: Plugin Settings (Recommended)

__Path:__ WordPress Admin → Knowledge Base → Settings → Output

1. Scroll to __"Custom CSS"__ field at the bottom
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

---

## Free Styles Customization

### Classic Style

__Default Colors:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #2c3e50;
    --wzkb-color-dark: #1a252f;
    --wzkb-color-text: #333;
    --wzkb-color-text-light: #666;
    --wzkb-color-white: #fff;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #f8f9fa;
    --wzkb-bg-lighter: #ecf0f1;
    --wzkb-bg-section: #f8f9fa;

    /* Border colors */
    --wzkb-border-main: #bdc3c7;
    --wzkb-border-light: #dee2e6;
    --wzkb-border-dark: #2c3e50;

    /* Accent colors */
    --wzkb-color-accent: #3498db;
    --wzkb-color-hover: #2980b9;
}
```

__Customization Example:__

```css
/* Modern Blue Theme for Classic */
:root {
    --wzkb-color-primary: #0066cc;
    --wzkb-color-dark: #004499;
    --wzkb-color-accent: #3399ff;
    --wzkb-bg-section: #f0f7ff;
    --wzkb-border-main: #99ccff;
}
```

---

### Legacy Style

__Default Colors:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #444;
    --wzkb-color-link: #0073aa;
    --wzkb-color-text: #333;
    --wzkb-color-text-light: #666;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #f5f5f5;
    --wzkb-bg-lighter: #fafafa;

    /* Border colors */
    --wzkb-border-color: #ddd;
    --wzkb-border-light: #e5e5e5;
}
```

__Customization Example:__

```css
/* WordPress Admin Colors for Legacy */
:root {
    --wzkb-color-primary: #23282d;
    --wzkb-color-link: #0073aa;
    --wzkb-bg-light: #f1f1f1;
    --wzkb-border-color: #ccd0d4;
}
```

---

## Pro Styles Customization

### Card Layout (modern.css)

__Default Colors:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #2563eb;
    --wzkb-color-dark: #1e40af;
    --wzkb-color-text: #1f2937;
    --wzkb-color-text-medium: #6b7280;
    --wzkb-color-white: #fff;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #f9fafb;
    --wzkb-bg-lighter: #f3f4f6;
    --wzkb-bg-card: #fff;

    /* Border colors */
    --wzkb-border-light: #e5e7eb;
    --wzkb-border-medium: #d1d5db;
    --wzkb-border-dark: #1e40af;

    /* Shadow */
    --wzkb-shadow-card: 0 1px 3px rgba(0, 0, 0, 0.1);
    --wzkb-shadow-hover: 0 4px 6px rgba(0, 0, 0, 0.15);
}
```

__Customization Example:__

```css
/* Purple Card Theme */
:root {
    --wzkb-color-primary: #7c3aed;
    --wzkb-color-dark: #5b21b6;
    --wzkb-bg-card: #faf5ff;
    --wzkb-border-dark: #7c3aed;
    --wzkb-shadow-card: 0 2px 4px rgba(124, 58, 237, 0.1);
}
```

---

### Minimal Style

__Default Colors:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #111827;
    --wzkb-color-dark: #030712;
    --wzkb-color-text: #374151;
    --wzkb-color-text-medium: #6b7280;
    --wzkb-color-text-light: #9ca3af;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #f9fafb;
    --wzkb-bg-lighter: #f3f4f6;

    /* Border colors */
    --wzkb-border-subtle: #f3f4f6;
    --wzkb-border-light: #e5e7eb;
}
```

__Customization Example:__

```css
/* Warm Minimal Theme */
:root {
    --wzkb-color-primary: #78350f;
    --wzkb-color-text: #451a03;
    --wzkb-bg-light: #fef3c7;
    --wzkb-bg-lighter: #fef9e7;
    --wzkb-border-light: #fde68a;
}
```

---

### Boxed Style

__Default Colors:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #1f2937;
    --wzkb-color-dark: #111827;
    --wzkb-color-text: #374151;
    --wzkb-color-text-medium: #6b7280;

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
    --wzkb-breadcrumb-separator: #6b7280;
    --wzkb-breadcrumb-border: #d1d5db;
}
```

__Customization Example:__

```css
/* Teal Boxed Theme */
:root {
    --wzkb-color-primary: #0d9488;
    --wzkb-color-dark: #115e59;
    --wzkb-bg-section-even: #f0fdfa;
    --wzkb-border-strong: #5eead4;
    --wzkb-border-dark: #14b8a6;
}
```

---

### Gradient Style

__Default Colors:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #7c3aed;
    --wzkb-color-secondary: #ec4899;
    --wzkb-color-dark: #5b21b6;
    --wzkb-color-text: #1f2937;
    --wzkb-color-text-medium: #6b7280;

    /* Gradient colors */
    --wzkb-gradient-start: #7c3aed;
    --wzkb-gradient-end: #ec4899;
    --wzkb-gradient-hover-start: #6d28d9;
    --wzkb-gradient-hover-end: #db2777;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #faf5ff;
    --wzkb-bg-lighter: #f5f3ff;

    /* Border colors */
    --wzkb-border-light: #e9d5ff;
    --wzkb-border-medium: #d8b4fe;
}
```

__Customization Example:__

```css
/* Ocean Gradient Theme */
:root {
    --wzkb-gradient-start: #0ea5e9;
    --wzkb-gradient-end: #0891b2;
    --wzkb-gradient-hover-start: #0284c7;
    --wzkb-gradient-hover-end: #0e7490;
    --wzkb-bg-light: #f0f9ff;
    --wzkb-border-light: #bae6fd;
}
```

---

### Compact Style

__Default Colors:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #1e293b;
    --wzkb-color-dark: #0f172a;
    --wzkb-color-text: #334155;
    --wzkb-color-text-medium: #64748b;
    --wzkb-color-accent: #3b82f6;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #f8fafc;
    --wzkb-bg-lighter: #f1f5f9;
    --wzkb-bg-section: #f8fafc;

    /* Border colors */
    --wzkb-border-light: #e2e8f0;
    --wzkb-border-medium: #cbd5e1;
}
```

__Customization Example:__

```css
/* Compact Dark Theme */
:root {
    --wzkb-color-primary: #334155;
    --wzkb-color-accent: #f59e0b;
    --wzkb-bg-section: #f1f5f9;
    --wzkb-border-light: #cbd5e1;
}
```

---

### Magazine Style

__Default Colors:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #dc2626;
    --wzkb-color-dark: #991b1b;
    --wzkb-color-text: #1f2937;
    --wzkb-color-text-medium: #6b7280;
    --wzkb-color-white: #fff;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #fef2f2;
    --wzkb-bg-lighter: #fee2e2;
    --wzkb-bg-accent: #dc2626;

    /* Border colors */
    --wzkb-border-strong: #991b1b;
    --wzkb-border-light: #fecaca;
    --wzkb-border-medium: #fca5a5;
}
```

__Customization Example:__

```css
/* Tech Magazine Theme */
:root {
    --wzkb-color-primary: #0ea5e9;
    --wzkb-color-dark: #0284c7;
    --wzkb-bg-light: #f0f9ff;
    --wzkb-bg-accent: #0ea5e9;
    --wzkb-border-strong: #0284c7;
}
```

---

### Professional Style

__Default Colors:__

```css
:root {
    /* Primary colors */
    --wzkb-color-primary: #1e40af;
    --wzkb-color-dark: #1e3a8a;
    --wzkb-color-text: #1f2937;
    --wzkb-color-text-medium: #6b7280;
    --wzkb-color-white: #fff;

    /* Background colors */
    --wzkb-bg-white: #fff;
    --wzkb-bg-light: #eff6ff;
    --wzkb-bg-lighter: #dbeafe;
    --wzkb-bg-primary: #1e40af;

    /* Border colors */
    --wzkb-border-strong: #1e40af;
    --wzkb-border-medium: #93c5fd;
    --wzkb-border-light: #bfdbfe;
}
```

__Customization Example:__

```css
/* Enterprise Green Theme */
:root {
    --wzkb-color-primary: #047857;
    --wzkb-color-dark: #065f46;
    --wzkb-bg-light: #ecfdf5;
    --wzkb-bg-primary: #047857;
    --wzkb-border-strong: #059669;
}
```

---

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

---

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

---

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

---

## Color Palette Generators

Need help choosing colors? Try these tools:

- __Coolors.co__ - Generate color palettes
- __Adobe Color__ - Color wheel and harmony rules
- __Material Design Colors__ - Pre-defined palettes
- __Paletton__ - Color scheme designer
- __Contrast Checker__ - Ensure accessibility

---

## Need More Help?

- __Documentation:__ <https://webberzone.com/support/knowledgebase/>
- __Support Forums:__ <https://wordpress.org/support/plugin/knowledgebase/>
- __GitHub Issues:__ <https://github.com/WebberZone/knowledgebase/issues>

---

## Summary Checklist

- [ ] Selected your style from plugin settings
- [ ] Found the CSS variables for that style
- [ ] Copied and customized variables
- [ ] Added CSS to plugin settings or theme
- [ ] Cleared browser cache
- [ ] Checked on mobile devices
- [ ] Verified color contrast for accessibility

---

*Last Updated: October 12, 2025*  
*Version: 3.0.0*  
*Guide Status: ✅ Complete*
