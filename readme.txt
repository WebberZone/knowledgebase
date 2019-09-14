=== Knowledge Base ===
Contributors: Ajay, webberzone
Donate link: https://ajaydsouza.com/donate/
Tags: knowledge base, knowledgebase, FAQ, frequently asked questions, support, documentation
Requires at least: 4.7
Tested up to: 5.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Quickly and efficiently create a highly-flexible knowledge base or FAQ on your WordPress blog.

== Description ==

As the name suggests, [Knowledge Base](https://webberzone.com/plugins/knowledgebase/) will allow you to create a knowledge base / FAQ section on your WordPress blog.

The plugin was born after I tried several free plugins and themes out there and that couldn't fit my purpose. It's designed to be very easy to install and use out of the box and I'll be adding more features into the core and as addons.

The plugin uses a custom post in conjunction with custom taxonomies to create and display your knowledge base.

= Main features: =

* Uses a custom post type `wz_knowledgebase` with a slug of `wzkb` ensuring your data always stays even if you choose to delete this plugin
* Customizable permalinks: Archives are enabled so your knowledge base can be viewed at `/knowledgebase/` automatically on activation. You can change this in the Settings page
* Uses Categories ( `kbcategory` ) to automatically draw up the knowledge base. You will need at least one category in order to display the knowledge base
* Additionally tags ( `kbtags` ) can also be used for each knowledge base article
* Shortcode `[knowledgebase]` will allow you to display the knowledge base on any page of your choosing
* Breadcrumbs: Default templates include breadcrumbs. Alternatively, use functions or shortcode to display this where you want
* Inbuilt styles that display the knowledge beautifully and are fully responsive - Uses the [Responsive Grid System](http://www.responsivegridsystem.com/)
* Supports unlimited nested of categories
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

Download the plugin
1. Extract the contents of knowledgebase.zip to wp-content/plugins/ folder. You should get a folder called knowledgebase.
2. Activate or Network activate the Plugin in WP-Admin under the Plugins screen
3. Create a new page or edit an existing one and add the shortcode `[knowledgebase]` to set up this page to display the knowledgebase
4. Visit `Knowledge Base &raquo; Add New` to add new Articles to the knowledge base
5. Visit `Knowledge Base &raquo; Sections` to add new categories to the knowledge base. Alternatively, you can add new categories from the meta box in the Add New page

The plugin supports unlimited levels of category hierarchy, however, the recommended setting for creating the knowledge base is to create a top level category with the name of the knowledge base and sub-level categories for each section of this knowledge base.

== Frequently Asked Questions ==

If your question isn't listed here, please create a new post at the [WordPress.org support forum](http://wordpress.org/support/plugin/knowledgebase). It is the fastest way to get support as I monitor the forums regularly. I also provide [premium *paid* support via email](https://webberzone.com/support/).

= 404 errors on the knowledge base =

This is usually because of outdated permalinks. To flush the existing permalinks rules simply visit Settings &raquo; Permalinks in your WordPress admin area.

= Shortcode =

You can display the knowledge base anywhere in your blog using the `[knowledgebase]` shortcode. The shortcode takes one optional attribute `category`:

`[knowledgebase category="92"]`

*category* : Category ID for which you want to display the knowledge base. You can find the ID in the Sections listing under the Knowledge Base menu in the WordPress Admin.

You can also display the search form using `[kbsearch]`

= Using your own templates for archives and search =

WebberZone Knowledge Base comes inbuilt with a set of custom templates to display archives of the articles, category archives as well as search results. You can easily override any of these templates by creating your own template in your theme's folder

1. Articles archive: archive-wz_knowledgebase.php
2. Category archive: taxonomy-wzkb_category.php
3. Search results: search-wz_knowledgebase.php

= How do I sort the posts or sections? =

The plugin doesn't have an inbuilt feature to sort posts or sections. You will need an external plugin like [Intuitive Custom Post Order](https://wordpress.org/plugins/intuitive-custom-post-order/) which allows you to easily drag and drop posts, sections or tags to display them in a custom order.


== Screenshots ==

1. Knowledge Base Menu in the WordPress Admin
2. Knowledge Base Viewer Facing with Default styles
3. Knowledge Base Category view in the WordPress Admin
4. Settings &raquo; General
5. Settings &raquo; Output
6. Settings &raquo; Styles
7. Shortcode for informative messages / alerts

== Upgrade Notice ==

= 1.8.0 =
* New features: Caching system.
Check the Changelog for more details


== Changelog ==

= 1.8.0 - 14 September 2019 =

Release post: [https://wzn.io/31oZf84](https://wzn.io/31oZf84)

* Features:
	* New option to enable the internal cache. Saving the settings page will delete the cache

= 1.7.0 - 12 February 2019 =

Release post: [https://wzn.io/2DwRbre](https://wzn.io/2DwRbre)

* Features:
	* New shortcode `[kbalert]` to display alerts / informative messages similar to [Bootstrap](https://getbootstrap.com/docs/4.3/components/alerts/). The types of alerts are primary, secondary, success, danger, warning, info, light and dark
	* New Output option to display empty sections
	* New option to display sidebar with the default templates

* Bug fixes:
	* Checkboxes on the admin page will now properly indicate their status for new options that aren't saved yet

= 1.6.0 - 5 December 2018 =

Release post: [https://wzn.io/2RDdiCe](https://wzn.io/2RDdiCe)

* Features:
	* New functions to add breadcrumbs - Use `wzkb_breadcrumb` to display the breadcrumb. `wzkb_get_breadcrumb` _returns_ the breadcrumb and `[kbbreadcrumb]` is the corresponding shortcode
	* The default category and archive templates now display the breadcrumb below the search bar
	* New option to set the title of the knowledge base. Find it under the Options tab under Settings

* Enhancements:
	* Support for Gutenberg/Block Editor in WordPress 5.0

= 1.5.0 - 6 August 2017 =

Release post: [https://wzn.io/2vbHZUt](https://wzn.io/2vbHZUt)

* Features:
	* New option to set the number of columns to display the knowledge base archives
	* New option to set what section level should the knowledge base start from. Ideal if you want to have separate knowledge bases for multiple products
	* New option to show excerpt below the link. Contributed by [@border0464111](https://github.com/border0464111)
	* New option to turn off the section link. Contributed by [@border0464111](https://github.com/border0464111)
	* New option to show the number of articles in an orange circle next to the header. You can override the color by styling `wzkb_section_count`
	* New function `wzkb_get_default_option()` to retrieve the default setting

* Enhancements:
	* Make nested wrapper. Contributed by [@border0464111](https://github.com/border0464111)
	* Settings page is now a tabbed interface using jQuery without needing to reload the page
	* Modified checkbox settings are indicated in the Settings page
	* Added the number of articles in "At a Glance" widget in the WordPress Dashboard

* Bug fixes:
	* Fixed several settings API functions

= Earlier versions =

For the changelog of earlier versions, please refer to the separate changelog.txt file or the [Github releases page](https://github.com/WebberZone/knowledgebase/releases)

