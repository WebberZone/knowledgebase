=== Knowledge Base ===
Contributors: Ajay, webberzone
Donate link: https://wzn.io/donate-wz
Tags: knowledge base, documentation, FAQ, support, wiki
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 3.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Build a multi-product knowledge base for WordPress. Reduce support tickets with self-service docs, FAQs, and a built-in help center.

== Description ==

[Knowledge Base](https://webberzone.com/plugins/knowledgebase/) makes building a knowledge base or FAQ for your WordPress site easy, fast, and scalable.

Whether you need a simple FAQ page, a full self-service help center, or a structured multi-product wiki, Knowledge Base scales to fit. Organize articles into products and sections, customize permalinks, and let your customers help themselves: no coding required.

Perfect for:

- Multi-product companies managing multiple help centers
- SaaS platforms with self-service documentation portals
- Ecommerce support centers reducing ticket volume
- Documentation hubs and internal company wikis
- Developers building customer-facing knowledge portals

[Live Demo](https://webberzone.com/support/knowledgebase/).

### Powerful features available in the Free version

- __Unlimited Knowledge Bases__: Support as many products as you like, with unlimited sections and sub-sections.
- __Beautiful, Responsive Layouts__: Ships with clean templates powered by the Responsive Grid System.
- __Customisable Permalinks__: View your KB at /knowledgebase/ by default or change the base slugs for articles, sections, products, and tags. Advanced custom permalink structures with dynamic placeholders are available in Pro.
- __Shortcodes + Gutenberg Blocks__: Add KB listings anywhere using [knowledgebase] or use the Knowledge Base block.
- __Built-in Breadcrumbs__: Improve UX and SEO with breadcrumb navigation.
- __Widgets Included__: WZKB Articles, WZKB Sections, WZKB Products, and WZKB Breadcrumbs widgets.
- __Built-in Caching__: Speed up your Knowledge Base without extra plugins. Configurable cache expiry settings are available in Pro.
- __Multilingual Ready__: Full WPML and Polylang compatibility — translate articles, sections, products, and tags; language-aware caching and widgets included out of the box.
- __Auto Table of Contents__: Automatically generate a linked Table of Contents from article headings, with configurable depth and minimum heading threshold.
- __Live Search Suggestions__: Show accessible AJAX search suggestions as visitors type in the Knowledge Base search form.
- __Related Articles__: Display related articles at the bottom of KB articles based on categories and tags.
- __Alerts__: Add attention-grabbing alert boxes with the [kbalert] shortcode or Alerts block.
- __Settings Export & Import__: Back up and restore all plugin settings as a JSON file. Sensitive values (API keys, webhook secrets) are automatically stripped on export and never overwritten on import.

### Pro features

[Knowledge Base Pro](https://webberzone.com/plugins/knowledgebase/#pro) enhances the plugin with advanced features for larger documentation sites, including ratings and feedback, a help widget, a powerful custom permalinks engine, premium layouts, enhanced TOC surfaces, and additional admin tools.

- __Article Rating & Feedback System__: Collect binary or 5-star feedback with optional follow-up questions, admin alerts, Bayesian sorting, and GDPR-friendly tracking modes.
- __Help Widget__: Offer an in-app support hub with live search, suggested articles, and a contact form inside a floating assistant.
- __Custom Permalinks Engine__: Craft advanced URL structures for articles, sections, tags, and products using dynamic placeholders.
- __Knowledge Base Homepage Mode__: Display the Knowledge Base on your site homepage. The Knowledge Base URL becomes the homepage and the Knowledge Base archive URL redirects to the homepage.
- __Premium Layout Pack__: Unlock seven additional frontend styles (Modern, Minimal, Boxed, Gradient, Compact, Magazine, Professional).
- __Enhanced Table of Contents__: Three Pro TOC delivery surfaces — a sidebar widget that renders the TOC for the current article, a Gutenberg block to insert the TOC inline, and a floating/sticky panel that follows the reader down the page.
- __Advanced Admin Tools__: Control knowledge base caching with expiry settings, on-demand cache clearing, and other productivity enhancements.
- __Documentation Layout Mode__: Transform any KB page into a three-column docs site with a sticky section-tree sidebar on the left, article content in the center, and an "On this page" TOC rail on the right. Collapsible accordion navigation adapts to the current product, section, or article automatically.
- __Section Tree Block & Widget__: Display a context-aware hierarchical navigation tree of your KB products, sections, and articles anywhere — as a Gutenberg block or a classic sidebar widget. The tree collapses and expands sections with an accessible accordion, and highlights the current page automatically.
- __GitHub Integration__: Sync markdown documentation from a GitHub repo. Push changes via webhooks and articles are created or updated automatically. YAML frontmatter controls slug, title, products, and sections.
- __Article Export & Import__: Export all Knowledge Base articles as a Markdown ZIP (with YAML frontmatter), a SQL INSERT dump, or an XLSX metadata spreadsheet. Re-import Markdown ZIPs to restore or migrate articles, with automatic taxonomy mapping and overwrite/skip control.
- __Ask the Docs (beta)__: Visitors ask a question in plain language and get a short answer drawn only from your published articles, with links to the sources. Uses the AI provider you connect under Settings > Connectors in WordPress 7.0 or later, with answer caching, per-visitor and daily limits, and a Content gaps report of unanswered questions.

### Key Concepts

* __Articles:__ Custom post type `wz_knowledgebase`: your FAQs, how-to guides, and documentation.
* __Products:__ Custom taxonomy `wzkb_product`: link articles to one or more products.
* __Sections:__ Custom taxonomy `wzkb_category`: organize content neatly into categories.
* __Tags:__ Optional `wzkb_tag` taxonomy: make finding content even easier.

### Multilingual sites

Knowledge Base works with WPML, Polylang and TranslatePress. TranslatePress translates the knowledge base with the rest of the page, including REST responses, related articles, and live-search suggestions. No additional Knowledge Base configuration is needed for TranslatePress.

Rendered output and REST caches are separated by language, preventing cached content and links from being reused across languages.

### Contribute

If you have an idea, I'd love to hear it. WebberZone Knowledge Base is also available on [Github](https://github.com/WebberZone/knowledgebase). You can [create an issue on the Github page](https://github.com/WebberZone/knowledgebase/issues) or, better yet, fork the plugin, add a new feature and send me a pull request.

== Installation ==

### WordPress install (The easy way)

1. Navigate to “Plugins” within your WordPress Admin Area
2. Click “Add new” and in the search box enter “Knowledgebase” or "Knowledge Base"
3. Find the plugin in the list (usually the first result) and click “Install Now”
4. Activate or Network activate the Plugin in WP-Admin under the Plugins screen

### Manual install

1. Download the plugin
2. Extract the contents of knowledgebase.zip to wp-content/plugins/ folder. You should get a folder called knowledgebase.
3. Activate or Network activate the Plugin in WP-Admin under the Plugins screen

### Quick Start

When you Activate the plugin for the first time, you will be taken to the Setup Wizard. Follow the instructions to set up your knowledge base.

After the Setup Wizard, you can:

1. Go to __Knowledge Base &raquo; Products__: add your first Products if you've selected Multi-Product mode.
2. Go to __Knowledge Base &raquo; Sections__: add your first categories.
3. Go to __Knowledge Base &raquo; Add New__— create articles and assign them to sections.

__Want a multi-product Knowledge Base only with Sections?__

1. Set the *First section level* under the Output tab to 2
2. Create a set of top-level sections for each product
3. Create sub-sections for each of the products

See a live example: [WebberZone Knowledge Base Demo](https://webberzone.com/support/knowledgebase/).

== Frequently Asked Questions ==

If you don't see your question answered below, please post it on the [WordPress.org support forum](http://wordpress.org/support/plugin/knowledgebase). This is the quickest way to get help, as I check the forums daily. For more personalized assistance, I also offer [premium *paid* support via email](https://webberzone.com/support/).

= Why are Knowledge Base pages giving 404 errors? =

Flush permalinks! Go to __Settings > Permalinks__ and just click __Save Changes__.

= What shortcodes are available? =

Check the full shortcode guide here: [Knowledge Base Shortcodes](https://webberzone.com/support/knowledgebase/knowledge-base-shortcodes/).

= Can I override templates? =

Absolutely! Copy these files into your theme or `wp-content/knowledgebase/templates/`:

* `single-wz_knowledgebase.php`
* `archive-wz_knowledgebase.php`
* `taxonomy-wzkb_category.php`
* `wzkb-search.php`

Or .html versions if you are using a block theme.

= How do I change the article or section order? =

Use a plugin like [Intuitive Custom Post Order](https://wordpress.org/plugins/intuitive-custom-post-order/) to easily drag and drop posts, sections or tags to display them in a custom order.

= Can I use this as a help center or wiki? =

Yes! Knowledge Base works equally well as a help center, wiki, FAQ site, or documentation portal. Use sections to organize topics and products to separate different areas of your documentation.

= Does it support multiple products or projects? =

Yes. Enable Multi-Product mode via the Setup Wizard to organize articles under separate Products, each with their own sections and sub-sections.

= Is it compatible with page builders like Elementor or Divi? =

Yes. You can use the [knowledgebase] shortcode in any page builder. The plugin also provides Gutenberg blocks for block-based themes.

= Can visitors search the knowledge base? =

Yes. The plugin includes a built-in search form (via the [wzkb_search] shortcode and a Search block for Gutenberg) with optional live AJAX suggestions. You can enable or disable live search from the plugin settings. The Pro version also adds a floating Help Widget with live search and suggested articles.

= Is it compatible with WPML or Polylang? =

Yes. Knowledge Base has built-in support for both WPML and Polylang:

* **Articles, sections, products, and tags** are all translatable. WPML uses `wpml-config.xml` (bundled with the plugin) for automatic configuration. Polylang auto-detects the public post type and taxonomies.
* **Widgets** (Articles, Sections, Products) translate stored term IDs to the current language automatically, so you can save a term ID in the default language and the widget will display the correct translation.
* **Archive URLs** resolve to the language-aware URL via `get_post_type_archive_link()`, which both WPML and Polylang filter automatically.
* **Caching** is language-aware — cached output is keyed per language so visitors never see content from the wrong locale.

**Known limitations:**

* The Pro Custom Permalinks feature builds URL structures using `home_url()`. With WPML you may need to set the *Language URL format* to *Directory* (e.g. `/en/`, `/fr/`) for custom permalink structures to resolve correctly per language.
* The built-in search form posts to `home_url( '/' )` — this is the standard WordPress search pattern and is handled correctly by both plugins' URL routing.

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/knowledgebase)

== Screenshots ==

1. Knowledge Base Menu in the WordPress Admin
2. Knowledge Base Viewer Facing with Default styles
3. Knowledge Base alerts
4. Settings &raquo; General
5. Settings &raquo; Output
6. Settings &raquo; Styles
7. Knowledge Base widgets

== Changelog ==

= 3.2.0 =

Release date: 7 October 2026
Release post: https://webberzone.com/announcements/knowledge-base-v3-2/

**Added**

* [Pro] Ask the Docs (beta): AI answers with article sources in Knowledge Base search boxes and the Help widget, with answer caching and daily and per-visitor limits. Requires WordPress 7.0 and a connected AI provider.
* [Pro] Opt-in Content gaps report for unanswered questions, with CSV export and an Empty log action.
* Abilities API tools for article search and section discovery: `knowledgebase/search-articles` and `knowledgebase/get-sections`.
* [Pro] Abilities API tools for draft-only article creation (`knowledgebase/create-article`) and grounded answers (`knowledgebase/ask`).
* `wzkb_search_posts_per_page` filter for the search results page size.

**Changed**

* Raised the minimum supported WordPress version to 6.9.
* Extended Clear Cache on the Tools and settings pages to clear cached REST API responses.
* Updated Freemius SDK to the latest version.

**Fixed**

* The Knowledge Base search results page size overrode the REST search endpoint's `limit` parameter.
* Knowledge Base search forms showed duplicate suggestions when Better Search live search was also active.
* [Pro] Help widget searches returned no articles with Better Search active, and the search box lost focus while typing.
* Settings repeater button labels used the wrong translation domain.

= Earlier versions =

For the changelog of earlier versions, please refer to the [releases page on GitHub](https://github.com/WebberZone/knowledgebase/releases).

== Upgrade Notice ==

= 3.2.0 =
Requires WordPress 6.9. Adds Abilities API tools for article search, section discovery and Pro draft creation. Pro also gains Ask the Docs (beta), grounded answers and a Content gaps report; this feature requires WordPress 7.0.
