=== Knowledge Base ===
Contributors: Ajay, webberzone
Donate link: https://ajaydsouza.com/donate/
Tags: knowledge base, knowledgebase, FAQ, support, documentation
Requires at least: 6.6
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 3.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Effortlessly create a powerful, multi-product knowledge base. Boost your support, reduce tickets, scale your documentation and make customers happy!

== Description ==

[Knowledge Base](https://webberzone.com/plugins/knowledgebase/) makes building a knowledge base or FAQ for your WordPress site easy, fast, and scalable.

🎯 Perfect for:
✅ Multi-product companies
✅ SaaS platforms
✅ Ecommerce support centres
✅ Documentation hubs

🔎 [Live Demo](https://webberzone.com/support/knowledgebase/).

### Main features

🚀 Unlimited Knowledge Bases — Support as many products as you like, with unlimited sections and sub-sections.
🎨 Beautiful, Responsive Layouts — Ships with clean templates powered by the Responsive Grid System.
🔗 Customisable Permalinks — View your KB at /knowledgebase/ by default or change it easily.
✨ Shortcodes + Gutenberg Blocks — Add KB listings anywhere using [knowledgebase] or use the Knowledge Base block.
🧭 Built-in Breadcrumbs — Improve UX and SEO with breadcrumb navigation.
🧩 Widgets Included — WZKB Articles, WZKB Sections, and WZKB Breadcrumbs widgets.
⚡ Built-in Caching — Speed up your Knowledge Base without extra plugins.

### Pro features

- ⭐ __Article Rating & Feedback System__ — Collect binary or 5-star feedback with optional follow-up questions, admin alerts, Bayesian sorting, and GDPR-friendly tracking modes.
- 💬 __Help Widget__ — Offer an in-app support hub with live search, suggested articles, and a contact form inside a floating assistant.
- 🧭 __Custom Permalinks Engine__ — Craft advanced URL structures for articles, sections, tags, and products using dynamic placeholders.
- 🎨 __Premium Layout Pack__ — Unlock seven additional frontend styles (Card, Minimal, Boxed, Gradient, Compact, Magazine, Professional).
- 🛠️ __Advanced Admin Tools__ — Control knowledge base caching with expiry settings, on-demand cache clearing, and other productivity enhancements.

### Key Concepts

* __Articles:__ Custom post type `wz_knowledgebase` — your FAQs, how-to guides, and documentation.
* __Products:__ Custom taxonomy `wzkb_product` — link articles to one or more products.
* __Sections:__ Custom taxonomy `wzkb_category` — organize content neatly into categories.
* __Tags:__ Optional `wzkb_tag` taxonomy — make finding content even easier.

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

1. Go to __Knowledge Base &raquo; Products__ — add your first Products if you've selected Multi-Product mode.
2. Go to __Knowledge Base &raquo; Sections__ — add your first categories.
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

* Features:
	* Introduced a hierarchical Products taxonomy (`wzkb_product`) for multi-product knowledge bases.
		* Migration wizard with dry-run and batch processing to map existing sections and articles to products.
		* Product-based frontend templates that preserve section hierarchy.
		* Admin UI enhancements for managing products, sections, and migration.
	* Setup Wizard to guide users through the initial configuration.
	* New Product widget to display sections for a specific product.
	* [Pro] Custom permalinks for Products, Sections, Tags, and Articles.
	* [Pro] Cache tools: Clear cache button and cache expiry option in the settings.
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
