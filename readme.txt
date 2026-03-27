=== Knowledge Base ===
Contributors: Ajay, webberzone
Donate link: https://wzn.io/donate-wz
Tags: knowledge base, documentation, FAQ, support, wiki
Requires at least: 6.7
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 3.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Build a multi-product knowledge base for WordPress. Reduce support tickets with self-service docs, FAQs, and a built-in help center.

== Description ==

[Knowledge Base](https://webberzone.com/plugins/knowledgebase/) makes building a knowledge base or FAQ for your WordPress site easy, fast, and scalable.

Whether you need a simple FAQ page, a full self-service help center, or a structured multi-product wiki, Knowledge Base scales to fit. Organize articles into products and sections, customize permalinks, and let your customers help themselves: no coding required.

Perfect for:

- Multi-product companies managing multiple help centers
- SaaS platforms with self-service documentation portals
- Ecommerce support centres reducing ticket volume
- Documentation hubs and internal company wikis
- Developers building customer-facing knowledge portals

[Live Demo](https://webberzone.com/support/knowledgebase/).

### Powerful features available in the Free version

- __Unlimited Knowledge Bases__: Support as many products as you like, with unlimited sections and sub-sections.
- __Beautiful, Responsive Layouts__: Ships with clean templates powered by the Responsive Grid System.
- __Customisable Permalinks__: View your KB at /knowledgebase/ by default or change it easily.
- __Shortcodes + Gutenberg Blocks__: Add KB listings anywhere using [knowledgebase] or use the Knowledge Base block.
- __Built-in Breadcrumbs__: Improve UX and SEO with breadcrumb navigation.
- __Widgets Included__: WZKB Articles, WZKB Sections, and WZKB Breadcrumbs widgets.
- __Built-in Caching__: Speed up your Knowledge Base without extra plugins.

### Pro features

[Knowledge Base Pro](https://webberzone.com/plugins/knowledgebase/#pro) enhances the plugin with advanced features for larger documentation sites, including ratings and feedback, a help widget, a powerful custom permalinks engine, premium layouts, and additional admin tools.

- __Article Rating & Feedback System__: Collect binary or 5-star feedback with optional follow-up questions, admin alerts, Bayesian sorting, and GDPR-friendly tracking modes.
- __Help Widget__: Offer an in-app support hub with live search, suggested articles, and a contact form inside a floating assistant.
- __Custom Permalinks Engine__: Craft advanced URL structures for articles, sections, tags, and products using dynamic placeholders.
- __Knowledge Base Homepage Mode__: Display the Knowledge Base on your site homepage. The Knowledge Base URL becomes the homepage and the Knowledge Base archive URL redirects to the homepage.
- __Premium Layout Pack__: Unlock seven additional frontend styles (Card, Minimal, Boxed, Gradient, Compact, Magazine, Professional).
- __Advanced Admin Tools__: Control knowledge base caching with expiry settings, on-demand cache clearing, and other productivity enhancements.

### Key Concepts

* __Articles:__ Custom post type `wz_knowledgebase`: your FAQs, how-to guides, and documentation.
* __Products:__ Custom taxonomy `wzkb_product`: link articles to one or more products.
* __Sections:__ Custom taxonomy `wzkb_category`: organize content neatly into categories.
* __Tags:__ Optional `wzkb_tag` taxonomy: make finding content even easier.

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

Yes! Knowledge Base works equally well as a help center, wiki, FAQ site, or documentation portal. Use sections to organise topics and products to separate different areas of your documentation.

= Does it support multiple products or projects? =

Yes. Enable Multi-Product mode via the Setup Wizard to organise articles under separate Products, each with their own sections and sub-sections.

= Is it compatible with page builders like Elementor or Divi? =

Yes. You can use the [knowledgebase] shortcode in any page builder. The plugin also provides Gutenberg blocks for block-based themes.

= Can visitors search the knowledge base? =

Yes. The plugin includes a built-in search form (via the [wzkb_search] shortcode and a Search block for Gutenberg). The Pro version also adds a floating Help Widget with live search and suggested articles.

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

== Upgrade Notice ==

= 3.0.0 =
Major update: Multi-product mode, new Setup Wizard. Introduced Pro.


== Changelog ==

= 3.0.0 =

Release post: [https://webberzone.com/announcements/knowledge-base-v3-0-0/](https://webberzone.com/announcements/knowledge-base-v3-0-0/)

* Features:
	* Introduced a hierarchical Products taxonomy (`wzkb_product`) for multi-product knowledge bases.
		* Migration wizard with dry-run and batch processing to map existing sections and articles to products.
		* Product-based frontend templates that preserve section hierarchy.
		* Admin UI enhancements for managing products, sections, and migration.
	* Setup Wizard to guide users through the initial configuration.
	* New Product widget to display sections for a specific product.
	* Block Templates and Patterns:
		* Full Site Editor (FSE) support with custom block templates for Knowledge Base layouts.
		* Pre-designed block patterns including single article, archives, sections, products, and sidebar layouts.
		* Sidebar pattern with search, sections, products, and recent articles for easy navigation.
		* Templates work with both classic and block themes.
	* [Pro] Custom permalinks for Products, Sections, Tags, and Articles.
	* [Pro] Cache tools: Clear cache button and cache expiry option in the Settings page.
	* [Pro] Flush permalinks button in the Settings page.
	* [Pro] Knowledge Base Homepage Mode: Display the Knowledge Base on your site homepage, and redirect the Knowledge Base archive URL to the homepage.
	* [Pro] Article Rating System:
		* Binary or 5-star voting, optional follow-up feedback, shortcode support, and Tools page controls.
		* Multiple tracking methods (none, cookie, IP, cookie + IP, logged-in users) with hashed IP storage for GDPR compliance.
		* Email alerts, per-article reset tools, feedback storage, privacy exporter/eraser, and Bayesian average sorting in admin lists.
	* [Pro] Floating Help Widget providing a branded assistant with live search, suggested articles, configurable labels/colours, and a contact form with HTML email notifications.
	* [Pro] Premium layout pack with seven additional frontend styles (Card, Minimal, Boxed, Gradient, Compact, Magazine, Professional).

* Modifications:
	* Standardised CSS class names to use consistent hyphenation (e.g. `wzkb_section` → `wzkb-section`). If you have custom CSS targeting the old class names, you'll need to update your stylesheets.
	* Added `Hooks_Registry` class to organise hooks and prevent accidental duplicates.
	* Upgraded the WebberZone Settings API.
	* Media Handler now supports the FIFU WordPress plugin for featured image detection.
	* Knowledge Base Block will dynamically load the global settings when first inserted.

* Breaking Changes:
	* CSS classes have been renamed for consistency, for example:
		* `wzkb_section` → `wzkb-section`
		* `wzkb_section_wrapper` → `wzkb-section-wrapper`
		* `wzkb_section_name` → `wzkb-section-name`
		* `wzkb_section_count` → `wzkb-section-count`
		* `wzkb_shortcode` → `wzkb-shortcode`
		* `wzkb_block` → `wzkb-block`
		* and other similar class name changes.
	* If you have custom CSS targeting these classes, please update your selectors.

= Earlier versions =

For the changelog of earlier versions, please refer to the separate changelog.txt file or the [Github releases page](https://github.com/WebberZone/knowledgebase/releases)
