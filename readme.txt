=== Knowledge Base ===
Contributors: Ajay, webberzone
Donate link: https://ajaydsouza.com/donate/
Tags: knowledge base, knowledgebase, FAQ, frequently asked questions, support, documentation
Requires at least: 6.3
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Effortlessly build a comprehensive knowledge base for unlimited products on your WordPress site and elevate your customer support experience.

== Description ==

[Knowledge Base](https://webberzone.com/plugins/knowledgebase/) is an easy-to-use WordPress plugin that allows you to create a knowledge base / FAQ section on your site.

You can use it to create a single or multi-product knowledge base with little effort.

The plugin was born after I tried several free plugins and themes that didn't fit my purpose. It's designed to be very easy to install and use out of the box.

You can view a [live demo of my knowledge base](https://webberzone.com/support/knowledgebase/).

= Terminology =

* __Articles__: A custom post type `wz_knowledgebase` is used to store all the knowledge base articles
* __Sections__: A custom taxonomy ( `kbcategory` ) used to create the knowledge base. You will need *at least one category* to display the knowledge base. Add these categories under *Knowledge Base > Sections*
* __Tags__: Additionally you can use tags ( `kbtags` ) can also be used for each knowledge base article.

= Main features =

* Supports unlimited knowledge bases using different sections with unlimited nested levels
* Inbuilt styles that display the Knowledge Base beautifully and are fully responsive - Uses the [Responsive Grid System](http://www.responsivegridsystem.com/)
* Customizable permalinks: Archives are enabled so your knowledge base can be viewed automatically at `/knowledgebase/` upon activation. You can change this on the Settings page
* Shortcode: `[knowledgebase]` will allow you to display the knowledge base on any page you choose. For other shortcodes, check the FAQ
* Gutenberg block: You can display the knowledge base using a block. Find it by typing `kb` or `knowledge base` when adding a new block
* Breadcrumbs: Default templates include breadcrumbs. Alternatively, use the function or shortcode to display this where you want
* Widgets: WZKB Articles, WZKB Sections and WZKB Breadcrumbs
* Inbuilt cache to speed up the display of your knowledge base articles

= Contribute =

If you have an idea, I'd love to hear it. WebberZone Knowledge Base is also available on [Github](https://github.com/WebberZone/knowledgebase). You can [create an issue on the Github page](https://github.com/WebberZone/knowledgebase/issues) or, better yet, fork the plugin, add a new feature and send me a pull request.

== Installation ==

= WordPress install (The easy way) =

1. Navigate to “Plugins” within your WordPress Admin Area
2. Click “Add new” and in the search box enter “Knowledgebase” or "Knowledge Base"
3. Find the plugin in the list (usually the first result) and click “Install Now”
4. Activate or Network activate the Plugin in WP-Admin under the Plugins screen

= Manual install =

1. Download the plugin
2. Extract the contents of knowledgebase.zip to wp-content/plugins/ folder. You should get a folder called knowledgebase.
3. Activate or Network activate the Plugin in WP-Admin under the Plugins screen

= Usage =

1. Visit `Knowledge Base &raquo; Sections` to add new categories to the knowledge base
2. Visit `Knowledge Base &raquo; Add New` to add new Articles to the knowledge base. You can select a section from there while adding
3. Optionally, create a new page or edit an existing one and add the shortcode `[knowledgebase]` or use the block to set up this page to display the knowledgebase

The plugin supports unlimited levels of category hierarchy. To build a multi-product knowledge base:

1. Set the *First section level* under the Output tab to 2
2. Create a set of top-level sections for each product
3. Create sub-sections for each of the products

[This live demo](https://webberzone.com/support/knowledgebase/) is a working example of a multi-product knowledge base.

== Frequently Asked Questions ==

If your question is not listed below, please create a new post at the [WordPress.org support forum](http://wordpress.org/support/plugin/knowledgebase). It is the fastest way to get support, as I monitor the forums regularly. I also provide [premium *paid* support via email](https://webberzone.com/support/).

= 404 errors on the knowledge base =

This is usually due to outdated permalinks. To flush the existing permalink rules, visit Settings > Permalinks in your WordPress admin area.

= Shortcodes =

For details on all the shortcodes included in the plugin, refer to [this Knowledge Base article](https://webberzone.com/support/knowledgebase/knowledge-base-shortcodes/).

= Using your own templates for archives and search =

WebberZone Knowledge Base comes built with custom templates to display archives of the articles, category archives, and search results. You can easily override any of these templates by creating your template in your theme's folder or in `wp-content/knowledgebase/templates`

1. Article view: single-wz_knowledgebase.php or single-wz_knowledgebase.html
2. Articles archive: archive-wz_knowledgebase.php or archive-wz_knowledgebase.html
3. Category archive: taxonomy-wzkb_category.php or taxonomy-wzkb_category.html
4. Search results: wzkb-search.php or wzkb-search.html

= How do I sort the posts or sections? =

The plugin doesn't have an inbuilt feature to sort posts or sections. You will need an external plugin like [Intuitive Custom Post Order](https://wordpress.org/plugins/intuitive-custom-post-order/) which allows you to easily drag and drop posts, sections or tags to display them in a custom order.

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

= 2.3.0 =
Completely rewritten. Several new features and enhancements.

== Changelog ==

= 2.3.0 =

The plugin has been completely rewritten to use classes and autoloading.

* Features:
	* New block: Knowledge Base Articles.
	* New block: Knowledge Base Breadcrumbs.

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
