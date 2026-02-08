# Knowledge Base v3.0 – Multi-Product Support, Ratings, and Premium Polish

Knowledge Base v3.0 is the biggest update in the plugin's history. I've completely re-architected the core to support multi-product documentation, added a setup wizard for new users, and introduced a powerful Pro add-on for those who need enterprise-grade features.

Whether you're running a simple FAQ or a complex support hub for multiple software products, this release has something for you.

## Multi-Product Support (Free)

The most requested feature is now in the free core. You can finally organise your knowledge base by **Product**, not just categories.

- **Hierarchical Structure:** Create a `wzkb_product` taxonomy to group sections and articles under specific products.
- **Smart Templates:** The frontend automatically adapts to show product-specific landing pages and navigation.
- **Migration Wizard:** Moving from a flat structure? I've included a built-in tool to map your existing content to products in bulk.
- **Product Widget:** A dedicated widget to display sections for a specific product in your sidebar.

If you manage documentation for multiple plugins, themes, or services, this changes everything.

## Setup Wizard (Free)

Setting up a knowledge base shouldn't be a chore. The new Setup Wizard guides you through the essentials—permalinks, style preferences, and product mode—so you're ready to publish in under two minutes.

## Developer Notes: Standardised CSS (Free)

I've cleaned up the codebase to follow modern standards. CSS class names now use consistent hyphenation (e.g., `wzkb_section` is now `wzkb-section`).

If you have custom CSS targeting the old underscores, you'll need to update your stylesheets. This change ensures better compatibility with themes and easier styling moving forward.

## Pro Features

For those who need more power, the Pro add-on unlocks a suite of tools designed for professional support teams.

### Custom Permalinks for Multi-Product Sites

Craft article, product, section, and tag URLs using placeholders like `%product_name%`, `%section_name%`, `%post_id%`, or `%postname%`.

Why does this matter?

- Multi-product documentation stays organised with logical URL hierarchies.
- SEO benefits from human-readable, keyword-rich permalinks.
- Visitors understand where they are just by glancing at the address bar.

The engine handles rewrite rules, query vars, and clash prevention automatically. Even root-level article URLs work without a hitch.

### Article Rating System

Want to know which articles actually help your readers? The new rating system gives you two options:

- **Binary mode:** Simple "Useful" / "Not Useful" buttons.
- **Scale mode:** Classic 1–5 star ratings.

Ratings appear automatically at the bottom of each article, or you can place them anywhere using the `[wzkb_rating]` shortcode.

I've built in five tracking methods—cookie, hashed IP, cookie + IP, logged-in users only, or no tracking at all—so you can balance accuracy with privacy requirements. The admin column shows ratings at a glance, and you can sort articles by a Bayesian average that rewards consistently helpful content over flukes.

If a reader marks an article as unhelpful, they can optionally leave feedback. You'll receive an email notification so you can improve the content.

### Help Widget – Self-Service Support on Every Page

The floating Help Widget brings your knowledge base to visitors wherever they are on your site. Click the button and they can:

- Search your articles instantly.
- Browse suggested reads based on the current page.
- Send you a message if they can't find an answer.

Everything is customisable: button position, colours, greeting text, and whether to show on mobile. The widget reuses the Related Articles query for smart suggestions and includes honeypot protection to keep spam out of your inbox.

Think of it as a mini help desk that's always available—without the overhead of live chat.

### Seven Premium Layouts

First impressions matter. Knowledge Base Pro ships with seven designer layouts:

![Knowledge Base Pro Premium Styles Showcase](insert-link-to-styles-collage.jpg)
*Caption: From top-left: Card, Minimal, Gradient, and Professional layouts.*

- Card
- Minimal
- Boxed
- Gradient
- Compact
- Magazine
- Professional

Switch between them in **Knowledge Base → Settings → Styles**. Each layout loads its own CSS, so you get a polished look without writing a single line of code.

### Rating Block for Gutenberg

If you're building with the block editor, you'll appreciate the new Rating block. Drop it into any template or article and it honours your global rating settings automatically. When ratings are disabled, the block displays a helpful notice in the editor so you know exactly what's happening.

### Ready for Busy Teams

Once activated, the Pro Admin loader removes "PRO" locks from registered settings and adds a Clear Cache button directly to the settings footer, saving time for support teams. The main Pro bootstrapper instantiates modules only when required, registers premium styles via filters, and exposes an `uninstall_pro()` helper to tidy options if you ever retire the add-on.

### Upgrade Today

Knowledge Base v3.0 turns your site into a complete self-service portal. Whether you're using the free version for multi-product docs or upgrading to Pro for ratings and premium styles, this release is built to help you support your users better.

[Download Knowledge Base Free](https://wordpress.org/plugins/knowledgebase/) | [Get Knowledge Base Pro](https://webberzone.com/plugins/knowledgebase/pro/)
