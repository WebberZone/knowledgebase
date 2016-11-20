=== Knowledgebase ===
Contributors: Ajay, webberzone
Donate link: https://ajaydsouza.com/donate/
Tags: knowledgebase, FAQ, frequently asked questions, knowledge base, support, documentation
Requires at least: 3.9
Tested up to: 4.5
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The easiest way to create a Knowledgebase or FAQ on your WordPress blog.

== Description ==

As the name suggests, [Knowledgebase](https://webberzone.com/plugins/knowledgebase/) will allow you to create a simple Knowledgebase / FAQ section on your WordPress blog.

The plugin was born after I tried several free plugins and themes out there and that couldn't fit my purpose. It's designed to be very easy to install and use out of the box and I'll be adding more features into the core and as addons.

The plugin uses a custom post in conjunction with custom taxonomies to create and display your knowledgebase.

= Main features: =

* Uses a custom post type `wz_knowledgebase` ensuring your data always stays even if you choose to delete this plugin
* Customizable permalinks: Archives are enabled so your knowledgebase can be viewed at `/knowledgebase/` automatically on activation. You can change this in the Settings page along with the permalinks for viewing cateogories and tags
* Uses Categories ( `kbcategory` ) to automatically draw up the knowledgebase. You will need at least one category in order to display the knowledgebase
* You can also use tags ( `kbtags` ) for each knowledgebase article
* Shortcode `[[knowledgebase]]` will allow you to display the entire knowledgebase or pertaining to a section (category) of your choosing
* Inbuilt styles that display the knowledge beautifully and are fully responsive
* Supports unlimited level of categories

= Contribute =

Knowledgebase is fully functional and in fact, I use this to power https://webberzone.com/support/knowledgebase. However, there are still many features that I plan to add to this plugin. This includes inbuilt templates for articles, live search amongst others.

If you have an idea, I'd love to hear it. Knowledgebase is also available on [Github](https://github.com/WebberZone/knowledgebase). You can [create an issue on the Github page](https://github.com/WebberZone/knowledgebase/issues) or, better yet, fork the plugin, add a new feature and send me a pull request.

== Installation ==

= WordPress install (The easy way) =

1. Navigate to “Plugins” within your WordPress Admin Area
2. Click “Add new” and in the search box enter “Knowledgebase”
3. Find the plugin in the list (usually the first result) and click “Install Now”
4. Activate or Network activate the Plugin in WP-Admin under the Plugins screen

= Manual install =

Download the plugin
1. Extract the contents of knowledgebase.zip to wp-content/plugins/ folder. You should get a folder called knowledgebase.
2. Activate or Network activate the Plugin in WP-Admin under the Plugins screen
3. Create a new page or edit an existing one and add the shortcode `[knowledgebase]` to set up this page to display the knowledgebase
4. Visit `Knowledgebase &raquo; Add New` to add new Articles to the knowledgebase
5. Visit `Knowledgebase &raquo; Sections` to add new categories to the knowledgebase. Alternatively, you can add new categories from the meta box in the Add New page

The plugin supports unlimited levels of category hierarchy, however, the recommended setting for creating the knowledge base is to create a top level category with the name of the knowledgebase and sub-level categories for each section of this knowledgebase. Check out the Category view screenshot as an example.


== Frequently Asked Questions ==

If your question isn't listed here, please create a new post at the [WordPress.org support forum](http://wordpress.org/support/plugin/knowledgebase). It is the fastest way to get support as I monitor the forums regularly. I also provide [premium *paid* support via email](https://webberzone.com/support/).

= 404 errors on the knowledgebase =

This is usually because of outdated permalinks. To flush the existing permalinks rules simply visit Settings &raquo; Permalinks in your WordPress admin area.

= Shortcode =

You can display the knowledgebase anywhere in your blog using the `[knowledgebase]` shortcode. The shortcode takes one optional attribute `category`:

`[knowledgebase category="92"]`

*category* : Category ID for which you want to display the knowledge base. You can find the ID in the Sections listing under the Knowledgebase menu in the WordPress Admin.

You can also display the search form using `[kbsearch]`

= Using your own templates for archives and search =

Knowledgebase comes inbuilt with a set of custom templates to display archives of the articles, category archives as well as search results. You can easily override any of these templates by creating your own template in your theme's folder:

1. Articles archive: archive-wz_knowledgebase.php
2. Category archive: taxonomy-wzkb_category.php
3. Search results: search-wz_knowledgebase.php

= How do I sort the posts or sections? =

The plugin doesn't have an inbuilt feature to sort posts or sections. You will need an external plugin like [Intuitive Custom Post Order](https://wordpress.org/plugins/intuitive-custom-post-order/) which allows you to easily drag and drop posts, sections or tags to display them in a custom order.


== Screenshots ==

1. Knowledgebase Menu in the WordPress Admin
2. Knowledgebase Viewer Facing with Default styles
3. Knowledgebase Category view in the WordPress Admin
4. Settings &raquo; General
5. Settings &raquo; Styles


== Changelog ==

= 1.3.0 =

* Enhancements:
	* Articles now support comments

* Bug fixes:
	* Fixed labels where Section was still called Category

= 1.2.0 - 24 January 2016 =

* Features:
	* Settings page: Customize the slugs, disable styles, etc. Change in base slug from `kb-articles` to `knowledgebase`. If you're upgrading this plugin, and have previously used `knowledgebase` as the slug for the page you created, then either change the slug for the knowledgebase in the Settings page or delete the page you created.
	* Shortcode to display the Knowledgebase search form - Use `[kbsearch]` for this purpose

* Enhancements:
	* Merged archive stylesheet with the main stylesheet
	* Hide the first level header when displaying archives

* Bug fixes:
	* Undefined index notice on Search results pages outside of the knowledgebase

= 1.1.0 - 29 June 2015 =
* Features:
	* Network activate now works on multisite
	* Main archive displays the Knowledgebase. You can override the default template by adding a file `archive-wz_knowledgebase.php` in your theme folder
	* Category archive displays the knowledgebase for the specific category. You can override the default template by adding a file `taxonomy-wzkb_category.php` in your theme folder
	* Major rewrite of knowledgebase HTML markup and default styles. If you're using your own custom styles, then you will need to remove

* Enhancements:
	* Custom post type slug changed to `knowledgebase` from `wzkb`
	* Custom post type archives and category archives changed to `kb-articles`. Tag archives changed to `kb-tags`
	* Duplicate code cleanup

* Bug fixes:
	* If term has no children terms, then directly display the articles under it

= 1.0.0 - 17 May 2015 =
* Initial release


== Upgrade Notice ==

= 1.2.0 =
* New features. New permalink settings. Upgrade highly recommended. Please verify settings and flush permalinks on upgrade.
Check the Changelog for more details

