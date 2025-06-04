=== Knowledge Base ===
Contributors: Ajay, webberzone
Donate link: https://ajaydsouza.com/donate/
Tags: knowledge base, knowledgebase, FAQ, support, documentation
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.3.1
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

= 2.3.1 =
Fixed security issue where arguments passed to the shortcodes were not properly sanitized.

== Changelog ==

= 3.0.0 =

* Features:
	* Introduced a new hierarchical Products taxonomy (`wzkb_product`) enabling multi-product support for articles and sections.
		* Migration wizard to map existing sections and articles to products, with dry-run and batch processing.
		* Product-based frontend templates that preserve section hierarchy.
		* Admin UI enhancements for managing products, sections, and migration.
	* Setup Wizard to guide users through the initial setup process.
	* New Product widget to display the Sections for a specific Product.

* Modifications:
	* Standardized CSS class names to use consistent hyphenation (e.g., `wzkb_section` is now `wzkb-section`). If you have custom CSS targeting the old class names, you'll need to update your stylesheets.
	* New Hooks_Registry class added to handle hooks in a more organized way and prevents accidental duplicate hooks.

* Breaking Changes:
	* CSS classes have been renamed for consistency:
		* `wzkb_section` → `wzkb-section`
		* `wzkb_section_wrapper` → `wzkb-section-wrapper`
		* `wzkb_section_name` → `wzkb-section-name`
		* `wzkb_section_count` → `wzkb-section-count`
		* `wzkb_shortcode` → `wzkb-shortcode`
		* `wzkb_block` → `wzkb-block`
		* And other similar class name changes
	* If you have custom CSS targeting these classes, you'll need to update your selectors

= 2.3.1 =

* Bug fixes:
	* Fixed security issue where arguments passed to the shortcodes were not properly sanitized.

= 2.3.0 =

Release post: [https://webberzone.com/blog/knowledge-base-v2-3-0/](https://webberzone.com/blog/knowledge-base-v2-3-0/)

The plugin has been completely rewritten to use classes and autoloading.

* Features:
	* New block: Knowledge Base Articles.
	* New block: Knowledge Base Breadcrumbs.
	* New block: Knowledge Base Sections.

* Modifications:
	* Enhanced breadcrumb navigation with semantic HTML5 markup and improved accessibility
	* Added Schema.org BreadcrumbList markup for better SEO
	* Added support for custom Unicode separators in breadcrumbs

= 2.2.1 =

* Enhancements:
	* The plugin will now load RTL styles if your site is in RTL mode.
	* Only load CSS on the frontend if the option is enabled in the Settings page.

* Bug fixes:
	* Fixed a security issue in the alerts block that impacted edge cases of stored data from contributors. Now the alert block content is passed through `wp_kses_post` before being displayed.
	* Fixed a bug where the block would not render correctly in the editor

= 2.2.0 =

Release post: [https://webberzone.com/blog/knowledge-base-v2-2-0/](https://webberzone.com/blog/knowledge-base-v2-2-0/)

* Enhancements:
	* The plugin will now look for templates within `wp-content/knowledgebase/templates` folder if it is not found within the existing theme before using the plugin's included templates
	* Alerts block now shows a preview and the Default style is inserted correctly
	* Upgrade settings handling to use the WebberZone Settings_API class
	* Knowledge Base block is wrapped in the `<Disabled>` component which prevent any accidental clicking when you're using it in the block editor (Gutenberg)

= Earlier versions =

For the changelog of earlier versions, please refer to the separate changelog.txt file or the [Github releases page](https://github.com/WebberZone/knowledgebase/releases)
